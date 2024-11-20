<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxTotalHandler extends UblHandler {

	public function handle( $data, $options = array() ) {
		$formatted_tax_array = array_map( function( $item ) {
			return array(
				'name'  => 'cac:TaxSubtotal',
				'value' => array(
					array(
						'name'       => 'cbc:TaxableAmount',
						'value'      => wc_round_tax_total( $item['total_ex'] ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
					array(
						'name'       => 'cbc:TaxAmount',
						'value'      => wc_round_tax_total( $item['total_tax'] ),
						'attributes' => array(
							'currencyID' => $this->document->order->get_currency(),
						),
					),
					array(
						'name'  => 'cac:TaxCategory',
						'value' => array(
							array(
								'name'  => 'cbc:ID',
								'value' => strtoupper( $item['category'] ),
							),
							array(
								'name'  => 'cbc:Name',
								'value' => $item['name'],
							),
							array(
								'name'  => 'cbc:Percent',
								'value' => round( $item['percentage'], 1 ),
							),
							array(
								'name'  => 'cac:TaxScheme',
								'value' => array(
									array(
										'name'  => 'cbc:ID',
										'value' => strtoupper( $item['scheme'] ),
									),
								),
							),
						),
					),
				),
			);
		}, $this->document->order_tax_data );

		$array = array(
			'name'  => 'cac:TaxTotal',
			'value' => array(
				array(
					'name'       => 'cbc:TaxAmount',
					'value'      => round( $this->document->order->get_total_tax(), 2 ),
					'attributes' => array(
						'currencyID' => $this->document->order->get_currency(),
					),
				),
				$formatted_tax_array
			),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_TaxTotal', $array, $data, $options, $this );

		return $data;
	}

}
