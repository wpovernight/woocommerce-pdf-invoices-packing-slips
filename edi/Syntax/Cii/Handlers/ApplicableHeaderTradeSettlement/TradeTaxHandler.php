<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement;

use WPO\IPS\EDI\Abstracts\AbstractHandler;
use WPO\IPS\EDI\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TradeTaxHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$taxReasons   = TaxesSettings::get_available_reasons();
		$order        = $this->document->order;
		$orderTaxData = $this->document->order_tax_data;

		// Fallback
		if ( empty( $orderTaxData ) ) {
			$orderTaxData = array(
				0 => array(
					'total_ex'  => $order->get_total(),
					'total_tax' => 0,
					'items'     => array(),
					'name'      => '',
				),
			);
		}

		foreach ( apply_filters( 'wpo_ips_edi_cii_order_tax_data', $orderTaxData, $data, $options, $this ) as $item ) {
			$percent   = ! empty( $item['percentage'] ) ? $item['percentage'] : 0;
			$category  = ! empty( $item['category'] ) ? $item['category'] : wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $order );
			$reasonKey = ! empty( $item['reason'] ) ? $item['reason'] : wpo_ips_edi_get_tax_data_from_fallback( 'reason', null, $order );
			$reason    = ! empty( $taxReasons[ $reasonKey ] ) ? $taxReasons[ $reasonKey ] : $reasonKey;
			$scheme    = ! empty( $item['scheme'] ) ? $item['scheme'] : wpo_ips_edi_get_tax_data_from_fallback( 'scheme', null, $order );

			$taxNode = array(
				'name'  => 'ram:ApplicableTradeTax',
				'value' => array(
					array(
						'name'       => 'ram:CalculatedAmount',
						'value'      => wc_round_tax_total( $item['total_tax'] ),
						'attributes' => array(
							'currencyID' => $order->get_currency(),
						),
					),
					array(
						'name'       => 'ram:BasisAmount',
						'value'      => wc_round_tax_total( $item['total_ex'] ),
						'attributes' => array(
							'currencyID' => $order->get_currency(),
						),
					),
					array(
						'name'  => 'ram:CategoryCode',
						'value' => strtoupper( $category ),
					),
					array(
						'name'  => 'ram:TypeCode',
						'value' => strtoupper( $scheme ),
					),
					array(
						'name'  => 'ram:RateApplicablePercent',
						'value' => round( $percent, 1 ),
					),
				),
			);

			// Exemption if any
			if ( 'none' !== $reasonKey ) {
				$taxNode['value'][] = array(
					'name'  => 'ram:ExemptionReasonCode',
					'value' => $reasonKey,
				);
				$taxNode['value'][] = array(
					'name'  => 'ram:ExemptionReason',
					'value' => $reason,
				);
			}

			$data[] = apply_filters( 'wpo_ips_edi_cii_handle_TradeTax', $taxNode, $item, $options, $this );
		}

		return $data;
	}

}
