<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblVersionIdHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$UBLVersionID = array(
			'name'  => 'cbc:UBLVersionID',
			'value' => '2.1',
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_UBLVersionID', $UBLVersionID, $data, $options, $this );

		return $data;
	}

}
