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
		$tax_reasons    = EN16931::get_vatex();
		$order_tax_data = $this->document->order_tax_data;
		$currency       = $this->document->order->get_currency();
		
		// Fallback if no tax data is available
		if ( empty( $order_tax_data ) ) {
			$order_tax_data = array(
				0 => array(
					'total_ex'  => $this->document->order->get_total(),
					'total_tax' => 0,
					'items'     => array(),
					'name'      => '',
				),
			);
		}

		// Group tax data by rate, category, reason, and scheme
		$grouped_tax_data = array();
		foreach ( apply_filters( 'wpo_ips_edi_ubl_order_tax_data', $order_tax_data, $data, $options, $this ) as $item ) {
			$percentage = (float) ( $item['percentage'] ?? 0 );
			$category   = strtoupper( trim( $item['category'] ?? wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $this->document->order ) ) );
			$reason     = strtoupper( trim( $item['reason']   ?? wpo_ips_edi_get_tax_data_from_fallback( 'reason',   null, $this->document->order ) ) );
			$scheme     = strtoupper( trim( $item['scheme']   ?? wpo_ips_edi_get_tax_data_from_fallback( 'scheme',   null, $this->document->order ) ) );
			
			if ( '' === $reason || 'NONE' === $reason ) {
				$reason = 'VATEX-EU-AE';
			}
			
			$key = implode( '|', array( $percentage, $category, $reason, $scheme ) );

			if ( ! isset( $grouped_tax_data[ $key ] ) ) {
				$grouped_tax_data[ $key ]               = $item;
				$grouped_tax_data[ $key ]['percentage'] = $percentage;
				$grouped_tax_data[ $key ]['category']   = $category;
				$grouped_tax_data[ $key ]['reason']     = $reason;
				$grouped_tax_data[ $key ]['scheme']     = $scheme;
			} else {
				$grouped_tax_data[ $key ]['total_ex']  += $item['total_ex'];
				$grouped_tax_data[ $key ]['total_tax'] += $item['total_tax'];
			}
		}
		
		// Fallback for zero‑tax lines
		$lines_total_ex = 0;
		foreach ( $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $i ) {
			$lines_total_ex += $i->get_total();
		}
		
		$grouped_total_ex = array_reduce(
			$grouped_tax_data,
			function ( $carry, $item ) {
				return $carry + ( $item['total_ex'] ?? 0 );
			},
			0
		);
		
		$missing_ex = wc_round_tax_total( $lines_total_ex - $grouped_total_ex );

		if ( $missing_ex > 0 ) {
			$ae_key = '0|AE|VATEX-EU-AE|VAT';
			
			if ( ! isset( $grouped_tax_data[ $ae_key ] ) ) {
				$grouped_tax_data[ $ae_key ] = array(
					'total_ex'   => $missing_ex,
					'total_tax'  => 0,
					'percentage' => 0,
					'category'   => 'AE',
					'reason'     => 'VATEX-EU-AE',
					'scheme'     => 'VAT',
					'name'       => '',
				);
			} else {
				$grouped_tax_data[ $ae_key ]['total_ex'] += $missing_ex;
			}
		}

		$formatted_tax_array = array_map( function( $item ) use ( $tax_reasons, $currency ) {
			$item_tax_percentage = ! empty( $item['percentage'] )
				? $item['percentage']
				: 0;
			$item_tax_category   = ! empty( $item['category'] )
				? $item['category']
				: wpo_ips_edi_get_tax_data_from_fallback( 'category', null, $this->document->order );
			$item_tax_reason_key = ! empty( $item['reason'] )
				? $item['reason']
				: wpo_ips_edi_get_tax_data_from_fallback( 'reason', null, $this->document->order );
			$item_tax_reason     = ! empty( $tax_reasons[ $item_tax_reason_key ] )
				? $tax_reasons[ $item_tax_reason_key ]
				: $item_tax_reason_key;
			$item_tax_scheme     = ! empty( $item['scheme'] )
				? $item['scheme']
				: wpo_ips_edi_get_tax_data_from_fallback( 'scheme', null, $this->document->order );
			
			$tax_category = array(
				array(
					'name'  => 'cbc:ID',
					'value' => strtoupper( $item_tax_category ),
				),
				array(
					'name'  => 'cbc:Percent',
					'value' => round( $item_tax_percentage, 1 ),
				),
			);
			
			if ( $item_tax_percentage == 0 && strcasecmp( $item_tax_reason_key, 'none' ) !== 0 ) {
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
						'value' => strtoupper( $item_tax_scheme ),
					),
				),
			);

			return array(
				'name'  => 'cac:TaxSubtotal',
				'value' => array(
					array(
						'name'       => 'cbc:TaxableAmount',
						'value'      => wc_round_tax_total( $item['total_ex'] ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => wc_round_tax_total( $item['total_tax'] ),
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
		}, array_values( $grouped_tax_data ) );

		$tax_total = array(
			'name'  => 'cac:TaxTotal',
			'value' => array(
				array(
					'name'       => 'cbc:TaxAmount',
					'value'      => round( $this->document->order->get_total_tax(), 2 ),
					'attributes' => array(
						'currencyID' => $currency,
					),
				),
				$formatted_tax_array
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_tax_total', $tax_total, $data, $options, $this );

		return $data;
	}

}
