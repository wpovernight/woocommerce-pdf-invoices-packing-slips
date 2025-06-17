<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DocumentCurrencyCodeHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$document_currency_code = array(
			'name'       => 'cbc:DocumentCurrencyCode',
			'value'      => $this->document->order->get_currency(),
			'attributes' => array(
				'listID'       => 'ISO4217',
				'listAgencyID' => '6',
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_document_currency_code', $document_currency_code, $data, $options, $this );

		return $data;
	}

}
