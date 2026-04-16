<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IncludedSupplyChainTradeLineItemHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$order = $this->document->order;
		$items = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );

		foreach ( $items as $item_id => $item ) {
			// Resolve tax meta for this line
			$meta = $this->resolve_item_tax_meta( $item );

			$tax_children = array(
				array(
					'name'  => 'ram:TypeCode',
					'value' => $meta['scheme'],
				),
				array(
					'name'  => 'ram:CategoryCode',
					'value' => $meta['category'],
				),
			);

			// For VAT category O ("Not subject to VAT"), do NOT emit RateApplicablePercent.
			if ( 'O' !== strtoupper( (string) ( $meta['category'] ?? '' ) ) && isset( $meta['percentage'] ) && '' !== $meta['percentage'] ) {
				$tax_children[] = array(
					'name'  => 'ram:RateApplicablePercent',
					'value' => $this->format_decimal( $meta['percentage'], 1 ),
				);
			}

			$tax_nodes = array(
				array(
					'name'  => 'ram:ApplicableTradeTax',
					'value' => $tax_children,
				),
			);

			// Price parts
			$parts = $this->compute_item_price_parts( $item, false );

			$price_decimal_places = 2;
			$qty                  = (float) $parts['qty'];

			// When WooCommerce rounds tax at subtotal level, derive the XML unit
			// prices from the line totals to keep the line prices consistent with the
			// final line total amount.
			if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) && $qty > 0 ) {
				$price_decimal_places = 4;
			}

			$xml_gross_unit_f = $qty > 0
				? (float) $this->format_decimal( (float) $parts['gross_total'] / $qty, $price_decimal_places )
				: 0.0;

			$xml_net_unit_f = $qty > 0
				? (float) $this->format_decimal( (float) $parts['net_total'] / $qty, $price_decimal_places )
				: (float) $this->format_decimal( $parts['net_total'], $price_decimal_places );

			$xml_unit_discount_f = $xml_gross_unit_f - $xml_net_unit_f;
			if ( $xml_unit_discount_f < 0 ) {
				$xml_unit_discount_f = 0.0;
			}

			$xml_unit_discount_f = (float) $this->format_decimal( $xml_unit_discount_f, $price_decimal_places );

			// Recompute net from gross - discount to guarantee equality in XML.
			$xml_net_unit_f = $xml_gross_unit_f - $xml_unit_discount_f;

			$gross_unit    = $this->format_decimal( $xml_gross_unit_f, $price_decimal_places );
			$net_unit      = $this->format_decimal( $xml_net_unit_f, $price_decimal_places );
			$unit_discount = $this->format_decimal( $xml_unit_discount_f, $price_decimal_places );

			$gross_price_children = array(
				array(
					'name'  => 'ram:ChargeAmount',
					'value' => $gross_unit,
				),
				array(
					'name'       => 'ram:BasisQuantity',
					'value'      => 1,
					'attributes' => array(
						'unitCode' => 'C62',
					),
				),
			);

			// Emit price-level allowance on the gross price for discounted product lines.
			if ( $xml_unit_discount_f > 0 && $xml_gross_unit_f > 0 && is_a( $item, 'WC_Order_Item_Product' ) ) {
				$gross_price_children[] = array(
					'name'  => 'ram:AppliedTradeAllowanceCharge',
					'value' => array(
						array(
							'name'  => 'ram:ChargeIndicator',
							'value' => array(
								array(
									'name'  => 'udt:Indicator',
									'value' => 'false',
								),
							),
						),
						array(
							'name'  => 'ram:ActualAmount',
							'value' => $unit_discount,
						),
					),
				);
			}

			$net_price_children = array(
				array(
					'name'  => 'ram:ChargeAmount',
					'value' => $net_unit,
				),
				array(
					'name'       => 'ram:BasisQuantity',
					'value'      => 1,
					'attributes' => array(
						'unitCode' => 'C62',
					),
				),
			);

			// Build SpecifiedTradeProduct
			$product_value = array(
				array(
					'name'  => 'ram:Name',
					'value' => wpo_ips_edi_sanitize_string( $item->get_name() ),
				),
			);

			// Optionally append ApplicableProductCharacteristic from meta
			if ( wpo_ips_edi_include_item_meta() ) {
				$meta_rows = $this->get_item_meta( $item );

				if ( ! empty( $meta_rows ) ) {
					foreach ( $meta_rows as $row ) {
						$product_value[] = array(
							'name'  => 'ram:ApplicableProductCharacteristic',
							'value' => array(
								array(
									'name'  => 'ram:Description',
									'value' => $row['name'],
								),
								array(
									'name'  => 'ram:Value',
									'value' => $row['value'],
								),
							),
						);
					}
				}
			}

			$quantity_value = $parts['qty'];

			// Use Woo’s net_total for the line total amount.
			$net_line_total_f = (float) $this->format_decimal( $parts['net_total'], 2 );
			$net_line_total   = $this->format_decimal( $net_line_total_f, 2 );

			$line_item = array(
				'name'  => 'ram:IncludedSupplyChainTradeLineItem',
				'value' => array(
					array(
						'name'  => 'ram:AssociatedDocumentLineDocument',
						'value' => array(
							array(
								'name'  => 'ram:LineID',
								'value' => $item_id,
							),
						),
					),
					array(
						'name'  => 'ram:SpecifiedTradeProduct',
						'value' => $product_value,
					),
					array(
						'name'  => 'ram:SpecifiedLineTradeAgreement',
						'value' => array(
							array(
								'name'  => 'ram:GrossPriceProductTradePrice',
								'value' => $gross_price_children,
							),
							array(
								'name'  => 'ram:NetPriceProductTradePrice',
								'value' => $net_price_children,
							),
						),
					),
					array(
						'name'  => 'ram:SpecifiedLineTradeDelivery',
						'value' => array(
							array(
								'name'       => 'ram:BilledQuantity',
								'value'      => $quantity_value,
								'attributes' => array(
									'unitCode' => 'C62',
								),
							),
						),
					),
					array(
						'name'  => 'ram:SpecifiedLineTradeSettlement',
						'value' => array_merge(
							$tax_nodes,
							array(
								array(
									'name'  => 'ram:SpecifiedTradeSettlementLineMonetarySummation',
									'value' => array(
										array(
											'name'  => 'ram:LineTotalAmount',
											'value' => $net_line_total,
										),
									),
								),
							)
						),
					),
				),
			);

			$data[] = apply_filters( 'wpo_ips_edi_cii_included_supply_chain_trade_line_item', $line_item, $data, $options, $item, $this );
		}

		return $data;
	}

}
