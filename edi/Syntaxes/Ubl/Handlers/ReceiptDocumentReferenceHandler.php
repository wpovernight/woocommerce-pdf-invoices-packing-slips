<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ReceiptDocumentReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		if ( $this->document->order_document && $this->document->order_document->exists() && function_exists( 'WPO_WCPDF_Pro' ) ) {
			$order   = \wpo_ips_edi_get_parent_order( $this->document->order );
			$receipt = \wcpdf_get_document( 'receipt', $order );
			
			if ( $receipt && $receipt->exists() ) {
				$number_instance = $receipt->get_number();
				
				$receipt_document_reference = array(
					'name'  => 'cac:ReceiptDocumentReference',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => ! empty( $number_instance ) ? $number_instance->get_formatted() : '',
						),
					),
				);

				$data[] = apply_filters( 'wpo_ips_edi_ubl_receipt_document_reference', $receipt_document_reference, $data, $options, $this );
			}
		}

		return $data;
	}

}
