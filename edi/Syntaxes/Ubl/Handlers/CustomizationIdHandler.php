<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CustomizationIdHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$customization_id = array(
			'name'  => 'cbc:CustomizationID',
			'value' => 'urn:cen.eu:en16931:2017',
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_customization_id', $customization_id, $data, $options, $this );

		return $data;
	}

}
