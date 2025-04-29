<?php
namespace WPO\IPS\EInvoice\Sintax\Cii\Handlers;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$exchangedDocument = array(
			'name'  => 'rsm:ExchangedDocument',
			'value' => array(
				array(
					'name'  => 'ram:ID',
					'value' => $this->document->order_document->get_number()->get_formatted(),
				),
				array(
					'name'  => 'ram:TypeCode',
					'value' => '380', // Commercial Invoice
				),
				array(
					'name'  => 'ram:IssueDateTime',
					'value' => array(
						array(
							'name'       => 'udt:DateTimeString',
							'value'      => $this->document->order_document->get_date()->date_i18n( 'Y-m-d' ),
							'attributes' => array(
								'format' => '610', // YYYY-MM-DD
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_ExchangedDocument', $exchangedDocument, $data, $options, $this );

		return $data;
	}

}
