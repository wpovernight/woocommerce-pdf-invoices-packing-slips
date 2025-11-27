<?php
/**
 * Use this file for all your template filters and actions.
 * Requires PDF Invoices & Packing Slips for WooCommerce 1.4.13 or higher
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter( 'wpo_ips_ink_saving_supported_templates', function( $templates ) {
	$templates[] = 'default/Simple';
	return $templates;
} );

add_filter( 'wpo_ips_ink_saving_css', function( $css, $document, $current_template ) {
	if ( 'default/Simple' !== $current_template ) {
		return $css;
	}

	$css .= "
		.order-details thead th {
			color: black;
			background-color: white;
			border-width: 0 0 0.8pt 0;
			border-style: solid;
			border-color: black;
		}
		.notes-totals .totals tfoot tr.order_total th,
		.notes-totals .totals tfoot tr.order_total td {
			border-top: .8pt solid black;
			border-bottom: .8pt solid black;
		}
	";

	return $css;
}, 10, 3 );
