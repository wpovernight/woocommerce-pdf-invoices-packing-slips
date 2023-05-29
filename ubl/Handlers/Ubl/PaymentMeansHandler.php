<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class PaymentMeansHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$paymentMeans = [
			'name' => 'cac:PaymentMeans',
			'value' => [],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_PaymentMeans', $paymentMeans, $data, $options, $this );

		return $data;
	}
}