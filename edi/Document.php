<?php

namespace WPO\IPS\EDI;

use WPO\IPS\Documents\OrderDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Document {

	public string $syntax;
	public string $format;
	public OrderDocument $order_document;
	public \WC_Abstract_Order $order;
	public array $order_tax_data;
	public array $order_coupons_data;
	public ?object $format_document;
	public string $output;

	/**
	 * Constructor
	 *
	 * @param string $syntax
	 * @param string $format
	 * @param OrderDocument $order_document
	 */
	public function __construct( string $syntax, string $format, OrderDocument $order_document ) {
		$this->syntax             = $syntax;
		$this->format             = $format;
		$this->order_document     = $order_document;
		$this->order              = $this->order_document->order;
		$this->order_tax_data     = $this->get_tax_rates();
		$this->order_coupons_data = $this->get_order_coupons_data();
		$this->format_document    = $this->get_format_document();

		if ( ! $this->format_document ) {
			$error = sprintf( 'Format "%s" for syntax "%s" is not available.', $format, $syntax );
			wpo_ips_edi_log( $error, 'critical' );
			throw new \Exception( $error );
		}
	}
	
	/**
	 * Get the format document instance
	 *
	 * @return object|null
	 */
	public function get_format_document(): ?object {
		$format        = wpo_ips_edi_syntax_formats( $this->syntax, $this->format );
		$document_type = $this->order_document->get_type();

		if ( ! $format ) {
			wpo_ips_edi_log(
				sprintf(
					'Format "%s" for syntax "%s" is not available.',
					$this->format,
					$this->syntax
				),
				'critical'
			);
			return null;
		}
		
		if ( ! in_array( $document_type, array_keys( $format['documents'] ) ) ) {
			wpo_ips_edi_log(
				sprintf(
					'Document type "%s" is not supported for format "%s".',
					$document_type,
					$this->format
				),
				'critical'
			);
			return null;
		}
		
		return new $format['documents'][ $document_type ]();
	}

	/**
	 * Get the document structure
	 *
	 * @return array|false
	 */
	public function get_structure() {
		$structure = apply_filters(
			'wpo_ips_edi_document_structure',
			$this->format_document->get_structure(),
			$this
		);

		if ( empty( $structure ) ) {
			wpo_ips_edi_log( 'Document structure is empty.', 'error' );
			return false;
		}

		foreach ( $structure as $key => $element ) {
			if ( false === $element['enabled'] ) {
				unset( $structure[ $key ] );
			}
		}

		return $structure;
	}
	
	/**
	 * Get the document type code
	 *
	 * @return string
	 */
	public function get_type_code(): string {
		return apply_filters(
			'wpo_ips_edi_document_type_code',
			$this->format_document->get_type_code(),
			$this
		);
	}
	
	/**
	 * Get the document root element
	 *
	 * @return string
	 */
	public function get_root_element(): string {
		return apply_filters(
			'wpo_ips_edi_document_root_element',
			$this->format_document->get_root_element(),
			$this
		);
	}
	
	/**
	 * Get the document additional attributes
	 *
	 * @return array
	 */
	public function get_additional_attributes(): array {
		return apply_filters(
			'wpo_ips_edi_document_additional_attributes',
			$this->format_document->get_additional_attributes(),
			$this
		);
	}
	
	/**
	 * Get the document namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array {
		return apply_filters(
			'wpo_ips_edi_document_namespaces',
			$this->format_document->get_namespaces(),
			$this
		);
	}
	
	/**
	 * Assemble the element list for the XML writer.
	 *
	 * Works for both UBL and CII syntaxes:
	 *
	 * • If a handler sets `options[root]`, its output is wrapped in that tag  
	 *   (e.g. `cac:AccountingSupplierParty`, `ram:SupplyChainTradeTransaction`).
	 * • Without a root option, the fragment is written directly under the
	 *   document root.
	 * • The order of handlers in `get_structure()` is preserved, so the final
	 *   sequence respects the schema of either standard.
	 *
	 * @return array Structured data ready for the XML builder.
	 */
	public function get_data(): array {
		$data_by_root = array();

		foreach ( $this->get_structure() as $key => $value ) {
			// skip disabled or mis-configured entries
			if ( empty( $value['enabled'] ) || empty( $value['handler'] ) ) {
				continue;
			}

			$options   = $value['options'] ?? array();
			$handlers  = is_array( $value['handler'] ) ? $value['handler'] : array( $value['handler'] );
			$root_name = $options['root'] ?? null;
			$fragment  = array();

			foreach ( $handlers as $handler_class ) {
				if ( class_exists( $handler_class ) ) {
					$handler  = new $handler_class( $this );
					$fragment = $handler->handle( $fragment, $options );
				}
			}

			if ( $root_name ) {
				// merge with any previous output for the same root
				if ( ! isset( $data_by_root[ $root_name ] ) ) {
					$data_by_root[ $root_name ] = array();
				}
				$data_by_root[ $root_name ] = array_merge( $data_by_root[ $root_name ], $fragment );
			} else {
				// append root-less fragment under a *numeric* key -> preserves order
				$data_by_root[] = $fragment;
			}
		}

		$data = array();

		foreach ( $data_by_root as $root => $value ) {
			if ( is_string( $root ) ) {
				// named root -> wrap
				$data[] = array(
					'name'  => $root,
					'value' => $value,
				);
			} else {
				// numeric key -> flatten directly
				$data = array_merge( $data, $value );
			}
		}

		return apply_filters( 'wpo_ips_edi_document_data', $data, $this );
	}

	/**
	 * Get tax rates
	 *
	 * @return array
	 */
	public function get_tax_rates(): array {
		$order_tax_data = array();
		$items          = $this->order->get_items( array( 'fee', 'line_item', 'shipping' ) );

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$tax_data_container = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$tax_data_key       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$line_total_key     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';
			$line_tax_data      = $item[ $tax_data_container ];
			
			foreach ( $line_tax_data[ $tax_data_key ] as $tax_id => $tax ) {
				if ( is_numeric( $tax ) ) {
					if ( empty( $order_tax_data[ $tax_id ] ) ) {
						$order_tax_data[ $tax_id ] = array(
							'total_ex'  => $item[ $line_total_key ],
							'total_tax' => $tax,
							'items'     => array( $item_id ),
						);
					} else {
						$order_tax_data[ $tax_id ]['total_ex']  += $item[ $line_total_key ];
						$order_tax_data[ $tax_id ]['total_tax'] += $tax;
						$order_tax_data[ $tax_id ]['items'][]    = $item_id;
					}
				}
			}
		}

		$tax_items = $this->order->get_items( array( 'tax' ) );

		if ( empty( $tax_items ) ) {
			return $order_tax_data;
		}

		$use_historical_settings = $this->order_document->use_historical_settings();

		// Loop through all the tax items...
		foreach ( $order_tax_data as $tax_data_key => $tax_data ) {
			$percentage = 0;
			$category   = '';
			$scheme     = '';
			$reason     = '';

			foreach ( $tax_items as $tax_item_key => $tax_item ) {
				if ( $tax_item['rate_id'] !== $tax_data_key ) {
					continue;
				}

				// we use the tax total from the tax item because this
				// takes into account possible line item rounding settings as well
				// we still apply rounding on the total (for non-checkout orders)
				$order_tax_data[ $tax_data_key ]['total_tax'] = wc_round_tax_total( $tax_item['tax_amount'] ) + wc_round_tax_total( $tax_item['shipping_tax_amount'] );

				if ( is_callable( array( $tax_item, 'get_rate_percent' ) ) && version_compare( '3.7.0', $this->order->get_version(), '>=' ) ) {
					$percentage = $tax_item->get_rate_percent();
				} else {
					$percentage = wc_get_order_item_meta( $tax_item_key, '_wcpdf_rate_percentage', true );
				}

				$tax_rate_id = absint( $tax_item['rate_id'] );

				if ( ! is_numeric( $percentage ) ) {
					$percentage = $this->get_percentage_from_fallback( $tax_data, $tax_rate_id );
					wc_update_order_item_meta( $tax_item_key, '_wcpdf_rate_percentage', $percentage );
				}

				$fields = array( 'category', 'scheme', 'reason' );

				foreach ( $fields as $field ) {
					$meta_key = '_wpo_ips_edi_tax_' . $field;
					$value    = wc_get_order_item_meta( $tax_item_key, $meta_key, true );

					// If the value is empty, try to get it from legacy meta key
					if ( empty( $value ) ) {
						$legacy_meta_key = '_wcpdf_ubl_tax_' . $field;
						$value           = wc_get_order_item_meta( $tax_item_key, $legacy_meta_key, true ) ?: $value;
					}

					if ( empty( $value ) || 'default' === $value || ! $use_historical_settings ) {
						$value = wpo_ips_edi_get_tax_data_from_fallback( $field, $tax_rate_id, $this->order );
					}

					if ( $use_historical_settings ) {
						wc_update_order_item_meta( $tax_item_key, $meta_key, $value );
					}

					${$field} = $value;
				}
			}

			$order_tax_data[ $tax_data_key ]['percentage'] = $percentage;
			$order_tax_data[ $tax_data_key ]['category']   = $category;
			$order_tax_data[ $tax_data_key ]['scheme']     = $scheme;
			$order_tax_data[ $tax_data_key ]['reason']     = $reason;
			$order_tax_data[ $tax_data_key ]['name']       = ! empty( $tax_item['label'] ) ? $tax_item['label'] : $tax_item['name'];
		}

		return $order_tax_data;
	}

	/**
	 * Get order coupons data
	 *
	 * @return array
	 */
	public function get_order_coupons_data(): array {
		$order      = $this->order;
		$order_data = array();

		// Get applied coupons
		$applied_coupons = $order->get_coupon_codes();
		$coupons_data    = array();

		foreach ( $applied_coupons as $coupon_code ) {
			$coupon         = new \WC_Coupon( $coupon_code );
			$coupons_data[] = array(
				'code'   => $coupon->get_code(),
				'type'   => $coupon->get_discount_type(),
				'amount' => $coupon->get_amount(),
			);
		}

		// Get item-level discounts
		$items_data = array();

		foreach ( $order->get_items() as $item_id => $item ) {
			$subtotal = $item->get_subtotal();
			$total    = $item->get_total();
			$discount = $subtotal - $total;

			$items_data[ $item_id ] = [
				'name'     => $item->get_name(),
				'subtotal' => $subtotal,
				'total'    => $total,
				'discount' => (float) $discount,
			];
		}

		$order_data['coupons'] = $coupons_data;
		$order_data['items']   = $items_data;

		return $order_data;
	}

	/**
	 * Get percentage from fallback
	 *
	 * @param array $tax_data
	 * @param int   $rate_id
	 * @return float|int
	 */
	public function get_percentage_from_fallback( array $tax_data, int $rate_id ) {
		$percentage = ( 0 != $tax_data['total_ex'] ) ? ( $tax_data['total_tax'] / $tax_data['total_ex'] ) * 100 : 0;

		if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
			$tax_rate = \WC_Tax::_get_tax_rate( $rate_id, OBJECT );

			if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
				$difference = $percentage - $tax_rate->tax_rate;

				// Turn negative into positive for easier comparison below
				if ( $difference < 0 ) {
					$difference = -$difference;
				}

				// Use stored tax rate if difference is smaller than 0.3
				if ( $difference < 0.3 ) {
					$percentage = $tax_rate->tax_rate;
				}
			}
		}

		return $percentage;
	}

}
