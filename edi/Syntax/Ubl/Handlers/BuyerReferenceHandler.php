<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BuyerReferenceHandler extends AbstractUblHandler {

	public function handle( array $data, array $options = array() ): array {
		$buyerReference = array(
			'name'  => 'cbc:BuyerReference',
			'value' => $this->document->order->get_id(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_buyer_reference', $buyerReference, $data, $options, $this );

		return $data;
	}

}
