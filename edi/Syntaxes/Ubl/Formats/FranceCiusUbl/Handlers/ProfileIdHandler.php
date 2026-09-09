<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ProfileIdHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$profile_id = array(
			'name'  => 'cbc:ProfileID',
			'value' => 'B1',
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_profile_id', $profile_id, $data, $options, $this );

		return $data;
	}

}
