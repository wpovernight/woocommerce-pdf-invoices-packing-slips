<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class InvoiceTypeCodeHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$invoice_type_code = array(
			'name'       => 'cbc:InvoiceTypeCode',
			'value'      => $this->document->get_type_code(),
			'attributes' => array(
				'listID'       => 'UNCL1001',
				'listAgencyID' => '6',
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_invoice_type_code', $invoice_type_code, $data, $options, $this );

		return $data;
	}

}
