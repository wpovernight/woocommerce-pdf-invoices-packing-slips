<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IdHandler extends AbstractUblHandler {

	public function handle( array $data, array $options = array() ): array {
		$ID = array(
			'name'  => 'cbc:ID',
			'value' => $this->document->order_document->get_number()->get_formatted(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_id', $ID, $data, $options, $this );

		return $data;
	}

}
