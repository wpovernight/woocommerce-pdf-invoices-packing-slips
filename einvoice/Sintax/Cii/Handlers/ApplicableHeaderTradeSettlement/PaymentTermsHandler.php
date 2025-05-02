<?php
namespace WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PaymentTermsHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$due_date_timestamp = is_callable( array( $this->document->order_document, 'get_due_date' ) )
			? $this->document->order_document->get_due_date()
			: 0;

		$due_date = $this->normalize_date( $due_date_timestamp, 'Y-m-d' ); // 610 => YYYY-MM-DD

		if ( empty( $due_date ) ) {
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
								'format' => '610', // YYYY-MM-DD
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_PaymentTerms', $node, $data, $options, $this );

		return $data;
	}

}
