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
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		global $wp_version;
		if ( WPO_WCPDF()->admin->is_order_page() ) {

			// STYLES
			wp_enqueue_style( 'thickbox' );

			wp_enqueue_style(
				'wpo-wcpdf-order-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/order-styles'.$suffix.'.css',
				array(),
				WPO_WCPDF_VERSION
			);

			if ( version_compare( $wp_version, '5.3', '<' ) ) {
				// WC2.1 - WC3.2 (MP6) is used: bigger buttons
				// also applied to WC3.3+ but without affect due to .column-order_actions class being deprecated in 3.3+
				wp_enqueue_style(
					'wpo-wcpdf-order-styles-buttons',
					WPO_WCPDF()->plugin_url() . '/assets/css/order-styles-buttons-wc38'.$suffix.'.css',
					array(),
					WPO_WCPDF_VERSION
				);
			} elseif ( version_compare( $wp_version, '5.3', '>=' ) ) {
				// WP5.3 or newer is used: realign img inside buttons
				wp_enqueue_style(
					'wpo-wcpdf-order-styles-buttons',
					WPO_WCPDF()->plugin_url() . '/assets/css/order-styles-buttons-wc39'.$suffix.'.css',
					array(),
					WPO_WCPDF_VERSION
				);
			}

			// SCRIPTS
			wp_enqueue_script(
				'wpo-wcpdf',
				WPO_WCPDF()->plugin_url() . '/assets/js/order-script'.$suffix.'.js',
				array( 'jquery', 'jquery-blockui' ),
				WPO_WCPDF_VERSION
			);

			$bulk_actions = array();
			$documents    = WPO_WCPDF()->documents->get_documents();
			foreach ( $documents as $document ) {
				$bulk_actions[$document->get_type()] = "PDF " . $document->get_title();
			}
			$bulk_actions = apply_filters( 'wpo_wcpdf_bulk_actions', $bulk_actions );
			
			wp_localize_script(
				'wpo-wcpdf',
				'wpo_wcpdf_ajax',
				array(
					'ajaxurl'			           => admin_url( 'admin-ajax.php' ), // URL to WordPress ajax handling page  
					'nonce'				           => wp_create_nonce('generate_wpo_wcpdf'),
					'bulk_actions'		           => array_keys( $bulk_actions ),
					'confirm_delete'	           => __( 'Are you sure you want to delete this document? This cannot be undone.', 'woocommerce-pdf-invoices-packing-slips'),
					'confirm_regenerate'           => __( 'Are you sure you want to regenerate this document? This will make the document reflect the most current settings (such as footer text, document name, etc.) rather than using historical settings.', 'woocommerce-pdf-invoices-packing-slips'),
					'sticky_document_data_metabox' => apply_filters( 'wpo_wcpdf_sticky_document_data_metabox', true ),
				)
			);
		}

		// only load on our own settings page
		// maybe find a way to refer directly to WPO\WC\PDF_Invoices\Settings::$options_page_hook ?
		if ( $hook == 'woocommerce_page_wpo_wcpdf_options_page' || $hook == 'settings_page_wpo_wcpdf_options_page' || ( isset($_GET['page']) && $_GET['page'] == 'wpo_wcpdf_options_page' ) ) {
			wp_enqueue_style(
				'wpo-wcpdf-settings-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/settings-styles'.$suffix.'.css',
				array('woocommerce_admin_styles'),
				WPO_WCPDF_VERSION
			);
			wp_add_inline_style( 'wpo-wcpdf-settings-styles', ".next-number-input.ajax-waiting {
				background-image: url(".WPO_WCPDF()->plugin_url().'/assets/images/spinner.gif'.") !important;
				background-position: 95% 50% !important;
				background-repeat: no-repeat !important;
			}" );
			wp_add_inline_style( 'wpo-wcpdf-settings-styles', "#preview-order-search.ajax-waiting {
				background-image: url(".WPO_WCPDF()->plugin_url().'/assets/images/spinner.gif'.") !important;
				background-repeat: no-repeat !important;
				background-position: right 10px center !important;
			}" );
			wp_add_inline_style( 'wpo-wcpdf-settings-styles', "#wpo-wcpdf-preview-wrapper .slider.slide-left:after {
					content: '".__( 'Preview', 'woocommerce-pdf-invoices-packing-slips' )."';
				}
				#wpo-wcpdf-preview-wrapper .slider.slide-right:after {
					content: '".__( 'Settings', 'woocommerce-pdf-invoices-packing-slips' )."';
			}" );
			wp_add_inline_style( 'wpo-wcpdf-settings-styles', "#upgrade-table td span.feature-available {
				background-image: url(".WPO_WCPDF()->plugin_url().'/assets/images/checkmark.svg'.") !important;
			}" );

			// SCRIPTS
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_script(
				'wpo-wcpdf-admin',
				WPO_WCPDF()->plugin_url() . '/assets/js/admin-script'.$suffix.'.js',
				array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'jquery-tiptip' ),
				WPO_WCPDF_VERSION
			);
			wp_localize_script(
				'wpo-wcpdf-admin',
				'wpo_wcpdf_admin',
				array(
					'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
					'template_paths'            => WPO_WCPDF()->settings->get_installed_templates(),
					'pdfjs_worker'              => WPO_WCPDF()->plugin_url() . '/assets/js/pdf_js/pdf.worker.js',
					'preview_excluded_settings' => apply_filters( 'wpo_wcpdf_preview_excluded_settings', array(
						// general
						'download_display',
						'test_mode',
						// document
						'enabled',
						'archive_pdf',
						'auto_generate_for_statuses',
						'attach_to_email_ids',
						'disable_for_statuses',
						'reset_number_yearly',
						'my_account_buttons',
						'invoice_number_column',
						'invoice_date_column',
						'disable_free',
						'use_latest_settings',
						'mark_printed',
						'unmark_printed'
					) ),
					'pointers'                  => array(
						'wcpdf_document_settings_sections' => array(
							'target'        => '.wcpdf_document_settings_sections',
							'content'       => sprintf(
								'<h3>%s</h3><p>%s</p>',
								__( 'Document settings', 'woocommerce-pdf-invoices-packing-slips' ),
								__( 'Select a document in the dropdown menu above to edit its settings.', 'woocommerce-pdf-invoices-packing-slips' )
							),
							'pointer_class' => 'wp-pointer arrow-top wpo-wcpdf-pointer',
							'pointer_width' => 300,
							'position'      => array(
								'edge'  => 'top',
								'align' => 'left',
							),
						),
					),
					'dismissed_pointers'        => get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ),
					'mysql_int_size_limit'      => __( 'The number should be smaller than 2147483647. Please note you should add your next document number without prefix, suffix or padding.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			);

			wp_enqueue_media();
			wp_enqueue_script(
				'wpo-wcpdf-media-upload',
				WPO_WCPDF()->plugin_url() . '/assets/js/media-upload'.$suffix.'.js',
				array( 'jquery' ),
				WPO_WCPDF_VERSION
			);

			if ( wp_script_is( 'wp-pointer', 'enqueued' ) === false ) {
				wp_enqueue_script( 'wp-pointer' );
			}

			if ( wp_style_is( 'wp-pointer', 'enqueued' ) === false ) {
				wp_enqueue_style( 'wp-pointer' );
			}

		}
		
		// status/debug page scripts
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wpo_wcpdf_options_page' && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'debug' ) {
			
			wp_enqueue_style(
				'wpo-wcpdf-debug-tools-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/debug-tools'.$suffix.'.css',
				WPO_WCPDF_VERSION
			);
			wp_enqueue_script(
				'wpo-wcpdf-debug',
				WPO_WCPDF()->plugin_url() . '/assets/js/debug-script'.$suffix.'.js',
				[ 'jquery', 'jquery-blockui' ],
				WPO_WCPDF_VERSION
			);
			wp_localize_script(
				'wpo-wcpdf-debug',
				'wpo_wcpdf_debug',
				[
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( 'wpo_wcpdf_debug_nonce' ),
					'download_label' => __( 'Download', 'woocommerce-pdf-invoices-packing-slips' ),
				]
			);
			
		}
	}

}

endif; // class_exists

return new Assets();