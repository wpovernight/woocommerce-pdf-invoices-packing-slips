<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class PaymentTermsHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$paymentTerms = [
			'name' => 'cac:PaymentTerms',
			'value' => [ [
				'name' => 'cbc:Note',
				'value' => '',
			]],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_PaymentTerms', $paymentTerms, $data, $options, $this );

		return $data;
	}
}