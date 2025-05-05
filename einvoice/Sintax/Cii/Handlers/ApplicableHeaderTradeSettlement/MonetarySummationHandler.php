<?php
namespace WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeSettlement;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MonetarySummationHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$order    = $this->document->order;
		$currency = $order ? $order->get_currency() : get_woocommerce_currency();

		$total         = $order->get_total();
		$total_inc_tax = $total;
		$total_tax     = $order->get_total_tax();
		$total_exc_tax = $total - $total_tax;

		$node = array(
			'name'  => 'ram:SpecifiedTradeSettlementHeaderMonetarySummation',
			'value' => array(
				array(
					'name'       => 'ram:LineTotalAmount',
					'value'      => $total_exc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:TaxBasisTotalAmount',
					'value'      => $total_exc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:TaxTotalAmount',
					'value'      => $total_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:GrandTotalAmount',
					'value'      => $total_inc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:DuePayableAmount',
					'value'      => $total_inc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_MonetarySummation', $node, $data, $options, $this );

		return $data;
	}
	
}
