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
		return is_callable( array( $this->document->order_document, 'get_setting' ) )
			? absint( $this->document->order_document->get_setting( 'due_date_days' ) )
			: 0;
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
		$method_id = $order ? $order->get_payment_method() : '';
		$title     = $order ? $order->get_payment_method_title() : '';


		$mapping = apply_filters( 'wpo_ips_edi_payment_means_code_mapping', array(
			'bacs'    => '58', // SEPA Credit Transfer
			'paypal'  => '68', // Online payment
			'stripe'  => '54', // Credit card
			'cod'     => '46', // Interbank debit transfer
			'default' => '97', // Clearing between partners
		), $method_id, EN16931::get_payment(), $this );

		$type_code = $mapping[ $method_id ] ?? $mapping['default'];

		$data = array(
			'type_code' => $type_code,
			'method'    => $method_id,
			'title'     => $title,
		);

		switch ( $method_id ) {
			case 'bacs':
				$accounts = get_option( 'woocommerce_bacs_accounts', array() );

				if ( empty( $accounts ) ) {
					break;
				}

				$account  = apply_filters( 'wpo_ips_edi_payment_means_bacs_default_account', reset( $accounts ), $accounts, $this );
				$data     = array_merge( $data, $account );
				break;

			case 'paypal':
				$data['transaction_id'] = $order->get_transaction_id();
				break;

			case 'stripe':
				$data['transaction_id'] = $order->get_meta( '_stripe_source_id', true );
				break;
		}

		return $data;
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
				$datetime = $datetime->setTimezone( function_exists( 'wc_timezone' ) ? \wc_timezone() : new \DateTimeZone( 'UTC' ) );
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
	 * Get grouped order tax data by rate, category, reason, and scheme.
	 *
	 * @return array
	 */
	protected function get_grouped_order_tax_data(): array {
		$grouped_tax_data = array();
		$order_tax_data   = $this->document->order_tax_data;

		// Fallback if no tax data is available
		if ( empty( $order_tax_data ) ) {
			$order_tax_data = array(
				0 => array(
					'total_ex'  => $this->document->order->get_total(),
					'total_tax' => 0,
					'items'     => array(),
					'name'      => '',
				),
			);
		}

		$order_category = wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $this->document->order );
		$order_reason   = wpo_ips_edi_get_tax_data_from_fallback( 'reason',   null, $this->document->order );
		$order_scheme   = wpo_ips_edi_get_tax_data_from_fallback( 'scheme',   null, $this->document->order );

		foreach ( $order_tax_data as $item ) {
			$percentage = (float) ( $item['percentage'] ?? 0 );
			$category   = strtoupper( trim( (string) ( $item['category'] ?? $order_category ) ) );
			$reason     = strtoupper( trim( (string) ( $item['reason']   ?? $order_reason   ) ) );
			$scheme     = strtoupper( trim( (string) ( $item['scheme']   ?? $order_scheme   ) ) );

			if ( '' === $reason || 'NONE' === $reason ) {
				$reason = 'NONE';
			}

			if ( '' === $scheme ) {
				$scheme = 'VAT';
			}

			if ( '' === $category ) {
				$category = ( 0.0 === $percentage ) ? 'Z' : 'S';
			}

			$key = implode( '|', array( $percentage, $category, $reason, $scheme ) );

			$line_total_ex  = (float) ( $item['total_ex']  ?? 0 );
			$line_total_tax = (float) ( $item['total_tax'] ?? 0 );

			if ( ! isset( $grouped_tax_data[ $key ] ) ) {
				$grouped_tax_data[ $key ] = $item;

				// Ensure required keys exist with proper types
				$grouped_tax_data[ $key ]['percentage'] = $percentage;
				$grouped_tax_data[ $key ]['category']   = $category;
				$grouped_tax_data[ $key ]['reason']     = $reason;
				$grouped_tax_data[ $key ]['scheme']     = $scheme;
				$grouped_tax_data[ $key ]['total_ex']   = $line_total_ex;
				$grouped_tax_data[ $key ]['total_tax']  = $line_total_tax;
			} else {
				$grouped_tax_data[ $key ]['total_ex']  = ( $grouped_tax_data[ $key ]['total_ex']  ?? 0.0 ) + $line_total_ex;
				$grouped_tax_data[ $key ]['total_tax'] = ( $grouped_tax_data[ $key ]['total_tax'] ?? 0.0 ) + $line_total_tax;
			}
		}

		// Ensure Z group is consolidated and correct before returning
		$grouped_tax_data = $this->ensure_one_tax_z_group( $grouped_tax_data );

		// Reindex so callers always get a numeric array
		$grouped_tax_data = array_values( $grouped_tax_data );

		return apply_filters( 'wpo_ips_edi_order_tax_data', $grouped_tax_data, $this );
	}

	/**
	 * Consolidate and ensure exactly one Z group in the grouped tax data.
	 *
	 * @param array $grouped_tax_data Grouped tax data.
	 * @return array Updated grouped tax data.
	 */
	protected function ensure_one_tax_z_group( array $grouped_tax_data ): array {
		$z_first_key = null;

		// Consolidate any existing Z groups from $order_tax_data
		foreach ( $grouped_tax_data as $key => $g ) {
			if ( 'Z' === strtoupper( $g['category'] ?? '' ) ) {
				if ( is_null( $z_first_key ) ) {
					$z_first_key = $key;
				} else {
					unset( $grouped_tax_data[ $key ] );
				}
			}
		}

		// Compute the Z taxable basis strictly from lines
		$z_basis_from_lines = 0.0;
		$has_z_line         = false;

		foreach ( $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $item ) {
			$line_total = (float) $item->get_total();
			$taxes      = $item->get_taxes();
			$rows       = ( is_array( $taxes['total'] ?? null ) ) ? $taxes['total'] : array();

			// Does this line have any non-zero tax amount?
			$has_nonzero_row = false;
			foreach ( $rows as $amt ) {
				if ( is_numeric( $amt ) && (float) $amt !== 0.0 ) {
					$has_nonzero_row = true;
					break;
				}
			}

			$line_is_z = false;

			if ( $has_nonzero_row ) {
				// classify by the non-zero row's category/rate
				foreach ( $rows as $tax_id => $amt ) {
					if ( ! is_numeric( $amt ) || (float) $amt === 0.0 ) {
						continue;
					}

					$info = $this->document->order_tax_data[ $tax_id ] ?? array();
					$cat  = strtoupper( $info['category'] ?? '' );
					$rate = (float) ( $info['percentage'] ?? 0 );

					if ( 'Z' === $cat || 0.0 === $rate ) {
						$line_is_z = true;
						break;
					}
				}
			} else {
				// No non-zero tax rows at all → treat as zero-rated (Z)
				$line_is_z = true;
			}

			if ( $line_is_z ) {
				$has_z_line          = true;
				$z_basis_from_lines += $line_total; // contributes to Z taxable amount
			}
		}

		// Ensure exactly one Z group if there is any Z line (even with basis 0)
		if ( $has_z_line || $z_first_key ) {
			$z_key = $z_first_key ?: '0|Z|NONE|VAT';

			$grouped_tax_data[ $z_key ] = array(
				'total_ex'   => $this->format_decimal( wc_round_tax_total( $z_basis_from_lines ) ),
				'total_tax'  => '0.00',
				'percentage' => '0.0',
				'category'   => 'Z',
				'reason'     => 'NONE',
				'scheme'     => 'VAT',
				'name'       => $grouped_tax_data[ $z_first_key ]['name'] ?? '',
			);
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
		$total         = $order->get_total();
		$total_tax_raw = $order->get_total_tax();
		$total_exc_tax = $total - $total_tax_raw;
		$total_inc_tax = $total;
		$currency      = $order->get_currency();

		// Tax rounding.
		$total_tax     = wc_round_tax_total( $total_tax_raw );
		$rounding_diff = wc_round_tax_total( $total_inc_tax - ( $total_exc_tax + $total_tax ) );

		// Config / inputs.
		$has_due_days   = ! empty( $this->get_due_date_days() );
		$prepaid_amount = (float) apply_filters( 'wpo_ips_edi_prepaid_amount', 0.0, $order, $this );

		// Threshold for treating rounding diff as significant.
		$rounding_is_significant = ( abs( $rounding_diff ) >= 0.01 );

		// Default rule:
		// - If there's NO due date AND no explicit prepaid set, treat as fully prepaid (paid on issue).
		// - Otherwise, use the provided prepaid (or 0) and compute payable normally.
		if ( $prepaid_amount <= 0.0 && ! $has_due_days ) {
			// Fully prepaid by default.
			$prepaid_amount = $total_inc_tax;

			// Absorb rounding diff into prepaid so payable stays 0.00.
			if ( $rounding_is_significant ) {
				$prepaid_amount += $rounding_diff;
			}

			$payable_amount = 0.0;
		} else {
			// Not fully prepaid; customer owes the remainder.
			$payable_amount = $total_inc_tax - $prepaid_amount;

			// Apply rounding diff to payable to reconcile to grand total.
			if ( $rounding_is_significant ) {
				$payable_amount += $rounding_diff;
			}
		}

		$totals = compact(
			'total_exc_tax',
			'total_inc_tax',
			'total_tax',
			'prepaid_amount',
			'rounding_diff',
			'payable_amount'
		);

		return apply_filters( 'wpo_ips_edi_order_payment_totals', $totals, $order, $this );
	}

	/**
	 * Get the tax rows bucket for an order item ('subtotal' for products, 'total' otherwise).
	 *
	 * @param \WC_Order_Item $item
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
	 * Resolve tax meta (scheme/category/percentage) for an item by inspecting its first non-zero tax row.
	 *
	 * @param \WC_Order_Item $item
	 * @return array
	 */
	protected function resolve_item_tax_meta( \WC_Order_Item $item ): array {
		$order_tax_data = $this->document->order_tax_data;

		$scheme   = 'VAT';
		$category = null;
		$percent  = 0.0;
		$rows     = $this->get_item_tax_rows( $item );

		foreach ( $rows as $tax_id => $tax_amt ) {
			if ( ! is_numeric( $tax_amt ) || (float) $tax_amt == 0.0 ) {
				continue;
			}

			$row      = $order_tax_data[ $tax_id ]   ?? array();
			$scheme   = strtoupper( $row['scheme']   ?? 'VAT' );
			$category = strtoupper( $row['category'] ?? 'Z'   );
			$percent  = (float) ( $row['percentage'] ?? 0     );
			break;
		}

		// Fallback: no non-zero rows -> Zero-rated (Z / 0%)
		if ( null === $category ) {
			$scheme   = 'VAT';
			$category = 'Z';
			$percent  = 0.0;
		}

		return array(
			'scheme'     => $scheme,
			'category'   => $category,
			'percentage' => $percent,
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
	
}
