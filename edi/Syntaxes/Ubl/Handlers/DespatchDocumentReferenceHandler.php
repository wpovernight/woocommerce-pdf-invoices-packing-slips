<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DespatchDocumentReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$despatch_document_reference = array(
			'name'  => 'cac:DespatchDocumentReference', // Delivery Note number reference. To be implemented later: https://github.com/wpovernight/woocommerce-pdf-ips-pro/issues/612
			'value' => array(
				array(
					'name'  => 'cbc:ID',
					'value' => '',
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_despatch_document_reference', $despatch_document_reference, $data, $options, $this );

		return $data;
	}

}
