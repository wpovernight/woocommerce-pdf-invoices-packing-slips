<?php

namespace WPO\IPS\EDI\Abstracts;

use WPO\IPS\EDI\Interfaces\HandlerInterface;
use WPO\IPS\EDI\Document;
use WPO\IPS\EDI\Standards\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractHandler implements HandlerInterface {

	public Document $document;

	/**
	 * Constructor.
	 *
	 * @param Document $document The document instance.
	 */
	public function __construct( Document $document ) {
		$this->document = $document;
	}

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	abstract public function handle( array $data, array $options = array() ): array;

	/**
	 * Get the order customer VAT number.
	 *
	 * @param \WC_Order $order
	 * @return string|null
	 */
	protected function get_order_customer_vat_number( \WC_Order $order ): ?string {
		return apply_filters(
			'wpo_ips_edi_order_customer_vat_number',
			wpo_wcpdf_get_order_customer_vat_number( $order ),
			$order
		);
	}

	/**
	 * Get the supplier identifiers data.
	 *
	 * @param string $key The data key (e.g., 'shop_name', 'coc_number', 'shop_address_line_1', 'shop_address_postcode').
	 * @return string
	 */
	protected function get_supplier_identifiers_data( string $key ): string {
		$general_settings = WPO_WCPDF()->settings->general;
		$language         = wpo_ips_edi_get_settings( 'supplier_identifiers_language' );

		if ( empty( $language ) ) {
			$language = 'default';
		}

		return $general_settings->get_setting( $key, $language ) ?: '';
	}

	/**
	 * Returns the due date for the document.
	 *
	 * @return string
	 */
	protected function get_due_date(): string {
		$due_date = is_callable( array( $this->document->order_document, 'get_due_date' ) )
			? $this->document->order_document->get_due_date()
			: 0;

		return $this->normalize_date( $due_date, 'Y-m-d' );
	}

	/**
	 * Returns the due date days for the document.
	 *
	 * @return int
	 */
	protected function get_due_date_days(): int {
		$due_date_days = is_callable( array( $this->document->order_document, 'get_setting' ) )
			? absint( $this->document->order_document->get_setting( 'due_date_days' ) )
			: 0;

		return apply_filters( 'wpo_ips_edi_due_date_days', $due_date_days, $this->document->order_document, $this );
	}

	/**
	 * Get normalized WooCommerce payment data for the current order.
	 *
	 * @return array {
	 *     @type string $type_code      EN16931 TypeCode
	 *     @type string $method         WooCommerce method ID
	 *     @type string $title          WooCommerce method title
	 *     @type string $iban           Optional IBAN
	 *     @type string $bic            Optional BIC
	 *     @type string $transaction_id Optional transaction ID
	 *     @type string $account_name   Optional account name
	 * }
	 */
	protected function get_payment_means_data(): array {
		$order     = \wpo_ips_edi_get_parent_order( $this->document->order );
		$method_id = $order ? (string) $order->get_payment_method() : '';
		$title     = $order ? (string) $order->get_payment_method_title() : '';

		$mapping = apply_filters(
			'wpo_ips_edi_payment_means_code_mapping',
			array(
				'cheque'  => '20', // Cheque
				'bacs'    => '58', // SEPA Credit Transfer
				'paypal'  => '68', // Online payment
				'stripe'  => '54', // Credit card
				'cod'     => '46', // Interbank debit transfer
				'default' => '97', // Clearing between partners
			),
			$method_id,
			EN16931::get_payment(),
			$this
		);

		$type_code = $mapping[ $method_id ] ?? $mapping['default'];

		$data = array(
			'type_code'      => $type_code,
			'method'         => $method_id,
			'title'          => $title,
			'iban'           => '',
			'bic'            => '',
			'account_name'   => '',
			'transaction_id' => '',
		);

		$payment_methods_with_bank_details = \wpo_ips_edi_get_settings( 'supplier_bank_details' );

		if ( null === $payment_methods_with_bank_details ) {
			$payment_methods_with_bank_details = array( 'bacs' );
		}

		$show_supplier_bank_details = in_array( $method_id, (array) $payment_methods_with_bank_details, true );

		if ( $show_supplier_bank_details ) {
			$accounts = get_option( 'woocommerce_bacs_accounts', array() );

			if ( ! empty( $accounts ) && is_array( $accounts ) ) {
				$selected_account_index = \wpo_ips_edi_get_settings( 'supplier_bacs_account' );

				if ( null !== $selected_account_index && isset( $accounts[ $selected_account_index ] ) ) {
					$account = $accounts[ $selected_account_index ];
				} else {
					$account = reset( $accounts );
				}

				$account = apply_filters( 'wpo_ips_edi_payment_means_bacs_default_account', $account, $accounts, $this );

				$data = array_merge(
					$data,
					array(
						'iban'         => isset( $account['iban'] )         ? (string) $account['iban']         : '',
						'bic'          => isset( $account['bic'] )          ? (string) $account['bic']          : '',
						'account_name' => isset( $account['account_name'] ) ? (string) $account['account_name'] : '',
					)
				);
			}
		}

		switch ( $method_id ) {
			case 'paypal':
				$data['transaction_id'] = $order ? (string) $order->get_transaction_id() : '';
				break;

			case 'stripe':
				$data['transaction_id'] = $order ? (string) $order->get_meta( '_stripe_source_id', true ) : '';
				break;
		}

		return apply_filters( 'wpo_ips_edi_payment_means_data', $data, $method_id, $order, $this );
	}

	/**
	 * Normalize a raw date input into a specific format.
	 *
	 * @param mixed  $raw    The input date (DateTime, string, timestamp, etc.)
	 * @param string $format The output format (default: 'Y-m-d')
	 * @return string
	 */
	protected function normalize_date( $raw, string $format = 'Y-m-d' ): string {
		if ( empty( $raw ) ) {
			return '';
		}

		// Handle UNIX timestamp (int or numeric string)
		if ( is_numeric( $raw ) && (int) $raw > 1000000000 ) {
			try {
				$datetime = new \DateTimeImmutable( '@' . (int) $raw );
				$datetime = $datetime->setTimezone( wp_timezone() );
				return $datetime->format( $format );
			} catch ( \Exception $e ) {
				return '';
			}
		}

		// Handle DateTimeInterface objects
		if ( $raw instanceof \DateTimeInterface ) {
			return $raw->format( $format );
		}

		// Try WooCommerce string parser
		if ( function_exists( 'wc_string_to_datetime' ) ) {
			try {
				$datetime = \wc_string_to_datetime( $raw );
				return $datetime->format( $format );
			} catch ( \Exception $e ) {
				// fallback below
			}
		}

		// Fallback with strtotime
		$timestamp = strtotime( $raw );
		if ( $timestamp ) {
			$datetime = new \DateTimeImmutable( '@' . $timestamp );
			$datetime = $datetime->setTimezone( function_exists( 'wp_timezone' ) ? \wp_timezone() : new \DateTimeZone( 'UTC' ) );
			return $datetime->format( $format );
		}

		return '';
	}

	/**
	 * Format a decimal number to a string with fixed decimal places,
	 * using WooCommerce normalization and avoiding scientific notation.
	 *
	 * @param float|string $amount The amount to format.
	 * @param int          $decimal_places Number of decimal places (default: 2).
	 * @return string
	 */
	protected function format_decimal( $amount, int $decimal_places = 2 ): string {
		// Normalize using WooCommerce helper (handles locale, strings, etc.).
		$value = (float) wc_format_decimal( $amount, $decimal_places, false );

		// Treat tiny float residue as zero (10^-(decimal_places + 2)).
		$tiny_threshold = pow( 10, - ( $decimal_places + 2 ) );
		if ( abs( $value ) < $tiny_threshold ) {
			$value = 0.0;
		}

		// Round to 2dp and avoid "-0.00".
		$value = round( $value, $decimal_places );
		if ( $value === -0.0 ) {
			$value = 0.0;
		}

		// Emit plain decimal string (no exponent).
		return number_format( $value, $decimal_places, '.', '' );
	}

	/**
	 * Get normalized zero-tax meta (scheme/category/reason), with filter support.
	 *
	 * @param \WC_Order_Item|\WC_Abstract_Order|null $context Optional context.
	 * @return array
	 */
	protected function get_zero_tax_meta( $context = null ): array {
		$defaults = array(
			'scheme'   => 'VAT',
			'category' => 'Z',
			'reason'   => 'NONE',
		);

		$meta = apply_filters(
			'wpo_ips_edi_zero_tax_meta',
			$defaults,
			$context,
			$this
		);

		$scheme   = strtoupper( trim( (string) ( $meta['scheme']   ?? $defaults['scheme'] ) )   );
		$category = strtoupper( trim( (string) ( $meta['category'] ?? $defaults['category'] ) ) );
		$reason   = strtoupper( trim( (string) ( $meta['reason']   ?? $defaults['reason'] ) )   );

		if ( '' === $scheme ) {
			$scheme = $defaults['scheme'];
		}
		if ( '' === $category ) {
			$category = $defaults['category'];
		}
		if ( '' === $reason ) {
			$reason = $defaults['reason'];
		}

		return compact( 'scheme', 'category', 'reason' );
	}

	/**
	 * Get grouped order tax data by rate, category, reason, and scheme.
	 *
	 * @return array
	 */
	protected function get_grouped_order_tax_data(): array {
		$order      = $this->document->order;
		$groups     = array();
		$line_items = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );

		$order_category = wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $order );
		$order_reason   = wpo_ips_edi_get_tax_data_from_fallback( 'reason', null, $order );
		$order_scheme   = wpo_ips_edi_get_tax_data_from_fallback( 'scheme', null, $order );

		$zero_meta     = $this->get_preferred_zero_tax_meta( $order );
		$zero_category = $zero_meta['category'];
		$zero_scheme   = $zero_meta['scheme'];
		$zero_reason   = $zero_meta['reason'];

		foreach ( $line_items as $item ) {
			$parts     = $this->compute_item_price_parts( $item, false );
			$net_total = (float) $this->format_decimal( $parts['net_total'], 2 );

			// Tax meta (scheme, category, percentage).
			$tax_meta   = $this->resolve_item_tax_meta( $item );
			$percentage = (float) ( $tax_meta['percentage'] ?? 0.0 );
			$category   = strtoupper( trim( (string) ( $tax_meta['category'] ?? $order_category ) ) );
			$scheme     = strtoupper( trim( (string) ( $tax_meta['scheme'] ?? $order_scheme ) ) );

			if ( '' === $scheme ) {
				$scheme = 'VAT';
			}

			if ( '' === $category ) {
				$category = ( 0.0 === $percentage ) ? $zero_category : 'S';
			}

			$reason = strtoupper( trim( (string) ( $tax_meta['reason'] ?? $order_reason ) ) );
			if ( '' === $reason || 'NONE' === $reason ) {
				if ( 0.0 === $percentage && $category === $zero_category && $scheme === $zero_scheme ) {
					$reason = $zero_reason;
				} else {
					$reason = 'NONE';
				}
			}

			$key = implode( '|', array( $percentage, $category, $reason, $scheme ) );

			// Sum tax amounts for the item across all tax rows to get the total tax for this line.
			$item_tax_rows = $this->get_item_final_tax_rows( $item );
			$item_tax      = 0.0;

			foreach ( $item_tax_rows as $tax_amt ) {
				if ( is_numeric( $tax_amt ) ) {
					$item_tax += (float) $tax_amt;
				}
			}

			if ( ! isset( $groups[ $key ] ) ) {
				$groups[ $key ] = array(
					'total_ex'   => 0.0,
					'total_tax'  => 0.0,
					'percentage' => $percentage,
					'category'   => $category,
					'reason'     => $reason,
					'scheme'     => $scheme,
					'name'       => '',
				);
			}

			$groups[ $key ]['total_ex']  += $net_total;
			$groups[ $key ]['total_tax'] += $item_tax;
		}

		// Fallback only when there are really no grouped lines at all.
		if ( empty( $groups ) ) {
			$lines_net = $this->get_lines_net_total( $order );

			$groups[ sprintf( '0|%s|%s|%s', $zero_category, $zero_reason, $zero_scheme ) ] = array(
				'total_ex'   => $lines_net,
				'total_tax'  => 0.0,
				'percentage' => 0.0,
				'category'   => $zero_category,
				'reason'     => $zero_reason,
				'scheme'     => $zero_scheme,
				'name'       => '',
			);
		}

		$groups = $this->ensure_one_zero_tax_group( $groups );
		$groups = array_values( $groups );

		return apply_filters( 'wpo_ips_edi_order_tax_data', $groups, $this );
	}

	/**
	 * Consolidate existing preferred zero-tax groups without creating new ones.
	 *
	 * @param array $grouped_tax_data Grouped tax data.
	 * @return array Updated grouped tax data.
	 */
	protected function ensure_one_zero_tax_group( array $grouped_tax_data ): array {
		$zero_meta     = $this->get_preferred_zero_tax_meta( $this->document->order );
		$zero_category = strtoupper( $zero_meta['category'] );
		$zero_scheme   = strtoupper( $zero_meta['scheme'] );
		$zero_reason   = strtoupper( $zero_meta['reason'] );

		$first_zero_key = null;

		foreach ( $grouped_tax_data as $key => $group ) {
			$group_category = strtoupper( (string) ( $group['category'] ?? '' ) );
			$group_scheme   = strtoupper( (string) ( $group['scheme'] ?? '' ) );
			$group_reason   = strtoupper( (string) ( $group['reason'] ?? 'NONE' ) );
			$group_rate     = (float) ( $group['percentage'] ?? 0.0 );

			$is_preferred_zero_group = (
				0.0 === $group_rate &&
				$group_category === $zero_category &&
				$group_scheme === $zero_scheme &&
				$group_reason === $zero_reason
			);

			if ( ! $is_preferred_zero_group ) {
				continue;
			}

			if ( null === $first_zero_key ) {
				$first_zero_key = $key;
				continue;
			}

			$grouped_tax_data[ $first_zero_key ]['total_ex']  += (float) ( $group['total_ex'] ?? 0.0 );
			$grouped_tax_data[ $first_zero_key ]['total_tax'] += (float) ( $group['total_tax'] ?? 0.0 );

			unset( $grouped_tax_data[ $key ] );
		}

		return $grouped_tax_data;
	}

	/**
	 * Get calculated payment totals for an order.
	 *
	 * @param \WC_Abstract_Order $order
	 * @return array
	 */
	protected function get_order_payment_totals( \WC_Abstract_Order $order ): array {
		$grouped_tax_data = $this->get_grouped_order_tax_data();

		$total_exc_raw = 0.0;
		$total_tax_raw = 0.0;

		foreach ( $grouped_tax_data as $g ) {
			$ex_base = (float) ( $g['total_ex']   ?? 0 );
			$rate    = (float) ( $g['percentage'] ?? 0 );

			// Sum taxable base
			$total_exc_raw += $ex_base;

			// Tax per category = base × rate / 100, rounded as tax.
			$tax_calc       = wc_round_tax_total( $ex_base * $rate / 100 );
			$total_tax_raw += $tax_calc;
		}

		// Invoice total amount without VAT.
		$total_exc_tax = (float) $this->format_decimal( $total_exc_raw, 2 );

		// Invoice total VAT amount (sum of category tax).
		$total_tax = (float) $this->format_decimal( $total_tax_raw, 2 );

		// Invoice total amount with VAT = total_exc_tax + total_tax.
		$total_inc_tax = (float) $this->format_decimal( $total_exc_tax + $total_tax, 2 );

		// For diagnostics also compute invoice line net sum.
		$lines_net         = (float) $this->get_lines_net_total( $order );
		$lines_net_rounded = (float) $this->format_decimal( $lines_net, 2 );

		// Log if base of groups and base from lines differ materially.
		$base_diff = $total_exc_tax - $lines_net_rounded;
		if ( abs( $base_diff ) >= 0.01 ) {
			wpo_ips_edi_log(
				sprintf(
					'Base mismatch for order #%d: grouped_ex=%s, lines_net=%s, diff=%s',
					$order->get_id(),
					$this->format_decimal( $total_exc_tax ),
					$this->format_decimal( $lines_net_rounded ),
					$this->format_decimal( $base_diff )
				),
				'warning'
			);
		}

		// Reconcile to WooCommerce order total.
		$order_total   = (float) $order->get_total();
		$rounding_diff = wc_round_tax_total( $order_total - $total_inc_tax );

		// Config / inputs.
		$has_due_days   = ! empty( $this->get_due_date_days() );
		$prepaid_amount = (float) apply_filters( 'wpo_ips_edi_prepaid_amount', 0.0, $order, $this );

		// Threshold for treating rounding diff as significant.
		$rounding_is_significant = ( abs( $rounding_diff ) >= 0.01 );

		if ( $rounding_is_significant ) {
			wpo_ips_edi_log(
				'Rounding difference detected for order #' . $order->get_id() . ': ' .
				'order_total=' . $order_total . ', total_inc_tax=' . $total_inc_tax .
				', total_exc_tax=' . $total_exc_tax . ', total_tax=' . $total_tax .
				', rounding_diff=' . $rounding_diff,
				'warning'
			);
		}

		// Gross invoice amount including rounding (should equal Woo order total).
		$gross_total = (float) $this->format_decimal( $total_inc_tax + $rounding_diff, 2 );

		// Default rule:
		// - If there's NO due date AND no explicit prepaid set, treat as fully prepaid (paid on issue).
		// - Otherwise, use the provided prepaid (or 0) and compute payable normally.
		if ( $prepaid_amount <= 0.0 && ! $has_due_days ) {
			// Fully prepaid by default.
			$prepaid_amount = $gross_total;
			$payable_amount = 0.0;
		} else {
			// Not fully prepaid; customer owes the remainder.
			$payable_amount = $gross_total - $prepaid_amount;
		}

		$totals = compact(
			'total_exc_tax',
			'total_inc_tax',
			'total_tax',
			'prepaid_amount',
			'rounding_diff',
			'payable_amount',
			'lines_net'
		);

		return apply_filters( 'wpo_ips_edi_order_payment_totals', $totals, $order, $this );
	}

	/**
	 * Get sum of line net amounts (excl. VAT).
	 *
	 * @param \WC_Abstract_Order $order
	 * @return float
	 */
	protected function get_lines_net_total( \WC_Abstract_Order $order ): float {
		$lines_net      = 0.0;
		$include_coupon = apply_filters( 'wpo_ips_edi_ubl_discount_as_line', false, $this );
		$line_items     = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );

		foreach ( $line_items as $item ) {
			$parts           = $this->compute_item_price_parts( $item, (bool) $include_coupon );
			$line_net_total  = (float) $this->format_decimal( $parts['net_total'], 2 );
			$lines_net      += $line_net_total;
		}

		// If discounts are rendered as separate lines, include them as negative net amounts.
		if ( $include_coupon ) {
			$coupons = $order->get_items( 'coupon' );

			foreach ( $coupons as $coupon_item ) {
				if ( ! is_object( $coupon_item ) || ! method_exists( $coupon_item, 'get_discount' ) ) {
					continue;
				}

				$discount_excl_tax = (float) $coupon_item->get_discount();
				$net_total         = -1.0 * (float) $this->format_decimal( $discount_excl_tax, 2 );

				if ( 0.0 === $net_total ) {
					continue;
				}

				$lines_net += $net_total;
			}
		}

		return (float) $this->format_decimal( $lines_net, 2 );
	}

	/**
	 * Get the tax rows used for item tax classification.
	 *
	 * Product items use the subtotal bucket so the original tax class can still be
	 * detected on fully discounted lines. Other item types use the total bucket.
	 *
	 * @param \WC_Order_Item $item Order item instance.
	 * @return array
	 */
	protected function get_item_tax_rows( \WC_Order_Item $item ): array {
		$type       = $item->get_type();
		$taxes      = $item->get_taxes();
		$tax_bucket = ( 'line_item' === $type ) ? 'subtotal' : 'total';

		return ( isset( $taxes[ $tax_bucket ] ) && is_array( $taxes[ $tax_bucket ] ) )
			? $taxes[ $tax_bucket ]
			: array();
	}

	/**
	 * Get the final tax rows for an order item.
	 *
	 * These rows reflect the final tax amounts stored on the line after discounts.
	 *
	 * @param \WC_Order_Item $item Order item instance.
	 * @return array
	 */
	protected function get_item_final_tax_rows( \WC_Order_Item $item ): array {
		$taxes = $item->get_taxes();

		return ( isset( $taxes['total'] ) && is_array( $taxes['total'] ) )
			? $taxes['total']
			: array();
	}

	/**
	 * Get the preferred zero-tax metadata for the current order context.
	 *
	 * @param \WC_Order_Item|\WC_Abstract_Order|null $context Optional context.
	 * @return array
	 */
	protected function get_preferred_zero_tax_meta( $context = null ): array {
		$zero_meta = $this->get_zero_tax_meta( $context );
		$order     = $this->document->order;

		if ( ! wpo_wcpdf_order_is_vat_exempt( $order ) ) {
			return $zero_meta;
		}

		$scheme   = strtoupper( trim( (string) wpo_ips_edi_get_tax_data_from_fallback( 'scheme', null, $order ) ) );
		$category = strtoupper( trim( (string) wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $order ) ) );
		$reason   = strtoupper( trim( (string) wpo_ips_edi_get_tax_data_from_fallback( 'reason', null, $order ) ) );

		if ( '' === $scheme || 'DEFAULT' === $scheme ) {
			$scheme = $zero_meta['scheme'];
		}

		if ( '' === $category || 'DEFAULT' === $category ) {
			$category = $zero_meta['category'];
		}

		if ( '' === $reason || 'DEFAULT' === $reason ) {
			$reason = (
				$category === $zero_meta['category'] &&
				$scheme === $zero_meta['scheme']
			) ? $zero_meta['reason'] : 'NONE';
		}

		return compact( 'scheme', 'category', 'reason' );
	}

	/**
	 * Determine whether the given tax metadata represents the generic zero-tax classification.
	 *
	 * @param array $tax_meta Tax metadata to inspect.
	 * @param \WC_Order_Item|\WC_Abstract_Order|null $context Optional context.
	 * @return bool
	 */
	protected function is_generic_zero_tax_meta( array $tax_meta, $context = null ): bool {
		$zero_meta  = $this->get_zero_tax_meta( $context );
		$scheme     = strtoupper( trim( (string) ( $tax_meta['scheme'] ?? '' ) ) );
		$category   = strtoupper( trim( (string) ( $tax_meta['category'] ?? '' ) ) );
		$percentage = (float) ( $tax_meta['percentage'] ?? 0 );

		return (
			0.0 === $percentage &&
			$scheme === $zero_meta['scheme'] &&
			$category === $zero_meta['category']
		);
	}

	/**
	 * Determine whether the resolved tax metadata is invalid for standard-rated VAT.
	 *
	 * @param array $tax_meta Tax metadata.
	 * @return bool
	 */
	protected function is_invalid_standard_tax_meta( array $tax_meta ): bool {
		$category   = strtoupper( trim( (string) ( $tax_meta['category'] ?? '' ) ) );
		$percentage = (float) ( $tax_meta['percentage'] ?? 0.0 );

		return 'S' === $category && $percentage <= 0.0;
	}

	/**
	 * Build the list of tax classification candidates for an order item.
	 *
	 * @param \WC_Order_Item $item Order item instance.
	 * @return array[]
	 */
	protected function get_item_tax_candidates( \WC_Order_Item $item ): array {
		$candidates = array();

		foreach ( $this->get_item_tax_rows( $item ) as $tax_id => $tax_amt ) {
			if ( ! is_numeric( $tax_amt ) ) {
				continue;
			}

			$row = $this->document->order_tax_data[ $tax_id ] ?? array();

			$candidates[] = array(
				'tax_id'     => $tax_id,
				'scheme'     => strtoupper( $row['scheme']   ?? 'VAT' ),
				'category'   => strtoupper( $row['category'] ?? 'Z' ),
				'percentage' => (float) ( $row['percentage'] ?? 0 ),
				'reason'     => strtoupper( $row['reason']   ?? 'NONE' ),
				'tax_amount' => (float) $tax_amt,
			);
		}

		return $candidates;
	}

	/**
	 * Select the final tax classification candidate for an order item.
	 *
	 * @param \WC_Order_Item $item Order item instance.
	 * @param array[]        $candidates Candidate tax classifications for the item.
	 * @return array
	 */
	protected function select_item_tax_classification( \WC_Order_Item $item, array $candidates ): array {
		$first_candidate        = null;
		$first_non_generic_zero = null;

		foreach ( $candidates as $candidate ) {
			if ( null === $first_candidate ) {
				$first_candidate = $candidate;
			}

			if ( ! $this->is_generic_zero_tax_meta( $candidate, $item ) && null === $first_non_generic_zero ) {
				$first_non_generic_zero = $candidate;
			}
		}

		if ( null !== $first_non_generic_zero ) {
			return $first_non_generic_zero;
		}

		if ( null !== $first_candidate ) {
			return $first_candidate;
		}

		$zero_meta = $this->get_preferred_zero_tax_meta( $item );

		return array(
			'scheme'     => $zero_meta['scheme'],
			'category'   => $zero_meta['category'],
			'percentage' => 0.0,
			'reason'     => $zero_meta['reason'],
		);
	}

	/**
	 * Resolve tax metadata for an order item.
	 *
	 * @param \WC_Order_Item $item Order item instance.
	 * @return array
	 */
	protected function resolve_item_tax_meta( \WC_Order_Item $item ): array {
		$candidates = $this->get_item_tax_candidates( $item );

		// If there are no usable tax candidates on the item, fall back to the
		// configured item tax class before using generic zero-tax handling.
		if ( empty( $candidates ) ) {
			return $this->get_item_tax_class_fallback_meta( $item );
		}

		$resolved = $this->select_item_tax_classification( $item, $candidates );

		if ( $this->is_generic_zero_tax_meta( $resolved, $item ) ) {
			$zero_meta = $this->get_preferred_zero_tax_meta( $item );

			return array(
				'scheme'     => $zero_meta['scheme'],
				'category'   => $zero_meta['category'],
				'percentage' => 0.0,
				'reason'     => $zero_meta['reason'],
			);
		}

		return array(
			'scheme'     => strtoupper( trim( (string) ( $resolved['scheme'] ?? 'VAT' ) ) ),
			'category'   => strtoupper( trim( (string) ( $resolved['category'] ?? 'Z' ) ) ),
			'percentage' => (float) ( $resolved['percentage'] ?? 0.0 ),
			'reason'     => strtoupper( trim( (string) ( $resolved['reason'] ?? 'NONE' ) ) ),
		);
	}

	/**
	 * Compute line totals, unit prices and unit discount for an item.
	 *
	 * @param \WC_Order_Item $item
	 * @param bool $lock_net_to_subtotal
	 * @return array
	 */
	protected function compute_item_price_parts( $item, bool $lock_net_to_subtotal = false ): array {
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			$gross_total = (float) $item->get_subtotal();                                                  // ex-VAT, before discounts
			$net_total   = (float) ( $lock_net_to_subtotal ? $item->get_subtotal() : $item->get_total() ); // ex-VAT, after discounts
			$qty         = max( 1, (int) $item->get_quantity() );
		} else {
			$gross_total = (float) $item->get_total();
			$net_total   = (float) $item->get_total();
			$qty         = 1;
		}

		$gross_unit = $qty > 0 ? $gross_total / $qty : 0.0;
		$net_unit   = $qty > 0 ? $net_total   / $qty : (float) $item->get_total();

		$unit_discount = max( 0.0, $gross_unit - $net_unit );

		return compact( 'gross_total', 'net_total', 'qty', 'gross_unit', 'net_unit', 'unit_discount' );
	}

	/**
	 * Get order item meta.
	 *
	 * @param \WC_Order_Item $item Order item object.
	 * @param array          $args
	 * @return array
	 */
	protected function get_item_meta( \WC_Order_Item $item, array $args = array() ): array {
		$args = wp_parse_args(
			$args,
			array(
				'include_hidden'    => false,
				'include_empty'     => false,
				'only_keys'         => array(),
				'exclude_keys'      => array(),
				'use_display_label' => true,
				'max_length'        => 0,
			)
		);

		// Skip meta for certain items (override via filter if needed).
		$skip_types = apply_filters(
			'wpo_ips_edi_skip_item_meta_for_types',
			array( 'WC_Order_Item_Shipping', 'WC_Order_Item_Fee', 'WC_Order_Item_Tax', 'WC_Order_Item_Coupon' ),
			$item,
			$args,
			$this
		);
		foreach ( (array) $skip_types as $class ) {
			if ( is_a( $item, $class ) ) {
				$rows = apply_filters( 'wpo_ips_edi_get_item_meta', array(), $item, $args, $this );
				return is_array( $rows ) ? $rows : array();
			}
		}

		$meta_items = method_exists( $item, 'get_all_formatted_meta_data' )
			? $item->get_all_formatted_meta_data()
			: $item->get_formatted_meta_data();

		// Ensure we can iterate even if something exotic was returned.
		if ( ! is_iterable( $meta_items ) ) {
			$meta_items = array();
		}

		$rows = array();

		foreach ( $meta_items as $meta_id => $m ) {
			$raw_key   = isset( $m->key )   ? (string) $m->key : '';
			$raw_value = isset( $m->value ) ? $m->value        : '';

			// Hidden meta starts with underscore.
			if ( ! $args['include_hidden'] && '' !== $raw_key && '_' === substr( $raw_key, 0, 1 ) ) {
				continue;
			}

			// Whitelist / blacklist
			if ( $args['only_keys'] && ! in_array( $raw_key, (array) $args['only_keys'], true ) ) {
				continue;
			}
			if ( $args['exclude_keys'] && in_array( $raw_key, (array) $args['exclude_keys'], true ) ) {
				continue;
			}

			$label = $args['use_display_label'] && isset( $m->display_key ) && '' !== $m->display_key
				? (string) $m->display_key
				: $raw_key;

			// Prefer WooCommerce's display_value (already humanized), but force plain text.
			$value = isset( $m->display_value ) ? $m->display_value : $raw_value;

			// Flatten arrays/objects deterministically.
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
			}

			// Strip tags and normalize whitespace for XML.
			$value      = wp_strip_all_tags( (string) $value, true );
			$normalized = preg_replace( '/\s+/u', ' ', $value ); // may return null on invalid UTF-8
			$value      = is_string( $normalized ) ? $normalized : (string) $value;
			$value      = trim( $value );

			// Optional truncation.
			if ( $args['max_length'] ) {
				$len   = (int) $args['max_length'];
				$value = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $len ) : substr( $value, 0, $len );
			}

			if ( ! $args['include_empty'] && '' === $value ) {
				continue;
			}

			$rows[] = array(
				'name'  => $label,
				'value' => $value,
			);
		}

		$rows = apply_filters( 'wpo_ips_edi_get_item_meta', $rows, $item, $args, $this );

		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		return $rows;
	}

	/**
	 * Resolve fallback tax metadata from the order item's tax class.
	 *
	 * This is used when the item has no usable tax rows to classify from, such as
	 * products with a final price of zero or fully discounted items where WooCommerce
	 * no longer provides meaningful tax row data for export classification.
	 *
	 * @param \WC_Order_Item $item Order item instance.
	 * @return array
	 */
	protected function get_item_tax_class_fallback_meta( \WC_Order_Item $item ): array {
		$order = $this->document->order;

		if ( wpo_wcpdf_order_is_vat_exempt( $order ) ) {
			$zero_meta = $this->get_preferred_zero_tax_meta( $item );

			return array(
				'scheme'     => $zero_meta['scheme'],
				'category'   => $zero_meta['category'],
				'percentage' => 0.0,
				'reason'     => $zero_meta['reason'],
			);
		}

		// Only product items should fall back to their stored tax class.
		// Shipping/fee items without usable tax rows should not be forced into S.
		if ( 'line_item' !== $item->get_type() ) {
			$zero_meta = $this->get_zero_tax_meta( $item );

			return array(
				'scheme'     => $zero_meta['scheme'],
				'category'   => $zero_meta['category'],
				'percentage' => 0.0,
				'reason'     => $zero_meta['reason'],
			);
		}

		$tax_class = is_callable( array( $item, 'get_tax_class' ) )
			? (string) $item->get_tax_class()
			: '';

		if ( '' === $tax_class ) {
			$tax_class = 'standard';
		}

		$settings          = (array) wpo_ips_edi_get_tax_settings();
		$class_settings    = isset( $settings['class'][ $tax_class ] ) && is_array( $settings['class'][ $tax_class ] )
			? $settings['class'][ $tax_class ]
			: array();
		$standard_settings = isset( $settings['class']['standard'] ) && is_array( $settings['class']['standard'] )
			? $settings['class']['standard']
			: array();

		$scheme = strtoupper( trim( (string) ( $class_settings['scheme'] ?? '' ) ) );
		if ( '' === $scheme || 'DEFAULT' === $scheme ) {
			$scheme = strtoupper( trim( (string) ( $standard_settings['scheme'] ?? 'VAT' ) ) );
		}

		$category = strtoupper( trim( (string) ( $class_settings['category'] ?? '' ) ) );
		if ( '' === $category || 'DEFAULT' === $category ) {
			$category = strtoupper( trim( (string) ( $standard_settings['category'] ?? 'S' ) ) );
		}

		$reason = strtoupper( trim( (string) ( $class_settings['reason'] ?? '' ) ) );
		if ( '' === $reason || 'DEFAULT' === $reason ) {
			$reason = strtoupper( trim( (string) ( $standard_settings['reason'] ?? 'NONE' ) ) );
		}

		if ( '' === $category ) {
			$zero_meta = $this->get_zero_tax_meta( $item );

			return array(
				'scheme'     => $zero_meta['scheme'],
				'category'   => $zero_meta['category'],
				'percentage' => 0.0,
				'reason'     => $zero_meta['reason'],
			);
		}

		$percentage = 0.0;

		if ( class_exists( '\WC_Tax' ) && is_callable( array( '\WC_Tax', 'find_rates' ) ) ) {
			$country  = $order->get_shipping_country();
			$state    = $order->get_shipping_state();
			$postcode = $order->get_shipping_postcode();
			$city     = $order->get_shipping_city();

			if ( '' === $country ) {
				$country  = $order->get_billing_country();
				$state    = $order->get_billing_state();
				$postcode = $order->get_billing_postcode();
				$city     = $order->get_billing_city();
			}

			$rates = \WC_Tax::find_rates(
				array(
					'country'   => $country,
					'state'     => $state,
					'postcode'  => $postcode,
					'city'      => $city,
					'tax_class' => 'standard' === $tax_class ? '' : $tax_class,
				)
			);

			if ( ! empty( $rates ) ) {
				$first_rate = reset( $rates );

				if ( is_array( $first_rate ) && isset( $first_rate['rate'] ) && is_numeric( $first_rate['rate'] ) ) {
					$percentage = (float) $first_rate['rate'];
				}
			}
		}

		$fallback = array(
			'scheme'     => '' !== $scheme ? $scheme : 'VAT',
			'category'   => $category,
			'percentage' => $percentage,
			'reason'     => '' !== $reason ? $reason : 'NONE',
		);

		// Never return S with a zero or missing percentage.
		if ( $this->is_invalid_standard_tax_meta( $fallback ) ) {
			$zero_meta = $this->get_zero_tax_meta( $item );

			return array(
				'scheme'     => $zero_meta['scheme'],
				'category'   => $zero_meta['category'],
				'percentage' => 0.0,
				'reason'     => $zero_meta['reason'],
			);
		}

		return $fallback;
	}

}
