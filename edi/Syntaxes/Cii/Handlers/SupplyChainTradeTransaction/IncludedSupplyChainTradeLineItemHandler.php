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
		$order    = $this->document->order;
		$items    = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$tax_data = $this->document->order_tax_data;

		foreach ( $items as $item_id => $item ) {
			$taxes = $item->get_taxes();

			// Build exactly one ApplicableTradeTax node per line
			$effective_rate = 0.0;
			$category       = null;
			$scheme         = 'VAT';
			$has_tax_rows   = false;

			if ( ! empty( $taxes['total'] ) && is_array( $taxes['total'] ) ) {
				foreach ( $taxes['total'] as $tax_id => $tax_amount ) {
					if ( ! is_numeric( $tax_amount ) ) {
						continue;
					}
					$has_tax_rows = true;

					$tax_info = $tax_data[ $tax_id ] ?? array();
					// Use the first category we see; keep one per line
					$category       = $category ?: strtoupper( $tax_info['category'] ?? 'S' );
					$scheme         = strtoupper( $tax_info['scheme'] ?? 'VAT' );
					$effective_rate += (float) ( $tax_info['percentage'] ?? 0 );
				}
			}

			// Fallback: shipping with no tax rows -> Zero-rated Z / 0%
			if ( 'shipping' === $item->get_type() && ! $has_tax_rows ) {
				$category       = 'Z';
				$scheme         = 'VAT';
				$effective_rate = 0.0;
			}

			// Always output exactly one node (rate included even if 0)
			$tax_nodes = array(
				array(
					'name'  => 'ram:ApplicableTradeTax',
					'value' => array(
						array(
							'name'  => 'ram:TypeCode',
							'value' => $scheme,
						),
						array(
							'name'  => 'ram:CategoryCode',
							'value' => $category ?: 'S',
						),
						array(
							'name'  => 'ram:RateApplicablePercent',
							'value' => $this->format_decimal( wc_round_tax_total( $effective_rate ) ),
						),
					),
				),
			);

			// Calculate item totals
			if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
				$gross_total = (float) $item->get_subtotal(); // before discounts, ex-VAT
				$net_total   = (float) $item->get_total();    // after discounts, ex-VAT
			} else {
				$gross_total = (float) $item->get_total();
				$net_total   = (float) $item->get_total();
			}

			$qty           = is_a( $item, 'WC_Order_Item_Product' ) ? max( 1, (int) $item->get_quantity() ) : 1;
			$gross_unit    = $qty > 0 ? $gross_total / $qty : 0.0;
			$net_unit      = $qty > 0 ? $net_total   / $qty : 0.0;

			$gross_unit    = round( $gross_unit, 2 );
			$net_unit      = round( $net_unit,   2 );
			$unit_discount = max( 0.0, round( $gross_unit - $net_unit, 2 ) );

			$price_children = array(
				array(
					'name'       => 'ram:ChargeAmount',
					'value'      => $this->format_decimal( $net_unit ),
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
							'value' => $this->format_decimal( $gross_unit ),
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
								'value'      => $qty,
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
											'name' => 'ram:LineTotalAmount',
											'value' => $this->format_decimal( $net_total ),
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
