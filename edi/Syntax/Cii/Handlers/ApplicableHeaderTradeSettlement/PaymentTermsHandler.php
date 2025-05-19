<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentTermsHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$due_date_timestamp = is_callable( array( $this->document->order_document, 'get_due_date' ) )
			? $this->document->order_document->get_due_date()
			: 0;

		if ( empty( $due_date_timestamp ) ) {
			return $data;
		}
		
		$date_format_code = $this->get_date_format_code();
		$php_date_format  = $this->get_php_date_format_from_code( $date_format_code );
		$due_date         = $this->normalize_date( $due_date_timestamp, $php_date_format );
		
		if ( ! $this->validate_cii_date_format( $due_date, $date_format_code ) ) {
			return $data;
		}

		$node = array(
			'name'  => 'ram:SpecifiedTradePaymentTerms',
			'value' => array(
				array(
					'name'  => 'ram:DueDateDateTime',
					'value' => array(
						array(
							'name'       => 'udt:DateTimeString',
							'value'      => $due_date,
							'attributes' => array(
								'format' => $date_format_code,
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_handle_PaymentTerms', $node, $data, $options, $this );

		return $data;
	}

}
