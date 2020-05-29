<?php
/**
 * Use this file for all your template filters and actions.
 * Requires WooCommerce PDF Invoices & Packing Slips 1.4.13 or higher
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'wpo_wcpdf_custom_styles', 'wpo_wcpdf_set_header_logo_height', 10, 2 );

function wpo_wcpdf_set_header_logo_height( $document_type, $document ) {
    $header_logo_height = $document->get_header_logo_height();
	if ( !empty( $header_logo_height ) ) {
		?>
        td.header img {
            max-height: <?php echo $header_logo_height; ?>;
        }
        <?php
    }
}