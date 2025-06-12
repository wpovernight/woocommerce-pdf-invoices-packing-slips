<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblVersionIdHandler extends AbstractUblHandler {

	public function handle( $data, $options = array() ) {
		$UBLVersionID = array(
			'name'  => 'cbc:UBLVersionID',
			'value' => '2.1',
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_version_id', $UBLVersionID, $data, $options, $this );

		return $data;
	}

}
