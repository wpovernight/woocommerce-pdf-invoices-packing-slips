<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class OrderReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
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
