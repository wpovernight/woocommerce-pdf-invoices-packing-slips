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
		$tax_reasons      = EN16931::get_vatex();
		$currency         = $this->document->order->get_currency();

		// Group tax data by rate, category, reason, and scheme
		$grouped_tax_data = $this->get_grouped_order_tax_data();
		
		// Format grouped tax data into UBL structure
		$formatted_tax_array = array_map( function( $item ) use ( $tax_reasons, $currency ) {
			$item_tax_percentage = (float) ( $item['percentage']            ?? 0        );
			$item_tax_category   = strtoupper( (string) ( $item['category'] ?? ''     ) );
			$item_tax_reason_key = strtoupper( (string) ( $item['reason']   ?? 'NONE' ) );
			$item_tax_scheme     = strtoupper( (string) ( $item['scheme']   ?? 'VAT'  ) );

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

			return array(
				'name'  => 'cac:TaxSubtotal',
				'value' => array(
					array(
						'name'       => 'cbc:TaxableAmount',
						'value'      => $this->format_decimal( wc_round_tax_total( (float) ( $item['total_ex']  ?? 0 ) ) ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => $this->format_decimal( wc_round_tax_total( (float) ( $item['total_tax'] ?? 0 ) ) ),
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
		}, $grouped_tax_data );

		$tax_total = array(
			'name'  => 'cac:TaxTotal',
			'value' => array_merge(
				array(
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => $this->format_decimal( wc_round_tax_total( $this->document->order->get_total_tax() ) ),
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
