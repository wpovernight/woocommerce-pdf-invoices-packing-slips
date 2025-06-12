<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DocumentCurrencyCodeHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$documentCurrencyCode = array(
			'name'       => 'cbc:DocumentCurrencyCode',
			'value'      => $this->document->order->get_currency(),
			'attributes' => array(
				'listID'       => 'ISO4217',
				'listAgencyID' => '6',
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_document_currency_code', $documentCurrencyCode, $data, $options, $this );

		return $data;
	}

}
