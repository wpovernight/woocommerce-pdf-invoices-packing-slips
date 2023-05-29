<?php

namespace WPO\WC\UBL\Transformers;

use WPO\WC\UBL\Models\Address;

defined( 'ABSPATH' ) or exit;

class AddressTransformer
{
	/**
	 * @return Address
	 */
	public function transform( \WC_Abstract_Order $item, $billing_or_shipping )
	{
		$model = new Address();
		$model->address_1 = $item->{'get_'.$billing_or_shipping.'_address_1'}();
		$model->address_2 = $item->{'get_'.$billing_or_shipping.'_address_2'}();
		$model->first_name = $item->{'get_'.$billing_or_shipping.'_first_name'}();
		$model->last_name = $item->{'get_'.$billing_or_shipping.'_last_name'}();
		$model->city = $item->{'get_'.$billing_or_shipping.'_city'}();
		$model->state = $item->{'get_'.$billing_or_shipping.'_state'}();
		$model->postcode = $item->{'get_'.$billing_or_shipping.'_postcode'}();
		$model->country = $item->{'get_'.$billing_or_shipping.'_country'}();

		if ( $billing_or_shipping == 'billing' ) {
			$model->email = $item->get_billing_email();
			$model->phone = $item->get_billing_phone();
		}

		return $model;
	}
}