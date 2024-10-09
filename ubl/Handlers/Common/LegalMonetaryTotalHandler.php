<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LegalMonetaryTotalHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
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

		$data[] = apply_filters( 'wpo_wc_ubl_handle_LegalMonetaryTotal', $legalMonetaryTotal, $data, $options, $this );

		return $data;
	}

}
