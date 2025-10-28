<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$payment = $this->get_payment_means_data();
		
		if ( ! $payment ) {
			wpo_ips_edi_log(
				sprintf(
					'UBL PaymentMeans: No payment means data in order %d',
					$this->document->order->get_id()
				),
				'error'
			);
			return $data; // No payment means data available
		}

		// If no usable type code, skip
		if ( empty( $payment['type_code'] ) ) {
			wpo_ips_edi_log(
				sprintf(
					'UBL PaymentMeans: No usable type code in order %d',
					$this->document->order->get_id()
				),
				'error'
			);
			return $data;
		}

		$payment_means = array(
			'name'  => 'cac:PaymentMeans',
			'value' => array(
				array(
					'name'  => 'cbc:PaymentMeansCode',
					'value' => $payment['type_code'],
				),
			),
		);

		// Add account
		if ( ! empty( $payment['iban'] ) || ! empty( $payment['account_number'] ) ) {
			$account_id = ! empty( $payment['iban'] )
				? strtoupper( preg_replace( '/\s+/', '', $payment['iban'] ) )
				: $payment['account_number'];
				
			$account = array(
				'name'  => 'cac:PayeeFinancialAccount',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $account_id,
					),
				),
			);

			if ( ! empty( $payment['account_name'] ) ) {
				$account['value'][] = array(
					'name'  => 'cbc:Name',
					'value' => wpo_ips_edi_sanitize_string( $payment['account_name'] ),
				);
			}

			$payment_means['value'][] = $account;

		// Add transaction ID
		} elseif ( ! empty( $payment['transaction_id'] ) ) {
			$name = ucfirst( $payment['method'] ); // PayPal, Stripe...
			
			$payment_means['value'][] = array(
				'name'  => 'cac:PayeeFinancialAccount',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $payment['transaction_id'],
					),
					array(
						'name'  => 'cbc:Name',
						'value' => $name,
					),
				),
			);
		}

		$data[] = apply_filters( 'wpo_ips_edi_ubl_payment_means', $payment_means, $options, $this );

		return $data;
	}
	
}
