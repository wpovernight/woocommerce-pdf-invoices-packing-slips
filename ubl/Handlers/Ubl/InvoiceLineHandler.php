<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class InvoiceLineHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$items = $this->document->order->get_items( [ 'line_item', 'fee', 'shipping' ] );

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxSubtotal      = [];
			$taxDataContainer = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$taxDataKey       = ( $item['type'] == 'line_item' ) ? 'subtotal' : 'total';
			$lineTotalKey     = ( $item['type'] == 'line_item' ) ? 'line_total' : 'total';
			$line_tax_data    = $item[$taxDataContainer];
			
			foreach ( $line_tax_data[$taxDataKey] as $tax_id => $tax ) {
				if ( ! is_numeric( $tax ) ) {
					continue;
				}

				$taxOrderData  = $this->document->order_tax_data[$tax_id];

				$taxSubtotal[] = [
					'name' => 'cac:TaxSubtotal',
					'value' => [ [
						'name' => 'cbc:TaxableAmount',
						'value' => round( $item[$lineTotalKey], 2 ),
						'attributes' => [
							'currencyID' => $this->document->order->get_currency(),
						],
					], [
						'name' => 'cbc:TaxAmount',
						'value' => round( $tax, 2 ),
						'attributes' => [
							'currencyID' => $this->document->order->get_currency(),
						],
					], [
						'name' => 'cac:TaxCategory',
						'value' => [ [
							'name' => 'cbc:ID',
							'value' => strtoupper( $taxOrderData['category'] ),
						], [
							'name' => 'cbc:Name',
							'value' => $taxOrderData['name'],
						], [
							'name' => 'cbc:Percent',
							'value' => round( $taxOrderData['percentage'], 2 ),
						], [
							'name' => 'cac:TaxScheme',
							'value' => [ [ 
								'name' => 'cbc:ID',
								'value' => strtoupper( $taxOrderData['scheme'] ),
							] ],
						] ]
					] ]
				];
			}

			$invoiceLine = [
				'name' => 'cac:InvoiceLine',
				'value' => [
					[
						'name' => 'cbc:ID',
						'value' => $item_id,
					], [
						'name' => 'cbc:InvoicedQuantity',
						'value' => $item->get_quantity(),
					], [
						'name' => 'cbc:LineExtensionAmount',
						'value' => round( $item->get_total(), 2 ),
						'attributes' => [
							'currencyID' => $this->document->order->get_currency(),
						],
					], [
						'name' => 'cac:TaxTotal',
						'value' => [
							[
								'name' => 'cbc:TaxAmount',
								'value' => round( $item->get_total_tax(), 2),
								'attributes' => [
									'currencyID' => $this->document->order->get_currency(),
								],
							], $taxSubtotal ]
					], [
						'name' => 'cac:Item',
						'value' => [ [ 
							'name' => 'cbc:Name',
							'value' => $item->get_name(),
						]],
					]
				],
			];


			$data[] = apply_filters( 'wpo_wc_ubl_handle_InvoiceLine', $invoiceLine, $data, $options, $item, $this );

			// Empty this array at the end of the loop per item, so data doesn't stack
			$taxSubtotal = [];
		}

		return $data;
	}
}