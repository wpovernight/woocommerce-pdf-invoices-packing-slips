<?php
namespace WPO\IPS\EInvoice\Formats\Ubl\Handlers;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentTermsHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$paymentTerms = array(
			'name'  => 'cac:PaymentTerms',
			'value' => array(
				array(
					'name'  => 'cbc:Note',
					'value' => '',
				),
			),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_PaymentTerms', $paymentTerms, $data, $options, $this );

		return $data;
	}

}
