<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentContextHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$exchanged_document_context = array(
			'name'  => 'rsm:ExchangedDocumentContext',
			'value' => array(
				array(
					'name'  => 'ram:GuidelineSpecifiedDocumentContextParameter',
					'value' => array(
						array(
							'name'  => 'ram:ID',
							'value' => $this->document->format_document->get_context(),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_exchanged_document_context', $exchanged_document_context, $data, $options, $this );

		return $data;
	}

}
