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
			wpo_ips_edi_log(
				sprintf(
					'CII ApplicableHeaderTradeSettlementHandler: Payment reference is empty in order %d.',
					$order->get_id()
				),
				'error'
			);
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
			wpo_ips_edi_log(
				sprintf(
					'CII ApplicableHeaderTradeSettlementHandler: No usable payment means data found in order %d.',
					$this->document->order->get_id()
				),
				'error'
			);
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

		// Add account
		if ( ! empty( $payment['iban'] ) || ! empty( $payment['account_number'] ) ) {
			$name  = ! empty( $payment['iban'] ) ? 'ram:IBANID' : 'ram:ProprietaryID';
			$value = ! empty( $payment['iban'] )
				? strtoupper( preg_replace( '/\s+/', '', $payment['iban'] ) )
				: $payment['account_number'];

			$payment_means['value'][] = array(
				'name'  => 'ram:PayeePartyCreditorFinancialAccount',
				'value' => array(
					array(
						'name'  => $name,
						'value' => $value,
					),
				),
			);
		}

		// Add transaction ID if applicable (e.g., PayPal, Stripe)
		if ( ! empty( $payment['transaction_id'] ) ) {
			$payment_means['value'][] = array(
				'name'  => 'ram:Information',
				'value' => wpo_ips_edi_sanitize_string( $payment['transaction_id'] ),
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
		$tax_reasons       = EN16931::get_vatex();
		$grouped_tax_data  = $this->get_grouped_order_tax_data();
		$trade_tax         = array();

		foreach ( $grouped_tax_data as $item ) {
			$percent  = (float) ( $item['percentage']            ?? 0        );
			$category = strtoupper( (string) ( $item['category'] ?? ''     ) );
			$reason   = strtoupper( (string) ( $item['reason']   ?? 'NONE' ) );
			$scheme   = strtoupper( (string) ( $item['scheme']   ?? 'VAT'  ) );

			$basis    = wc_round_tax_total( (float) ( $item['total_ex']  ?? 0 ) );
			$tax      = wc_round_tax_total( (float) ( $item['total_tax'] ?? 0 ) );

			// Skip empty non-Z groups.
			$is_z = ( 'Z' === $category );
			if ( 0.0 === $basis && 0.0 === $tax && ! $is_z ) {
				continue;
			}

			$node = array(
				'name'  => 'ram:ApplicableTradeTax',
				'value' => array(
					array(
						'name'  => 'ram:CalculatedAmount',
						'value' => $this->format_decimal( $tax ),
					),
					array(
						'name'  => 'ram:TypeCode',
						'value' => $scheme ?: 'VAT',
					),
					array(
						'name'  => 'ram:BasisAmount',
						'value' => $this->format_decimal( $basis ),
					),
					array(
						'name'  => 'ram:CategoryCode',
						'value' => $category ?: 'Z',
					),
					array(
						'name'  => 'ram:RateApplicablePercent',
						'value' => $this->format_decimal( $percent, 1 ),
					),
				),
			);

			// Only emit exemption data for 0% non-Z with an explicit reason.
			if ( 0.0 === $percent && 'Z' !== $category && 'NONE' !== $reason ) {
				$reason_mapped = ! empty( $tax_reasons[ $reason ] ) ? $tax_reasons[ $reason ] : $reason;

				$node['value'][] = array(
					'name'  => 'ram:ExemptionReasonCode',
					'value' => $reason_mapped,
				);
				$node['value'][] = array(
					'name'  => 'ram:ExemptionReason',
					'value' => $reason_mapped,
				);
			}

			$trade_tax[] = $node;
		}

		if ( empty( $trade_tax ) ) {
			return null;
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
			wpo_ips_edi_log( 'CII ApplicableHeaderTradeSettlementHandler: Invalid due date format.', 'error' );
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
		$totals   = $this->get_order_payment_totals( $this->document->order );
		$currency = $this->document->order->get_currency();
		
		$line_total = isset( $totals['lines_net'] )
			? $totals['lines_net']
			: $totals['total_exc_tax'];

		$monetary_summation = array(
			'name'  => 'ram:SpecifiedTradeSettlementHeaderMonetarySummation',
			'value' => array(
				array(
					'name'  => 'ram:LineTotalAmount',
					'value' => $this->format_decimal( $line_total ),
				),
				array(
					'name'  => 'ram:TaxBasisTotalAmount',
					'value' => $this->format_decimal( $totals['total_exc_tax'] ),
				),
				array(
					'name'       => 'ram:TaxTotalAmount',
					'value'      => $this->format_decimal( $totals['total_tax'] ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'  => 'ram:GrandTotalAmount',
					'value' => $this->format_decimal( $totals['total_inc_tax'] ),
				),
			),
		);

		// Only output TotalPrepaidAmount when there is an actual prepayment.
		if ( $totals['prepaid_amount'] > 0 ) {
			$monetary_summation['value'][] = array(
				'name'  => 'ram:TotalPrepaidAmount',
				'value' => $this->format_decimal( $totals['prepaid_amount'] ),
			);
		}

		// Only include RoundingAmount when materially non-zero.
		if ( abs( $totals['rounding_diff'] ) >= 0.01 ) {
			$monetary_summation['value'][] = array(
				'name'  => 'ram:RoundingAmount',
				'value' => $this->format_decimal( $totals['rounding_diff'] ),
			);
		}

		$monetary_summation['value'][] = array(
			'name'  => 'ram:DuePayableAmount',
			'value' => $this->format_decimal( $totals['payable_amount'] ),
		);

		return apply_filters( 'wpo_ips_edi_cii_monetary_summation', $monetary_summation, $this );
	}

}
