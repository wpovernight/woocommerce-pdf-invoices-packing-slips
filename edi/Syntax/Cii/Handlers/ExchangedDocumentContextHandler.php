<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers;

use WPO\IPS\EDI\Syntax\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentContextHandler extends AbstractCiiHandler {

	public function handle( $data, $options = array() ) {
		$exchangedDocumentContext = array(
			'name'  => 'rsm:ExchangedDocumentContext',
			'value' => array(
				array(
					'name'  => 'ram:GuidelineSpecifiedDocumentContextParameter',
					'value' => array(
						array(
							'name'  => 'ram:ID',
							'value' => 'urn:cen.eu:en16931:2017', // Standard EN16931 guideline
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_exchanged_document_context', $exchangedDocumentContext, $data, $options, $this );

		return $data;
	}

}
