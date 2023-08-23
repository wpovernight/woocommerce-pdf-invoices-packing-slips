<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends UblHandler {
	
	public function handle( $data, $options = array() ) {
		$items = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxSubtotal      = [];
			$taxDataContainer = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$taxDataKey       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$lineTotalKey     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';
			$line_tax_data    = $item[ $taxDataContainer ];
			
			foreach ( $line_tax_data[ $taxDataKey ] as $tax_id => $tax ) {
				if ( ! is_numeric( $tax ) ) {
					continue;
				}

				$taxOrderData  = $this->document->order_tax_data[ $tax_id ];

				$taxSubtotal[] = array(
					'name'  => 'cac:TaxSubtotal',
					'value' => array(
						array(
							'name'       => 'cbc:TaxableAmount',
							'value'      => round( $item[ $lineTotalKey ], 2 ),
							'attributes' => array(
								'currencyID' => $this->document->order->get_currency(),
							),
						),
						array(
							'name'       => 'cbc:TaxAmount',
							'value'      => round( $tax, 2 ),
							'attributes' => array(
								'currencyID' => $this->document->order->get_currency(),
							),
						),
						array(
							'name'  => 'cac:TaxCategory',
							'value' => array(
								array(
									'name'  => 'cbc:ID',
									'value' => strtoupper( $taxOrderData['category'] ),
								),
								array(
									'name'  => 'cbc:Name',
									'value' => $taxOrderData['name'],
								),
								array(
									'name'  => 'cbc:Percent',
									'value' => round( $taxOrderData['percentage'], 2 ),
								),
								array(
									'name'  => 'cac:TaxScheme',
									'value' => array(
										array(
											'name'  => 'cbc:ID',
											'value' => strtoupper( $taxOrderData['scheme'] ),
										),
									),
								),
							),
						),
					),
				);
			}

			$invoiceLine = array(
				'name'  => 'cac:InvoiceLine',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $item_id,
					),
					array(
						'name'  => 'cbc:InvoicedQuantity',
						'value' => $item->get_quantity(),
					),
					array(
						'name'       => 'cbc:LineExtensionAmount',
						'value'      => round( $item->get_total(), 2 ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
					array(
						'name'  => 'cac:TaxTotal',
						'value' => array(
							array(
								'name'       => 'cbc:TaxAmount',
								'value'      => round( $item->get_total_tax(), 2),
								'attributes' => array(
									'currencyID' => $this->document->order->get_currency(),
								),
							),
							$taxSubtotal,
						),
					),
					array(
						'name'  => 'cac:Item',
						'value' => array(
							array(
								'name'  => 'cbc:Name',
								'value' => $item->get_name(),
							),
						),
					),
				),
			);


			$data[] = apply_filters( 'wpo_wc_ubl_handle_InvoiceLine', $invoiceLine, $data, $options, $item, $this );

			// Empty this array at the end of the loop per item, so data doesn't stack
			$taxSubtotal = [];
		}

		return $data;
	}
	
}