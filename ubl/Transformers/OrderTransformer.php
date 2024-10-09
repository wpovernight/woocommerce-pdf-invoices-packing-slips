<?php

namespace WPO\IPS\UBL\Transformers;

use WPO\IPS\UBL\Models\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class OrderTransformer {

	/**
	 * @return Order
	 */
	public function transform( \WC_Abstract_Order $item ) {
		$datetime_transformer    = new DateTimeTransformer();
		$address_transformer     = new AddressTransformer();

		$model                   = new Order();
		$model->id               = $item->get_id();
		$model->date             = $datetime_transformer->transform( $item );
		$model->shipping_address = $address_transformer->transform( $item, 'shipping' );
		$model->billing_address  = $address_transformer->transform( $item, 'billing' );

		return $model;
	}

}
