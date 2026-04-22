<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeliveryHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$grouped_tax_data = $this->get_grouped_order_tax_data();
		$has_k_category   = false;

		foreach ( $grouped_tax_data as $tax_group ) {
			if ( 'K' === strtoupper( (string) ( $tax_group['category'] ?? '' ) ) ) {
				$has_k_category = true;
				break;
			}
		}

		$delivery_date = '';
		$country_code  = '';

		if ( $has_k_category ) {
			$delivery_date = $this->document->order->get_date_completed();

			if ( ! $delivery_date ) {
				$date_instance = $this->document->order_document->get_date();
				$delivery_date = ! empty( $date_instance ) ? $date_instance->date_i18n( 'Y-m-d' ) : '';
			}

			$country_code = strtoupper( (string) $this->document->order->get_shipping_country() );

			if ( '' === $country_code ) {
				$country_code = strtoupper( (string) $this->document->order->get_billing_country() );
			}
		}

		$delivery_value = array();

		if ( ! empty( $delivery_date ) ) {
			$delivery_value[] = array(
				'name'  => 'cbc:ActualDeliveryDate',
				'value' => $delivery_date,
			);
		}

		if ( ! empty( $country_code ) ) {
			$delivery_value[] = array(
				'name'  => 'cac:DeliveryLocation',
				'value' => array(
					array(
						'name'  => 'cac:Address',
						'value' => array(
							array(
								'name'  => 'cac:Country',
								'value' => array(
									array(
										'name'  => 'cbc:IdentificationCode',
										'value' => $country_code,
									),
								),
							),
						),
					),
				),
			);
		}

		if ( empty( $delivery_value ) ) {
			return $data;
		}

		$delivery = array(
			'name'  => 'cac:Delivery',
			'value' => $delivery_value,
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_delivery', $delivery, $data, $options, $this );

		return $data;
	}

}
