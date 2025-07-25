<?php
namespace WPO\IPS;

use WPO\IPS\UBL\Settings\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Assets' ) ) :

class Assets {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()	{
		add_action( 'admin_enqueue_scripts', array( $this, 'backend_scripts_styles' ) );
	}

	/**
	 * Load styles & scripts
	 */
	public function backend_scripts_styles( $hook ) {
		$suffix        = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$pdfjs_version = '4.3.136';

		global $wp_version;

		if ( WPO_WCPDF()->admin->is_order_page() ) {

			// STYLES
			wp_enqueue_style( 'thickbox' );

			wp_enqueue_style(
				'wpo-wcpdf-order-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/order-styles' . $suffix . '.css',
				array(),
				WPO_WCPDF_VERSION
			);

			if ( version_compare( $wp_version, '5.3', '<' ) ) {
				// WC2.1 - WC3.2 (MP6) is used: bigger buttons
				// also applied to WC3.3+ but without affect due to .column-order_actions class being deprecated in 3.3+
				wp_enqueue_style(
					'wpo-wcpdf-order-styles-buttons',
					WPO_WCPDF()->plugin_url() . '/assets/css/order-styles-buttons-wc38' . $suffix . '.css',
					array(),
					WPO_WCPDF_VERSION
				);
			} elseif ( version_compare( $wp_version, '5.3', '>=' ) ) {
				// WP5.3 or newer is used: realign img inside buttons
				wp_enqueue_style(
					'wpo-wcpdf-order-styles-buttons',
					WPO_WCPDF()->plugin_url() . '/assets/css/order-styles-buttons-wc39' . $suffix . '.css',
					array(),
					WPO_WCPDF_VERSION
				);
			}

			// SCRIPTS
			wp_enqueue_script(
				'wpo-wcpdf',
				WPO_WCPDF()->plugin_url() . '/assets/js/order-script' . $suffix . '.js',
				array( 'jquery', 'jquery-blockui' ),
				WPO_WCPDF_VERSION
			);

			wp_localize_script(
				'wpo-wcpdf',
				'wpo_wcpdf_ajax',
				array(
					'ajaxurl'                      => admin_url( 'admin-ajax.php' ), // URL to WordPress ajax handling page
					'nonce'                        => wp_create_nonce( 'generate_wpo_wcpdf' ),
					'bulk_actions'                 => array_keys( wcpdf_get_bulk_actions() ),
					'select_orders'                => __( 'You have to select order(s) first!', 'woocommerce-pdf-invoices-packing-slips' ),
					'confirm_delete'               => __( 'Are you sure you want to delete this document? This cannot be undone.', 'woocommerce-pdf-invoices-packing-slips' ),
					'confirm_regenerate'           => __( 'Are you sure you want to regenerate this document? This will make the document reflect the most current settings (such as footer text, document name, etc.) rather than using historical settings.', 'woocommerce-pdf-invoices-packing-slips' ),
					'sticky_document_data_metabox' => apply_filters( 'wpo_wcpdf_sticky_document_data_metabox', true ),
					'error_loading_number_preview' => __( 'Error loading preview', 'woocommerce-pdf-invoices-packing-slips' )
				)
			);
		}

		// only load on our own settings page
		// maybe find a way to refer directly to WPO\IPS\Settings::$options_page_hook ?
		if ( ! empty( $hook ) && false !== strpos( $hook, 'wpo_wcpdf_options_page' ) ) {
			$tab = filter_input( INPUT_GET, 'tab', FILTER_DEFAULT );
			$tab = sanitize_text_field( $tab );

			wp_enqueue_style(
				'wpo-wcpdf-settings-styles',
				WPO_WCPDF()->plugin_url() . '/assets/css/settings-styles' . $suffix . '.css',
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

			wp_enqueue_script( 'wc-enhanced-select' );

			if ( ! wp_script_is( 'wp-pointer', 'enqueued' ) ) {
				wp_enqueue_script( 'wp-pointer' );
			}

			if ( ! wp_style_is( 'wp-pointer', 'enqueued' ) ) {
				wp_enqueue_style( 'wp-pointer' );
			}

			if ( ! wp_script_is( 'jquery-tiptip', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery-tiptip' );
			}

			wp_enqueue_script(
				'wpo-wcpdf-admin',
				WPO_WCPDF()->plugin_url() . '/assets/js/admin-script' . $suffix . '.js',
				array( 'jquery', 'wc-enhanced-select', 'jquery-blockui', 'jquery-tiptip', 'wp-pointer', 'jquery-ui-datepicker' ),
				WPO_WCPDF_VERSION
			);

			wp_localize_script(
				'wpo-wcpdf-admin',
				'wpo_wcpdf_admin',
				array(
					'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
					'nonce'                     => wp_create_nonce( 'wpo_wcpdf_admin_nonce' ),
					'template_paths'            => WPO_WCPDF()->settings->get_installed_templates(),
					'pdfjs_worker'              => WPO_WCPDF()->plugin_url() . '/assets/js/pdf_js/pdf.worker.min.js?ver=' . $pdfjs_version, // taken from https://cdnjs.com/libraries/pdf.js
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
						'unmark_printed',
						'include_encrypted_pdf',
						'include_email_link',
						'include_email_link_placement',
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
					'mysql_int_size_limit'      => sprintf(
						/* translators: mysql int size */
						__( 'The number should be smaller than %s. Please note you should add your next document number without prefix, suffix or padding.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>2147483647</code>'
					),
					'shop_country_changed_messages' => array(
						'loading' => __( 'Loading', 'woocommerce-pdf-invoices-packing-slips' ) . '...',
						'empty'   => __( 'No states available', 'woocommerce-pdf-invoices-packing-slips' ),
						'error'   => __( 'Error loading', 'woocommerce-pdf-invoices-packing-slips' ),
					),
				)
			);

			// preview PDFJS
			$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );
			if ( ! isset( $debug_settings['disable_preview'] ) ) {
				wp_enqueue_script(
					'wpo-wcpdf-pdfjs',
					WPO_WCPDF()->plugin_url() . '/assets/js/pdf_js/pdf.min.js', // taken from https://cdnjs.com/libraries/pdf.js
					array(),
					$pdfjs_version
				);
			}

			wp_enqueue_media();
			wp_enqueue_script(
				'wpo-wcpdf-media-upload',
				WPO_WCPDF()->plugin_url() . '/assets/js/media-upload' . $suffix . '.js',
				array( 'jquery' ),
				WPO_WCPDF_VERSION
			);

			// status/debug page scripts
			if ( 'debug' === $tab ) {
				wp_enqueue_style( 'jquery-ui-style' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_enqueue_style(
					'wpo-wcpdf-debug-tools-styles',
					WPO_WCPDF()->plugin_url() . '/assets/css/debug-tools' . $suffix . '.css',
					array(),
					WPO_WCPDF_VERSION
				);

				wp_enqueue_script(
					'wpo-wcpdf-debug',
					WPO_WCPDF()->plugin_url() . '/assets/js/debug-script' . $suffix . '.js',
					array( 'jquery', 'jquery-blockui', 'jquery-ui-datepicker' ),
					WPO_WCPDF_VERSION
				);

				wp_localize_script(
					'wpo-wcpdf-debug',
					'wpo_wcpdf_debug',
					array(
						'ajaxurl'              => admin_url( 'admin-ajax.php' ),
						'nonce'                => wp_create_nonce( 'wpo_wcpdf_debug_nonce' ),
						'download_label'       => __( 'Download', 'woocommerce-pdf-invoices-packing-slips' ),
						'confirm_reset'        => __( 'Are you sure you want to reset this settings? This cannot be undone.', 'woocommerce-pdf-invoices-packing-slips' ),
						'select_document_type' => __( 'Please select a document type', 'woocommerce-pdf-invoices-packing-slips' ),
						'danger_zone'          => array(
							'enabled' => isset( WPO_WCPDF()->settings->debug_settings['enable_danger_zone_tools'] ) ? true : false,
							'message' => sprintf(
								/* translators: 1. open anchor tag, 2. close anchor tag */
								__( '<strong>Enabled</strong>: %1$sclick here%2$s to start using the tools.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<a href="' . esc_url( add_query_arg( 'section', 'tools' ) ) . '#danger_zone">',
								'</a>'
							),
						),
					)
				);

			}

			// ubl taxes
			if ( 'ubl' === $tab ) {
				wp_enqueue_script(
					'wpo-wcpdf-ubl',
					WPO_WCPDF()->plugin_url() . '/assets/js/ubl-script' . $suffix . '.js',
					array( 'jquery' ),
					WPO_WCPDF_VERSION,
					true
				);

				wp_localize_script(
					'wpo-wcpdf-ubl',
					'wpo_wcpdf_ubl',
					array(
						'code'    => __( 'Code', 'woocommerce-pdf-invoices-packing-slips' ),
						'new'     => __( 'New', 'woocommerce-pdf-invoices-packing-slips' ),
						'unsaved' => __( 'unsaved', 'woocommerce-pdf-invoices-packing-slips' ),
						'remarks' => TaxesSettings::get_available_remarks(),
					)
				);
			}

		}

		if (
			$hook === 'woocommerce_page_wc-admin' &&
			WPO_WCPDF()->order_util->is_wc_admin_page()
		) {
			wp_enqueue_script(
				'wpo-wcpdf-analytics-order',
				WPO_WCPDF()->plugin_url() . '/assets/js/analytics-order' . $suffix . '.js',
				array( 'wp-hooks' ),
				WPO_WCPDF_VERSION,
				true
			);

			wp_localize_script(
				'wpo-wcpdf-analytics-order',
				'wpo_wcpdf_analytics_order',
				array(
					'label' => __( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			);
		}

	}

}

endif; // class_exists
