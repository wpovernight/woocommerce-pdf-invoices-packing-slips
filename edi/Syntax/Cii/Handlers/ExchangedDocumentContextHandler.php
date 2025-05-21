<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentContextHandler extends AbstractHandler {

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

		$data[] = apply_filters( 'wpo_ips_edi_cii_handle_ExchangedDocumentContext', $exchangedDocumentContext, $data, $options, $this );

		return $data;
	}

}
