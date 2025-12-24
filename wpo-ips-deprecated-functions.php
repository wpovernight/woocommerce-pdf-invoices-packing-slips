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
	_deprecated_function( __FUNCTION__, '5.0.0', '' );
	return false;
}

/**
 * Get UBL Maker
 *
 * @return WPO\IPS\Makers\EDIMaker
 * 
 * @deprecated 5.0.0 Use wpo_ips_edi_get_maker() instead.
 */
function wcpdf_get_ubl_maker() {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_get_maker' );
	return wpo_ips_edi_get_maker();
}

/**
 * Check if UBL is available
 *
 * @return bool
 * 
 * @deprecated 5.0.0 Use wpo_ips_edi_is_available() instead.
 */
function wcpdf_is_ubl_available(): bool {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_is_available' );
	return wpo_ips_edi_is_available();
}

/**
 * Write UBL file
 *
 * @param \WPO\IPS\Documents\OrderDocument $document
 * @param bool $attachment
 * @param bool $contents_only
 *
 * @return string|false
 * 
 * @deprecated 5.0.0 Use wpo_ips_edi_write_file() instead.
 */
function wpo_ips_write_ubl_file( \WPO\IPS\Documents\OrderDocument $document, bool $attachment = false, bool $contents_only = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_write_file' );
	return wpo_ips_edi_write_file( $document, $attachment, $contents_only );
}

/**
 * UBL file headers
 *
 * @param string $filename
 * @param int|false $size
 * @return void
 * 
 * @deprecated 5.0.0 Use wpo_ips_edi_file_headers() instead.
 */
function wcpdf_ubl_headers( $filename, $size ): void {
	_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_edi_file_headers' );
	wpo_ips_edi_file_headers( $filename, $size );
}

/**
 * Save order Peppol data
 * 
 * @param \WC_Abstract_Order $order
 * @return void
 * 
 * @deprecated 5.3.1 Use wpo_ips_edi_maybe_save_order_peppol_data() instead.
 */
function wpo_ips_edi_maybe_save_order_customer_peppol_data( \WC_Abstract_Order $order ): void {
	_deprecated_function( __FUNCTION__, '5.3.1', 'wpo_ips_edi_maybe_save_order_peppol_data' );
	wpo_ips_edi_maybe_save_order_peppol_data( $order );
}
