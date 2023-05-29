<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Handlers\Ubl;

use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class TaxTotalHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$formatted_tax_array = array_map(function($item) {
			return [
				'name' => 'cac:TaxSubtotal',
				'value' => [ [
					'name' => 'cbc:TaxableAmount',
					'value' => round($item['total_ex'], 2),
					'attributes' => [
						'currencyID' => $this->document->order->get_currency(),
					],
				], [
					'name' => 'cbc:TaxAmount',
					'value' => round($item['total_tax'], 2),
					'attributes' => [
						'currencyID' => $this->document->order->get_currency(),
					],
				], [
					'name' => 'cac:TaxCategory',
					'value' => [ [ 
						'name' => 'cbc:ID',
						'value' => strtoupper($item['category']),
					], [
						'name' => 'cbc:Name',
						'value' => $item['name'],
					], [
						'name' => 'cbc:Percent',
						'value' => round( $item['percentage'], 1),
					], [
						'name' => 'cac:TaxScheme',
						'value' => [[
							'name' => 'cbc:ID',
							'value' => strtoupper($item['scheme']),
						] ],
					] ],
				] ],
			];
		}, $this->document->order_tax_data);

		$array = [
			'name' => 'cac:TaxTotal',
			'value' => [ [
				'name' => 'cbc:TaxAmount',
				'value' => round($this->document->order->get_total_tax(), 2),
				'attributes' => [
					'currencyID' => $this->document->order->get_currency(),
				],
			], $formatted_tax_array ],
		];


		$data[] = apply_filters( 'wpo_wc_ubl_handle_TaxTotal', $array, $data, $options, $this );

		return $data;
	}
}