<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class PaymentMeansHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$payment = $this->get_payment_means_data();

		// If no usable payment data, exit early
		if ( empty( $payment['type_code'] ) ) {
			return $data;
		}

		$node = array(
			'name'  => 'ram:SpecifiedTradeSettlementPaymentMeans',
			'value' => array(
				array(
					'name'  => 'ram:TypeCode',
					'value' => $payment['type_code'],
				),
			),
		);

		// Add IBAN if available
		if ( ! empty( $payment['iban'] ) ) {
			$account = array(
				'name'  => 'ram:PayeePartyCreditorFinancialAccount',
				'value' => array(
					array(
						'name'  => 'ram:IBANID',
						'value' => strtoupper( preg_replace( '/\s+/', '', $payment['iban'] ) ),
					),
				),
			);

			if ( ! empty( $payment['account_name'] ) ) {
				$account['value'][] = array(
					'name'  => 'ram:AccountName',
					'value' => wpo_ips_ubl_sanitize_string( $payment['account_name'] ),
				);
			}

			$node['value'][] = $account;
		}

		// Add transaction ID if applicable (e.g., PayPal, Stripe)
		elseif ( ! empty( $payment['transaction_id'] ) ) {
			$node['value'][] = array(
				'name'  => 'ram:PayeePartyCreditorFinancialAccount',
				'value' => array(
					array(
						'name'  => 'ram:ID',
						'value' => $payment['transaction_id'],
					),
				),
			);
		}

		// Add BIC if available (only relevant when IBAN is used)
		if ( ! empty( $payment['bic'] ) ) {
			$node['value'][] = array(
				'name'  => 'ram:PayeeSpecifiedCreditorFinancialInstitution',
				'value' => array(
					array(
						'name'  => 'ram:BICID',
						'value' => strtoupper( $payment['bic'] ),
					),
				),
			);
		}

		$data[] = apply_filters( 'wpo_ips_edi_cii_handle_PaymentMeans', $node, $data, $options, $this );

		return $data;
	}
}
