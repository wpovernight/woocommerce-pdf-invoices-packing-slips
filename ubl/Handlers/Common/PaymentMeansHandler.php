<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$payment_means = array(
			'name'  => 'cac:PaymentMeans',
			'value' => $this->get_payment_means(),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_PaymentMeans', $payment_means, $options, $this );
		
		return $data;
	}

	private function get_payment_means_code( string $payment_method ): string {
		// Map WooCommerce payment methods to Payment Means Code
		// All available codes: https://docs.peppol.eu/poacc/billing/3.0/2024-Q2/codelist/UNCL4461/
		$mapping = apply_filters( 'wpo_wc_ubl_payment_means_code_mapping', array(
			'cod'    => '10',
			'bacs'   => '30',
			'cheque' => '20',
			'paypal' => 'ZZZ',
			'stripe' => 'ZZZ',
		), $this );

		return isset( $mapping[ $payment_method ] ) ? $mapping[ $payment_method ] : '97'; // Default to 'Other'
	}

	public function get_payment_means(): array {
		$payment_method    = $this->document->order->get_payment_method();
		$payment_type_code = $this->get_payment_means_code( $payment_method );

		$payment_means = array(
			array(
				'name'  => 'cbc:PaymentMeansCode',
				'value' => $payment_type_code,
			),
		);

		switch ( $payment_method ) {
			case 'bacs':
				$bank_accounts = get_option(' woocommerce_bacs_accounts', array() );

				if ( ! empty( $bank_accounts ) && is_array( $bank_accounts ) ) {
					$default_account = reset( $bank_accounts ); // Use the first bank account
					
					$payment_means[] = array(
						'name'  => 'cac:PayeeFinancialAccount',
						'value' => array(
							array(
								'name'  => 'cbc:ID',
								'value' => $default_account['iban'] ?? '',
							),
							array(
								'name'  => 'cbc:Name',
								'value' => $default_account['account_name'] ?? $this->document->order_document->get_shop_name(),
							),
						),
					);
				}
				break;

			case 'paypal':
				$paypal_transaction_id = $this->document->order->get_meta( '_transaction_id', true );

				$payment_means[] = array(
					'name'  => 'cac:PayeeFinancialAccount',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => $paypal_transaction_id,
						),
						array(
							'name'  => 'cbc:Name',
							'value' => 'PayPal',
						),
					),
				);
				break;

			case 'stripe':
				$stripe_source_id = $this->document->order->get_meta( '_stripe_source_id', true );

				$payment_means[] = array(
					'name'  => 'cac:PayeeFinancialAccount',
					'value' => array(
						array(
							'name'  => 'cbc:ID',
							'value' => $stripe_source_id,
						),
						array(
							'name'  => 'cbc:Name',
							'value' => 'Stripe',
						),
					),
				);
				break;

			default: // Other Payment Methods
				$payment_means[] = array(
					'name'  => 'cbc:InstructionNote',
					'value' => $this->document->order->get_payment_method_title(),
				);
				break;
		}

		return $payment_means;
	}

}
