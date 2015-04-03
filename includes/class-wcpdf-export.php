<?php
/**
 * PDF Export class
 */
if ( ! class_exists( 'WooCommerce_PDF_Invoices_Export' ) ) {

	class WooCommerce_PDF_Invoices_Export {

		public $template_directory_name;
		public $template_base_path;
		public $template_default_base_path;
		public $template_default_base_uri;
		public $template_path;

		public $order;
		public $template_type;
		public $order_id;
		public $output_body;

		/**
		 * Constructor
		 */
		public function __construct() {					
			global $woocommerce;
			$this->order = new WC_Order('');
			$this->general_settings = get_option('wpo_wcpdf_general_settings');
			$this->template_settings = get_option('wpo_wcpdf_template_settings');
			$this->debug_settings = get_option('wpo_wcpdf_debug_settings');

			$this->template_directory_name = 'pdf';
			$this->template_base_path = (defined('WC_TEMPLATE_PATH')?WC_TEMPLATE_PATH:$woocommerce->template_url) . $this->template_directory_name . '/';
			$this->template_default_base_path = WooCommerce_PDF_Invoices::$plugin_path . 'templates/' . $this->template_directory_name . '/';
			$this->template_default_base_uri = WooCommerce_PDF_Invoices::$plugin_url . 'templates/' . $this->template_directory_name . '/';

			$this->template_path = isset( $this->template_settings['template_path'] )?$this->template_settings['template_path']:'';

			// backwards compatible template path (1.4.4+ uses relative paths instead of absolute)
			$backslash_abspath = str_replace('/', '\\', ABSPATH);
			if (strpos($this->template_path, ABSPATH) === false && strpos($this->template_path, $backslash_abspath) === false) {
				// add site base path, double check it exists!
				if ( file_exists( ABSPATH . $this->template_path ) ) {
					$this->template_path = ABSPATH . $this->template_path;
				}
			}

			if ( file_exists( $this->template_path . '/template-functions.php' ) ) {
				require_once( $this->template_path . '/template-functions.php' );
			}

			add_action( 'wp_ajax_generate_wpo_wcpdf', array($this, 'generate_pdf_ajax' ));
			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf_to_email' ), 99, 3);

			// check if an invoice number filter has already been registered, if not, use settings
			if ( !has_filter( 'wpo_wcpdf_invoice_number' ) ) {
				add_filter( 'wpo_wcpdf_invoice_number', array( $this, 'format_invoice_number' ), 20, 4 );
			}

			if ( isset($this->debug_settings['enable_debug'])) {
				$this->enable_debug();
			}

			if ( isset($this->debug_settings['html_output'])) {
				add_filter( 'wpo_wcpdf_output_html', '__return_true' );
				add_filter( 'wpo_wcpdf_use_path', '__return_false' );
			}

			if ( class_exists('WC_Subscriptions') ) {
				add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'reset_invoice_data' ), 10, 4 );
			}

		}

		/**
		 * Install/create plugin tmp folders
		 */
		public function init_tmp ( $tmp_base ) {
			// create plugin base temp folder
			@mkdir( $tmp_base );

			// create subfolders & protect
			$subfolders = array( 'attachments', 'fonts', 'dompdf' );
			foreach ( $subfolders as $subfolder ) {
				$path = $tmp_base . $subfolder . '/';
				@mkdir( $path );

				// copy font files
				if ( $subfolder == 'fonts' ) {
					$this->copy_fonts( $path );
				}

				// create .htaccess file and empty index.php to protect in case an open webfolder is used!
				@file_put_contents( $path . '.htaccess', 'deny from all' );
				@touch( $path . 'index.php' );
			}

		}

		/**
		 * Copy DOMPDF fonts to wordpress tmp folder
		 */
		public function copy_fonts ( $path ) {
			$dompdf_font_dir = WooCommerce_PDF_Invoices::$plugin_path . "lib/dompdf/lib/fonts/";

			// first try the easy way with glob!
			if ( function_exists('glob') ) {
				$files = glob($dompdf_font_dir."*.*");
				foreach($files as $file){
					if(!is_dir($file) && is_readable($file)) {
						$dest = $path . basename($file);
						copy($file, $dest);
					}
				}
			} else {
				// fallback method using font cache file (glob is disabled on some servers with disable_functions)
				$font_cache_file = $dompdf_font_dir . "dompdf_font_family_cache.php";
				$font_cache_dist_file = $dompdf_font_dir . "dompdf_font_family_cache.dist.php";
				$fonts = @require_once( $font_cache_file );
				$extensions = array( '.ttf', '.ufm', '.ufm.php', '.afm' );

				foreach ($fonts as $font_family => $filenames) {
					foreach ($filenames as $filename) {
						foreach ($extensions as $extension) {
							$file = $filename.$extension;
							if (file_exists($file)) {
								$dest = $path . basename($file);
								copy($file, $dest);
							}
						}
					}
				}

				// copy cache files separately
				copy($font_cache_file, $path.basename($font_cache_file));
				copy($font_cache_dist_file, $path.basename($font_cache_dist_file));
			}

		}

		/**
		 * Return tmp path for different plugin processes
		 */
		public function tmp_path ( $type = '' ) {
			// get temp setting
			$old_tmp = isset($this->debug_settings['old_tmp']);

			$tmp_base = $this->get_tmp_base();
			if (!$old_tmp) {
				// check if tmp folder exists => if not, initialize 
				if ( !@is_dir( $tmp_base ) ) {
					$this->init_tmp( $tmp_base );
				}
			}
			
			if ( empty( $type ) ) {
				return $tmp_base;
			}

			switch ( $type ) {
				case 'DOMPDF_TEMP_DIR':
					// original value : sys_get_temp_dir()
					// 1.5+           : $tmp_base . 'dompdf'
					$tmp_path = $old_tmp ? sys_get_temp_dir() : $tmp_base . 'dompdf';
					break;
				case 'DOMPDF_FONT_DIR': // NEEDS TRAILING SLASH!
					// original value : DOMPDF_DIR."/lib/fonts/"
					// 1.5+           : $tmp_base . 'fonts/'
					$tmp_path = $old_tmp ? DOMPDF_DIR."/lib/fonts/" : $tmp_base . 'fonts/';
					break;
				case 'DOMPDF_FONT_CACHE':
					// original value : DOMPDF_FONT_DIR
					// 1.5+           : $tmp_base . 'fonts'
					$tmp_path = $old_tmp ? DOMPDF_FONT_DIR : $tmp_base . 'fonts';
					break;
				case 'attachments':
					// original value : WooCommerce_PDF_Invoices::$plugin_path . 'tmp/'
					// 1.5+           : $tmp_base . 'attachments/'
					$tmp_path = $old_tmp ? WooCommerce_PDF_Invoices::$plugin_path . 'tmp/' : $tmp_base . 'attachments/';
					break;
				default:
					$tmp_path = $tmp_base . $type;
					break;
			}

			// double check for existence, in case tmp_base was installed, but subfolder not created
			if ( !@is_dir( $tmp_path ) ) {
				@mkdir( $tmp_path );
			}

			return $tmp_path;
		}

		/**
		 * return the base tmp folder (usually uploads)
		 */
		public function get_tmp_base () {
			// wp_upload_dir() is used to set the base temp folder, under which a
			// 'wpo_wcpdf' folder and several subfolders are created
			// 
			// wp_upload_dir() will:
			// * default to WP_CONTENT_DIR/uploads
			// * UNLESS the ‘UPLOADS’ constant is defined in wp-config (http://codex.wordpress.org/Editing_wp-config.php#Moving_uploads_folder)
			// 
			// May also be overridden by the wpo_wcpdf_tmp_path filter

			$upload_dir = wp_upload_dir();
			$upload_base = trailingslashit( $upload_dir['basedir'] );
			$tmp_base = trailingslashit( apply_filters( 'wpo_wcpdf_tmp_path', $upload_base . 'wpo_wcpdf/' ) );
			return $tmp_base;
		}
		
		/**
		 * Generate the template output
		 */
		public function process_template( $template_type, $order_ids ) {
			$this->template_type = $template_type;
			$order_ids = $this->order_ids = apply_filters( 'wpo_wcpdf_process_order_ids', $order_ids, $template_type );

			do_action( 'wpo_wcpdf_process_template', $template_type );

			$output_html = array();
			foreach ($order_ids as $order_id) {
				$this->order = new WC_Order( $order_id );
				do_action( 'wpo_wcpdf_process_template_order', $template_type, $order_id );

				$template = $this->template_path . '/' . $template_type . '.php';
				$template = apply_filters( 'wpo_wcpdf_template_file', $template, $template_type );

				if (!file_exists($template)) {
					throw new Exception('Template not found! Check if the following file exists: <pre>'.$template.'</pre><br/>');
				}

				// Set the invoice number
				if ( $template_type == 'invoice' ) {
					$this->set_invoice_number( $order_id );
				}

				$output_html[$order_id] = $this->get_template($template);

				// store meta to be able to check if an invoice for an order has been created already
				if ( $template_type == 'invoice' ) {
					update_post_meta( $order_id, '_wcpdf_invoice_exists', 1 );
				}


				// Wipe post from cache
				wp_cache_delete( $order_id, 'posts' );
				wp_cache_delete( $order_id, 'post_meta' );
			}

			$print_script = "<script language=javascript>window.onload = function(){ window.print(); };</script>";
			// <div style="page-break-before: always;"></div>
			$page_break = "\n<div style=\"page-break-before: always;\"></div>\n";


			if (apply_filters('wpo_wcpdf_output_html', false, $template_type) && apply_filters('wpo_wcpdf_print_html', false, $template_type)) {
				$this->output_body = $print_script . implode($page_break, $output_html);
			} else {
				$this->output_body = implode($page_break, $output_html);
			}

			// Try to clean up a bit of memory
			unset($output_html);

			$template_wrapper = $this->template_path . '/html-document-wrapper.php';

			if (!file_exists($template_wrapper)) {
				throw new Exception('Template wrapper not found! Check if the following file exists: <pre>'.$template_wrapper.'</pre><br/>');
			}		

			$complete_document = $this->get_template($template_wrapper);

			// Try to clean up a bit of memory
			unset($this->output_body);
			
			// clean up special characters
			$complete_document = utf8_decode(mb_convert_encoding($complete_document, 'HTML-ENTITIES', 'UTF-8'));


			return $complete_document;
		}

		/**
		 * Create & render DOMPDF object
		 */
		public function generate_pdf( $template_type, $order_ids )	{
			$paper_size = apply_filters( 'wpo_wcpdf_paper_format', $this->template_settings['paper_size'], $template_type );
			$paper_orientation = apply_filters( 'wpo_wcpdf_paper_orientation', 'portrait', $template_type);

			do_action( 'wpo_wcpdf_before_pdf', $template_type );
			if ( !class_exists('DOMPDF') ) {
				// extra check to avoid clashes with other plugins using DOMPDF
				// This could have unwanted side-effects when the version that's already
				// loaded is different, and it could also miss fonts etc, but it's better
				// than not checking...
				require_once( WooCommerce_PDF_Invoices::$plugin_path . "lib/dompdf/dompdf_config.inc.php" );
			}

			$dompdf = new DOMPDF();
			$dompdf->load_html($this->process_template( $template_type, $order_ids ));
			$dompdf->set_paper( $paper_size, $paper_orientation );
			$dompdf->render();
			do_action( 'wpo_wcpdf_after_pdf', $template_type );

			// Try to clean up a bit of memory
			unset($complete_pdf);

			return $dompdf;
		}

		/**
		 * Stream PDF
		 */
		public function stream_pdf( $template_type, $order_ids, $filename ) {
			$dompdf = $this->generate_pdf( $template_type, $order_ids );
			$dompdf->stream($filename);
		}
		
		/**
		 * Get PDF
		 */
		public function get_pdf( $template_type, $order_ids ) {
			try {
				$dompdf = $this->generate_pdf( $template_type, $order_ids );
				return $dompdf->output();
			} catch (Exception $e) {
				echo $e->getMessage();
				return false;
			}

		}

		/**
		 * Load and generate the template output with ajax
		 */
		public function generate_pdf_ajax() {
			// Check the nonce
			if( empty( $_GET['action'] ) || ! is_user_logged_in() || !check_admin_referer( $_GET['action'] ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wcpdf' ) );
			}
			
			// Check if all parameters are set
			if( empty( $_GET['template_type'] ) || empty( $_GET['order_ids'] ) ) {
				wp_die( __( 'Some of the export parameters are missing.', 'wpo_wcpdf' ) );
			}

			// Check the user privileges
			if( !current_user_can( 'manage_woocommerce_orders' ) && !current_user_can( 'edit_shop_orders' ) && !isset( $_GET['my-account'] ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wcpdf' ) );
			}

			$order_ids = (array) explode('x',$_GET['order_ids']);
			// Process oldest first: reverse $order_ids array
			$order_ids = array_reverse($order_ids);

			// User call from my-account page
			if ( isset( $_GET['my-account'] ) ) {
				// Only for single orders!
				if ( count( $order_ids ) > 1 ) {
					wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wcpdf' ) );
				}

				// Get user_id of order
				$this->order = new WC_Order ( $order_ids[0] );	

				// Check if current user is owner of order IMPORTANT!!!
				if ( $this->order->user_id != get_current_user_id() ) {
					wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wcpdf' ) );
				}

				// if we got here, we're safe to go!
			}
		
			// Generate the output
			$template_type = $_GET['template_type'];
			// die($this->process_template( $template_type, $order_ids )); // or use the filter switch below!
			
			if (apply_filters('wpo_wcpdf_output_html', false, $template_type)) {
				// Output html to browser for debug
				// NOTE! images will be loaded with the server path by default
				// use the wpo_wcpdf_use_path filter (return false) to change this to http urls
				die($this->process_template( $template_type, $order_ids ));
			}
		
			if ( !($pdf = $this->get_pdf( $template_type, $order_ids )) ) {
				exit;
			}

			$filename = $this->build_filename( $template_type, $order_ids, 'download' );

			do_action( 'wpo_wcpdf_created_manually', $pdf, $filename );

			// Get output setting
			$output_mode = isset($this->general_settings['download_display'])?$this->general_settings['download_display']:'';

			// Switch headers according to output setting
			if ( $output_mode == 'display' || empty($output_mode) ) {
				header('Content-type: application/pdf');
				header('Content-Disposition: inline; filename="'.$filename.'"');
			} else {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.$filename.'"'); 
				header('Content-Transfer-Encoding: binary');
				header('Connection: Keep-Alive');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}

			// output PDF data
			echo($pdf);

			exit;
		}

		/**
		 * Build filename
		 */
		public function build_filename( $template_type, $order_ids, $context ) {
			$count = count($order_ids);

			switch ($template_type) {
				case 'invoice':
					$name = _n( 'invoice', 'invoices', $count, 'wpo_wcpdf' );
					$number = $this->get_display_number( $order_ids[0] );
					break;		
				case 'packing-slip':
					$name = _n( 'packing-slip', 'packing-slips', $count, 'wpo_wcpdf' );
					$number = $this->order->get_order_number();
					break;
				default:
					$name = $template_type;
					$number = $this->order->get_order_number();
					break;
			}

			if ( $count == 1 ) {
				$suffix = $number;			
			} else {
				$suffix = date('Y-m-d'); // 2020-11-11
			}

			$filename = $name . '-' . $suffix . '.pdf';

			// Filter depending on context (for legacy filter support)
			if ( $context == 'download' ) {
				$filename = apply_filters( 'wpo_wcpdf_bulk_filename', $filename, $order_ids, $name, $template_type );
			} elseif ( $context == 'attachment' ) {
				$filename = apply_filters( 'wpo_wcpdf_attachment_filename', $filename, $number, $order_ids[0] );	
			}

			// Filter filename (use this filter instead of the above legacy filters!)
			$filename = apply_filters( 'wpo_wcpdf_filename', $filename, $template_type, $order_ids, $context );

			// sanitize filename (after filters to prevent human errors)!
			return sanitize_file_name( $filename );
		}

		/**
		 * Attach invoice to completed order or customer invoice email
		 */
		public function attach_pdf_to_email ( $attachments, $status, $order ) {
			// check if all variables properly set
			if ( !is_object( $order ) || !isset( $status ) ) {
				return $attachments;
			}

			// Skip User emails
			if ( get_class( $order ) == 'WP_User' ) {
				return $attachments;
			}

			// WooCommerce Booking compatibility
			if ( get_post_type( $order->id ) == 'wc_booking' && isset($order->order) ) {
				// $order is actually a WC_Booking object!
				$order = $order->order;
			}

			// do not process low stock notifications, user emails etc!
			if ( in_array( $status, array( 'no_stock', 'low_stock', 'backorder', 'customer_new_account', 'customer_reset_password' ) ) || get_post_type( $order->id ) != 'shop_order' ) {
				return $attachments; 
			}

			// Disable free setting check
			$order_total = $order->get_total();
			if ( $order_total == 0 && isset($this->general_settings['disable_free']) ) {
				return $attachments; 
			}

			$this->order = $order;

			$tmp_path = $this->tmp_path('attachments');

			// clear pdf files from temp folder (from http://stackoverflow.com/a/13468943/1446634)
			array_map('unlink', ( glob( $tmp_path.'*.pdf' ) ? glob( $tmp_path.'*.pdf' ) : array() ) );

			// set allowed statuses for invoices
			$invoice_allowed = isset($this->general_settings['email_pdf']) ? array_keys( $this->general_settings['email_pdf'] ) : array();
			$documents = array(
				'invoice'	=>  apply_filters( 'wpo_wcpdf_email_allowed_statuses', $invoice_allowed ), // Relevant (default) statuses: new_order, customer_invoice, customer_processing_order, customer_completed_order
			);
			$documents = apply_filters('wpo_wcpdf_attach_documents', $documents );
			
			foreach ($documents as $template_type => $allowed_statuses ) {
				// convert 'lazy' status name
				foreach ($allowed_statuses as $key => $order_status) {
					if ($order_status == 'completed' || $order_status == 'processing') {
						$allowed_statuses[$key] = "customer_" . $order_status . "_order";
					}
				}

				// legacy filter, use wpo_wcpdf_custom_attachment_condition instead!
				$attach_invoice = apply_filters('wpo_wcpdf_custom_email_condition', true, $order, $status );
				if ( $template_type == 'invoice' && !$attach_invoice ) {
					// don't attach invoice, continue with other documents
					continue;
				}

				// use this filter to add an extra condition - return false to disable the PDF attachment
				$attach_document = apply_filters('wpo_wcpdf_custom_attachment_condition', true, $order, $status, $template_type );
				if( in_array( $status, $allowed_statuses ) && $attach_document ) {
					// create pdf data
					$pdf_data = $this->get_pdf( $template_type, (array) $order->id );

					if ( !$pdf_data ) {
						// something went wrong, continue trying with other documents
						continue;
					}

					// compose filename
					$pdf_filename = $this->build_filename( $template_type, (array) $order->id, 'attachment' );

					$pdf_path = $tmp_path . $pdf_filename;
					file_put_contents ( $pdf_path, $pdf_data );
					$attachments[] = $pdf_path;

					do_action( 'wpo_wcpdf_email_attachment', $pdf_path, $template_type );
				}
			}
			
			return $attachments;
		}

		public function set_invoice_number( $order_id ) {
			// first check: get invoice number from post meta
			$invoice_number = get_post_meta( $order_id, '_wcpdf_invoice_number', true );

			// add invoice number if it doesn't exist
			if ( empty($invoice_number) || !isset($invoice_number) ) {
				$next_invoice_number = $this->template_settings['next_invoice_number'];

				if ( empty($next_invoice_number) ) {
					// First time! We start numbering from order_number or order_id
					
					// Check if $order_number is an integer
					$order_number = ltrim($this->order->get_order_number(), '#');
					if ( ctype_digit( (string)$order_number ) ) {
						// order_number == integer: use as starting point.
						$invoice_number = $order_number;
					} else {
						// fallback: use order_id as starting point.
						$invoice_number = $order_id;
					}

				} else {
					$invoice_number = $next_invoice_number;
				}
				// die($invoice_number);

				update_post_meta($order_id, '_wcpdf_invoice_number', $invoice_number);

				// increase next_order_number
				$template_settings = get_option('wpo_wcpdf_template_settings');
				$template_settings['next_invoice_number'] = $this->template_settings['next_invoice_number'] = $invoice_number+1;
				update_option( 'wpo_wcpdf_template_settings', $template_settings );
			}

			// store invoice_number in class object
			$this->invoice_number = $invoice_number;

			// store invoice number in _POST superglobal to prevent the number from being cleared in a save action
			// (http://wordpress.org/support/topic/customer-invoice-selection-from-order-detail-page-doesnt-record-invoice-id?replies=1)
			$_POST['_wcpdf_invoice_number'] = $invoice_number;

			return $invoice_number;
		}

		public function get_invoice_number( $order_id ) {
			// get invoice number from post meta
			if ( $invoice_number = get_post_meta( $order_id, '_wcpdf_invoice_number', true ) ) {
				// check if we have already loaded this order
				if ( $this->order->id == $order_id ) {
					$order_number = $this->order->get_order_number();
					$order_date = $this->order->order_date;
				} else {
					$order = new WC_Order( $order_id );
					$order_number = $order->get_order_number();
					$order_date = $order->order_date;
				}

				return apply_filters( 'wpo_wcpdf_invoice_number', $invoice_number, $order_number, $order_id, $order_date );
			} else {
				// no invoice number for this order
				return false;
			}
		}

		/**
		 * Reset invoice data for WooCommerce subscription renewal orders
		 * https://wordpress.org/support/topic/subscription-renewal-duplicate-invoice-number?replies=6#post-6138110
		 */
		public function reset_invoice_data ( $renewal_order, $original_order, $product_id, $new_order_role ) {
			// delete invoice number, invoice date & invoice exists meta
			delete_post_meta( $renewal_order->id, '_wcpdf_invoice_number' );
			delete_post_meta( $renewal_order->id, '_wcpdf_invoice_date' );
			delete_post_meta( $renewal_order->id, '_wcpdf_invoice_exists' );
		}

		public function format_invoice_number( $invoice_number, $order_number, $order_id, $order_date ) {
			// get format settings
			$order_year = date_i18n( 'Y', strtotime( $order_date ) );
			$order_month = date_i18n( 'm', strtotime( $order_date ) );

			
			$formats['prefix'] = isset($this->template_settings['invoice_number_formatting_prefix'])?$this->template_settings['invoice_number_formatting_prefix']:'';
			$formats['suffix'] = isset($this->template_settings['invoice_number_formatting_suffix'])?$this->template_settings['invoice_number_formatting_suffix']:'';
			$formats['padding'] = isset($this->template_settings['invoice_number_formatting_padding'])?$this->template_settings['invoice_number_formatting_padding']:'';

			// Replacements
			foreach ($formats as $key => $value) {
				$value = str_replace('[order_year]', $order_year, $value);
				$value = str_replace('[order_month]', $order_month, $value);
				$formats[$key] = $value;
			}

			// Padding - minimum of 3 for safety
			if ( ctype_digit( (string)$formats['padding'] ) && $formats['padding'] > 3 ) {
				$invoice_number = sprintf('%0'.$formats['padding'].'d', $invoice_number);
			}

			$formatted_invoice_number = $formats['prefix'] . $invoice_number . $formats['suffix'] ;

			return $formatted_invoice_number;
		}

		public function get_display_number( $order_id ) {
			if ( !isset($this->order->id) ) {
				$this->order = new WC_Order ( $order_id );
			}

			if ( isset($this->template_settings['display_number']) && $this->template_settings['display_number'] == 'invoice_number' ) {
				// use invoice number
				$display_number = $this->get_invoice_number( $order_id );
				// die($display_number);
			} else {
				// use order number
				$display_number = ltrim($this->order->get_order_number(), '#');
			}

			return $display_number;
		}

		/**
		 * Return evaluated template contents
		 */
		public function get_template( $file ) {
			ob_start();
			if (file_exists($file)) {
				include($file);
			}
			return ob_get_clean();
		}			
		
		/**
		 * Get the current order
		 */
		public function get_order() {
			return $this->order;
		}

		/**
		 * Get the current order items
		 */
		public function get_order_items() {
			global $woocommerce;
			global $_product;

			$items = $this->order->get_items();
			$data_list = array();
		
			if( sizeof( $items ) > 0 ) {
				foreach ( $items as $item_id => $item ) {
					// Array with data for the pdf template
					$data = array();

					// Set the item_id
					$data['item_id'] = $item_id;
					
					// Set the id
					$data['product_id'] = $item['product_id'];
					$data['variation_id'] = $item['variation_id'];

					// Set item name
					$data['name'] = $item['name'];
					
					// Set item quantity
					$data['quantity'] = $item['qty'];

					// Set the line total (=after discount)
					$data['line_total'] = $this->wc_price( $item['line_total'] );
					$data['single_line_total'] = $this->wc_price( $item['line_total'] / max( 1, $item['qty'] ) );
					$data['line_tax'] = $this->wc_price( $item['line_tax'] );
					$data['single_line_tax'] = $this->wc_price( $item['line_tax'] / max( 1, $item['qty'] ) );
					
					$line_tax_data = maybe_unserialize( isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '' );
					$data['tax_rates'] = $this->get_tax_rate( $item['tax_class'], $item['line_total'], $item['line_tax'], $line_tax_data );
					
					// Set the line subtotal (=before discount)
					$data['line_subtotal'] = $this->wc_price( $item['line_subtotal'] );
					$data['line_subtotal_tax'] = $this->wc_price( $item['line_subtotal_tax'] );
					$data['ex_price'] = $this->get_formatted_item_price ( $item, 'total', 'excl' );
					$data['price'] = $this->get_formatted_item_price ( $item, 'total' );
					$data['order_price'] = $this->order->get_formatted_line_subtotal( $item ); // formatted according to WC settings

					// Calculate the single price with the same rules as the formatted line subtotal (!)
					// = before discount
					$data['ex_single_price'] = $this->get_formatted_item_price ( $item, 'single', 'excl' );
					$data['single_price'] = $this->get_formatted_item_price ( $item, 'single' );

					// Pass complete item array
					$data['item'] = $item;
					
					// Create the product to display more info
					$data['product'] = null;
					
					$product = $this->order->get_product_from_item( $item );
					
					// Checking fo existance, thanks to MDesigner0 
					if(!empty($product)) {
						// Set the thumbnail id DEPRICATED (does not support thumbnail sizes), use thumbnail_path or thumbnail instead
						$data['thumbnail_id'] = $this->get_thumbnail_id( $product );

						// Thumbnail (full img tag)
						$data['thumbnail'] = $this->get_thumbnail ( $product );

						// Set the single price (turned off to use more consistent calculated price)
						// $data['single_price'] = woocommerce_price ( $product->get_price() );
										
						// Set item SKU
						$data['sku'] = $product->get_sku();
		
						// Set item weight
						$data['weight'] = $product->get_weight();
						
						// Set item dimensions
						$data['dimensions'] = $product->get_dimensions();
					
						// Pass complete product object
						$data['product'] = $product;
					
					}
					
					// Set item meta
					$meta = new WC_Order_Item_Meta( $item['item_meta'], $product );
					$data['meta'] = $meta->display( false, true );

					$data_list[$item_id] = apply_filters( 'wpo_wcpdf_order_item_data', $data, $this->order );
				}
			}

			return apply_filters( 'wpo_wcpdf_order_items_data', $data_list, $this->order );
		}
		
		/**
		 * Gets price - formatted for display.
		 *
		 * @access public
		 * @param mixed $item
		 * @return string
		 */
		public function get_formatted_item_price ( $item, $type, $tax_display = '' ) {
			$item_price = 0;
			$divider = ($type == 'single' && $item['qty'] != 0 )?$item['qty']:1; //divide by 1 if $type is not 'single' (thus 'total')

			if ( ! isset( $item['line_subtotal'] ) || ! isset( $item['line_subtotal_tax'] ) ) 
				return;

			if ( $tax_display == 'excl' ) {
				$item_price = $this->wc_price( ($this->order->get_line_subtotal( $item )) / $divider );
			} else {
				$item_price = $this->wc_price( ($this->order->get_line_subtotal( $item, true )) / $divider );
			}

			return $item_price;
		}

		/**
		 * wrapper for wc2.1 depricated price function
		 */
		public function wc_price( $price, $args = array() ) {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
				// WC 2.1 or newer is used
				$args['currency'] = $this->order->get_order_currency();
				$formatted_price = wc_price( $price, $args );
			} else {
				$formatted_price = woocommerce_price( $price );
			}

			return $formatted_price;
		}

		/**
		 * Get the tax rates/percentages for a given tax class
		 * @param  string $tax_class tax class slug
		 * @return string $tax_rates imploded list of tax rates
		 */
		public function get_tax_rate( $tax_class, $line_total, $line_tax, $line_tax_data = '' ) {
			if ( $line_tax == 0 ) {
				return '-'; // no need to determine tax rate...
			}

			// first try the easy wc2.2 way, using line_tax_data
			if ( !empty( $line_tax_data ) && isset($line_tax_data['total']) ) {
				$tax_rates = array();

				$line_taxes = $line_tax_data['total'];
				foreach ( $line_taxes as $tax_id => $tax ) {
					if ( !empty($tax) && $tax != 0 ) {
						$tax_rates[] = $this->get_tax_rate_by_id( $tax_id ) . ' %';
					}
				}

				$tax_rates = implode(' ,', $tax_rates );
				return $tax_rates;
			}

			if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 && !apply_filters( 'wpo_wcpdf_calculate_tax_rate', false ) ) {
				// WC 2.1 or newer is used

				// if (empty($tax_class))
				// $tax_class = 'standard';// does not appear to work anymore - get_rates does accept an empty tax_class though!
				
				$tax = new WC_Tax();
				$taxes = $tax->get_rates( $tax_class );

				$tax_rates = array();

				foreach ($taxes as $tax) {
					$tax_rates[$tax['label']] = round( $tax['rate'], 2 ).' %';
				}

				if (empty($tax_rates)) {
					// one last try: manually calculate
					if ( $line_total != 0) {
						$tax_rates[] = round( ($line_tax / $line_total)*100, 1 ).' %';
					} else {
						$tax_rates[] = '-';
					}
				}

				$tax_rates = implode(' ,', $tax_rates );
			} else {
				// Backwards compatibility/fallback: calculate tax from line items
				if ( $line_total != 0) {
					$tax_rates = round( ($line_tax / $line_total)*100, 1 ).' %';
				} else {
					$tax_rates = '-';
				}
			}
			
			return $tax_rates;
		}

		/**
		 * Returns the percentage rate (float) for a given tax rate ID.
		 * @param  int    $rate_id  woocommerce tax rate id
		 * @return float  $rate     percentage rate
		 */
		public function get_tax_rate_by_id( $rate_id ) {
			global $wpdb;
			$rate = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %d;", $rate_id ) );
			return (float) $rate;
		}

		/**
		 * Returns a an array with rate_id => tax rate data (array) of all tax rates in woocommerce
		 * @return array  $tax_rate_ids  keyed by id
		 */
		public function get_tax_rate_ids() {
			global $wpdb;
			$rates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_tax_rates" );

			$tax_rate_ids = array();
			foreach ($rates as $rate) {
				// var_dump($rate->tax_rate_id);
				// die($rate);
				$rate_id = $rate->tax_rate_id;
				unset($rate->tax_rate_id);
				$tax_rate_ids[$rate_id] = (array) $rate;
			}

			return $tax_rate_ids;
		}

		/**
		 * Get order custom field
		 */
		public function get_order_field( $field ) {
			if( isset( $this->get_order()->order_custom_fields[$field] ) ) {
				return $this->get_order()->order_custom_fields[$field][0];
			} 
			return;
		}

		/**
		 * Returns the main product image ID
		 * Adapted from the WC_Product class
		 *
		 * @access public
		 * @return string
		 */
		public function get_thumbnail_id ( $product ) {
			// DEPRICATED (does not support thumbnail sizes)
			global $woocommerce;
	
	    	if ( $product->variation_id && has_post_thumbnail( $product->variation_id ) ) {
				$thumbnail_id = get_post_thumbnail_id ( $product->variation_id );
			} elseif ( has_post_thumbnail( $product->id ) ) {
				$thumbnail_id = get_post_thumbnail_id ( $product->id );
			} elseif ( ( $parent_id = wp_get_post_parent_id( $product->id ) ) && has_post_thumbnail( $parent_id ) ) {
				$thumbnail_id = get_post_thumbnail_id ( $parent_id );
			} else {
				$thumbnail_id = $woocommerce->plugin_url() . '/assets/images/placeholder.png';
			}
	
			return $thumbnail_id;
		}

		public function get_thumbnail ( $product ) {
			// Get default WooCommerce img tag (url/http)
			$size = apply_filters( 'wpo_wcpdf_thumbnail_size', 'shop_thumbnail' );
			$thumbnail_img_tag_url = $product->get_image( $size, array( 'title' => '' ) );
			
			// Extract the url from img
			preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $thumbnail_img_tag_url, $thumbnail_url );
			// convert url to path
			$thumbnail_path = str_replace( get_site_url() . '/', ABSPATH, array_pop($thumbnail_url));

			// Thumbnail (full img tag)
			if (apply_filters('wpo_wcpdf_use_path', true) && file_exists($thumbnail_path)) {
				// load img with server path by default
				$thumbnail = sprintf('<img width="90" height="90" src="%s" class="attachment-shop_thumbnail wp-post-image">', $thumbnail_path );
			} else {
				// load img with http url when filtered
				$thumbnail = $thumbnail_img_tag_url;
			}

			// die($thumbnail);

			return $thumbnail;
		}
		
		public function enable_debug () {
			error_reporting( E_ALL );
			ini_set( 'display_errors', 1 );
		}

		/**
		 * Log messages
		 */

		public function log( $order_id, $message ) {
			$current_date_time = date("Y-m-d H:i:s");
			$message = $order_id . ' ' . $current_date_time .' ' .$message ."\n";
			$file = $this->tmp_path() . 'log.txt';

			file_put_contents($file, $message, FILE_APPEND);
		}
	}

}
