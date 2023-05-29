<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Handlers\Ubl;

use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class LegalMonetaryTotalHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$total = $this->document->order->get_total();
		$total_inc_tax = $total;
		$total_exc_tax = $total - $this->document->order->get_total_tax();

		$legalMonetaryTotal = [
			'name' => 'cac:LegalMonetaryTotal',
			'value' => [ [
				'name' => 'cbc:LineExtensionAmount',
				'value' => $total_exc_tax,
				'attributes' => [
					'currencyID' => $this->document->order->get_currency(),
				],
			], [
				'name' => 'cbc:TaxExclusiveAmount',
				'value' => $total_exc_tax,
				'attributes' => [
					'currencyID' => $this->document->order->get_currency(),
				],
			], [
				'name' => 'cbc:TaxInclusiveAmount',
				'value' => $total_inc_tax,
				'attributes' => [
					'currencyID' => $this->document->order->get_currency(),
				],
			], [
				'name' => 'cbc:PayableAmount',
				'value' => $total,
				'attributes' => [
					'currencyID' => $this->document->order->get_currency(),
				],
			] ],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_LegalMonetaryTotal', $legalMonetaryTotal, $data, $options, $this );

		return $data;
	}
}