<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LegalMonetaryTotalHandler extends AbstractUblHandler {

	public function handle( array $data, array $options = array() ): array {
		$total         = $this->document->order->get_total();
		$total_inc_tax = $total;
		$total_exc_tax = $total - $this->document->order->get_total_tax();

		$legalMonetaryTotal = array(
			'name'  => 'cac:LegalMonetaryTotal',
			'value' => array(
				array(
					'name'       => 'cbc:LineExtensionAmount',
					'value'      => $total_exc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				array(
					'name'       => 'cbc:TaxExclusiveAmount',
					'value'      => $total_exc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				array(
					'name'       => 'cbc:TaxInclusiveAmount',
					'value'      => $total_inc_tax,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				array(
					'name'       => 'cbc:PayableAmount',
					'value'      => $total,
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_legal_monetary_total', $legalMonetaryTotal, $data, $options, $this );

		return $data;
	}

}
