<?php
namespace WPO\IPS\EInvoice\Syntax\Ubl\Handlers;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AllowanceChargeHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$allowanceCharge = array(
			'name'  => 'cac:AllowanceCharge',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AllowanceCharge', $allowanceCharge, $data, $options, $this );

		return $data;
	}

}
