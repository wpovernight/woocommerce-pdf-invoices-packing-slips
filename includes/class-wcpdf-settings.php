<?php

/**
 * Settings class
 */
if ( ! class_exists( 'WooCommerce_PDF_Invoices_Settings' ) ) {

	class WooCommerce_PDF_Invoices_Settings {
	
		public $options_page_hook;
		public $general_settings;
		public $template_settings;

		public function __construct() {
			add_action( 'admin_menu', array( &$this, 'menu' ) ); // Add menu.
			add_action( 'admin_init', array( &$this, 'init_settings' ) ); // Registers settings
			add_filter( 'option_page_capability_wpo_wcpdf_template_settings', array( &$this, 'settings_capabilities' ) );
			add_filter( 'option_page_capability_wpo_wcpdf_general_settings', array( &$this, 'settings_capabilities' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts_styles' ) ); // Load scripts
			
			// Add links to WordPress plugins page
			add_filter( 'plugin_action_links_'.WooCommerce_PDF_Invoices::$plugin_basename, array( &$this, 'wpo_wcpdf_add_settings_link' ) );
			add_filter( 'plugin_row_meta', array( $this, 'add_support_links' ), 10, 2 );
			
			$this->general_settings = get_option('wpo_wcpdf_general_settings');
			$this->template_settings = get_option('wpo_wcpdf_template_settings');
		}
	
		public function menu() {
			if (class_exists('WPOvernight_Core')) {
				$parent_slug = 'wpo-core-menu';
			} else {
				$parent_slug = 'woocommerce';
			}
			
			$this->options_page_hook = add_submenu_page(
				$parent_slug,
				__( 'PDF Invoices', 'wpo_wcpdf' ),
				__( 'PDF Invoices', 'wpo_wcpdf' ),
				'manage_woocommerce',
				'wpo_wcpdf_options_page',
				array( $this, 'settings_page' )
			);
		}

		/**
		 * Set capability for settings page
		 */
		public function settings_capabilities() {
			return 'manage_woocommerce';
		}		
		
		/**
		 * Styles for settings page
		 */
		public function load_scripts_styles ( $hook ) {
			if( $hook != $this->options_page_hook ) 
				return;
			
			wp_enqueue_script(
				'wcpdf-upload-js',
				plugins_url( 'js/media-upload.js' , dirname(__FILE__) ),
				array( 'jquery' ),
				WooCommerce_PDF_Invoices::$version
			);

			wp_enqueue_style(
				'wpo-wcpdf',
				WooCommerce_PDF_Invoices::$plugin_url . 'css/style.css',
				array(),
				WooCommerce_PDF_Invoices::$version
			);
			wp_enqueue_media();
		}
	
		/**
		 * Add settings link to plugins page
		 */
		public function wpo_wcpdf_add_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wpo_wcpdf_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}
		
		/**
		 * Add various support links to plugin page
		 * after meta (version, authors, site)
		 */
		public function add_support_links( $links, $file ) {
			if ( !current_user_can( 'install_plugins' ) ) {
				return $links;
			}
		
			if ( $file == WooCommerce_PDF_Invoices::$plugin_basename ) {
				// $links[] = '<a href="..." target="_blank" title="' . __( '...', 'wpo_wcpdf' ) . '">' . __( '...', 'wpo_wcpdf' ) . '</a>';
			}
			return $links;
		}
	
		public function settings_page() {
			$settings_tabs = apply_filters( 'wpo_wcpdf_settings_tabs', array (
					'general'	=> __('General','wpo_wcpdf'),
					'template'	=> __('Template','wpo_wcpdf'),
				)
			);

			// add status tab last in row
			$settings_tabs['debug'] = __('Status','wpo_wcpdf');

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';

			?>
	
				<div class="wrap">
					<div class="icon32" id="icon-options-general"><br /></div>
					<h2><?php _e( 'WooCommerce PDF Invoices', 'wpo_wcpdf' ); ?></h2>
					<h2 class="nav-tab-wrapper">
					<?php
					foreach ($settings_tabs as $tab_slug => $tab_title ) {
						printf('<a href="?page=wpo_wcpdf_options_page&tab=%1$s" class="nav-tab nav-tab-%1$s %2$s">%3$s</a>', $tab_slug, (($active_tab == $tab_slug) ? 'nav-tab-active' : ''), $tab_title);
					}
					?>
					</h2>

					<?php
					do_action( 'wpo_wcpdf_before_settings_page', $active_tab );

					if ( !( class_exists('WooCommerce_PDF_IPS_Pro') && class_exists('WooCommerce_PDF_IPS_Dropbox') && class_exists('WooCommerce_PDF_IPS_Templates') && class_exists('WooCommerce_Ext_PrintOrders') ) ) {
						include('wcpdf-extensions.php');
					}

					?>
					<form method="post" action="options.php">
						<?php
							settings_fields( 'wpo_wcpdf_'.$active_tab.'_settings' );
							do_settings_sections( 'wpo_wcpdf_'.$active_tab.'_settings' );
	
							submit_button();
						?>
	
					</form>
					<?php

					if ( $active_tab=='debug' ) {
						$this->status_page();
					}

					do_action( 'wpo_wcpdf_after_settings_page', $active_tab ); ?>
	
				</div>
	
			<?php
		}

		public function status_page() {
			?>
			<?php include('dompdf-status.php'); ?>
			<?php
		}
		
		/**
		 * User settings.
		 * 
		 */
		
		public function init_settings() {
			global $woocommerce, $wpo_wcpdf;
	
			/**************************************/
			/*********** GENERAL SETTINGS *********/
			/**************************************/
	
			$option = 'wpo_wcpdf_general_settings';
		
			// Create option in wp_options.
			if ( false === get_option( $option ) ) {
				$this->default_settings( $option );
			}
		
			// Section.
			add_settings_section(
				'general_settings',
				__( 'General settings', 'wpo_wcpdf' ),
				array( &$this, 'section_options_callback' ),
				$option
			);
	
			add_settings_field(
				'download_display',
				__( 'How do you want to view the PDF?', 'wpo_wcpdf' ),
				array( &$this, 'select_element_callback' ),
				$option,
				'general_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'download_display',
					'options' 		=> array(
						'download'	=> __( 'Download the PDF' , 'wpo_wcpdf' ),
						'display'	=> __( 'Open the PDF in a new browser tab/window' , 'wpo_wcpdf' ),
					),
				)
			);
			
			$tmp_path  = $wpo_wcpdf->export->tmp_path( 'attachments' );
			$tmp_path_check = !is_writable( $tmp_path );

			$wc_emails = array(
				'new_order'			=> __( 'Admin New Order email' , 'wpo_wcpdf' ),
				'processing'		=> __( 'Customer Processing Order email' , 'wpo_wcpdf' ),
				'completed'			=> __( 'Customer Completed Order email' , 'wpo_wcpdf' ),
				'customer_invoice'	=> __( 'Customer Invoice email' , 'wpo_wcpdf' ),
			);

			add_settings_field(
				'email_pdf',
				__( 'Attach invoice to:', 'wpo_wcpdf' ),
				array( &$this, 'multiple_checkbox_element_callback' ),
				$option,
				'general_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'email_pdf',
					'options' 		=> apply_filters( 'wpo_wcpdf_wc_emails', $wc_emails ),
					'description'	=> $tmp_path_check ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'wpo_wcpdf' ), $tmp_path ).'</span>':'',
				)
			);

			// Section.
			add_settings_section(
				'interface',
				__( 'Interface', 'wpo_wcpdf' ),
				array( &$this, 'section_options_callback' ),
				$option
			);

			// $documents = array(
			// 	'invoice'		=> __( 'Invoice', 'wpo_wcpdf' ),
			// 	'packing-slip'	=> __( 'Packing Slip', 'wpo_wcpdf' ),
			// );

			// $contexts = array(
			// 	'orders-list'	=> __( 'Orders list', 'wpo_wcpdf' ),
			// 	'orders-bulk'	=> __( 'Bulk order actions', 'wpo_wcpdf' ),
			// 	'order-single'	=> __( 'Single order page', 'wpo_wcpdf' ),
			// 	'my-account'	=> __( 'My Account page', 'wpo_wcpdf' ),
			// );

			// add_settings_field(
			// 	'buttons',
			// 	__( 'Show download buttons', 'wpo_wcpdf' ),
			// 	array( &$this, 'checkbox_table_callback' ),
			// 	$option,
			// 	'interface',
			// 	array(
			// 		'menu'		=> $option,
			// 		'id'		=> 'buttons',
			// 		'rows' 		=> $contexts,
			// 		'columns'	=> apply_filters( 'wpo_wcpdf_documents_buttons', $documents ),
			// 	)
			// );

			// get list of WooCommerce statuses
			if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
				$statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
				foreach ( $statuses as $status ) {
					$order_statuses[esc_attr( $status->slug )] = esc_html__( $status->name, 'woocommerce' );
				}
			} else {
				$statuses = wc_get_order_statuses();
				foreach ( $statuses as $status_slug => $status ) {
					$status_slug   = 'wc-' === substr( $status_slug, 0, 3 ) ? substr( $status_slug, 3 ) : $status_slug;
					$order_statuses[$status_slug] = $status;
				}

			}

			add_settings_field(
				'my_account_buttons',
				__( 'Allow My Account invoice download', 'wpo_wcpdf' ),
				array( &$this, 'select_element_callback' ),
				$option,
				'interface',
				array(
					'menu'		=> $option,
					'id'		=> 'my_account_buttons',
					'options' 		=> array(
						'available'	=> __( 'Only when an invoice is already created/emailed' , 'wpo_wcpdf' ),
						'custom'	=> __( 'Only for specific order statuses (define below)' , 'wpo_wcpdf' ),
						'always'	=> __( 'Always' , 'wpo_wcpdf' ),
					),
					'custom'		=> array(
						'type'		=> 'multiple_checkbox_element_callback',
						'args'		=> array(
							'menu'			=> $option,
							'id'			=> 'my_account_restrict',
							'options'		=> $order_statuses,
						),
					),
				)
			);

			add_settings_field(
				'invoice_number_column',
				__( 'Enable invoice number column in the orders list', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'interface',
				array(
					'menu'			=> $option,
					'id'			=> 'invoice_number_column',
				)
			);

			// Register settings.
			register_setting( $option, $option, array( &$this, 'validate_options' ) );
	
			$option_values = get_option($option);
			// convert old 'statusless' setting to new status array
			if ( isset( $option_values['email_pdf'] ) && !is_array( $option_values['email_pdf'] ) ) {
				$default_status = apply_filters( 'wpo_wcpdf_attach_to_status', 'completed' );
				$option_values['email_pdf'] = array (
						$default_status		=> 1,
						'customer_invoice'	=> 1,
					);
				update_option( $option, $option_values );
			}

			/**************************************/
			/********** TEMPLATE SETTINGS *********/
			/**************************************/
	
			$option = 'wpo_wcpdf_template_settings';
		
			// Create option in wp_options.
			if ( false === get_option( $option ) ) {
				$this->default_settings( $option );
			}
	
			// Section.
			add_settings_section(
				'template_settings',
				__( 'PDF Template settings', 'wpo_wcpdf' ),
				array( &$this, 'section_options_callback' ),
				$option
			);


			$theme_path = get_stylesheet_directory() . '/' . $wpo_wcpdf->export->template_base_path;
			$theme_template_path = substr($theme_path, strpos($theme_path, 'wp-content')) . 'yourtemplate';
			$plugin_template_path = 'wp-content/plugins/woocommerce-pdf-invoices-packing-slips/templates/pdf/Simple';

			add_settings_field(
				'template_path',
				__( 'Choose a template', 'wpo_wcpdf' ),
				array( &$this, 'template_select_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'template_path',
					'options' 		=> $this->find_templates(),
					'description'	=> sprintf( __( 'Want to use your own template? Copy all the files from <code>%s</code> to your (child) theme in <code>%s</code> to customize them' , 'wpo_wcpdf' ), $plugin_template_path, $theme_template_path),
				)
			);

			add_settings_field(
				'paper_size',
				__( 'Paper size', 'wpo_wcpdf' ),
				array( &$this, 'select_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'paper_size',
					'options' 		=> array(
						'a4'		=> __( 'A4' , 'wpo_wcpdf' ),
						'letter'	=> __( 'Letter' , 'wpo_wcpdf' ),
					),
				)
			);

			add_settings_field(
				'header_logo',
				__( 'Shop header/logo', 'wpo_wcpdf' ),
				array( &$this, 'media_upload_callback' ),
				$option,
				'template_settings',
				array(
					'menu'							=> $option,
					'id'							=> 'header_logo',
					'uploader_title'				=> __( 'Select or upload your invoice header/logo', 'wpo_wcpdf' ),
					'uploader_button_text'			=> __( 'Set image', 'wpo_wcpdf' ),
					'remove_button_text'			=> __( 'Remove image', 'wpo_wcpdf' ),
					//'description'					=> __( '...', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'shop_name',
				__( 'Shop Name', 'wpo_wcpdf' ),
				array( &$this, 'text_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'shop_name',
					'size'			=> '72',
				)
			);

			add_settings_field(
				'shop_address',
				__( 'Shop Address', 'wpo_wcpdf' ),
				array( &$this, 'textarea_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'shop_address',
					'width'			=> '28',
					'height'		=> '8',
					//'description'			=> __( '...', 'wpo_wcpdf' ),
				)
			);
	
			add_settings_field(
				'footer',
				__( 'Footer: terms & conditions, policies, etc.', 'wpo_wcpdf' ),
				array( &$this, 'textarea_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'footer',
					'width'			=> '72',
					'height'		=> '4',
					//'description'			=> __( '...', 'wpo_wcpdf' ),
				)
			);

			// Section.
			add_settings_section(
				'invoice',
				__( 'Invoice', 'wpo_wcpdf' ),
				array( &$this, 'section_options_callback' ),
				$option
			);

			add_settings_field(
				'invoice_shipping_address',
				__( 'Display shipping address', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'invoice',
				array(
					'menu'				=> $option,
					'id'				=> 'invoice_shipping_address',
					'description'		=> __( 'Display shipping address on invoice (in addition to the default billing address) if different from billing address', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'invoice_email',
				__( 'Display email address', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'invoice',
				array(
					'menu'				=> $option,
					'id'				=> 'invoice_email',
				)
			);

			add_settings_field(
				'invoice_phone',
				__( 'Display phone number', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'invoice',
				array(
					'menu'				=> $option,
					'id'				=> 'invoice_phone',
				)
			);

			add_settings_field(
				'display_date',
				__( 'Display invoice date', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'invoice',
				array(
					'menu'				=> $option,
					'id'				=> 'display_date',
					'value' 			=> 'invoice_date',
				)
			);

			add_settings_field(
				'display_number',
				__( 'Display built-in sequential invoice number', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'invoice',
				array(
					'menu'				=> $option,
					'id'				=> 'display_number',
					'value' 			=> 'invoice_number',
				)
			);

			add_settings_field(
				'next_invoice_number',
				__( 'Next invoice number (without prefix/suffix etc.)', 'wpo_wcpdf' ),
				array( &$this, 'text_element_callback' ),
				$option,
				'invoice',
				array(
					'menu'			=> $option,
					'id'			=> 'next_invoice_number',
					'size'			=> '10',
					'description'	=> __( 'This is the number that will be used on the next invoice that is created. By default, numbering starts from the WooCommerce Order Number of the first invoice that is created and increases for every new invoice. Note that if you override this and set it lower than the highest (PDF) invoice number, this could create double invoice numbers!', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'invoice_number_formatting',
				__( 'Invoice number format', 'wpo_wcpdf' ),
				array( &$this, 'invoice_number_formatting_callback' ),
				$option,
				'invoice',
				array(
					'menu'					=> $option,
					'id'					=> 'invoice_number_formatting',
					'fields'				=> array(
						'prefix'			=> array(
							'title'			=> __( 'Prefix' , 'wpo_wcpdf' ),
							'size'			=> 20,
							'description'	=> __( 'to use the order year and/or month, use [order_year] or [order_month] respectively' , 'wpo_wcpdf' ),
						),
						'suffix'			=> array(
							'title'			=> __( 'Suffix' , 'wpo_wcpdf' ),
							'size'			=> 20,
							'description'	=> '',
						),
						'padding'			=> array(
							'title'			=> __( 'Padding' , 'wpo_wcpdf' ),
							'size'			=> 2,
							'description'	=> __( 'enter the number of digits here - enter "6" to display 42 as 000042' , 'wpo_wcpdf' ),
						),
					),
					'description'			=> __( 'note: if you have already created a custom invoice number format with a filter, the above settings will be ignored' , 'wpo_wcpdf' ),
				)
			);

			// Section.
			add_settings_section(
				'packing_slip',
				__( 'Packing Slip', 'wpo_wcpdf' ),
				array( &$this, 'section_options_callback' ),
				$option
			);

			add_settings_field(
				'packing_slip_billing_address',
				__( 'Display billing address', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'packing_slip',
				array(
					'menu'				=> $option,
					'id'				=> 'packing_slip_billing_address',
					'description'		=> __( 'Display billing address on packing slip (in addition to the default shipping address) if different from shipping address', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'packing_slip_email',
				__( 'Display email address', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'packing_slip',
				array(
					'menu'				=> $option,
					'id'				=> 'packing_slip_email',
				)
			);

			add_settings_field(
				'packing_slip_phone',
				__( 'Display phone number', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'packing_slip',
				array(
					'menu'				=> $option,
					'id'				=> 'packing_slip_phone',
				)
			);

			// Section.
			add_settings_section(
				'extra_template_fields',
				__( 'Extra template fields', 'wpo_wcpdf' ),
				array( &$this, 'custom_fields_section' ),
				$option
			);
	
			add_settings_field(
				'extra_1',
				__( 'Extra field 1', 'wpo_wcpdf' ),
				array( &$this, 'textarea_element_callback' ),
				$option,
				'extra_template_fields',
				array(
					'menu'			=> $option,
					'id'			=> 'extra_1',
					'width'			=> '28',
					'height'		=> '8',
					'description'	=> __( 'This is footer column 1 in the <i>Modern (Premium)</i> template', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'extra_2',
				__( 'Extra field 2', 'wpo_wcpdf' ),
				array( &$this, 'textarea_element_callback' ),
				$option,
				'extra_template_fields',
				array(
					'menu'			=> $option,
					'id'			=> 'extra_2',
					'width'			=> '28',
					'height'		=> '8',
					'description'	=> __( 'This is footer column 2 in the <i>Modern (Premium)</i> template', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'extra_3',
				__( 'Extra field 3', 'wpo_wcpdf' ),
				array( &$this, 'textarea_element_callback' ),
				$option,
				'extra_template_fields',
				array(
					'menu'			=> $option,
					'id'			=> 'extra_3',
					'width'			=> '28',
					'height'		=> '8',
					'description'	=> __( 'This is footer column 3 in the <i>Modern (Premium)</i> template', 'wpo_wcpdf' ),
				)
			);

			// Register settings.
			register_setting( $option, $option, array( &$this, 'validate_options' ) );

			$option_values = get_option($option);
			// determine highest invoice number if option not set
			if ( !isset( $option_values['next_invoice_number']) ) {
				// Based on code from WooCommerce Sequential Order Numbers
				global $wpdb;
				// get highest invoice_number in postmeta table
				$max_invoice_number = $wpdb->get_var( 'SELECT max(cast(meta_value as UNSIGNED)) from ' . $wpdb->postmeta . ' where meta_key="_wcpdf_invoice_number"' );
				// get highest order_number in postmeta table
				// $max_order_number = $wpdb->get_var( 'SELECT max(cast(meta_value as UNSIGNED)) from ' . $wpdb->postmeta . ' where meta_key="_order_number"' );
				// get highest post_id with type shop_order in post table
				// $max_order_id = $wpdb->get_var( 'SELECT max(cast(ID as UNSIGNED)) from ' . $wpdb->posts . ' where post_type="shop_order"' );
				
				$next_invoice_number = '';

				if ( isset($max_invoice_number) && !empty($max_invoice_number) ) {
					$next_invoice_number = $max_invoice_number+1;
				}

				$option_values['next_invoice_number'] = $next_invoice_number;
				update_option( $option, $option_values );
			}
			/**************************************/
			/******** DEBUG/STATUS SETTINGS *******/
			/**************************************/
	
			$option = 'wpo_wcpdf_debug_settings';
		
			// Create option in wp_options.
			if ( false === get_option( $option ) ) {
				$this->default_settings( $option );
			}

			// Section.
			add_settings_section(
				'debug_settings',
				__( 'Debug settings', 'wpo_wcpdf' ),
				array( &$this, 'debug_section' ),
				$option
			);

			add_settings_field(
				'enable_debug',
				__( 'Enable debug output', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'debug_settings',
				array(
					'menu'				=> $option,
					'id'				=> 'enable_debug',
					'description'		=> __( "Enable this option to output plugin errors if you're getting a blank page or other PDF generation issues", 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'html_output',
				__( 'Output to HTML', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'debug_settings',
				array(
					'menu'				=> $option,
					'id'				=> 'html_output',
					'description'		=> __( 'Send the template output as HTML to the browser instead of creating a PDF.', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'old_tmp',
				__( 'Use old tmp folder', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'debug_settings',
				array(
					'menu'				=> $option,
					'id'				=> 'old_tmp',
					'description'		=> __( 'Before version 1.5 of PDF Invoices, temporary files were stored in the plugin folder. This setting is only intended for backwards compatibility, not recommended on new installs!', 'wpo_wcpdf' ),
				)
			);

			// Register settings.
			register_setting( $option, $option, array( &$this, 'validate_options' ) );
	
		}

		/**
		 * Set default settings.
		 */
		public function default_settings( $option ) {
			global $wpo_wcpdf;

			switch ( $option ) {
				case 'wpo_wcpdf_general_settings':
					$default = array(
						'download_display'	=> 'download',
					);
					break;
				case 'wpo_wcpdf_template_settings':
					$default = array(
						'paper_size'				=> 'a4',
						'template_path'				=> $wpo_wcpdf->export->template_default_base_path . 'Simple',
						// 'invoice_shipping_address'	=> '1',
					);
					break;
				default:
					$default = array();
					break;
			}

			if ( false === get_option( $option ) ) {
				add_option( $option, $default );
			} else {
				update_option( $option, $default );

			}
		}
		
		// Text element callback.
		public function text_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$size = isset( $args['size'] ) ? $args['size'] : '25';
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$html = sprintf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" size="%4$s"/>', $id, $menu, $current, $size );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		}
		
		// Text element callback.
		public function textarea_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$width = $args['width'];
			$height = $args['height'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$html = sprintf( '<textarea id="%1$s" name="%2$s[%1$s]" cols="%4$s" rows="%5$s"/>%3$s</textarea>', $id, $menu, $current, $width, $height );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		}
	
	
		/**
		 * Checkbox field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Checkbox field.
		 */
		public function checkbox_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$value = isset( $args['value'] ) ? $args['value'] : 1;
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="%3$s"%4$s />', $id, $menu, $value, checked( $value, $current, false ) );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		}
		
		/**
		 * Multiple Checkbox field callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Checkbox field.
		 */
		public function multiple_checkbox_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
		
			foreach ( $args['options'] as $key => $label ) {
				$current = ( isset( $options[$id][$key] ) ) ? $options[$id][$key] : '';
				printf( '<input type="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="1"%4$s /> %5$s<br/>', $menu, $id, $key, checked( 1, $current, false ), $label );
			}

			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
		}

		/**
		 * Checkbox fields table callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Checkbox field.
		 */
		public function checkbox_table_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];

			$options = get_option( $menu );

			$rows = $args['rows'];
			$columns = $args['columns'];

			?>
			<table style="">
				<tr>
					<td style="padding:0 10px 5px 0;">&nbsp;</td>
					<?php foreach ( $columns as $column => $title ) { ?>
					<td style="padding:0 10px 5px 0;"><?php echo $title; ?></td>
					<?php } ?>
				</tr>
				<tr>
					<td style="padding: 0;">
						<?php foreach ($rows as $row) {
							echo $row.'<br/>';
						} ?>
					</td>
					<?php foreach ( $columns as $column => $title ) { ?>
					<td style="text-align:center; padding: 0;">
						<?php foreach ( $rows as $row => $title ) {
							$current = ( isset( $options[$id.'_'.$column][$row] ) ) ? $options[$id.'_'.$column][$row] : '';
							$name = sprintf('%1$s[%2$s_%3$s][%4$s]', $menu, $id, $column, $row);
							printf( '<input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s /><br/>', $name, checked( 1, $current, false ) );
						} ?>
					</td>
					<?php } ?>
				</tr>
			</table>

			<?php
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
		}

		/**
		 * Select element callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Select field.
		 */
		public function select_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			printf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $menu );
	
			foreach ( $args['options'] as $key => $label ) {
				printf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
	
			echo '</select>';
		

			if (isset($args['custom'])) {
				$custom = $args['custom'];

				$custom_id = $id.'_custom';

				printf( '<br/><br/><div id="%s" style="display:none;">', $custom_id );

				switch ($custom['type']) {
					case 'text_element_callback':
						$this->text_element_callback( $custom['args'] );
						break;		
					case 'multiple_text_element_callback':
						$this->multiple_text_element_callback( $custom['args'] );
						break;		
					case 'multiple_checkbox_element_callback':
						$this->multiple_checkbox_element_callback( $custom['args'] );
						break;		
					default:
						break;
				}

				echo '</div>';

				?>
				<script type="text/javascript">
				jQuery(document).ready(function($) {
					function check_<?php echo $id; ?>_custom() {
						var custom = $('#<?php echo $id; ?>').val();
						if (custom == 'custom') {
							$( '#<?php echo $custom_id; ?>').show();
						} else {
							$( '#<?php echo $custom_id; ?>').hide();
						}
					}

					check_<?php echo $id; ?>_custom();

					$( '#<?php echo $id; ?>' ).change(function() {
						check_<?php echo $id; ?>_custom();
					});

				});
				</script>
				<?php
			}

			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}

		}
		
		/**
		 * Displays a radio settings field
		 *
		 * @param array   $args settings field args
		 */
		public function radio_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
	
			$html = '';
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $menu, $id, $key, checked( $current, $key, false ) );
				$html .= sprintf( '<label for="%1$s[%2$s][%3$s]"> %4$s</label><br>', $menu, $id, $key, $label);
			}
			
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
	
			echo $html;
		}

		/**
		 * Media upload callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Media upload button & preview.
		 */
		public function media_upload_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}

			$uploader_title = $args['uploader_title'];
			$uploader_button_text = $args['uploader_button_text'];
			$remove_button_text = $args['remove_button_text'];

			$html = '';
			if( !empty($current) ) {
				$attachment = wp_get_attachment_image_src( $current, 'full', false );
				
				$attachment_src = $attachment[0];
				$attachment_width = $attachment[1];
				$attachment_height = $attachment[2];

				$attachment_resolution = round($attachment_height/(3/2.54));
				
				$html .= sprintf('<img src="%1$s" style="display:block" id="img-%4$s"/>', $attachment_src, $attachment_width, $attachment_height, $id );
				$html .= '<div class="attachment-resolution"><p class="description">'.__('Image resolution').': '.$attachment_resolution.'dpi (default height = 3cm)</p></div>';
				$html .= sprintf('<span class="button wpo_remove_image_button" data-input_id="%1$s">%2$s</span>', $id, $remove_button_text );
			}

			$html .= sprintf( '<input id="%1$s" name="%2$s[%1$s]" type="hidden" value="%3$s" />', $id, $menu, $current );
			
			$html .= sprintf( '<span class="button wpo_upload_image_button %4$s" data-uploader_title="%1$s" data-uploader_button_text="%2$s" data-remove_button_text="%3$s" data-input_id="%4$s">%2$s</span>', $uploader_title, $uploader_button_text, $remove_button_text, $id );
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		}

		/**
		 * Invoice number formatting callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Media upload button & preview.
		 */
		public function invoice_number_formatting_callback( $args ) {
			$menu = $args['menu'];
			$fields = $args['fields'];
			$options = get_option( $menu );

			echo '<table>';
			foreach ($fields as $key => $field) {
				$id = $args['id'] . '_' . $key;

				if ( isset( $options[$id] ) ) {
					$current = $options[$id];
				} else {
					$current = '';
				}

				$title = $field['title'];
				$size = $field['size'];
				$description = isset( $field['description'] ) ? '<span style="font-style:italic;">'.$field['description'].'</span>' : '';

				echo '<tr>';
				printf( '<td style="padding:0 1em 0 0; ">%1$s:</td><td style="padding:0;"><input type="text" id="%2$s" name="%3$s[%2$s]" value="%4$s" size="%5$s"/></td><td style="padding:0 0 0 1em;">%6$s</td>', $title, $id, $menu, $current, $size, $description );
				echo '</tr>';
			}
			echo '</table>';

		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
		
			// echo $html;
		}


		/**
		 * Template select element callback.
		 *
		 * @param  array $args Field arguments.
		 *
		 * @return string	  Select field.
		 */
		public function template_select_element_callback( $args ) {
			$menu = $args['menu'];
			$id = $args['id'];
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$html = sprintf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $menu );

			// backwards compatible template path (1.4.4+ uses relative paths instead of absolute)
			if (strpos($current, ABSPATH) !== false) {
				//  check if folder exists, then strip site base path.
				if ( file_exists( $current ) ) {
					$current = str_replace( ABSPATH, '', $current );
				}
			}

			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
			}
	
			$html .= '</select>';
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
			}
		
			echo $html;
		
		}

		/**
		 * Section null callback.
		 *
		 * @return void.
		 */
		public function section_options_callback() {
		}
		
		/**
		 * Debug section callback.
		 *
		 * @return void.
		 */
		public function debug_section() {
			_e( '<b>Warning!</b> The settings below are meant for debugging/development only. Do not use them on a live website!' , 'wpo_wcpdf' );
		}
		
		/**
		 * Custom fields section callback.
		 *
		 * @return void.
		 */
		public function custom_fields_section() {
			_e( 'These are used for the (optional) footer columns in the <em>Modern (Premium)</em> template, but can also be used for other elements in your custom template' , 'wpo_wcpdf' );
		}

		/**
		 * Validate options.
		 *
		 * @param  array $input options to valid.
		 *
		 * @return array		validated options.
		 */
		public function validate_options( $input ) {
			// Create our array for storing the validated options.
			$output = array();

			if (empty($input) || !is_array($input)) {
				return $input;
			}
		
			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {
		
				// Check to see if the current option has a value. If so, process it.
				if ( isset( $input[$key] ) ) {
					if ( is_array( $input[$key] ) ) {
						foreach ( $input[$key] as $sub_key => $sub_value ) {
							$output[$key][$sub_key] = $input[$key][$sub_key];
						}
					} else {
						$output[$key] = $input[$key];
					}
				}
			}
		
			// Return the array processing any additional functions filtered by this action.
			return apply_filters( 'wpo_wcpdf_validate_input', $output, $input );
		}

		/**
		 * List templates in plugin folder, theme folder & child theme folder
		 * @return array		template path => template name
		 */
		public function find_templates() {
			global $wpo_wcpdf;
			$installed_templates = array();

			// get base paths
			$template_paths = array (
					// note the order: child-theme before theme, so that array_unique filters out parent doubles
					'default'		=> $wpo_wcpdf->export->template_default_base_path,
					'child-theme'	=> get_stylesheet_directory() . '/' . $wpo_wcpdf->export->template_base_path,
					'theme'			=> get_template_directory() . '/' . $wpo_wcpdf->export->template_base_path,
				);

			$template_paths = apply_filters( 'wpo_wcpdf_template_paths', $template_paths );

			foreach ($template_paths as $template_source => $template_path) {
				$dirs = (array) glob( $template_path . '*' , GLOB_ONLYDIR);
				
				foreach ($dirs as $dir) {
					if ( file_exists($dir."/invoice.php") && file_exists($dir."/packing-slip.php"))
						// we're stripping abspath to make the plugin settings more portable
						$installed_templates[ str_replace( ABSPATH, '', $dir )] = basename($dir);
				}
			}

			// remove parent doubles
			$installed_templates = array_unique($installed_templates);

			if (empty($installed_templates)) {
				// fallback to Simple template for servers with glob() disabled
				$simple_template_path = str_replace( ABSPATH, '', $template_paths['default'] . 'Simple' );
				$installed_templates[$simple_template_path] = 'Simple';
			}

			return apply_filters( 'wpo_wcpdf_templates', $installed_templates );
		}

	} // end class WooCommerce_PDF_Invoices_Settings

} // end class_exists