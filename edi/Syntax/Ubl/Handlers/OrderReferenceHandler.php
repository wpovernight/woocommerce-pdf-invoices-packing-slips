<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class OrderReferenceHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$orderReference = array(
			'name'  => 'cac:OrderReference',
			'value' => array(
				'name'  => 'cbc:ID',
				'value' => $this->document->order->get_id(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_order_reference', $orderReference, $data, $options, $this );

		return $data;
	}

}
