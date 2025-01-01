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
 * Sanitizes a string for use in UBL documents by stripping all HTML tags and decoding HTML entities to plain text.
 *
 * @param $string
 *
 * @return string
 */
function wpo_ips_ubl_sanitize_string( $string ): string {
	$string = wp_strip_all_tags( $string );
	return htmlspecialchars_decode( $string, ENT_QUOTES );
}
