<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class LegalMonetaryTotalHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$totals   = $this->get_order_payment_totals( $this->document->order );
		$currency = $this->document->order->get_currency();
		
		$line_extension = isset( $totals['lines_net'] )
			? $totals['lines_net']
			: $totals['total_exc_tax'];

		$legal_total = array(
			'name'  => 'cac:LegalMonetaryTotal',
			'value' => array(
				array(
					'name'       => 'cbc:LineExtensionAmount',
					'value'      => $this->format_decimal( $line_extension ),
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'cbc:TaxExclusiveAmount',
					'value'      => $this->format_decimal( $totals['total_exc_tax'] ),
					'attributes' => array( 'currencyID' => $currency ),
				),
				array(
					'name'       => 'cbc:TaxInclusiveAmount',
					'value'      => $this->format_decimal( $totals['total_inc_tax'] ),
					'attributes' => array( 'currencyID' => $currency ),
				),
			),
		);

		// Only include PrepaidAmount when there is an actual prepayment.
		if ( $totals['prepaid_amount'] > 0 ) {
			$legal_total['value'][] = array(
				'name'       => 'cbc:PrepaidAmount',
				'value'      => $this->format_decimal( $totals['prepaid_amount'] ),
				'attributes' => array( 'currencyID' => $currency ),
			);
		}

		// Only include PayableRoundingAmount when materially non-zero.
		if ( abs( $totals['rounding_diff'] ) >= 0.01 ) {
			$legal_total['value'][] = array(
				'name'       => 'cbc:PayableRoundingAmount',
				'value'      => $this->format_decimal( $totals['rounding_diff'] ),
				'attributes' => array( 'currencyID' => $currency ),
			);
		}

		$legal_total['value'][] = array(
			'name'       => 'cbc:PayableAmount',
			'value'      => $this->format_decimal( $totals['payable_amount'] ),
			'attributes' => array( 'currencyID' => $currency ),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_legal_monetary_total', $legal_total, $data, $options, $this );

		return $data;
	}

}
