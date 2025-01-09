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
 * @param string $key      Can be category, scheme, or reason
 * @param int    $rate_id  The tax rate ID
 * @return string
 */
function wpo_ips_ubl_get_tax_data_from_fallback( string $key, int $rate_id ): string {
	$result = '';
	
	if ( ! in_array( $key, array( 'category', 'scheme', 'reason' ) ) ) {
		return $result;
	}

	if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
		$tax_rate = \WC_Tax::_get_tax_rate( $rate_id, OBJECT );

		if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
			$ubl_tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes', array() );
			$result           = isset( $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $key ] ) ? $ubl_tax_settings['rate'][ $tax_rate->tax_rate_id ][ $key ] : '';
			$tax_rate_class   = $tax_rate->tax_rate_class;

			if ( empty( $tax_rate_class ) ) {
				$tax_rate_class = 'standard';
			}

			if ( empty( $result ) || 'default' === $result ) {
				$result = isset( $ubl_tax_settings['class'][ $tax_rate_class ][ $key ] ) ? $ubl_tax_settings['class'][ $tax_rate_class ][ $key ] : '';
			}
		}
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
							$value = wpo_ips_ubl_get_tax_data_from_fallback( $field, $tax_rate_id );
						}
						
						wc_update_order_item_meta( $item_id, '_wcpdf_ubl_tax_' . $field, $value );
					}
				}
			}
		}
	}
}