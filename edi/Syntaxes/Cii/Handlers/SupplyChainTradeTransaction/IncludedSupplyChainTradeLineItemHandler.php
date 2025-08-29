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
			$tax_subtotal = array();
			$taxes        = $item->get_taxes();
			$line_total   = $item->get_total();

			foreach ( $taxes['total'] as $tax_id => $tax_amount ) {
				if ( ! is_numeric( $tax_amount ) ) {
					continue;
				}

				$tax_info = isset( $tax_data[ $tax_id ] ) ? $tax_data[ $tax_id ] : array();
				$category = strtoupper( $tax_info['category'] ?? 'S' );
				$scheme   = strtoupper( $tax_info['scheme'] ?? 'VAT' );
				$rate     = round( $tax_info['percentage'] ?? 0, 2 );

				$tax_node = array(
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
					)
				);

				if ( $rate > 0 ) {
					$tax_node['value'][] = array(
						'name'  => 'ram:RateApplicablePercent',
						'value' => $rate,
					);
				}

				$tax_subtotal[] = $tax_node;
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
								'value' => array(
									array(
										'name' => 'ram:ChargeAmount',
										'value' => round( $item->get_total() / max( $item->get_quantity(), 1 ), 2 ),
									),
								)
							),
						),
					),
					array(
						'name'  => 'ram:SpecifiedLineTradeDelivery',
						'value' => array(
							array(
								'name'       => 'ram:BilledQuantity',
								'value'      => $item->get_quantity(),
								'attributes' => array(
									'unitCode' => 'C62',
								),
							),
						),
					),
					array(
						'name'  => 'ram:SpecifiedLineTradeSettlement',
						'value' => array_merge(
							$tax_subtotal,
							array(
								array(
									'name'  => 'ram:SpecifiedTradeSettlementLineMonetarySummation',
									'value' => array(
										array(
											'name' => 'ram:LineTotalAmount',
											'value' => round( $line_total, 2 ),
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
