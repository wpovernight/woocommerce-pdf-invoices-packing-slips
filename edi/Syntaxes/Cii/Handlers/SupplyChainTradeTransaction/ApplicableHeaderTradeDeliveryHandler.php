<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ApplicableHeaderTradeDeliveryHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$applicable_header_trade_delivery = array(
			'name'  => 'ram:ApplicableHeaderTradeDelivery',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_delivery', $applicable_header_trade_delivery, $data, $options, $this );

		return $data;
	}

}
