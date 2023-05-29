<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class DocumentCurrencyCodeHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$documentCurrencyCode = [
			'name' => 'cbc:DocumentCurrencyCode',
			'value' => $this->document->order->get_currency(),
			'attributes' => [
				'listID' => 'ISO4217',
				'listAgencyID' => '6',
			]
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_DocumentCurrencyCode', $documentCurrencyCode, $data, $options, $this );

		return $data;
	}
}