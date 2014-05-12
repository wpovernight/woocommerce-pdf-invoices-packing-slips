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
				'manage_options',
				'wpo_wcpdf_options_page',
				array( $this, 'settings_page' )
			);
		}
		
		/**
		 * Styles for settings page
		 */
		public function load_scripts_styles ( $hook ) {
			if( $hook != $this->options_page_hook ) 
				return;
			
			wp_enqueue_script( 'wcpdf-upload-js', plugins_url( 'js/media-upload.js' , dirname(__FILE__) ) );
			wp_enqueue_style( 'wpo-wcpdf', WooCommerce_PDF_Invoices::$plugin_url . 'css/style.css' );
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

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
			?>
	
				<div class="wrap">
					<div class="icon32" id="icon-options-general"><br /></div>
					<h2><?php _e( 'WooCommerce PDF Invoices', 'wpo_wcpdf' ); ?></h2>
					<h2 class="nav-tab-wrapper">
					<?php foreach ($settings_tabs as $tab_slug => $tab_title ) {
						printf('<a href="?page=wpo_wcpdf_options_page&tab=%1$s" class="nav-tab %2$s">%3$s</a>', $tab_slug, (($active_tab == $tab_slug) ? 'nav-tab-active' : ''), $tab_title);
					}
					?>
						<a href="?page=wpo_wcpdf_options_page&tab=status" class="nav-tab <?php echo (($active_tab == 'status') ? 'nav-tab-active' : ''); ?>"><?php _e('Status','wpo_wcpdf'); ?></a>
					</h2>

					<?php do_action( 'wpo_wcpdf_before_settings_page', $active_tab ); ?>				

					<?php
					if (!class_exists('WooCommerce_PDF_IPS_Dropbox')) {
						$dropbox_link = '<a href="https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-dropbox/" target="_blank">WooCommerce PDF Invoices & Packing Slips to Dropbox</a>';
						?>
						<div class="wcpdf-dropbox" style="border: 1px solid #3D5C99; border-radius: 5px; padding: 15px 10px; margin-top: 15px; background-color: #EBF5FF;">
							<img src="<?php echo WooCommerce_PDF_Invoices::$plugin_url . 'images/dropbox_logo.png'; ?>" style="float:left;margin-right:10px;margin-top:-5px;">
							<?php printf( __("Upload all invoices automatically to your dropbox!<br/>Check out the %s extension.", 'wpo_wcpdf'), $dropbox_link );?> <br />
						</div>
						<?php
					} 

					if (!class_exists('WooCommerce_PDF_IPS_Templates') && $active_tab == 'template') {
						$template_link = '<a href="https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/" target="_blank">wpovernight.com</a>';
						$email_link = '<a href="mailto:support@wpovernight.com">support@wpovernight.com</a>'
						?>
						<div class="wcpdf-pro-templates" style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-top: 15px; background-color: #eee;">
							<?php printf( __("Looking for more advanced templates? Check out the Premium PDF Invoice & Packing Slips templates at %s.", 'wpo_wcpdf'), $template_link );?> <br />
							<?php printf( __("For custom templates, contact us at %s.", 'wpo_wcpdf'), $email_link );?>
						</div>
						<?php
					}

					if ( $active_tab=='status' ) {
						$this->status_page();
					} else {
					?>
					<form method="post" action="options.php">
						<?php
							settings_fields( 'wpo_wcpdf_'.$active_tab.'_settings' );
							do_settings_sections( 'wpo_wcpdf_'.$active_tab.'_settings' );
	
							submit_button();
						?>
	
					</form>
					<?php
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
			global $woocommerce;
	
			/**************************************/
			/*********** GENERAL SETTINGS *********/
			/**************************************/
	
			$option = 'wpo_wcpdf_general_settings';
		
			// Create option in wp_options.
			if ( false == get_option( $option ) ) {
				add_option( $option );
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
				array( &$this, 'radio_element_callback' ),
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
			
			$tmp_path  = WooCommerce_PDF_Invoices::$plugin_path . 'tmp/';
			$tmp_path_check = !is_writable( $tmp_path );

			add_settings_field(
				'email_pdf',
				__( 'Attach invoice to:', 'wpo_wcpdf' ),
				array( &$this, 'multiple_checkbox_element_callback' ),
				$option,
				'general_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'email_pdf',
					'options' 		=> array(
						'new_order'			=> __( 'Admin New Order email' , 'wpo_wcpdf' ),
						'processing'		=> __( 'Customer Processing Order email' , 'wpo_wcpdf' ),
						'completed'			=> __( 'Customer Completed Order email' , 'wpo_wcpdf' ),
						'customer_invoice'	=> __( 'Customer Invoice email' , 'wpo_wcpdf' ),
					),
					'description'	=> $tmp_path_check ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'wpo_wcpdf' ), $tmp_path ).'</span>':'',
				)
			);

			add_settings_field(
				'invoice_number_column',
				__( 'Enable invoice number column in the orders list', 'wpo_wcpdf' ),
				array( &$this, 'checkbox_element_callback' ),
				$option,
				'general_settings',
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
			if ( false == get_option( $option ) ) {
				add_option( $option );
			}
	
			// Section.
			add_settings_section(
				'template_settings',
				__( 'PDF Template settings', 'wpo_wcpdf' ),
				array( &$this, 'section_options_callback' ),
				$option
			);

			add_settings_field(
				'template_path',
				__( 'Choose a template', 'wpo_wcpdf' ),
				array( &$this, 'select_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'template_path',
					'options' 		=> $this->find_templates(),
					'description'	=> __( 'Want to use your own template? Copy all the files from <code>woocommerce-pdf-invoices-packing-slips/templates/pdf/Simple/</code> to <code>yourtheme/woocommerce/pdf/yourtemplate/</code> to customize them' , 'wpo_wcpdf' ),
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

			/*
			add_settings_field(
				'personal_notes',
				__( 'Personal notes', 'wpo_wcpdf' ),
				array( &$this, 'textarea_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'			=> $option,
					'id'			=> 'personal_notes',
					'width'			=> '72',
					'height'		=> '4',
					//'description'			=> __( '...', 'wpo_wcpdf' ),
				)
			);
			 */
	
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

			add_settings_field(
				'display_number',
				__( 'Number to display on invoice', 'wpo_wcpdf' ),
				array( &$this, 'select_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'				=> $option,
					'id'				=> 'display_number',
					'options' 			=> array(
						'order_number'	=> __( 'WooCommerce order number' , 'wpo_wcpdf' ),
						'invoice_number'=> __( 'Built-in sequential invoice number' , 'wpo_wcpdf' ),
					),
					'description'		=> __( 'If you are using the WooCommerce Sequential Order Numbers plugin, select the WooCommerce order number', 'wpo_wcpdf' ),
				)
			);

			add_settings_field(
				'next_invoice_number',
				__( 'Next invoice number (without prefix/suffix etc.)', 'wpo_wcpdf' ),
				array( &$this, 'text_element_callback' ),
				$option,
				'template_settings',
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
				'template_settings',
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



			add_settings_field(
				'display_date',
				__( 'Date to display on invoice', 'wpo_wcpdf' ),
				array( &$this, 'select_element_callback' ),
				$option,
				'template_settings',
				array(
					'menu'				=> $option,
					'id'				=> 'display_date',
					'options' 			=> array(
						'order_date'	=> __( 'Order date' , 'wpo_wcpdf' ),
						'invoice_date'	=> __( 'Invoice date' , 'wpo_wcpdf' ),
					),
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

			// Register defaults if settings empty
			$option_values = get_option($option);
			if ( !isset( $option_values['paper_size'] ) ) {
				$this->default_settings();
			}

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
		}

		/**
		 * Set default settings.
		 */
		public function default_settings() {
			global $wpo_wcpdf;

			$default_general = array(
				'download_display'	=> 'download',
			);

			$default_template = array(
				'paper_size'		=> 'a4',
				'template_path'		=> $wpo_wcpdf->export->template_default_base_path . 'Simple',
			);

			update_option( 'wpo_wcpdf_general_settings', $default_general );
			update_option( 'wpo_wcpdf_template_settings', $default_template );
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
		
			$options = get_option( $menu );
		
			if ( isset( $options[$id] ) ) {
				$current = $options[$id];
			} else {
				$current = isset( $args['default'] ) ? $args['default'] : '';
			}
		
			$html = sprintf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1"%3$s />', $id, $menu, checked( 1, $current, false ) );
		
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
		
			$html = sprintf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $menu );
	
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
				$html .= sprintf('<span class="button remove_image_button" data-input_id="%1$s">%2$s</span>', $id, $remove_button_text );
			}

			$html .= sprintf( '<input id="%1$s" name="%2$s[%1$s]" type="hidden" value="%3$s" />', $id, $menu, $current );
			
			$html .= sprintf( '<span class="button upload_image_button %4$s" data-uploader_title="%1$s" data-uploader_button_text="%2$s" data-remove_button_text="%3$s" data-input_id="%4$s">%2$s</span>', $uploader_title, $uploader_button_text, $remove_button_text, $id );
		
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

			foreach ($fields as $key => $field) {
				$id = $args['id'] . '_' . $key;

				if ( isset( $options[$id] ) ) {
					$current = $options[$id];
				} else {
					$current = '';
				}

				$title = $field['title'];
				$size = $field['size'];
				$description = isset( $field['description'] ) ? '<span style="font-style:italic; margin-left:5px;">'.$field['description'].'</span>' : '';

				printf( '<span style="display:inline-block; width: 5em;">%1$s:</span><input type="text" id="%2$s" name="%3$s[%2$s]" value="%4$s" size="%5$s"/>%6$s<br/>', $title, $id, $menu, $current, $size, $description );
			}
		
			// Displays option description.
			if ( isset( $args['description'] ) ) {
				printf( '<p class="description">%s</p>', $args['description'] );
			}
		
			// echo $html;
		}


		/**
		 * Section null callback.
		 *
		 * @return void.
		 */
		public function section_options_callback() {
		}
		
		/**
		 * Section null callback.
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
		
			// Loop through each of the incoming options.
			foreach ( $input as $key => $value ) {
		
				// Check to see if the current option has a value. If so, process it.
				if ( isset( $input[$key] ) ) {
					// Strip all HTML and PHP tags and properly handle quoted strings.
					if ( is_array( $input[$key] ) ) {
						foreach ( $input[$key] as $sub_key => $sub_value ) {
							$output[$key][$sub_key] = strip_tags( $input[$key][$sub_key] );
						}

					} else {
						$output[$key] = strip_tags( $input[$key] );
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
						$installed_templates[$dir] = basename($dir);
				}
			}

			// remove parent doubles
			$installed_templates = array_unique($installed_templates);

			return $installed_templates;
		}
	
	} // end class WooCommerce_PDF_Invoices_Settings

} // end class_exists