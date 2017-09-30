<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings' ) ) :

class Settings {
	public $options_page_hook;
	
	function __construct()	{
		$this->callbacks = include( 'class-wcpdf-settings-callbacks.php' );

		// include settings classes
		$this->general = include( 'class-wcpdf-settings-general.php' );
		$this->documents = include( 'class-wcpdf-settings-documents.php' );
		$this->debug = include( 'class-wcpdf-settings-debug.php' );


		// Settings menu item
		add_action( 'admin_menu', array( $this, 'menu' ) ); // Add menu.
		// Links on plugin page
		add_filter( 'plugin_action_links_'.WPO_WCPDF()->plugin_basename, array( $this, 'add_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_support_links' ), 10, 2 );

		// settings capabilities
		add_filter( 'option_page_capability_wpo_wcpdf_general_settings', array( $this, 'settings_capabilities' ) );

		$this->general_settings		= get_option('wpo_wcpdf_settings_general');
		$this->debug_settings		= get_option('wpo_wcpdf_settings_debug');

		// admin notice for auto_increment_increment
		// add_action( 'admin_notices', array( $this, 'check_auto_increment_increment') );

		// AJAX set number store
		add_action( 'wp_ajax_wpo_wcpdf_set_next_number', array($this, 'set_number_store' ));
	}

	public function menu() {
		$parent_slug = 'woocommerce';
		
		$this->options_page_hook = add_submenu_page(
			$parent_slug,
			__( 'PDF Invoices', 'woocommerce-pdf-invoices-packing-slips' ),
			__( 'PDF Invoices', 'woocommerce-pdf-invoices-packing-slips' ),
			'manage_woocommerce',
			'wpo_wcpdf_options_page',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Add settings link to plugins page
	 */
	public function add_settings_link( $links ) {
		$action_links = array(
			'settings' => '<a href="admin.php?page=wpo_wcpdf_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>',
		);
		
		return array_merge( $action_links, $links );
	}
	
	/**
	 * Add various support links to plugin page
	 * after meta (version, authors, site)
	 */
	public function add_support_links( $links, $file ) {
		if ( $file == WPO_WCPDF()->plugin_basename ) {
			$row_meta = array(
				'docs'    => '<a href="http://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/" target="_blank" title="' . __( 'Documentation', 'woocommerce-pdf-invoices-packing-slips' ) . '">' . __( 'Documentation', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>',
				'support' => '<a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips" target="_blank" title="' . __( 'Support Forum', 'woocommerce-pdf-invoices-packing-slips' ) . '">' . __( 'Support Forum', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	function check_auto_increment_increment() {
		global $wpdb;
		$row = $wpdb->get_row("SHOW VARIABLES LIKE 'auto_increment_increment'");
		if ( !empty($row) && !empty($row->Value) && $row->Value != 1 ) {
			$error = sprintf( __( "<strong>Warning!</strong> Your database has an AUTO_INCREMENT step size of %s, your invoice numbers may not be sequential. Enable the 'Calculate document numbers (slow)' setting in the Status tab to use an alternate method." , 'woocommerce-pdf-invoices-packing-slips' ), $row->Value );
			printf( '<div class="error"><p>%s</p></div>', $error );
		}
	}


	public function settings_page() {
		$settings_tabs = apply_filters( 'wpo_wcpdf_settings_tabs', array (
				'general'	=> __('General', 'woocommerce-pdf-invoices-packing-slips' ),
				'documents'	=> __('Documents', 'woocommerce-pdf-invoices-packing-slips' ),
			)
		);

		// add status tab last in row
		$settings_tabs['debug'] = __('Status', 'woocommerce-pdf-invoices-packing-slips' );

		$active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : 'general';
		$active_section = isset( $_GET[ 'section' ] ) ? sanitize_text_field( $_GET[ 'section' ] ) : '';

		include('views/wcpdf-settings-page.php');
	}

	public function add_settings_fields( $settings_fields, $page, $option_group, $option_name ) {
		foreach ( $settings_fields as $settings_field ) {
			if (!isset($settings_field['callback'])) {
				continue;
			} elseif ( is_callable( array( $this->callbacks, $settings_field['callback'] ) ) ) {
				$callback = array( $this->callbacks, $settings_field['callback'] );
			} elseif ( is_callable( $settings_field['callback'] ) ) {
				$callback = $settings_field['callback'];
			} else {
				continue;
			}

			if ( $settings_field['type'] == 'section' ) {
				add_settings_section(
					$settings_field['id'],
					$settings_field['title'],
					$callback,
					$page
				);
			} else {
				add_settings_field(
					$settings_field['id'],
					$settings_field['title'],
					$callback,
					$page,
					$settings_field['section'],
					$settings_field['args']
				);
				// register option separately for singular options
				if (is_string($settings_field['callback']) && $settings_field['callback'] == 'singular_text_element') {
					register_setting( $option_group, $settings_field['args']['option_name'], array( $this->callbacks, 'validate' ) );
				}
			}
		}
		// $page, $option_group & $option_name are all the same...
		register_setting( $option_group, $option_name, array( $this->callbacks, 'validate' ) );
		add_filter( 'option_page_capability_'.$page, array( $this, 'settings_capabilities' ) );

	}

	/**
	 * Set capability for settings page
	 */
	public function settings_capabilities() {
		return 'manage_woocommerce';
	}

	public function get_common_document_settings() {
		$common_settings = array(
			'paper_size'		=> isset( $this->general_settings['paper_size'] ) ? $this->general_settings['paper_size'] : '',
			'font_subsetting'	=> isset( $this->general_settings['font_subsetting'] ) || ( defined("DOMPDF_ENABLE_FONTSUBSETTING") && DOMPDF_ENABLE_FONTSUBSETTING === true ) ? true : false,
			'header_logo'		=> isset( $this->general_settings['header_logo'] ) ? $this->general_settings['header_logo'] : '',
			'shop_name'			=> isset( $this->general_settings['shop_name'] ) ? $this->general_settings['shop_name'] : '',
			'shop_address'		=> isset( $this->general_settings['shop_address'] ) ? $this->general_settings['shop_address'] : '',
			'footer'			=> isset( $this->general_settings['footer'] ) ? $this->general_settings['footer'] : '',
			'extra_1'			=> isset( $this->general_settings['extra_1'] ) ? $this->general_settings['extra_1'] : '',
			'extra_2'			=> isset( $this->general_settings['extra_2'] ) ? $this->general_settings['extra_2'] : '',
			'extra_3'			=> isset( $this->general_settings['extra_3'] ) ? $this->general_settings['extra_3'] : '',
		);
		return $common_settings;
	}

	public function get_document_settings( $document_type ) {
		$documents = WPO_WCPDF()->documents->get_documents('all');
		foreach ($documents as $document) {
			if ( $document->get_type() == $document_type ) {
				return $document->settings;
			}
		}
		return false;
	}

	public function get_output_format() {
		if ( isset( $this->debug_settings['html_output'] ) ) {
			$output_format = 'html';
		} else {
			$output_format = 'pdf';
		}
		return $output_format;
	}

	public function get_output_mode() {
		if ( isset( WPO_WCPDF()->settings->general_settings['download_display'] ) ) {
			switch ( WPO_WCPDF()->settings->general_settings['download_display'] ) {
				case 'display':
					$output_mode = 'inline';
					break;
				case 'download':
				default:
					$output_mode = 'download';
					break;
			}
		} else {
			$output_mode = 'download';
		}
		return $output_mode;
	}

	public function get_template_path( $document_type = NULL ) {
		$template_path = isset( $this->general_settings['template_path'] )?$this->general_settings['template_path']:'';
		// forward slash for consistency
		$template_path = str_replace('\\','/', $template_path);

		// add base path, checking if it's not already there
		// alternative setups like Bedrock have WP_CONTENT_DIR & ABSPATH separated
		if ( defined('WP_CONTENT_DIR') && strpos( WP_CONTENT_DIR, ABSPATH ) !== false ) {
			$forwardslash_basepath = str_replace('\\','/', ABSPATH);
		} else {
			// bedrock e.a
			$forwardslash_basepath = str_replace('\\','/', WP_CONTENT_DIR);
		}

		if ( strpos( $template_path, $forwardslash_basepath ) === false ) {
			$template_path = $forwardslash_basepath . $template_path;
		}

		return $template_path;
	}

	public function set_number_store() {
		check_ajax_referer( "wpo_wcpdf_next_{$_POST['store']}", 'security' );
		$number = isset( $_POST['number'] ) ? (int) $_POST['number'] : 0;
		$number_store_method = $this->get_sequential_number_store_method();
		$number_store = new Sequential_Number_Store( $_POST['store'], $number_store_method );
		$number_store->set_next( $number );
		echo "next number ({$_POST['store']}) set to {$number}";
		die();
	}

	public function get_sequential_number_store_method() {
		global $wpdb;
		$method = isset( $this->debug_settings['calculate_document_numbers'] ) ? 'calculate' : 'auto_increment';

		// safety first - always use calculate when auto_increment_increment is not 1
		$row = $wpdb->get_row("SHOW VARIABLES LIKE 'auto_increment_increment'");
		if ( !empty($row) && !empty($row->Value) && $row->Value != 1 ) {
			$method = 'calculate';
		}

		return $method;		
	}

}

endif; // class_exists

return new Settings();