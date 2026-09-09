<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers;

use WPO\IPS\EDI\Standards\EN16931\CIUS\France;
use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class NoteHandler extends AbstractUblHandler {

	/**
	 * Handle the France CIUS mandatory legal notes.
	 *
	 * Each mandatory legal notice is serialized as a separate cbc:Note
	 * using its France CIUS subject code.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		foreach ( array_keys( France::get_mandatory_legal_notes() ) as $code ) {
			$setting_id = 'fr_note_' . strtolower( $code );
			$value      = trim( (string) wpo_ips_edi_get_settings( $setting_id ) );

			if ( '' === $value ) {
				wpo_ips_edi_log(
					sprintf(
						'France CIUS UBL: Mandatory legal note %s is missing for order %d.',
						$code,
						$this->document->order->get_id()
					),
					'error'
				);

				continue;
			}

			$data[] = array(
				'name'  => 'cbc:Note',
				'value' => sprintf(
					'#%s#%s',
					$code,
					wpo_ips_edi_sanitize_string( $value )
				),
			);
		}

		$data[] = apply_filters( 'wpo_ips_edi_ubl_note', $note, $data, $options, $this );
		return $data;
	}

}
