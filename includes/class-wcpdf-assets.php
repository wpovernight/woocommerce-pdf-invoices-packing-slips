<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Assets' ) ) :

class Assets {
	
	function __construct()	{
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_scripts_styles' ) );
	}

	/**
	 * Load styles & scripts
	 */
	public function frontend_scripts_styles ( $hook ) {
		# none yet
	}

	/**
	 * Load styles & scripts
	 */
	public function backend_scripts_styles ( $hook ) {
		if( $this->is_order_page() ) {
			// STYLES
			wp_enqueue_style( 'thickbox' );

			wp_enqueue_style(
				'wpo-wcpdf-order-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/order-styles.css',
				array(),
				WPO_WCPDF_VERSION
			);

			if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
				// WC 2.1 or newer (MP6) is used: bigger buttons
				wp_enqueue_style(
					'wpo-wcpdf-order-styles-buttons',
					WPO_WCPDF()->plugin_url() . '/assets/css/order-styles-buttons.css',
					array(),
					WPO_WCPDF_VERSION
				);
			} else {
				// legacy WC 2.0 styles
				wp_enqueue_style(
					'wpo-wcpdf-order-styles-buttons',
					WPO_WCPDF()->plugin_url() . '/assets/css/order-styles-buttons-wc20.css',
					array(),
					WPO_WCPDF_VERSION
				);
			}

			// SCRIPTS
			wp_enqueue_script(
				'wpo-wcpdf',
				WPO_WCPDF()->plugin_url() . '/assets/js/order-script.js',
				array( 'jquery' ),
				WPO_WCPDF_VERSION
			);

			$bulk_actions = array();
			$documents = WPO_WCPDF()->documents->get_documents();
			foreach ($documents as $document) {
				$bulk_actions[$document->get_type()] = "PDF " . $document->get_title();
			}
			$bulk_actions = apply_filters( 'wpo_wcpdf_bulk_actions', $bulk_actions );
			
			wp_localize_script(
				'wpo-wcpdf',
				'wpo_wcpdf_ajax',
				array(
					'ajaxurl'		=> admin_url( 'admin-ajax.php' ), // URL to WordPress ajax handling page  
					'nonce'			=> wp_create_nonce('generate_wpo_wcpdf'),
					'bulk_actions'	=> array_keys( $bulk_actions ),
				)
			);
		}

		// only load on our own settings page
		// maybe find a way to refer directly to WPO\WC\PDF_Invoices\Settings::$options_page_hook ?
		if ( $hook == 'woocommerce_page_wpo_wcpdf_options_page' || $hook == 'settings_page_wpo_wcpdf_options_page' ) {
			wp_enqueue_style(
				'wpo-wcpdf-settings-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/settings-styles.css',
				array(),
				WPO_WCPDF_VERSION
			);
			wp_add_inline_style( 'wpo-wcpdf-settings-styles', ".next-number-input.ajax-waiting {
				background-image: url(".WPO_WCPDF()->plugin_url().'/assets/images/spinner.gif'.") !important;
				background-position: 95% 50% !important;
				background-repeat: no-repeat !important;
			}" );

			// SCRIPTS
			wp_enqueue_script(
				'wpo-wcpdf-admin',
				WPO_WCPDF()->plugin_url() . '/assets/js/admin-script.js',
				array( 'jquery' ),
				WPO_WCPDF_VERSION
			);
			wp_localize_script(
				'wpo-wcpdf-admin',
				'wpo_wcpdf_admin',
				array(
					'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
				)
			);

			wp_enqueue_media();
			wp_enqueue_script(
				'wpo-wcpdf-media-upload',
				WPO_WCPDF()->plugin_url() . '/assets/js/media-upload.js',
				array( 'jquery' ),
				WPO_WCPDF_VERSION
			);
		}
	}

	/**
	 * Check if this is a shop_order page (edit or list)
	 */
	public function is_order_page() {
		global $post_type;
		if( $post_type == 'shop_order' ) {
			return true;
		} else {
			return false;
		}
	}
}

endif; // class_exists

return new Assets();