<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AllowanceChargeHandler extends UblHandler {
	
	public function handle( $data, $options = array() ) {
		$allowanceCharge = array(
			'name'  => 'cac:AllowanceCharge',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AllowanceCharge', $allowanceCharge, $data, $options, $this );

		return $data;
	}
	
}