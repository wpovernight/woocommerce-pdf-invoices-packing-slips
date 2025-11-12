<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TypeCodeHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$root_element = $this->document->get_root_element();
		
		$type_code    = array(
			'name'  => "cbc:{$root_element}TypeCode",
			'value' => $this->document->get_type_code(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_type_code', $type_code, $data, $options, $this );
		return $data;
	}

}
