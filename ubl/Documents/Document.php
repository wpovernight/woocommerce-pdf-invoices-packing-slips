<?php

namespace WPO\WC\UBL\Documents;

use WPO\WC\PDF_Invoices\Documents\Order_Document;

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

	/** @var Order_Document */
	public $order_document;
	
	public function set_order( \WC_Abstract_Order $order ) {
		$this->order          = $order;
		$this->order_tax_data = $this->get_tax_rates();
	}

	public function set_order_document( Order_Document $order_document ) {
		$this->order_document = $order_document;
	}

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

		// Loop through all the tax items...
		foreach ( $order_tax_data as $tax_data_key => $tax_data ) {
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

				if ( ! is_numeric( $percentage ) ) {
					$percentage = $this->get_percentage_from_fallback( $tax_data, $tax_item['rate_id'] );
					wc_update_order_item_meta( $tax_item_key, '_wcpdf_rate_percentage', $percentage );
				}

				$category = wc_get_order_item_meta( $tax_item_key, '_wcpdf_ubl_tax_category', true );

				if ( empty( $category ) ) {
					$category = $this->get_category_from_fallback( $tax_data, $tax_item['rate_id'] );
					wc_update_order_item_meta( $tax_item_key, '_wcpdf_ubl_tax_category', $category );
				}

				$scheme = wc_get_order_item_meta( $tax_item_key, '_wcpdf_ubl_tax_scheme', true );

				if ( empty( $scheme ) ) {
					$scheme = $this->get_scheme_from_fallback( $tax_data, $tax_item['rate_id'] );
					wc_update_order_item_meta( $tax_item_key, '_wcpdf_ubl_tax_scheme', $scheme );
				}
			}

			$order_tax_data[ $tax_data_key ]['percentage'] = $percentage;
			$order_tax_data[ $tax_data_key ]['category']   = $category;
			$order_tax_data[ $tax_data_key ]['scheme']     = $scheme;
			$order_tax_data[ $tax_data_key ]['name']       = ! empty( $tax_item['label'] ) ? $tax_item['label'] : $tax_item['name'];
		}

		return $order_tax_data;
	}

	public function get_percentage_from_fallback( $tax_data, $rate_id ) {
		$percentage = ( $tax_data['total_tax'] / $tax_data['total_ex'] ) * 100;

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

	public function get_category_from_fallback( $tax_data, $rate_id ) {
		$category = '';

		if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
			$tax_rate = \WC_Tax::_get_tax_rate( $rate_id, OBJECT );

			if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
				$ubl_tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes' );
				$category         = isset( $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ]['category'] ) ? $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ]['category'] : '';
				$tax_rate_class   = $tax_rate->tax_rate_class;
				
				if ( empty( $tax_rate_class ) ) {
					$tax_rate_class = 'standard';
				}

				if ( empty( $category ) ) {
					$category = isset( $ubl_tax_settings['class'][ $tax_rate_class ]['category'] ) ? $ubl_tax_settings['class'][ $tax_rate_class ]['category'] : '';
				}
			}
		}

		return $category;
	}

	public function get_scheme_from_fallback( $tax_data, $rate_id ) {
		$scheme = '';
		
		if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
			$tax_rate = \WC_Tax::_get_tax_rate( $rate_id, OBJECT );

			if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
				$ubl_tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes' );
				$scheme           = isset( $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ]['scheme'] ) ? $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ]['scheme'] : '';
				$tax_rate_class   = $tax_rate->tax_rate_class;
				
				if ( empty( $tax_rate_class ) ) {
					$tax_rate_class = 'standard';
				}

				if ( empty( $scheme ) ) {
					$scheme = isset( $ubl_tax_settings['class'][ $tax_rate_class ]['scheme'] ) ? $ubl_tax_settings['class'][ $tax_rate_class ]['scheme'] : '';
				}
			}
		}

		return $scheme;
	}
	
}