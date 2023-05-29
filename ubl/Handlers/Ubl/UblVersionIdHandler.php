<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class UblVersionIdHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$UBLVersionID = [
			'name' => 'cbc:UBLVersionID',
			'value' => '2.1',
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_UBLVersionID', $UBLVersionID, $data, $options, $this );

		return $data;
	}
}