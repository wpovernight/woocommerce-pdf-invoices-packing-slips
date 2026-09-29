<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BillingReferenceHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$order   = \wpo_ips_edi_get_parent_order( $this->document->order );
		$invoice = \wcpdf_get_document( 'invoice', $order );
		
		if ( ! $invoice || ! $invoice->exists() ) {
			return $data;
		}
		
		$number_instance = $invoice->get_number();
		$date_instance   = $invoice->get_date();
		
		$billing_reference = array(
			'name'  => 'cac:BillingReference',
			'value' => array(
				array(
					'name'  => 'cac:InvoiceDocumentReference',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => ! empty( $number_instance ) ? $number_instance->get_formatted() : '',
						),
						array(
							'name'  => 'cbc:IssueDate',
							'value' => ! empty( $date_instance ) ? $date_instance->date_i18n( 'Y-m-d' ) : '',
						),
					)
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_billing_reference', $billing_reference, $data, $options, $this );

		return $data;
	}

}
