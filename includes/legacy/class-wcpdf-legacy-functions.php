<?php
namespace WPO\WC\PDF_Invoices\Legacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Legacy\\Legacy_Functions' ) ) :

class Legacy_Functions {
	
	protected static $_instance = null;
		
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get template name from slug
	 */
	public function get_template_name( $template_type ) {
		switch ( $template_type ) {
			case 'invoice':
				$template_name = apply_filters( 'wpo_wcpdf_invoice_title', __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' ) );
				break;
			case 'packing-slip':
				$template_name = apply_filters( 'wpo_wcpdf_packing_slip_title', __( 'Packing Slip', 'woocommerce-pdf-invoices-packing-slips' ) );
				break;
			default:
				// try to 'unslug' the name
				$template_name = ucwords( str_replace( array( '_', '-' ), ' ', $template_type ) );
				break;
		}

		return apply_filters( 'wpo_wcpdf_template_name', $template_name, $template_type );
	}

}

endif; // class_exists
