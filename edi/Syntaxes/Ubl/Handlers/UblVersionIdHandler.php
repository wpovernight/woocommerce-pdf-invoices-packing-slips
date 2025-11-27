<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblVersionIdHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$ubl_version_id = array(
			'name'  => 'cbc:UBLVersionID',
			'value' => '2.1',
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_version_id', $ubl_version_id, $data, $options, $this );

		return $data;
	}

}
