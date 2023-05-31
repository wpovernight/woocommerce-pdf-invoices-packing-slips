<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;
use WPO\WC\UBL\Models\Order;

defined( 'ABSPATH' ) or exit;

class AdditionalDocumentReferenceHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		if ( $this->document->order_document && $this->document->order_document->exists() && $this->document->order_document->get_setting( 'include_encrypted_pdf', false, 'ubl' ) ) {
			$additionalDocumentReference = [
				'name'  => 'cac:AdditionalDocumentReference',
				'value' => [
					[
						'name'  => 'cbc:ID',
						'value' => ! empty( $this->document->order_document->get_number() ) ? $this->document->order_document->get_number()->get_formatted() : '',
					],
					[
						'name'  => 'cbc:DocumentType',
						'value' => ! empty( $this->document->order_document->get_title() ) ? 'PDF '.$this->document->order_document->get_title() : '',
					],
					[
						'name'  => 'cac:Attachment',
						'value' => [
							'name'       => 'cbc:EmbeddedDocumentBinaryObject',
							'value'      => ! empty( $this->document->order_document->get_pdf() ) ? base64_encode( $this->document->order_document->get_pdf() ) : '',
							'attributes' => [
								'mimeCode' => 'application/pdf',
								'filename' => ! empty( $this->document->order_document->get_filename() ) ? $this->document->order_document->get_filename() : '',
							]
						],
					],
				]
			];
	
			$data[] = apply_filters( 'wpo_wc_ubl_handle_AdditionalDocumentReference', $additionalDocumentReference, $data, $options, $this );
		}

		return $data;
	}
}