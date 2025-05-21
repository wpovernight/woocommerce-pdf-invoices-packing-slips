<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeliveryHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$delivery = array(
			'name'  => 'cac:Delivery',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_Delivery', $delivery, $data, $options, $this );

		return $data;
	}

}
