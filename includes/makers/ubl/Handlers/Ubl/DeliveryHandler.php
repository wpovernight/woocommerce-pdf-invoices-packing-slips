<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Handlers\Ubl;

use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class DeliveryHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$delivery = [
			'name' => 'cac:Delivery',
			'value' => [],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_Delivery', $delivery, $data, $options, $this );

		return $data;
	}
}