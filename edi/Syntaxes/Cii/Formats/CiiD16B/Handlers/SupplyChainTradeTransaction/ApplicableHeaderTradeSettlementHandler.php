<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction\ApplicableHeaderTradeSettlementHandler as BaseApplicableHeaderTradeSettlementHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ApplicableHeaderTradeSettlementHandler extends BaseApplicableHeaderTradeSettlementHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$applicable_header_trade_settlement = array(
			'name'  => 'ram:ApplicableHeaderTradeSettlement',
			'value' => array_filter( array(
				$this->get_payment_reference(),
				$this->get_invoice_currency_code(),
				$this->get_payment_means(),
				$this->get_trade_tax(),
				$this->get_payment_terms(),
				$this->get_monetary_summation(),
			) ),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_settlement', $applicable_header_trade_settlement, $data, $options, $this );

		return $data;
	}
	
	/**
	 * Get the invoice currency code.
	 *
	 * @return array|null
	 */
	public function get_invoice_currency_code(): ?array {
		$currency              = $this->document->order->get_currency();
		$invoice_currency_code = array(
			'name'  => 'ram:InvoiceCurrencyCode',
			'value' => wpo_ips_edi_sanitize_string( $currency ),
		);

		return apply_filters( 'wpo_ips_edi_cii_invoice_currency_code', $invoice_currency_code, $this );
	}

}
