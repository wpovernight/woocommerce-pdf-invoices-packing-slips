<?php
namespace WPO\IPS\EInvoice\Sintax\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;
use WPO\IPS\EInvoice\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IncludedSupplyChainTradeLineItemHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$order       = $this->document->order;
		$items       = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );
		$taxReasons  = TaxesSettings::get_available_reasons();
		$taxData     = $this->document->order_tax_data;

		foreach ( $items as $item_id => $item ) {
			$taxSubtotal = array();
			$taxes       = $item->get_taxes();
			$lineTotal   = $item->get_total();

			foreach ( $taxes['total'] as $tax_id => $tax_amount ) {
				if ( ! is_numeric( $tax_amount ) ) {
					continue;
				}

				$taxInfo  = isset( $taxData[ $tax_id ] ) ? $taxData[ $tax_id ] : array();
				$category = strtoupper( $taxInfo['category'] ?? 'S' );
				$scheme   = strtoupper( $taxInfo['scheme'] ?? 'VAT' );
				$rate     = round( $taxInfo['percentage'] ?? 0, 2 );

				$taxNode = array(
					'name'  => 'ram:ApplicableTradeTax',
					'value' => array(
						array(
							'name'  => 'ram:TypeCode',
							'value' => strtoupper( $scheme ),
						),
						array(
							'name'  => 'ram:CategoryCode',
							'value' => strtoupper( $category ),
						),
					)
				);

				if ( $rate > 0 ) {
					$taxNode['value'][] = array(
						'name'  => 'ram:RateApplicablePercent',
						'value' => $rate,
					);
				}

				$taxSubtotal[] = $taxNode;
			}

			$lineItem = array(
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
								'value' => wpo_ips_ubl_sanitize_string( $item->get_name() ),
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
							$taxSubtotal,
							array(
								array(
									'name'  => 'ram:SpecifiedTradeSettlementLineMonetarySummation',
									'value' => array(
										array(
											'name' => 'ram:LineTotalAmount',
											'value' => round( $lineTotal, 2 ),
										),
									),
								),
							)
						),
					),
				),
			);

			$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_IncludedSupplyChainTradeLineItem', $lineItem, $data, $options, $item, $this );
		}

		return $data;
	}

}
