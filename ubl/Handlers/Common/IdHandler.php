<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IdHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$ID = array(
			'name'  => 'cbc:ID',
			'value' => $this->document->order_document->get_number()->get_formatted(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_ID', $ID, $data, $options, $this );

		return $data;
	}

}
