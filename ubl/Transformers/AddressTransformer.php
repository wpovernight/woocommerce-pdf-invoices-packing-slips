<?php

namespace WPO\WC\UBL\Transformers;

use WPO\WC\UBL\Models\Address;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AddressTransformer {

	/**
	 * @return Address
	 */
	public function transform( \WC_Abstract_Order $item, $billing_or_shipping ) {
		$model             = new Address();
		$model->address_1  = wpo_wcpdf_sanitize_html_content( $item->{'get_'.$billing_or_shipping.'_address_1'}(), 'address_1' );
		$model->address_2  = wpo_wcpdf_sanitize_html_content( $item->{'get_'.$billing_or_shipping.'_address_2'}(), 'address_2' );
		$model->first_name = wpo_wcpdf_sanitize_html_content( $item->{'get_'.$billing_or_shipping.'_first_name'}(), 'first_name' );
		$model->last_name  = wpo_wcpdf_sanitize_html_content( $item->{'get_'.$billing_or_shipping.'_last_name'}(), 'last_name' );
		$model->city       = wpo_wcpdf_sanitize_html_content( $item->{'get_'.$billing_or_shipping.'_city'}(), 'city' );
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
