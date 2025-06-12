<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers;

use WPO\IPS\EDI\Syntax\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentContextHandler extends AbstractCiiHandler {

	public function handle( array $data, array $options = array() ): array {
		$exchangedDocumentContext = array(
			'name'  => 'rsm:ExchangedDocumentContext',
			'value' => array(
				array(
					'name'  => 'ram:GuidelineSpecifiedDocumentContextParameter',
					'value' => array(
						array(
							'name'  => 'ram:ID',
							'value' => $this->format_document->get_context(),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_exchanged_document_context', $exchangedDocumentContext, $data, $options, $this );

		return $data;
	}

}
