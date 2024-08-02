<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DocumentCurrencyCodeHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$documentCurrencyCode = array(
			'name'       => 'cbc:DocumentCurrencyCode',
			'value'      => $this->document->order->get_currency(),
			'attributes' => array(
				'listID'       => 'ISO4217',
				'listAgencyID' => '6',
			),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_DocumentCurrencyCode', $documentCurrencyCode, $data, $options, $this );

		return $data;
	}

}
