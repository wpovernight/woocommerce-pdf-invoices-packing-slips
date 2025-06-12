<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends AbstractUblHandler {

	public function handle( $data, $options = array() ) {
		$payment = $this->get_payment_means_data();

		// If no usable type code, skip
		if ( empty( $payment['type_code'] ) ) {
			return $data;
		}

		$node = array(
			'name'  => 'cac:PaymentMeans',
			'value' => array(
				array(
					'name'  => 'cbc:PaymentMeansCode',
					'value' => $payment['type_code'],
				),
			),
		);

		// Add IBAN
		if ( ! empty( $payment['iban'] ) ) {
			$account = array(
				'name'  => 'cac:PayeeFinancialAccount',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => strtoupper( preg_replace( '/\s+/', '', $payment['iban'] ) ),
					),
				),
			);

			if ( ! empty( $payment['account_name'] ) ) {
				$account['value'][] = array(
					'name'  => 'cbc:Name',
					'value' => wpo_ips_edi_sanitize_string( $payment['account_name'] ),
				);
			}

			$node['value'][] = $account;

		// Add transaction ID
		} elseif ( ! empty( $payment['transaction_id'] ) ) {
			$name = ucfirst( $payment['method'] ); // PayPal, Stripe...
			$node['value'][] = array(
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

		// Fallback: instruction note
		if ( ! empty( $payment['title'] ) ) {
			$node['value'][] = array(
				'name'  => 'cbc:InstructionNote',
				'value' => $payment['title'],
			);
		}

		$data[] = apply_filters( 'wpo_ips_edi_ubl_payment_means', $node, $options, $this );

		return $data;
	}
	
}
