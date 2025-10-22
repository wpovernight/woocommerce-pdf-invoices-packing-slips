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
			// Determine the line tax
			$type               = $item->get_type();
			$tax_data_container = ( 'line_item' === $type ) ? 'line_tax_data' : 'taxes';
			$tax_data_key       = ( 'line_item' === $type ) ? 'subtotal'      : 'total';

			$line_tax_data = $item[ $tax_data_container ] ?? array();
			$rows          = ( isset( $line_tax_data[ $tax_data_key ] ) && is_array( $line_tax_data[ $tax_data_key ] ) )
				? $line_tax_data[ $tax_data_key ]
				: array();

			$scheme   = 'VAT';
			$category = null;
			$rate     = 0.0;

			// Consider only non-zero numeric rows (first one wins)
			foreach ( $rows as $tax_id => $tax_amt ) {
				if ( ! is_numeric( $tax_amt ) || (float) $tax_amt === 0.0 ) {
					continue;
				}
				
				$row      = $tax_data[ $tax_id ] ?? array();
				$scheme   = strtoupper( $row['scheme']   ?? 'VAT' );
				$category = strtoupper( $row['category'] ?? 'Z' );
				$rate     = (float) ( $row['percentage'] ?? 0 );
				break;
			}

			// Fallback: no non-zero rows -> Zero-rated (Z / 0%)
			if ( null === $category ) {
				$scheme   = 'VAT';
				$category = 'Z';
				$rate     = 0.0;
			}

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
							'value' => $category,
						),
						array(
							'name'  => 'ram:RateApplicablePercent',
							'value' => $this->format_decimal( $rate, 1 ),
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
