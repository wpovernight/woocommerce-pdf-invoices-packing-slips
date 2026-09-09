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
		$value = trim( (string) wpo_ips_edi_get_settings( 'fr_cadre_de_facturation' ) );

		if ( empty( $value ) ) {
			wpo_ips_edi_log( 'UBL/France CIUS ProfileID: French invoicing context is missing.', 'error' );
			return $data;
		}

		$profile_id = array(
			'name'  => 'cbc:ProfileID',
			'value' => $value,
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_profile_id', $profile_id, $data, $options, $this );

		return $data;
	}

}
