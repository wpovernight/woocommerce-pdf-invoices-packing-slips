<?php

namespace WPO\IPS\UBL\Documents;

use WPO\IPS\Documents\OrderDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Document {

	/** @var \WC_Abstract_Order */
	public $order;

	/** @var array */
	public $order_tax_data;

	/** @var string */
	public $output;

	/** @var OrderDocument */
	public $order_document;

	public function set_order( \WC_Abstract_Order $order ) {
		$this->order          = $order;
		$this->order_tax_data = $this->get_tax_rates();
	}

	public function set_order_document( OrderDocument $order_document ) {
		$this->order_document = $order_document;
		$this->set_order( $order_document->order );
	}

	abstract public function get_root_element();
	abstract public function get_additional_root_elements();
	abstract public function get_format();
	abstract public function get_namespaces();
	abstract public function get_data();

	public function get_tax_rates() {
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
					$meta_key = '_wcpdf_ubl_tax_' . $field;
					$value    = wc_get_order_item_meta( $tax_item_key, $meta_key, true );

					if ( empty( $value ) || 'default' === $value || ! $use_historical_settings ) {
						$value = wpo_ips_ubl_get_tax_data_from_fallback( $field, $tax_rate_id, $this->order );
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
