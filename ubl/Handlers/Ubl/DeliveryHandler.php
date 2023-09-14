<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeliveryHandler extends UblHandler {
	
	public function handle( $data, $options = array() ) {
		$delivery = array(
			'name'  => 'cac:Delivery',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_Delivery', $delivery, $data, $options, $this );

		return $data;
	}
	
}