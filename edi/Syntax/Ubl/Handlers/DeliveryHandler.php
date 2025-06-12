<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeliveryHandler extends AbstractUblHandler {

	public function handle( $data, $options = array() ) {
		$delivery = array(
			'name'  => 'cac:Delivery',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_delivery', $delivery, $data, $options, $this );

		return $data;
	}

}
