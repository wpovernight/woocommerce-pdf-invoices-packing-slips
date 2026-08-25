<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;
use WPO\IPS\EDI\Standards\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxTotalHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$tax_reasons = EN16931::get_vatex();
		$currency    = $this->document->order->get_currency();

		// Group tax data by rate, category, reason, and scheme
		$grouped_tax_data = $this->get_grouped_order_tax_data();

		// Internal sums for consistency checks.
		$sum_taxable = 0.0;
		$sum_tax     = 0.0;

		$formatted_tax_array = array();

		foreach ( $grouped_tax_data as $item ) {
			$item_tax_percentage = (float) ( $item['percentage']            ?? 0        );
			$item_tax_category   = strtoupper( (string) ( $item['category'] ?? ''     ) );
			$item_tax_reason_key = strtoupper( (string) ( $item['reason']   ?? 'NONE' ) );
			$item_tax_scheme     = strtoupper( (string) ( $item['scheme']   ?? 'VAT'  ) );

			$taxable_raw = (float) ( $item['total_ex'] ?? 0 );

			// Calculate tax for this group
			$tax_calc = wc_round_tax_total( $taxable_raw * $item_tax_percentage / 100 );

			// Internal sums for consistency checks.
			$sum_taxable += $taxable_raw;
			$sum_tax     += $tax_calc;

			$tax_category = array(
				array(
					'name'  => 'cbc:ID',
					'value' => $item_tax_category,
				),
				array(
					'name'  => 'cbc:Percent',
					'value' => $this->format_decimal( $item_tax_percentage, 1 ),
				),
			);
			
			// Only emit exemption reason for 0% non-Z categories and when reason is not NONE.
			if ( 0.0 === $item_tax_percentage && 'Z' !== $item_tax_category && 'NONE' !== $item_tax_reason_key ) {
				// Map reason key to VATEX text/code when present; otherwise keep the key.
				$item_tax_reason = ! empty( $tax_reasons[ $item_tax_reason_key ] )
					? $tax_reasons[ $item_tax_reason_key ]
					: $item_tax_reason_key;
				
				$tax_category[] = array(
					'name'  => 'cbc:TaxExemptionReasonCode',
					'value' => $item_tax_reason_key,
				);
				$tax_category[] = array(
					'name'  => 'cbc:TaxExemptionReason',
					'value' => $item_tax_reason,
				);
			}
			
			$tax_category[] = array(
				'name'  => 'cac:TaxScheme',
				'value' => array(
					array(
						'name'  => 'cbc:ID',
						'value' => $item_tax_scheme,
					),
				),
			);

			$formatted_tax_array[] = array(
				'name'  => 'cac:TaxSubtotal',
				'value' => array(
					array(
						'name'       => 'cbc:TaxableAmount',
						'value'      => $this->format_decimal( $taxable_raw ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => $this->format_decimal( $tax_calc ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'  => 'cac:TaxCategory',
						'value' => $tax_category,
					),
				),
			);
		}

		// Overall tax total = sum of TaxAmount in subtotals.
		$total_tax = wc_round_tax_total( $sum_tax );

		// Consistency check with lines net total.
		$lines_net           = $this->get_lines_net_total( $this->document->order );
		$taxable_sum_rounded = (float) $this->format_decimal( $sum_taxable );
		$lines_net_rounded   = (float) $this->format_decimal( $lines_net );

		$diff = $taxable_sum_rounded - $lines_net_rounded;

		if ( abs( $diff ) >= 0.01 ) {
			wpo_ips_edi_log(
				sprintf(
					'Tax subtotal taxable sum mismatch for order #%d: sum_taxable=%s, lines_net=%s, diff=%s',
					$this->document->order->get_id(),
					$taxable_sum_rounded,
					$lines_net_rounded,
					$this->format_decimal( $diff )
				),
				'warning'
			);
		}

		$tax_total = array(
			'name'  => 'cac:TaxTotal',
			'value' => array_merge(
				array(
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => $this->format_decimal( $total_tax ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
				),
				$formatted_tax_array
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_tax_total', $tax_total, $data, $options, $this );

		return $data;
	}

}
