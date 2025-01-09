<?php

namespace WPO\IPS\UBL\Handlers\Invoice;

use WPO\IPS\UBL\Handlers\UblHandler;
use WPO\IPS\UBL\Settings\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$items      = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$taxReasons = TaxesSettings::get_available_reasons();

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$taxSubtotal      = [];
			$taxDataContainer = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$taxDataKey       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$lineTotalKey     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';
			$line_tax_data    = $item[ $taxDataContainer ];

			foreach ( $line_tax_data[ $taxDataKey ] as $tax_id => $tax ) {
				if ( empty( $tax ) ) {
					$tax = 0;
				}

				if ( ! is_numeric( $tax ) ) {
					continue;
				}

				$taxOrderData = $this->document->order_tax_data[ $tax_id ];

				// Build the TaxCategory array
				$taxCategory = array(
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
				);

				// Add TaxExemptionReason only if it's not empty
				if ( ! empty( $taxOrderData['reason'] ) && 'none' !== $taxOrderData['reason'] ) {
					$reasonKey     = $taxOrderData['reason'];
					$reason        = ! empty( $taxReasons[ $reasonKey ] ) ? $taxReasons[ $reasonKey ] : $reasonKey;
					$taxCategory[] = array(
						'name'  => 'cbc:TaxExemptionReasonCode',
						'value' => $reasonKey,
					);
					$taxCategory[] = array(
						'name'  => 'cbc:TaxExemptionReason',
						'value' => $reason,
					);
				}

				// Place the TaxScheme after the TaxExemptionReason
				$taxCategory[] = array(
					'name'  => 'cac:TaxScheme',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => strtoupper( $taxOrderData['scheme'] ),
						),
					),
				);

				$taxSubtotal[] = array(
					'name'  => 'cac:TaxSubtotal',
					'value' => array(
						array(
							'name'       => 'cbc:TaxableAmount',
							'value'      => wc_round_tax_total( $item[ $lineTotalKey ] ),
							'attributes' => array(
								'currencyID' => $this->document->order->get_currency(),
							),
						),
						array(
							'name'       => 'cbc:TaxAmount',
							'value'      => wc_round_tax_total( $tax ),
							'attributes' => array(
								'currencyID' => $this->document->order->get_currency(),
							),
						),
						array(
							'name'  => 'cac:TaxCategory',
							'value' => $taxCategory,
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
								'value'      => wc_round_tax_total( $item->get_total_tax() ),
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
								'value' => wpo_ips_ubl_sanitize_string( $item->get_name() ),
							),
						),
					),
					array(
						'name'  => 'cac:Price',
						'value' => array(
							array(
								'name'       => 'cbc:PriceAmount',
								'value'      => round( $this->get_item_unit_price( $item ), 2 ),
								'attributes' => array(
									'currencyID' => $this->document->order->get_currency(),
								),
							),
							array(
								'name'       => 'cbc:BaseQuantity',
								'value'      => 1, // value should be 1, as we're using the unit price
								'attributes' => array(
									'unitCode' => 'EA', // EA = Each (https://docs.peppol.eu/pracc/catalogue/1.0/codelist/UNECERec20/)
								),
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

	/**
	 * Get the unit price of an item
	 *
	 * @param WC_Order_Item $item
	 * @return int|float
	 */
	private function get_item_unit_price( $item ) {
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			return $item->get_subtotal() / $item->get_quantity();
		} elseif ( is_a( $item, 'WC_Order_Item_Shipping' ) || is_a( $item, 'WC_Order_Item_Fee' ) ) {
			return $item->get_total();
		} else {
			return 0;
		}
	}

}
