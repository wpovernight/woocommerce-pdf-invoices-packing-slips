<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| UBL Document global functions
|--------------------------------------------------------------------------
*/

/**
 * Sanitizes a string for use in UBL documents by stripping all HTML tags and decoding HTML entities to plain text.
 *
 * @param string $string
 *
 * @return string
 */
function wpo_ips_ubl_sanitize_string( string $string ): string {
	$string = wp_strip_all_tags( $string );
	return htmlspecialchars_decode( $string, ENT_QUOTES );
}

/**
 * Get UBL tax data from fallback
 *
 * @param string                  $key      Can be category, scheme, or reason
 * @param int|null                $rate_id  The tax rate ID
 * @param \WC_Abstract_Order|null $order    The order object
 * @return string
 */
function wpo_ips_ubl_get_tax_data_from_fallback( string $key, ?int $rate_id, ?\WC_Abstract_Order $order ): string {
	$result = '';

	if ( ! in_array( $key, array( 'category', 'scheme', 'reason' ) ) ) {
		return $result;
	}

	$tax_rate_class   = '';
	$ubl_tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );

	if ( ! is_null( $rate_id ) && class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
		$tax_rate = \WC_Tax::_get_tax_rate( $rate_id, OBJECT );

		if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
			$result         = isset( $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $key ] ) ? $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $key ] : '';
			$tax_rate_class = $tax_rate->tax_rate_class;
		}
	}

	if ( empty( $tax_rate_class ) ) {
		$tax_rate_class = 'standard';
	}

	if ( empty( $result ) || 'default' === $result ) {
		$result = isset( $ubl_tax_settings['class'][ $tax_rate_class ][ $key ] ) ? $ubl_tax_settings['class'][ $tax_rate_class ][ $key ] : '';
	}

	// check if order is tax exempt
	if ( wpo_wcpdf_order_is_vat_exempt( $order ) ) {
		switch ( $key ) {
			case 'scheme':
				$result = 'VAT';
				break;
			case 'category':
				$result = 'AE';
				break;
			case 'reason':
				$result = 'VATEX-EU-AE';
				break;
		}

		$result = apply_filters( 'wpo_ips_ubl_get_tax_data_from_fallback_vat_exempt', $result, $key, $rate_id, $order );
	}

	return $result;
}

/**
 * Save UBL order taxes
 *
 * @param \WC_Abstract_Order $order
 * @return void
 */
function wpo_ips_ubl_save_order_taxes( \WC_Abstract_Order $order ): void {
	foreach ( $order->get_taxes() as $item_id => $tax_item ) {
		if ( is_a( $tax_item, '\WC_Order_Item_Tax' ) && is_callable( array( $tax_item, 'get_rate_id' ) ) ) {
			// get tax rate id from item
			$tax_rate_id = $tax_item->get_rate_id();

			// read tax rate data from db
			if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
				$tax_rate = \WC_Tax::_get_tax_rate( $tax_rate_id, OBJECT );

				if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
					// store percentage in tax item meta
					wc_update_order_item_meta( $item_id, '_wcpdf_rate_percentage', $tax_rate->tax_rate );

					$ubl_tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );
					$tax_fields       = array( 'category', 'scheme', 'reason' );

					foreach ( $tax_fields as $field ) {
						$value = isset( $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $field ] ) ? $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $field ] : '';

						if ( empty( $value ) || 'default' === $value ) {
							$value = wpo_ips_ubl_get_tax_data_from_fallback( $field, $tax_rate_id, $order );
						}

						wc_update_order_item_meta( $item_id, '_wcpdf_ubl_tax_' . $field, $value );
					}
				}
			}
		}
	}
}

/**
 * Check if the country format extension is active
 *
 * @return bool
 */
function wpo_ips_ubl_is_country_format_extension_active(): bool {
	return apply_filters( 'wpo_ips_ubl_is_country_format_extension_active', false );
}
