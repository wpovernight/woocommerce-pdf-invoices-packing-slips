<?php

namespace WPO\IPS\EDI\Abstracts;

use WPO\IPS\Documents\OrderDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractDocument {

	public string $syntax;
	public \WC_Abstract_Order $order;
	public array $order_tax_data;
	public array $order_coupons_data;
	public string $output;
	public OrderDocument $order_document;

	/**
	 * Get the root element
	 *
	 * @return string
	 */
	abstract public function get_root_element(): string;

	/**
	 * Get additional root elements
	 *
	 * @return array
	 */
	abstract public function get_additional_root_elements(): array;

	/**
	 * Get the namespaces
	 *
	 * @return array
	 */
	abstract public function get_namespaces(): array;

	/**
	 * Get the syntax formats
	 *
	 * @return array
	 */
	public function get_syntax_formats(): array {
		$all_formats = wpo_ips_edi_formats();

		return apply_filters(
			'wpo_ips_edi_syntax_formats',
			$all_formats[ $this->syntax ] ?? array(),
			$this
		);
	}

	/**
	 * Set the order
	 *
	 * @param \WC_Abstract_Order $order
	 * @return void
	 */
	public function set_order( \WC_Abstract_Order $order ): void {
		$this->order              = $order;
		$this->order_tax_data     = $this->get_tax_rates();
		$this->order_coupons_data = $this->get_order_coupons_data();
	}

	/**
	 * Set the order document
	 *
	 * @param OrderDocument $order_document
	 * @return void
	 */
	public function set_order_document( OrderDocument $order_document ): void {
		$this->order_document = $order_document;
		$this->set_order( $order_document->order );
	}

	/**
	 * Get the format structure
	 *
	 * @return array|false
	 */
	public function get_format_structure() {
		$format            = wpo_ips_edi_get_current_format();
		$available_formats = wpo_ips_edi_formats( $this->syntax );

		if ( ! isset( $available_formats[ $format ] ) ) {
			return false;
		}

		$structure = ( new $available_formats[ $format ]['class']() )->get_structure( $this->order_document->slug );

		if ( empty( $structure ) ) {
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
	 * Get tax rates
	 *
	 * @return array
	 */
	public function get_tax_rates(): array {
		$order_tax_data = array();
		$items          = $this->order->get_items( array( 'fee', 'line_item', 'shipping' ) );

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxDataContainer = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$taxDataKey       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$lineTotalKey     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';

			$line_tax_data = $item[ $taxDataContainer ];
			foreach ( $line_tax_data[ $taxDataKey ] as $tax_id => $tax ) {
				if ( is_numeric( $tax ) ) {
					if ( empty( $order_tax_data[ $tax_id ] ) ) {
						$order_tax_data[ $tax_id ] = array(
							'total_ex'  => $item[ $lineTotalKey ],
							'total_tax' => $tax,
							'items'     => array( $item_id ),
						);
					} else {
						$order_tax_data[ $tax_id ]['total_ex']  += $item[ $lineTotalKey ];
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

	/**
	 * Get the document data
	 *
	 * @return array
	 */
	public function get_data(): array {
		$data = array();

		foreach ( $this->get_format_structure() as $key => $value ) {
			$options  = isset( $value['options'] ) && is_array( $value['options'] ) ? $value['options'] : array();
			$handlers = is_array( $value['handler'] ) ? $value['handler'] : array( $value['handler'] );

			// Get the root from options if defined
			$root_name = isset( $options['root'] ) ? $options['root'] : null;
			$root_data = array();

			foreach ( $handlers as $handler_class ) {
				if ( ! class_exists( $handler_class ) ) {
					continue;
				}

				$handler   = new $handler_class( $this );
				$root_data = $handler->handle( $root_data, $options );
			}

			// Add to $data under the root name if specified, otherwise merge directly
			if ( $root_name ) {
				$data[] = array(
					'name'  => $root_name,
					'value' => $root_data,
				);
			} else {
				$data = array_merge( $data, $root_data );
			}
		}

		return apply_filters( 'wpo_ips_edi_document_data', $data, $this );
	}

}
