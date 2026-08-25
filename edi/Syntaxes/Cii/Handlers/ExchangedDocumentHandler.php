<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ExchangedDocumentHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$date_format_code = $this->get_date_format_code();
		$php_date_format  = $this->get_php_date_format_from_code( $date_format_code );
		$number_instance  = $this->document->order_document->get_number();
		$date_instance    = $this->document->order_document->get_date();
		
		$exchanged_document = array(
			'name'  => 'rsm:ExchangedDocument',
			'value' => array(
				array(
					'name'  => 'ram:ID',
					'value' => ! empty( $number_instance ) ? $number_instance->get_formatted() : '',
				),
				array(
					'name'  => 'ram:TypeCode',
					'value' => $this->document->get_type_code(),
				),
				array(
					'name'  => 'ram:IssueDateTime',
					'value' => array(
						array(
							'name'       => 'udt:DateTimeString',
							'value'      => ! empty( $date_instance ) ? $date_instance->date_i18n( $php_date_format ) : '',
							'attributes' => array(
								'format' => $date_format_code,
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_exchanged_document', $exchanged_document, $data, $options, $this );

		return $data;
	}

}
