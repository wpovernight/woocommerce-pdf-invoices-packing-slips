<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitizes a string for use in UBL documents by stripping all HTML tags and decoding HTML entities to plain text.
 *
 * @param string $string
 *
 * @deprecated 5.0.0 Use wpo_ips_edi_sanitize_string() instead.
 */
function wpo_ips_ubl_sanitize_string( string $string ): string {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_sanitize_string' );
	return wpo_ips_edi_sanitize_string( $string );
}

/**
 * Get UBL tax data from fallback
 *
 * @param string                  $key      Can be category, scheme, or reason
 * @param int|null                $rate_id  The tax rate ID
 * @param \WC_Abstract_Order|null $order    The order object
 * @return string
 *
 * @deprecated 5.0.0 Use wpo_ips_edi_get_tax_data_from_fallback() instead.
 */
function wpo_ips_ubl_get_tax_data_from_fallback( string $key, ?int $rate_id, ?\WC_Abstract_Order $order ): string {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_get_tax_data_from_fallback' );
	return wpo_ips_edi_get_tax_data_from_fallback( $key, $rate_id, $order );
}

/**
 * Save UBL order taxes
 *
 * @param \WC_Abstract_Order $order
 * @return void
 * 
 * @deprecated 5.0.0 Use wpo_ips_edi_save_order_taxes() instead.
 */
function wpo_ips_ubl_save_order_taxes( \WC_Abstract_Order $order ): void {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_save_order_taxes' );
	wpo_ips_edi_save_order_taxes( $order );
}

/**
 * Check if the country format extension is active
 *
 * @return bool
 * 
 * @deprecated 5.0.0 Use wpo_ips_edi_save_order_taxes() instead.
 */
function wpo_ips_ubl_is_country_format_extension_active(): bool {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_is_country_format_extension_active' );
	return wpo_ips_edi_is_country_format_extension_active();
}
