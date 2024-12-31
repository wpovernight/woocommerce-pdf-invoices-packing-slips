<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| UBL Document global getter functions
|--------------------------------------------------------------------------
*/

use WPO\IPS\Documents\OrderDocument;

/**
 * Get the shop name for the UBL document.
 *
 * @param OrderDocument $order_document
 *
 * @return string
 */
function wcpdf_ubl_get_shop_name( OrderDocument $order_document ): string {
	$shop_name = $order_document->get_settings_text( 'shop_name', get_bloginfo( 'name' ), false );
	$decoded_shop_name = htmlspecialchars_decode( $shop_name, ENT_QUOTES );

	return $decoded_shop_name;
}

function wcpdf_ubl_get_shop_address( OrderDocument $order_document ): string {
	$shop_address = $order_document->get_settings_text( 'shop_address', get_option( 'woocommerce_store_address' ), false );
	$decoded_shop_address = htmlspecialchars_decode( $shop_address, ENT_QUOTES );

	return $decoded_shop_address;
}
