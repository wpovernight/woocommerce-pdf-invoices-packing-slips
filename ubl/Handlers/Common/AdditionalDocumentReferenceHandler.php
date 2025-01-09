<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdditionalDocumentReferenceHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		if ( $this->document->order_document && $this->document->order_document->exists() && $this->document->order_document->get_setting( 'include_encrypted_pdf', false, 'ubl' ) ) {
			$additionalDocumentReference = array(
				'name'  => 'cac:AdditionalDocumentReference',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => ! empty( $this->document->order_document->get_number() ) ? $this->document->order_document->get_number()->get_formatted() : '',
					),
					array(
						'name'  => 'cbc:DocumentType',
						'value' => ! empty( $this->document->order_document->get_title() ) ? wpo_ips_ubl_sanitize_string( 'PDF ' . $this->document->order_document->get_title() ) : '',
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

			$data[] = apply_filters( 'wpo_wc_ubl_handle_AdditionalDocumentReference', $additionalDocumentReference, $data, $options, $this );
		}

		return $data;
	}

}
