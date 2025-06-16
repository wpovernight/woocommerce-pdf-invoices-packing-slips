<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntax\Cii\Abstracts\AbstractCiiHandler;
use WPO\IPS\EDI\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ApplicableHeaderTradeSettlementHandler extends AbstractCiiHandler {

	public function handle( array $data, array $options = array() ): array {
		$applicableHeaderTradeSettlement = array(
			'name'  => 'ram:ApplicableHeaderTradeSettlement',
			'value' => array_merge(
				array( $this->getPaymentReference() ),
				array( $this->getPaymentMeans() ),
				$this->getTradeTax(),
				array( $this->getPaymentTerms() ),
				array( $this->getMonetarySummation() )
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_settlement', $applicableHeaderTradeSettlement, $data, $options, $this );

		return $data;
	}

	/**
	 * Get the payment reference for the order.
	 *
	 * @return array
	 */
	private function getPaymentReference(): array {
		$order = $this->document->order;

		if ( ! $order ) {
			return array();
		}
		
		$reference = $order->get_order_number(); // Default to WooCommerce order number
		$reference = apply_filters( 'wpo_ips_edi_cii_payment_reference', $reference, $order, $this );

		if ( empty( $reference ) ) {
			return array();
		}
		
		$paymentReference = array(
			'name'  => 'ram:PaymentReference',
			'value' => wpo_ips_edi_sanitize_string( $reference ),
		);

		return apply_filters( 'wpo_ips_edi_cii_payment_reference', $paymentReference, $this );
	}
	
	/**
	 * Get the payment means data for the order.
	 *
	 * @return array
	 */
	private function getPaymentMeans(): array {
		$payment = $this->get_payment_means_data();

		// If no usable payment data, exit early
		if ( empty( $payment['type_code'] ) ) {
			return array();
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
					'value' => wpo_ips_edi_sanitize_string( $payment['account_name'] ),
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

		return apply_filters( 'wpo_ips_edi_cii_payment_means', $node, $this );
	}
	
	/**
	 * Get the trade tax data for the order.
	 *
	 * @return array
	 */
	public function getTradeTax(): array {
		$taxReasons   = TaxesSettings::get_available_reasons();
		$order        = $this->document->order;
		$orderTaxData = $this->document->order_tax_data;

		// Fallback
		if ( empty( $orderTaxData ) ) {
			$orderTaxData = array(
				0 => array(
					'total_ex'  => $order->get_total(),
					'total_tax' => 0,
					'items'     => array(),
					'name'      => '',
				),
			);
		}
		
		$orderTaxData = apply_filters( 'wpo_ips_edi_cii_order_tax_data', $orderTaxData, $this );
		if ( empty( $orderTaxData ) ) {
			return array(); // No tax data available
		}
		
		$tradeTax = array();

		foreach ( $orderTaxData as $item ) {
			$percent   = ! empty( $item['percentage'] )       ? $item['percentage']       : 0;
			$category  = ! empty( $item['category'] )         ? $item['category']         : wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $order );
			$reasonKey = ! empty( $item['reason'] )           ? $item['reason']           : wpo_ips_edi_get_tax_data_from_fallback( 'reason', null, $order );
			$reason    = ! empty( $taxReasons[ $reasonKey ] ) ? $taxReasons[ $reasonKey ] : $reasonKey;
			$scheme    = ! empty( $item['scheme'] )           ? $item['scheme']           : wpo_ips_edi_get_tax_data_from_fallback( 'scheme', null, $order );

			$taxNode = array(
				'name'  => 'ram:ApplicableTradeTax',
				'value' => array(
					array(
						'name'       => 'ram:CalculatedAmount',
						'value'      => wc_round_tax_total( $item['total_tax'] ),
						'attributes' => array(
							'currencyID' => $order->get_currency(),
						),
					),
					array(
						'name'  => 'ram:TypeCode',
						'value' => strtoupper( $scheme ),
					),
					array(
						'name'       => 'ram:BasisAmount',
						'value'      => wc_round_tax_total( $item['total_ex'] ),
						'attributes' => array(
							'currencyID' => $order->get_currency(),
						),
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
			if ( 'none' !== $reasonKey ) {
				$taxNode['value'][] = array(
					'name'  => 'ram:ExemptionReasonCode',
					'value' => $reasonKey,
				);
				$taxNode['value'][] = array(
					'name'  => 'ram:ExemptionReason',
					'value' => $reason,
				);
			}

			$tradeTax[] = apply_filters( 'wpo_ips_edi_cii_trade_tax', $taxNode, $this );
		}

		return $tradeTax;
	}
	
	/**
	 * Get the payment terms for the order.
	 *
	 * @return array
	 */
	private function getPaymentTerms(): array {
		$due_date_timestamp = is_callable( array( $this->document->order_document, 'get_due_date' ) )
			? $this->document->order_document->get_due_date()
			: 0;

		if ( empty( $due_date_timestamp ) ) {
			return array();
		}
		
		$date_format_code = $this->get_date_format_code();
		$php_date_format  = $this->get_php_date_format_from_code( $date_format_code );
		$due_date         = $this->normalize_date( $due_date_timestamp, $php_date_format );
		
		if ( ! $this->validate_date_format( $due_date, $date_format_code ) ) {
			return array();
		}

		$paymentTerms = array(
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

		return apply_filters( 'wpo_ips_edi_cii_payment_terms', $paymentTerms, $this );
	}
	
	/**
	 * Get the monetary summation for the order.
	 *
	 * @return array
	 */
	public function getMonetarySummation(): array {
		$order    = $this->document->order;
		$currency = $order ? $order->get_currency() : get_woocommerce_currency();

		$total         = $order->get_total();
		$total_inc_tax = $total;
		$total_tax     = $order->get_total_tax();
		$total_exc_tax = $total - $total_tax;

		$monetarySummation = array(
			'name'  => 'ram:SpecifiedTradeSettlementHeaderMonetarySummation',
			'value' => array(
				array(
					'name'       => 'ram:LineTotalAmount',
					'value'      => $total_exc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:TaxBasisTotalAmount',
					'value'      => $total_exc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:TaxTotalAmount',
					'value'      => $total_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:GrandTotalAmount',
					'value'      => $total_inc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'ram:DuePayableAmount',
					'value'      => $total_inc_tax,
					'attributes' => array( 'currencyID' => $currency ),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_cii_monetary_summation', $monetarySummation, $this );
	}

}
