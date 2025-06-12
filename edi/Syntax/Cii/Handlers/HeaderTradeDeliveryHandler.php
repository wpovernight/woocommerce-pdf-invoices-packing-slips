<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HeaderTradeDeliveryHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$order         = $this->document->order;
		$delivery_date = apply_filters( 'wpo_ips_edi_cii_delivery_date', null, $order, $this );
		
		if ( empty( $delivery_date ) ) {
			return $data;
		}
		
		$date_format_code = $this->get_date_format_code();
		$php_date_format  = $this->get_php_date_format_from_code( $date_format_code );
		$delivery_date    = $this->normalize_date( $delivery_date, $php_date_format );
		
		if ( ! $this->validate_cii_date_format( $delivery_date, $date_format_code ) ) {
			return $data;
		}

		$deliveryNode = array(
			'name'  => 'ram:ApplicableHeaderTradeDelivery',
			'value' => array(
				array(
					'name'  => 'ram:ActualDeliverySupplyChainEvent',
					'value' => array(
						array(
							'name'  => 'ram:OccurrenceDateTime',
							'value' => array(
								'name'       => 'udt:DateTimeString',
								'value'      => $delivery_date,
								'attributes' => array(
									'format' => $date_format_code,
								),
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_header_trade_delivery', $deliveryNode, $data, $options, $this );

		return $data;
	}
	
}
