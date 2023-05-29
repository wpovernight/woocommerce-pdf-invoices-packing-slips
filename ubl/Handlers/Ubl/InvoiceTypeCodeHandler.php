<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class InvoiceTypeCodeHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$invoiceTypeCode = [
			'name' => 'cbc:InvoiceTypeCode',
			'value' => '380',
			'attributes' => [
				'listID' => 'UNCL1001',
				'listAgencyID' => '6',
			]
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_InvoiceTypeCode', $invoiceTypeCode, $data, $options, $this );

		return $data;
	}
}