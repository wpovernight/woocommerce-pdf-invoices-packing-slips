<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdditionalDocumentReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		if ( $this->document->order_document && $this->document->order_document->exists() && wpo_ips_edi_embed_encrypted_pdf() ) {
			$number_instance = $this->document->order_document->get_number();
			
			$additional_document_reference = array(
				'name'  => 'cac:AdditionalDocumentReference',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => ! empty( $number_instance ) ? $number_instance->get_formatted() : '',
					),
					array(
						'name'  => 'cac:Attachment',
						'value' => array(
							'name'       => 'cbc:EmbeddedDocumentBinaryObject',
							'value'      => ! empty( $this->document->order_document->get_pdf() ) ? base64_encode( $this->document->order_document->get_pdf() ) : '',
							'attributes' => array(
								'mimeCode' => 'application/pdf',
								'filename' => ! empty( $this->document->order_document->get_filename() ) ? $this->document->order_document->get_filename() : '',
							),
						),
					),
				),
			);

			$data[] = apply_filters( 'wpo_ips_edi_ubl_additional_document_reference', $additional_document_reference, $data, $options, $this );
		}

		return $data;
	}

}
