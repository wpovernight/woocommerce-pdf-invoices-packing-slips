<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AllowanceChargeHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$allowance_charge = array(
			'name'  => 'cac:AllowanceCharge',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_allowance_charge', $allowance_charge, $data, $options, $this );

		return $data;
	}

}
