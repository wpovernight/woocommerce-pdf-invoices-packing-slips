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
		$total          = $this->document->order->get_total();
		$total_tax      = $this->document->order->get_total_tax();
		$total_exc_tax  = $total - $total_tax;
		$total_inc_tax  = $total_exc_tax + $total_tax;
		$currency       = $this->document->order->get_currency();
		$has_due_days   = ! empty( $this->get_due_date_days() );

		$prepaid_amount = $has_due_days ? 0 : $total;
		$payable_amount = $total_inc_tax - $prepaid_amount;
		$rounding_diff  = round( $total - $total_inc_tax, 2 );

		if ( abs( $rounding_diff ) >= 0.01 ) {
			$payable_amount += $rounding_diff;
		}

		$legal_total = array(
			'name'  => 'cac:LegalMonetaryTotal',
			'value' => array(
				array(
					'name'       => 'cbc:LineExtensionAmount',
					'value'      => $this->format_decimal( $total_exc_tax ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'       => 'cbc:TaxExclusiveAmount',
					'value'      => $this->format_decimal( $total_exc_tax ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				array(
					'name'       => 'cbc:TaxInclusiveAmount',
					'value'      => $this->format_decimal( $total_inc_tax ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
			),
		);

		if ( ! $has_due_days ) {
			$legal_total['value'][] = array(
				'name'       => 'cbc:PrepaidAmount',
				'value'      => $this->format_decimal( $prepaid_amount ),
				'attributes' => array(
					'currencyID' => $currency,
				),
			);
		}

		if ( abs( $rounding_diff ) >= 0.01 ) {
			$legal_total['value'][] = array(
				'name'       => 'cbc:PayableRoundingAmount',
				'value'      => $this->format_decimal( $rounding_diff ),
				'attributes' => array(
					'currencyID' => $currency,
				),
			);
		}

		$legal_total['value'][] = array(
			'name'       => 'cbc:PayableAmount',
			'value'      => $this->format_decimal( $payable_amount ),
			'attributes' => array(
				'currencyID' => $currency,
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_legal_monetary_total', $legal_total, $data, $options, $this );

		return $data;
	}

}
