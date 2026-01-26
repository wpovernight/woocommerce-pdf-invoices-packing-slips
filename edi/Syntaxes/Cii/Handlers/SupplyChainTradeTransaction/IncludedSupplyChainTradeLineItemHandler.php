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

			$tax_nodes = array(
				array(
					'name'  => 'ram:ApplicableTradeTax',
					'value' => array(
						array(
							'name'  => 'ram:TypeCode',
							'value' => $meta['scheme'],
						),
						array(
							'name'  => 'ram:CategoryCode',
							'value' => $meta['category'],
						),
						array(
							'name'  => 'ram:RateApplicablePercent',
							'value' => $this->format_decimal( $meta['percentage'], 1 ),
						),
					),
				),
			);

			// Price parts
			$parts = $this->compute_item_price_parts( $item, false );

			// Round gross/net units first (numeric), then derive discount, then recompute net.
			$gross_unit_f = (float) $this->format_decimal( $parts['gross_unit'], 2 );
			$net_unit_f   = (float) $this->format_decimal( $parts['net_unit'],   2 );

			$unit_discount_f = $gross_unit_f - $net_unit_f;
			if ( $unit_discount_f < 0 ) {
				$unit_discount_f = 0.0;
			}

			$unit_discount_f = (float) $this->format_decimal( $unit_discount_f, 2 );

			// Recompute net from gross - discount to guarantee equality in XML.
			$net_unit_f = $gross_unit_f - $unit_discount_f;

			$gross_unit    = $this->format_decimal( $gross_unit_f, 2 );
			$net_unit      = $this->format_decimal( $net_unit_f,   2 );
			$unit_discount = $this->format_decimal( $unit_discount_f, 2 );

			$price_children = array(
				array(
					'name'  => 'ram:ChargeAmount',
					'value' => $net_unit,
				),
				array(
					'name'       => 'ram:BasisQuantity',
					'value'      => 1,
					'attributes' => array(
						'unitCode' => 'C62'
					)
				),
			);

			// Only products can have a price-level discount (already reflected in net price)
			// if ( $unit_discount > 0 && is_a( $item, 'WC_Order_Item_Product' ) ) {
			// 	$price_children[] = array(
			// 		'name'  => 'ram:AppliedTradeAllowanceCharge',
			// 		'value' => array(
			// 			array(
			// 				'name'  => 'ram:ChargeIndicator',
			// 				'value' => array(
			// 					array(
			// 						'name'  => 'udt:Indicator',
			// 						'value' => 'false'
			// 					),
			// 				),
			// 			),
			// 			array(
			// 				'name'  => 'ram:ActualAmount',
			// 				'value' => $unit_discount,
			// 			),
			// 			array(
			// 				'name'  => 'ram:BasisAmount',
			// 				'value' => $gross_unit,
			// 			),
			// 		),
			// 	);
			// }

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

			// Compute line net amount from the same unit price we emit in ChargeAmount
			$net_line_total_f = (float) $this->format_decimal( $net_unit_f * $quantity_value, 2 );
			$net_line_total   = $this->format_decimal( $net_line_total_f, 2 );

			$line_item = array(
				'name'  => 'ram:IncludedSupplyChainTradeLineItem',
				'value' => array(
					array(
						'name'  => 'ram:AssociatedDocumentLineDocument',
						'value' => array(
							array(
								'name' => 'ram:LineID',
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
								'name'  => 'ram:NetPriceProductTradePrice',
								'value' => $price_children,
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
