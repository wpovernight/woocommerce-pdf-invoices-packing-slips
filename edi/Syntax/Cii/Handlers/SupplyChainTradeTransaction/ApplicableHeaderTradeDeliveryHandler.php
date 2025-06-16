<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntax\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ApplicableHeaderTradeDeliveryHandler extends AbstractCiiHandler {

	public function handle( array $data, array $options = array() ): array {
		$applicableHeaderTradeDelivery = array(
			'name'  => 'ram:ApplicableHeaderTradeDelivery',
			'value' => array(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_delivery', $applicableHeaderTradeDelivery, $data, $options, $this );

		return $data;
	}

}
