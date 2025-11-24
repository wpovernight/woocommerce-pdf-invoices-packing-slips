<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class NoteHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$document_notes = $this->document->order_document->get_document_notes();
		
		$note = array(
			'name'  => 'cbc:Note',
			'value' => ! empty( $document_notes ) ? \wpo_ips_edi_sanitize_string( $document_notes ) : '',
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_note', $note, $data, $options, $this );
		return $data;
	}

}
