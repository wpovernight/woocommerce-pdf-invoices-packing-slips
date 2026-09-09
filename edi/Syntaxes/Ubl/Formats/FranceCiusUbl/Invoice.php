<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl;

use WPO\IPS\EDI\Syntaxes\Ubl\Formats\Ubl2p1\Invoice as UblInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends UblInvoice {

	public string $slug = 'frcius-ubl-invoice';
	public string $name = 'France CIUS UBL Invoice';

	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	public function get_structure(): array {
		$structure = parent::get_structure();

		if ( isset( $structure['note'] ) ) {
			$structure['note'] = array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers\NoteHandler::class,
			);
		}

		$profile_id = array(
			'profile_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers\ProfileIdHandler::class,
			),
		);

		$keys     = array_keys( $structure );
		$position = array_search( 'customization_id', $keys, true );

		if ( false === $position ) {
			return $structure;
		}

		return array_slice( $structure, 0, $position + 1, true )
			+ $profile_id
			+ array_slice( $structure, $position + 1, null, true );
	}

}
