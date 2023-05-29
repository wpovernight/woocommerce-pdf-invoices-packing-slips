<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Handlers\Ubl;

use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\UblHandler;
use WPO\WC\PDF_Invoices\Makers\UBL\Models\Order;

defined( 'ABSPATH' ) or exit;

class IdHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$ID = [
			'name' => 'cbc:ID',
			'value' => $this->document->order_document->get_number()->get_formatted(),
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_ID', $ID, $data, $options, $this );

		return $data;
	}
}