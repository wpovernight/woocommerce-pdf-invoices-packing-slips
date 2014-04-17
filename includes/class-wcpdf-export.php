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
			$this->order = new WC_Order();
			$this->general_settings = get_option('wpo_wcpdf_general_settings');
			$this->template_settings = get_option('wpo_wcpdf_template_settings');

			$this->template_directory_name = 'pdf';
			$this->template_base_path = (defined('WC_TEMPLATE_PATH')?WC_TEMPLATE_PATH:$woocommerce->template_url) . $this->template_directory_name . '/';
			$this->template_default_base_path = WooCommerce_PDF_Invoices::$plugin_path . 'templates/' . $this->template_directory_name . '/';
			$this->template_default_base_uri = WooCommerce_PDF_Invoices::$plugin_url . 'templates/' . $this->template_directory_name . '/';

			$this->template_path = isset( $this->template_settings['template_path'] )?$this->template_settings['template_path']:'';

			add_action( 'wp_ajax_generate_wpo_wcpdf', array($this, 'generate_pdf_ajax' ));
			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf_to_email' ), 99, 3);
		}
		
		/**
		 * Generate the template output
		 */
		public function process_template( $template_type, $order_ids ) {
			$this->template_type = $template_type;
			$this->order_ids = $order_ids;

			$output_html = array();
			foreach ($order_ids as $order_id) {
				$this->order = new WC_Order( $order_id );
				$template = $this->template_path . '/' . $template_type . '.php';

				if (!file_exists($template)) {
					die('Template not found! Check if the following file exists: <pre>'.$template.'</pre><br/>');
				}

				// Set the invoice number
				$this->set_invoice_number( $order_id );

				$output_html[$order_id] = $this->get_template($template);

				// store meta to be able to check if an invoice for an order has been created already
				if ( $template_type == 'invoice' ) {
					update_post_meta( $order_id, '_wcpdf_invoice_exists', 1 );
				}

				// Wipe post from cache
				wp_cache_delete( $order_id, 'posts' );
				wp_cache_delete( $order_id, 'post_meta' );
			}

			// Try to clean up a bit of memory
			unset($this->order);

			$print_script = "<script language=javascript>window.onload = function(){ window.print(); };</script>";
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
				die('Template wrapper not found! Check if the following file exists: <pre>'.$template_wrapper.'</pre><br/>');
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

			require_once( WooCommerce_PDF_Invoices::$plugin_path . "lib/dompdf/dompdf_config.inc.php" );  
			$dompdf = new DOMPDF();
			$dompdf->load_html($this->process_template( $template_type, $order_ids ));
			$dompdf->set_paper( $paper_size, $paper_orientation );
			$dompdf->render();

			// Try to clean up a bit of memory
			unset($complete_pdf);

			return $dompdf;
		}

		/**
		 * Stream PDF
		 */
		public function stream_pdf( $template_type, $order_ids, $filename ) {
			$pdf = $this->generate_pdf( $template_type, $order_ids );
			$pdf->stream($filename);
		}
		
		/**
		 * Get PDF
		 */
		public function get_pdf( $template_type, $order_ids ) {
			$pdf = $this->generate_pdf( $template_type, $order_ids );
			return $pdf->output();
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
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'wpo_wcpdf' ) );
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
				$order = new WC_Order ( $order_ids[0] );
				$order_user = $order->user_id;
				// Destroy object to save memory
				unset($order);
				// Get user_id of current user
				$user_id = get_current_user_id();	

				// Check if current user is owner of order IMPORTANT!!!
				if ( $order_user != $user_id ) {
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
		
			$invoice = $this->get_pdf( $template_type, $order_ids );

			// get template name
			if ($template_type == 'invoice' ) {
				$template_name = _n( 'invoice', 'invoices', count($order_ids), 'wpo_wcpdf' );
			} else {
				$template_name = _n( 'packing-slip', 'packing-slips', count($order_ids), 'wpo_wcpdf' );
			}

			// Filename
			if ( count($order_ids) > 1 ) {
				$filename = $template_name . '-' . date('Y-m-d') . '.pdf'; // 'invoices-2020-11-11.pdf'
			} else {
				$display_number = $this->get_display_number( $order_ids[0] );
				$filename = $template_name . '-' . $display_number . '.pdf'; // 'packing-slip-123456.pdf'
			}

			$filename = apply_filters( 'wpo_wcpdf_bulk_filename', $filename, $order_ids, $template_name, $template_type );
	
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
			echo($invoice);

			exit;
		}

		/**
		 * Attach invoice to completed order or customer invoice email
		 */
		public function attach_pdf_to_email ( $attachments, $status, $order ) {
			if (!isset($this->general_settings['email_pdf']) || !isset( $status ) ) {
				return;
			}

			// clear temp folder (from http://stackoverflow.com/a/13468943/1446634)
			$tmp_path = WooCommerce_PDF_Invoices::$plugin_path . 'tmp/';
			array_map('unlink', ( glob( $tmp_path.'*' ) ? glob( $tmp_path.'*' ) : array() ) );

			// Relevant (default) statuses:
			// new_order
			// customer_invoice
			// customer_processing_order
			// customer_completed_order

			$allowed_statuses = array_keys( $this->general_settings['email_pdf'] );
			
			foreach ($allowed_statuses as $key => $order_status) {
				// convert 'lazy' status name
				if ($order_status == 'completed' || $order_status == 'processing') {
					$allowed_statuses[$key] = "customer_" . $order_status . "_order";
				}
			}

			if ( !( apply_filters('wpo_wcpdf_custom_email_condition', true, $order, $status ) ) ) {
				// use this filter to add an extra condition - return false to disable the PDF attachment
				return;
			}

			if( in_array( $status, $allowed_statuses ) ) {
				// create pdf data
				$invoice = $this->get_pdf( 'invoice', (array) $order->id );

				$display_number = $this->get_display_number( $order->id );
				$pdf_filename_prefix = __( 'invoice', 'wpo_wcpdf' );
				$pdf_filename = $pdf_filename_prefix . '-' . $display_number . '.pdf';
				$pdf_filename = apply_filters( 'wpo_wcpdf_attachment_filename', $pdf_filename, $display_number, $order->id );
				$pdf_path = $tmp_path . $pdf_filename;
				file_put_contents ( $pdf_path, $invoice );
				$attachments[] = $pdf_path;
			}

			do_action( 'wpo_wcpdf_email_attachment', $pdf_path );

			return $attachments;
		}

		public function set_invoice_number( $order_id ) {
			// first check: get invoice number from post meta
			$invoice_number = get_post_meta( $order_id, '_wcpdf_invoice_number', true );

			// add invoice number if it doesn't exist
			if ( empty($invoice_number) || !isset($invoice_number) ) {
				$next_invoice_number = $this->template_settings['next_invoice_number'];

				if ( empty($next_invoice_number) ) {
					// First time! Use order number as starting point.
					$order_number = ltrim($this->order->get_order_number(), '#');
					$invoice_number = $order_number;
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
			$invoice_number = get_post_meta( $order_id, '_wcpdf_invoice_number', true );

			return apply_filters( 'wpo_wcpdf_invoice_number', $invoice_number, $this->order->get_order_number(), $this->order->id, date_i18n( get_option( 'date_format' ), strtotime( $this->order->order_date ) ) );
		}

		public function get_display_number( $order_id ) {
			if ( !isset($this->order) ) {
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
				foreach ( $items as $item ) {
					// Array with data for the pdf template
					$data = array();
					
					// Set the id
					$data['product_id'] = $item['product_id'];
					$data['variation_id'] = $item['variation_id'];

					// Set item name
					$data['name'] = $item['name'];
					
					// Set item quantity
					$data['quantity'] = $item['qty'];

					// Set the line total (=after discount)
					$data['line_total'] = $this->wc_price( $item['line_total'] );
					$data['single_line_total'] = $this->wc_price( $item['line_total'] / $item['qty'] );
					$data['line_tax'] = $this->wc_price( $item['line_tax'] );
					$data['single_line_tax'] = $this->wc_price( $item['line_tax'] / $item['qty'] );
					$data['tax_rates'] = $this->get_tax_rate( $item['tax_class'], $item['line_total'], $item['line_tax'] );
					
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
					
					// Set item meta and replace it when it is empty
					$meta = new WC_Order_Item_Meta( $item['item_meta'] );	
					$data['meta'] = $meta->display( false, true );

					// Pass complete item array
					$data['item'] = $item;
					
					// Create the product to display more info
					$data['product'] = null;
					
					$product = $this->order->get_product_from_item( $item );
					
					// Checking fo existance, thanks to MDesigner0 
					if(!empty($product)) {
						// Set the thumbnail id
						$data['thumbnail_id'] = $this->get_thumbnail_id( $product->id );

						// Set the thumbnail server path
						$data['thumbnail_path'] = get_attached_file( $data['thumbnail_id'] );

						// Thumbnail (full img tag)
						if (apply_filters('wpo_wcpdf_use_path', true)) {
							// load img with server path by default
							$data['thumbnail'] = sprintf('<img width="90" height="90" src="%s" class="attachment-shop_thumbnail wp-post-image">', $data['thumbnail_path']);
						} else {
							// load img with http url when filtered
							$data['thumbnail'] = $product->get_image( 'shop_thumbnail', array( 'title' => '' ) );
						}
						
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

					$data_list[] = apply_filters( 'wpo_wcpdf_order_item_data', $data );
				}
			}

			return apply_filters( 'wpo_wcpdf_order_items_data', $data_list );
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
			$divider = ($type == 'single')?$item['qty']:1; //divide by 1 if $type is not 'single' (thus 'total')

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
		public function get_tax_rate( $tax_class, $line_total, $line_tax ) {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
				// WC 2.1 or newer is used
				if ( $line_tax == 0 ) {
					return '-'; // no need to determine tax rate...
				}

				// if (empty($tax_class))
				// $tax_class = 'standard';// does not appear to work anymore - get_rates does accept an empty tax_class though!
				
				$tax = new WC_Tax();
				$taxes = $tax->get_rates( $tax_class );

				$tax_rates = array();

				foreach ($taxes as $tax) {
					$tax_rates[$tax['label']] = round( $tax['rate'], 2 ).'%';
				}

				if (empty($tax_rates)) {
					// one last try: manually calculate
					if ( $line_total != 0) {
						$tax_rates[] = round( ($line_tax / $line_total)*100, 1 ).'%';
					} else {
						$tax_rates[] = '-';
					}
				}

				$tax_rates = implode(' ,', $tax_rates );
			} else {
				// Backwards compatibility: calculate tax from line items
				if ( $line_total != 0) {
					$tax_rates = round( ($line_tax / $line_total)*100, 1 ).'%';
				} else {
					$tax_rates = '-';
				}
			}
			
			return $tax_rates;
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
		public function get_thumbnail_id ( $product_id ) {
			global $woocommerce;
	
			if ( has_post_thumbnail( $product_id ) ) {
				$thumbnail_id = get_post_thumbnail_id ( $product_id );
			} elseif ( ( $parent_id = wp_get_post_parent_id( $product_id ) ) && has_post_thumbnail( $product_id ) ) {
				$thumbnail_id = get_post_thumbnail_id ( $parent_id );
			} else {
				$thumbnail_id = $woocommerce->plugin_url() . '/assets/images/placeholder.png';
			}
	
			return $thumbnail_id;
		}
		
	}

}