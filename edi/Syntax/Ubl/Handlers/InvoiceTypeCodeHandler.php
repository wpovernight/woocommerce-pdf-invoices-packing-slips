<?php
namespace WPO\IPS\EInvoice\Syntax\Ubl\Handlers;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceTypeCodeHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$invoiceTypeCode = array(
			'name'       => 'cbc:InvoiceTypeCode',
			'value'      => '380',
			'attributes' => array(
				'listID'       => 'UNCL1001',
				'listAgencyID' => '6',
			),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_InvoiceTypeCode', $invoiceTypeCode, $data, $options, $this );

		return $data;
	}

}
