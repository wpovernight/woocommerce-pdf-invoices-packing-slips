<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HeaderTradeDeliveryHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$order         = $this->document->order;
		$delivery_date = apply_filters( 'wpo_ips_edi_cii_delivery_date', null, $order, $this );
		
		if ( empty( $delivery_date ) ) {
			return $data;
		}
		
		$date_format_code = $this->get_date_format_code();
		$php_date_format  = $this->get_php_date_format_from_code( $date_format_code );
		$delivery_date    = $this->normalize_date( $delivery_date, $php_date_format );
		
		if ( ! $this->validate_date_format( $delivery_date, $date_format_code ) ) {
			wpo_ips_edi_log(
				sprintf(
					'CII ApplicableHeaderTradeDelivery: Invalid delivery date format for %s in order %d.',
					$delivery_date,
					$order->get_id()
				),
				'error'
			);
			return $data;
		}

		$delivery_node = array(
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

		$data[] = apply_filters( 'wpo_ips_edi_cii_header_trade_delivery', $delivery_node, $data, $options, $this );

		return $data;
	}
	
}
