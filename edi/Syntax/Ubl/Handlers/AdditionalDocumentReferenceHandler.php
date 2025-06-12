<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdditionalDocumentReferenceHandler extends AbstractUblHandler {

	public function handle( $data, $options = array() ) {
		if ( $this->document->order_document && $this->document->order_document->exists() && wpo_ips_edi_embed_encrypted_pdf() ) {
			$additionalDocumentReference = array(
				'name'  => 'cac:AdditionalDocumentReference',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => ! empty( $this->document->order_document->get_number() ) ? $this->document->order_document->get_number()->get_formatted() : '',
					),
					array(
						'name'  => 'cbc:DocumentType',
						'value' => ! empty( $this->document->order_document->get_title() ) ? wpo_ips_edi_sanitize_string( 'PDF ' . $this->document->order_document->get_title() ) : '',
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

			$data[] = apply_filters( 'wpo_ips_edi_ubl_additional_document_reference', $additionalDocumentReference, $data, $options, $this );
		}

		return $data;
	}

}
