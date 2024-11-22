<?php

namespace WPO\IPS\UBL\Transformers;

use WPO\IPS\UBL\Models\Address;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AddressTransformer {

	/**
	 * @return Address
	 */
	public function transform( \WC_Abstract_Order $item, $billing_or_shipping ) {
		$model             = new Address();
		$model->address_1  = $item->{'get_'.$billing_or_shipping.'_address_1'}();
		$model->address_2  = $item->{'get_'.$billing_or_shipping.'_address_2'}();
		$model->first_name = $item->{'get_'.$billing_or_shipping.'_first_name'}();
		$model->last_name  = $item->{'get_'.$billing_or_shipping.'_last_name'}();
		$model->city       = $item->{'get_'.$billing_or_shipping.'_city'}();
		$model->state      = $item->{'get_'.$billing_or_shipping.'_state'}();
		$model->postcode   = $item->{'get_'.$billing_or_shipping.'_postcode'}();
		$model->country    = $item->{'get_'.$billing_or_shipping.'_country'}();

		if ( 'billing' === $billing_or_shipping ) {
			$model->email = sanitize_email( $item->get_billing_email() );
			$model->phone = wpo_wcpdf_sanitize_phone_number( $item->get_billing_phone() );
		}

		return $model;
	}

}
