<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;
use WPO\IPS\EDI\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceLineHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$items       = $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$tax_reasons = TaxesSettings::get_available_reasons();
		$currency    = $this->document->order->get_currency();

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$tax_subtotal       = array();
			$tax_data_container = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$tax_data_key       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$line_total_key     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';
			$line_tax_data      = $item[ $tax_data_container ];

			foreach ( $line_tax_data[ $tax_data_key ] as $tax_id => $tax ) {
				if ( empty( $tax ) ) {
					$tax = 0;
				}

				if ( ! is_numeric( $tax ) ) {
					continue;
				}

				$tax_order_data = $this->document->order_tax_data[ $tax_id ];

				// Build the TaxCategory array
				$tax_category = array(
					array(
						'name'  => 'cbc:ID',
						'value' => strtoupper( $tax_order_data['category'] ),
					),
					array(
						'name'  => 'cbc:Name',
						'value' => $tax_order_data['name'],
					),
					array(
						'name'  => 'cbc:Percent',
						'value' => round( $tax_order_data['percentage'], 2 ),
					),
				);

				// Add TaxExemptionReason only if it's not empty
				if ( ! empty( $tax_order_data['reason'] ) && 'none' !== $tax_order_data['reason'] ) {
					$reason_key     = $tax_order_data['reason'];
					$reason         = ! empty( $tax_reasons[ $reason_key ] ) ? $tax_reasons[ $reason_key ] : $reason_key;
					$tax_category[] = array(
						'name'  => 'cbc:TaxExemptionReasonCode',
						'value' => $reason_key,
					);
					$tax_category[] = array(
						'name'  => 'cbc:TaxExemptionReason',
						'value' => $reason,
					);
				}

				// Place the TaxScheme after the TaxExemptionReason
				$tax_category[] = array(
					'name'  => 'cac:TaxScheme',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => strtoupper( $tax_order_data['scheme'] ),
						),
					),
				);

				$tax_subtotal[] = array(
					'name'  => 'cac:TaxSubtotal',
					'value' => array(
						array(
							'name'       => 'cbc:TaxableAmount',
							'value'      => wc_round_tax_total( $item[ $line_total_key ] ),
							'attributes' => array(
								'currencyID' => $currency,
							),
						),
						array(
							'name'       => 'cbc:TaxAmount',
							'value'      => wc_round_tax_total( $tax ),
							'attributes' => array(
								'currencyID' => $currency,
							),
						),
						array(
							'name'  => 'cac:TaxCategory',
							'value' => $tax_category,
						),
					),
				);
			}

			$invoice_line = array(
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
							'currencyID' => $currency,
						),
					),
					array(
						'name'  => 'cac:TaxTotal',
						'value' => array(
							array(
								'name'       => 'cbc:TaxAmount',
								'value'      => wc_round_tax_total( $item->get_total_tax() ),
								'attributes' => array(
									'currencyID' => $currency,
								),
							),
							$tax_subtotal,
						),
					),
					array(
						'name'  => 'cac:Item',
						'value' => array(
							array(
								'name'  => 'cbc:Name',
								'value' => wpo_ips_edi_sanitize_string( $item->get_name() ),
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
									'currencyID' => $currency,
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

			$data[] = apply_filters( 'wpo_ips_edi_ubl_invoice_line', $invoice_line, $data, $options, $item, $this );

			// Empty this array at the end of the loop per item, so data doesn't stack
			$tax_subtotal = [];
		}

		return $data;
	}

}
