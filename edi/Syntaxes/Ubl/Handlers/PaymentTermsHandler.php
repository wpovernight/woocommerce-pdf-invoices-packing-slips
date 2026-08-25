<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentTermsHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$payment_terms = array();
		$due_date_days = $this->get_due_date_days();

		if ( ! empty( $due_date_days ) ) {
			$payment_terms = array(
				'name'  => 'cac:PaymentTerms',
				'value' => array(
					array(
						'name'  => 'cbc:Note',
						'value' => sprintf(
							/* translators: %d: days */
							__( 'Payment due within %d days', 'woocommerce-pdf-invoices-packing-slips' ),
							$due_date_days
						)
					),
				),
			);
		}

		$data[] = apply_filters( 'wpo_ips_edi_ubl_payment_terms', $payment_terms, $data, $options, $this );

		return $data;
	}

}
