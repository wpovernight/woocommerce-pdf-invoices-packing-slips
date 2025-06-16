<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IdHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$ID = array(
			'name'  => 'cbc:ID',
			'value' => $this->document->order_document->get_number()->get_formatted(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_id', $ID, $data, $options, $this );

		return $data;
	}

}
