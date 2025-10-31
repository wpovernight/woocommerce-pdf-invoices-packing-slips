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

			$gross_unit    = $this->format_decimal( $parts['gross_unit'], 2 );
			$net_unit      = $this->format_decimal( $parts['net_unit'],   2 );
			$unit_discount = max( 0.0, $this->format_decimal( $parts['gross_unit'] - $parts['net_unit'], 2 ) );

			$price_children = array(
				array(
					'name'  => 'ram:ChargeAmount',
					'value' => $this->format_decimal( $parts['net_unit'], 2 ),
				),
				array(
					'name'       => 'ram:BasisQuantity',
					'value'      => 1,
					'attributes' => array(
						'unitCode' => 'C62'
					)
				),
			);

			// Only products can have a price-level discount
			if ( $unit_discount > 0 && is_a( $item, 'WC_Order_Item_Product' ) ) {
				$price_children[] = array(
					'name'  => 'ram:AppliedTradeAllowanceCharge',
					'value' => array(
						array(
							'name'  => 'ram:ChargeIndicator',
							'value' => array(
								array(
									'name'  => 'udt:Indicator',
									'value' => 'false'
								),
							),
						),
						array(
							'name'  => 'ram:ActualAmount',
							'value' => $this->format_decimal( $unit_discount ),
						),
						array(
							'name'  => 'ram:BasisAmount',
							'value' => $gross_unit,
						),
					),
				);
			}

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
						'value' => array(
							array(
								'name' => 'ram:Name',
								'value' => wpo_ips_edi_sanitize_string( $item->get_name() ),
							),
						),
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
								'value'      => $parts['qty'],
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
											'value' => $this->format_decimal( $parts['net_total'] ),
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
