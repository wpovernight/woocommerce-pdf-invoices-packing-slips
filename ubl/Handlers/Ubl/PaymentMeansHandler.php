<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends UblHandler {
	
	public function handle( $data, $options = array() ) {
		$payment_means = array(
			'name'  => 'cac:PaymentMeans',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_PaymentMeans', $payment_means, $data, $options, $this );

		return $data;
	}
	
}