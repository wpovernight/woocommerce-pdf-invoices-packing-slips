<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get UBL Maker
 * Legacy function < v3.9.1
 *
 * @return WPO\IPS\Makers\XMLMaker
 */
function wcpdf_get_ubl_maker() {
	wcpdf_deprecated_function( 'wcpdf_get_ubl_maker', '3.9.1', 'wcpdf_get_xml_maker' );
	return wcpdf_get_xml_maker();
}