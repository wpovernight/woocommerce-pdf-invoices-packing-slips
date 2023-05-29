<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class AllowanceChargeHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$allowanceCharge = [
			'name' => 'cac:AllowanceCharge',
			'value' => [],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AllowanceCharge', $allowanceCharge, $data, $options, $this );

		return $data;
	}
}