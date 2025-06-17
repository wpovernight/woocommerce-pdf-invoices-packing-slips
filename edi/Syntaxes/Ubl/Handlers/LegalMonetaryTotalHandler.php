<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LegalMonetaryTotalHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$total         = $this->document->order->get_total();
		$total_inc_tax = $total;
		$total_exc_tax = $total - $this->document->order->get_total_tax();
		$currency      = $this->document->order->get_currency();

		$legal_monetary_total = array(
			'name'  => 'cac:LegalMonetaryTotal',
			'value' => array(
				array(
					'name'       => 'cbc:LineExtensionAmount',
					'value'      => $total_exc_tax,
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'       => 'cbc:TaxExclusiveAmount',
					'value'      => $total_exc_tax,
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'       => 'cbc:TaxInclusiveAmount',
					'value'      => $total_inc_tax,
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'       => 'cbc:PayableAmount',
					'value'      => $total,
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_legal_monetary_total', $legal_monetary_total, $data, $options, $this );

		return $data;
	}

}
