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
				$reason = 'NONE';
			}
			
			$key = implode( '|', array( $percentage, $category, $reason, $scheme ) );

			if ( ! isset( $grouped_tax_data[ $key ] ) ) {
				$grouped_tax_data[ $key ]               = $item;
				$grouped_tax_data[ $key ]['percentage'] = $percentage;
				$grouped_tax_data[ $key ]['category']   = $category;
				$grouped_tax_data[ $key ]['reason']     = $reason;
				$grouped_tax_data[ $key ]['scheme']     = $scheme;
			} else {
				$grouped_tax_data[ $key ]['total_ex']  += ( $item['total_ex']  ?? 0 );
				$grouped_tax_data[ $key ]['total_tax'] += ( $item['total_tax'] ?? 0 );
			}
		}
		
		// Consolidate any existing Z groups from $order_tax_data
		$z_total_ex   = 0.0;
		$z_total_tax  = 0.0;
		$z_first_key  = null;
		$z_other_keys = array();

		foreach ( $grouped_tax_data as $key => $g ) {
			if ( 'Z' === strtoupper( $g['category'] ?? '' ) ) {
				if ( is_null( $z_first_key ) ) {
					$z_first_key    = $key;
				} else {
					$z_other_keys[] = $key;
				}
				
				$z_total_ex  += (float) ( $g['total_ex']  ?? 0 );
				$z_total_tax += (float) ( $g['total_tax'] ?? 0 );
			}
		}

		// Remove duplicate Z groups (keep the first; we'll rewrite it later)
		foreach ( $z_other_keys as $dup_key ) {
			unset( $grouped_tax_data[ $dup_key ] );
		}

		// Compute the Z basis from lines
		$z_missing_ex = 0.0;
		$has_z_line   = false;

		foreach ( $this->document->order->get_items( array( 'line_item', 'fee', 'shipping' ) ) as $it ) {
			$line_total = (float) $it->get_total();
			$taxes      = $it->get_taxes();
			$rows       = ( is_array( $taxes['total'] ?? null ) ) ? $taxes['total'] : array();

			// Does this line have any non-zero tax amount?
			$has_nonzero_row = false;
			foreach ( $rows as $amt ) {
				if ( is_numeric( $amt ) && (float) $amt !== 0.0 ) {
					$has_nonzero_row = true;
					break;
				}
			}

			$line_is_z = false;

			if ( $has_nonzero_row ) {
				// classify by the non-zero row's category/rate
				foreach ( $rows as $tax_id => $amt ) {
					if ( ! is_numeric( $amt ) || (float) $amt === 0.0 ) {
						continue;
					}

					$info = $this->document->order_tax_data[ $tax_id ] ?? array();
					$cat  = strtoupper( $info['category'] ?? '' );
					$rate = (float) ( $info['percentage'] ?? 0 );

					if ( 'Z' === $cat || 0.0 === $rate ) {
						$line_is_z = true;
						break;
					}
				}
			} else {
				// No non-zero tax rows at all → treat as zero-rated (Z)
				$line_is_z = true;
			}

			if ( $line_is_z ) {
				$has_z_line    = true;
				$z_missing_ex += $line_total; // contributes to Z taxable amount
			}
		}

		$z_total_ex += $z_missing_ex;
		$z_total_tax = 0.0;

		// Ensure exactly one Z group if there is any Z line (even with basis 0)
		if ( $has_z_line || $z_first_key ) {
			$z_key = $z_first_key ?: '0|Z|NONE|VAT';

			$grouped_tax_data[ $z_key ] = array(
				'total_ex'   => wc_round_tax_total( $z_total_ex ),
				'total_tax'  => 0,
				'percentage' => 0,
				'category'   => 'Z',
				'reason'     => 'NONE',
				'scheme'     => 'VAT',
				'name'       => $grouped_tax_data[ $z_first_key ]['name'] ?? '',
			);
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
			
			// Only emit exemption reason for 0% non-Z categories (e.g., E/AE/K)
			if ( $item_tax_percentage == 0 && 'Z' !== strtoupper( $item_tax_category ) && strcasecmp( $item_tax_reason_key, 'none' ) !== 0 ) {
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
						'value'      => wc_round_tax_total( $item['total_ex'] ?? 0 ),
						'attributes' => array(
							'currencyID' => $currency,
						),
					),
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => wc_round_tax_total( $item['total_tax'] ?? 0 ),
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
			'value' => array_merge(
				array(
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => round( $this->document->order->get_total_tax(), 2 ),
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
