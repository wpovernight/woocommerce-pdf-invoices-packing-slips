<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;
use WPO\WC\UBL\Models\Order;

defined( 'ABSPATH' ) or exit;

class OrderReferenceHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$orderReference = [
			'name' => 'cac:OrderReference',
			'value' => [
				'name' => 'cbc:ID',
				'value' => $this->document->order->get_id(),
			],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_OrderReference', $orderReference, $data, $options, $this );

		return $data;
	}
}