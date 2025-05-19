<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$date_format_code  = $this->get_date_format_code();
		$php_date_format   = $this->get_php_date_format_from_code( $date_format_code );
		
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
							'value'      => $this->document->order_document->get_date()->date_i18n( $php_date_format ),
							'attributes' => array(
								'format' => $date_format_code,
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_handle_ExchangedDocument', $exchangedDocument, $data, $options, $this );

		return $data;
	}

}
