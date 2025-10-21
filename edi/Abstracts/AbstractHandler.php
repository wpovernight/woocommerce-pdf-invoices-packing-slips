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
	 * @return string|null
	 */
	protected function get_order_customer_vat_number(): ?string {
		return apply_filters(
			'wpo_ips_edi_order_customer_vat_number',
			wpo_wcpdf_get_order_customer_vat_number( $this->document->order ),
			$this->document->order
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
	 * Returns the due date days for the document.
	 *
	 * @return int
	 */
	public function get_due_date_days(): int {
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
		$order     = $this->document->order;
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
			'type_code'      => $type_code,
			'method'         => $method_id,
			'title'          => $title,
			'iban'           => '',
			'bic'            => '',
			'transaction_id' => '',
			'account_name'   => '',
		);

		switch ( $method_id ) {
			case 'bacs':
				$accounts = get_option( 'woocommerce_bacs_accounts', array() );
				$account  = is_array( $accounts ) ? reset( $accounts ) : array();

				$data['iban']         = $account['iban'] ?? '';
				$data['bic']          = $account['bic'] ?? '';
				$data['account_name'] = $account['account_name'] ?? '';
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
	 * Get the net unit price (ex-VAT, after discounts) of an item.
	 *
	 * @param \WC_Order_Item $item
	 * @return float
	 */
	protected function get_item_unit_price( \WC_Order_Item $item ) {
		$qty = ( is_a( $item, 'WC_Order_Item_Product' ) ) ? max( 1, (int) $item->get_quantity() ) : 1;

		// WooCommerce semantics:
		// - get_subtotal(): before discounts (ex tax)
		// - get_total():    after discounts (ex tax)
		$net = (float) $item->get_total();

		// For product lines we want unit net price; for fees/shipping qty is 1
		return $net / $qty;
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
	protected function format_decimal( $amount, int $decimal_places = 2 ): string
	{
		// Normalize using WooCommerce helper (handles locale, strings, etc.).
		$value = (float) wc_format_decimal( $amount, $decimal_places, false );

		// Treat tiny float residue as zero (10^-(decimal_places + 2)).
		$tiny_threshold = pow( 10, - ( $decimal_places + 2 ) );
		if ( abs( $value ) < $tiny_threshold ) {
			$value = 0.0;
		}

		// Round to 2dp and avoid "-0.00".
		$value = round( $value, $decimal_places );
		if ( $value == 0.0 ) {
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
		
		foreach ( $order_tax_data as $item ) {
			$percentage = (float) ( $item['percentage'] ?? 0 );
			$category   = strtoupper( trim( $item['category'] ?? wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $this->document->order ) ) );
			$reason     = strtoupper( trim( $item['reason']   ?? wpo_ips_edi_get_tax_data_from_fallback( 'reason',   null, $this->document->order ) ) );
			$scheme     = strtoupper( trim( $item['scheme']   ?? wpo_ips_edi_get_tax_data_from_fallback( 'scheme',   null, $this->document->order ) ) );
			
			if ( '' === $reason || 'NONE' === $reason ) {
				$reason = 'NONE';
			}
			
			$key = implode( '|', array( $percentage, $category, $reason, $scheme ) );

			if ( ! isset( $grouped_tax_data[ $key ] ) ) {
				$grouped_tax_data[ $key ]               = $item;
				$grouped_tax_data[ $key ]['percentage'] = $percentage;
				$grouped_tax_data[ $key ]['category']   = $category;
				$grouped_tax_data[ $key ]['reason']     = $reason;
				$grouped_tax_data[ $key ]['scheme']     = $scheme;
			} else {
				$grouped_tax_data[ $key ]['total_ex']  += ( $item['total_ex']  ?? 0 );
				$grouped_tax_data[ $key ]['total_tax'] += ( $item['total_tax'] ?? 0 );
			}
		}
		
		// Ensure Z group is consolidated and correct before returning
		$grouped_tax_data = $this->ensure_one_tax_z_group( $grouped_tax_data );
		
		return $grouped_tax_data;
	}
	
	/**
	 * Consolidate and ensure exactly one Z group in the grouped tax data.
	 *
	 * - Consolidates existing Z buckets (keeps first, sums totals)
	 * - Computes missing Z taxable basis from lines (treats lines with no non-zero tax rows as Z)
	 * - Ensures exactly one Z bucket is present with correct totals
	 *
	 * @param array $grouped_tax_data Grouped tax data.
	 * @return array Updated grouped tax data.
	 */
	protected function ensure_one_tax_z_group( array $grouped_tax_data ): array {
		// Consolidate any existing Z groups from $order_tax_data
		$z_total_ex   = 0.0;
		$z_first_key  = null;
		$z_other_keys = array();

		foreach ( $grouped_tax_data as $key => $g ) {
			if ( 'Z' === strtoupper( $g['category'] ?? '' ) ) {
				if ( is_null( $z_first_key ) ) {
					$z_first_key = $key;
				} else {
					$z_other_keys[] = $key;
				}

				$z_total_ex += (float) ( $g['total_ex'] ?? 0 );
			}
		}

		// Remove duplicate Z groups (keep the first; we'll rewrite it later)
		foreach ( $z_other_keys as $dup_key ) {
			unset( $grouped_tax_data[ $dup_key ] );
		}

		// Compute the Z basis from lines
		$z_missing_ex = 0.0;
		$has_z_line   = false;

		foreach ( $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $it ) {
			$line_total = (float) $it->get_total();
			$taxes      = $it->get_taxes();
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
				$has_z_line    = true;
				$z_missing_ex += $line_total; // contributes to Z taxable amount
			}
		}

		$z_total_ex += $z_missing_ex;

		// Ensure exactly one Z group if there is any Z line (even with basis 0)
		if ( $has_z_line || $z_first_key ) {
			$z_key = $z_first_key ?: '0|Z|NONE|VAT';

			$grouped_tax_data[ $z_key ] = array(
				'total_ex'   => $this->format_decimal( wc_round_tax_total( $z_total_ex ) ),
				'total_tax'  => $this->format_decimal( 0, 2 ),
				'percentage' => $this->format_decimal( 0, 1 ),
				'category'   => 'Z',
				'reason'     => 'NONE',
				'scheme'     => 'VAT',
				'name'       => $grouped_tax_data[ $z_first_key ]['name'] ?? '',
			);
		}

		return $grouped_tax_data;
	}

}
