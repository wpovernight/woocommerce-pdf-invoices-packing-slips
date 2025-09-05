<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;
use WPO\IPS\EDI\Standards\EN16931;

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
		$tax_reasons = EN16931::get_vatex();
		$currency    = $this->document->order->get_currency();

		// Build the tax totals array
		foreach ( $items as $item_id => $item ) {
			$tax_data_container = ( $item['type'] == 'line_item' ) ? 'line_tax_data' : 'taxes';
			$tax_data_key       = ( $item['type'] == 'line_item' ) ? 'subtotal'      : 'total';
			$line_total_key     = ( $item['type'] == 'line_item' ) ? 'line_total'    : 'total';
			$line_tax_data      = $item[ $tax_data_container ];
			$tax_category       = array();

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
						'name'  => 'cbc:Percent',
						'value' => round( $tax_order_data['percentage'], 2 ),
					),
					array(
						'name'  => 'cac:TaxScheme',
						'value' => array(
							array(
								'name'  => 'cbc:ID',
								'value' => strtoupper( $tax_order_data['scheme'] ),
							),
						),
					),
				);
			}
			
			// Fallback if no tax rows were found
			if ( empty( $tax_category ) ) {
				$is_shipping = ( 'shipping' === $item->get_type() );

				if ( $is_shipping ) {
					// Mirror CII: shipping with no tax -> Zero-rated Z/0%
					$tax_category = array(
						array( 'name' => 'cbc:ID',      'value' => 'Z' ),
						array( 'name' => 'cbc:Percent', 'value' => 0 ),
						array(
							'name'  => 'cac:TaxScheme',
							'value' => array(
								array( 'name' => 'cbc:ID', 'value' => 'VAT' ),
							),
						),
					);
				} else {
					// Non-shipping without tax rows: choose your policy.
					// If you also want these as Z, copy the block above.
					// Otherwise, inherit the first standard rate found (S):
					$std = null;
					foreach ( (array) $this->document->order_tax_data as $row ) {
						if ( strtoupper( $row['category'] ?? '' ) === 'S' ) { $std = $row; break; }
					}
					$tax_category = array(
						array( 'name' => 'cbc:ID',      'value' => strtoupper( $std['category'] ?? 'Z' ) ),
						array( 'name' => 'cbc:Percent', 'value' => round( $std['percentage'] ?? 0, 2 ) ),
						array(
							'name'  => 'cac:TaxScheme',
							'value' => array(
								array( 'name' => 'cbc:ID', 'value' => strtoupper( $std['scheme'] ?? 'VAT' ) ),
							),
						),
					);
				}
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
						'attributes' => array(
							'unitCode' => 'C62', // https://docs.peppol.eu/pracc/catalogue/1.0/codelist/UNECERec20/
						),
					),
					array(
						'name'       => 'cbc:LineExtensionAmount',
						'value'      => round( $item->get_total(), 2 ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'  => 'cac:Item',
						'value' => array(
							array(
								'name'  => 'cbc:Name',
								'value' => wpo_ips_edi_sanitize_string( $item->get_name() ),
							),
							array(
								'name'  => 'cac:ClassifiedTaxCategory',
								'value' => $tax_category,
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
									'unitCode' => 'C62', // https://docs.peppol.eu/pracc/catalogue/1.0/codelist/UNECERec20/
								),
							),
						),
					),
				),
			);

			$data[] = apply_filters( 'wpo_ips_edi_ubl_invoice_line', $invoice_line, $data, $options, $item, $this );
		}

		return $data;
	}

}
