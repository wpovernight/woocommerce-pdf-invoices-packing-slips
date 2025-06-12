<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceTypeCodeHandler extends AbstractUblHandler {

	public function handle( $data, $options = array() ) {
		$invoiceTypeCode = array(
			'name'       => 'cbc:InvoiceTypeCode',
			'value'      => '380',
			'attributes' => array(
				'listID'       => 'UNCL1001',
				'listAgencyID' => '6',
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_invoice_type_code', $invoiceTypeCode, $data, $options, $this );

		return $data;
	}

}
