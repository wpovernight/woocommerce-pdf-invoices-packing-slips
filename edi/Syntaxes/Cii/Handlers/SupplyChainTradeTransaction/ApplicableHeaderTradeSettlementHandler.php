<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;
use WPO\IPS\EDI\Standards\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ApplicableHeaderTradeSettlementHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$applicable_header_trade_settlement = array(
			'name'  => 'ram:ApplicableHeaderTradeSettlement',
			'value' => array_filter( array(
				$this->get_payment_reference(),
				$this->get_payment_means(),
				$this->get_trade_tax(),
				$this->get_payment_terms(),
				$this->get_monetary_summation(),
			) ),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_settlement', $applicable_header_trade_settlement, $data, $options, $this );

		return $data;
	}

	/**
	 * Get the payment reference for the order.
	 *
	 * @return array|null
	 */
	public function get_payment_reference(): ?array {
		$order     = $this->document->order;
		$reference = $order->get_order_number(); // Default to WooCommerce order number
		$reference = apply_filters( 'wpo_ips_edi_cii_payment_reference', $reference, $order, $this );

		if ( empty( $reference ) ) {
			wpo_ips_edi_log( 'CII ApplicableHeaderTradeSettlementHandler: Payment reference is empty.' );
			return null;
		}
		
		$payment_reference = array(
			'name'  => 'ram:PaymentReference',
			'value' => wpo_ips_edi_sanitize_string( $reference ),
		);

		return apply_filters( 'wpo_ips_edi_cii_payment_reference', $payment_reference, $this );
	}
	
	/**
	 * Get the payment means data for the order.
	 *
	 * @return array|null
	 */
	public function get_payment_means(): ?array {
		$payment = $this->get_payment_means_data();

		// If no usable payment data, exit early
		if ( empty( $payment['type_code'] ) ) {
			return null;
		}

		$payment_means = array(
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
					'value' => wpo_ips_edi_sanitize_string( $payment['account_name'] ),
				);
			}

			$payment_means['value'][] = $account;
		}

		// Add transaction ID if applicable (e.g., PayPal, Stripe)
		elseif ( ! empty( $payment['transaction_id'] ) ) {
			$payment_means['value'][] = array(
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
			$payment_means['value'][] = array(
				'name'  => 'ram:PayeeSpecifiedCreditorFinancialInstitution',
				'value' => array(
					array(
						'name'  => 'ram:BICID',
						'value' => strtoupper( $payment['bic'] ),
					),
				),
			);
		}

		return apply_filters( 'wpo_ips_edi_cii_payment_means', $payment_means, $this );
	}
	
	/**
	 * Get the trade tax data for the order.
	 *
	 * @return array|null
	 */
	public function get_trade_tax(): ?array {
		$tax_reasons    = EN16931::get_available_reasons();
		$order          = $this->document->order;
		$order_tax_data = $this->document->order_tax_data;

		// Fallback
		if ( empty( $order_tax_data ) ) {
			$order_tax_data = array(
				0 => array(
					'total_ex'  => $order->get_total(),
					'total_tax' => 0,
					'items'     => array(),
					'name'      => '',
				),
			);
		}
		
		$order_tax_data = apply_filters( 'wpo_ips_edi_cii_order_tax_data', $order_tax_data, $this );
		if ( empty( $order_tax_data ) ) {
			return null; // No tax data available
		}
		
		$trade_tax = array();

		foreach ( $order_tax_data as $item ) {
			$percent    = ! empty( $item['percentage'] )
				? $item['percentage']
				: 0;
			$category   = ! empty( $item['category'] )
				? $item['category']
				: wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $order );
			$reason_key = ! empty( $item['reason'] )
				? $item['reason']
				: wpo_ips_edi_get_tax_data_from_fallback( 'reason', null, $order );
			$reason     = ! empty( $tax_reasons[ $reason_key ] )
				? $tax_reasons[ $reason_key ]
				: $reason_key;
			$scheme     = ! empty( $item['scheme'] )
				? $item['scheme']
				: wpo_ips_edi_get_tax_data_from_fallback( 'scheme', null, $order );

			$tax_node = array(
				'name'  => 'ram:ApplicableTradeTax',
				'value' => array(
					array(
						'name'  => 'ram:CalculatedAmount',
						'value' => wc_round_tax_total( $item['total_tax'] ),
					),
					array(
						'name'  => 'ram:TypeCode',
						'value' => strtoupper( $scheme ),
					),
					array(
						'name'  => 'ram:BasisAmount',
						'value' => wc_round_tax_total( $item['total_ex'] ),
					),
					array(
						'name'  => 'ram:CategoryCode',
						'value' => strtoupper( $category ),
					),
					array(
						'name'  => 'ram:RateApplicablePercent',
						'value' => round( $percent, 1 ),
					),
				),
			);

			// Exemption if any
			if ( 'none' !== $reason_key ) {
				$tax_node['value'][] = array(
					'name'  => 'ram:ExemptionReasonCode',
					'value' => $reason_key,
				);
				$tax_node['value'][] = array(
					'name'  => 'ram:ExemptionReason',
					'value' => $reason,
				);
			}

			$trade_tax[] = $tax_node;
		}

		return apply_filters( 'wpo_ips_edi_cii_trade_tax', $trade_tax, $this );
	}
	
	/**
	 * Get the payment terms for the order.
	 *
	 * @return array|null
	 */
	public function get_payment_terms(): ?array {
		$due_date_timestamp = is_callable( array( $this->document->order_document, 'get_due_date' ) )
			? $this->document->order_document->get_due_date()
			: 0;

		if ( empty( $due_date_timestamp ) ) {
			return null;
		}
		
		$date_format_code = $this->get_date_format_code();
		$php_date_format  = $this->get_php_date_format_from_code( $date_format_code );
		$due_date         = $this->normalize_date( $due_date_timestamp, $php_date_format );
		
		if ( ! $this->validate_date_format( $due_date, $date_format_code ) ) {
			wpo_ips_edi_log( 'CII ApplicableHeaderTradeSettlementHandler: Invalid due date format.' );
			return null;
		}

		$payment_terms = array(
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

		return apply_filters( 'wpo_ips_edi_cii_payment_terms', $payment_terms, $this );
	}
	
	/**
	 * Get the monetary summation for the order.
	 *
	 * @return array|null
	 */
	public function get_monetary_summation(): ?array {
		$order           = $this->document->order;
		$total           = $order->get_total();
		$total_tax       = $order->get_total_tax();
		$total_exc_tax   = $total - $total_tax;
		$total_inc_tax   = $total;
		$currency        = $order->get_currency();
		$has_due_days    = ! empty( $this->get_due_date_days() );

		$prepaid_amount  = $has_due_days ? 0 : $total_inc_tax;
		$payable_amount  = $total_inc_tax - $prepaid_amount;
		$rounding_diff   = round( $total - $total_inc_tax, 2 );

		if ( abs( $rounding_diff ) >= 0.01 ) {
			$payable_amount += $rounding_diff;
		}

		$monetary_summation = array(
			'name'  => 'ram:SpecifiedTradeSettlementHeaderMonetarySummation',
			'value' => array(
				array(
					'name'  => 'ram:LineTotalAmount',
					'value' => $total_exc_tax,
				),
				array(
					'name'  => 'ram:TaxBasisTotalAmount',
					'value' => $total_exc_tax,
				),
				array(
					'name'       => 'ram:TaxTotalAmount',
					'value'      => $total_tax,
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'  => 'ram:GrandTotalAmount',
					'value' => $total_inc_tax,
				),
			),
		);

		if ( ! $has_due_days ) {
			$monetary_summation['value'][] = array(
				'name'  => 'ram:TotalPrepaidAmount',
				'value' => $prepaid_amount,
			);
		}

		if ( abs( $rounding_diff ) >= 0.01 ) {
			$monetary_summation['value'][] = array(
				'name'  => 'ram:RoundingAmount',
				'value' => $rounding_diff,
			);
		}

		$monetary_summation['value'][] = array(
			'name'  => 'ram:DuePayableAmount',
			'value' => $payable_amount,
		);

		return apply_filters( 'wpo_ips_edi_cii_monetary_summation', $monetary_summation, $this );
	}


}
