<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

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
		$number_instance = $this->document->order_document->get_number();
		
		$id = array(
			'name'  => 'cbc:ID',
			'value' => ! empty( $number_instance ) ? $number_instance->get_formatted() : '',
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_id', $id, $data, $options, $this );

		return $data;
	}

}
