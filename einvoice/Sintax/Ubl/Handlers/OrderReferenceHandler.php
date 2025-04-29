<?php
namespace WPO\IPS\EInvoice\Sintax\Ubl\Handlers;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

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

		$data[] = apply_filters( 'wpo_wc_ubl_handle_OrderReference', $orderReference, $data, $options, $this );

		return $data;
	}

}
