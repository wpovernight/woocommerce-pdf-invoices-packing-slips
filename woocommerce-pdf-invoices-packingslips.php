<?php
/**
 * Plugin Name: WooCommerce PDF Invoices & Packing Slips
 * Plugin URI: http://www.wpovernight.com
 * Description: Create, print & email PDF invoices & packing slips for WooCommerce orders.
 * Version: 1.4.7
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: wpo_wcpdf
 */

if ( !class_exists( 'WooCommerce_PDF_Invoices' ) ) {

	class WooCommerce_PDF_Invoices {
	
		public static $plugin_prefix;
		public static $plugin_url;
		public static $plugin_path;
		public static $plugin_basename;
		
		public $writepanels;
		public $settings;
		public $export;

		/**
		 * Constructor
		 */
		public function __construct() {
			self::$plugin_prefix = 'wpo_wcpdf_';
			self::$plugin_basename = plugin_basename(__FILE__);
			self::$plugin_url = plugin_dir_url(self::$plugin_basename);
			self::$plugin_path = trailingslashit(dirname(__FILE__));
			
			// load the localisation & classes
			add_action( 'plugins_loaded', array( $this, 'translations' ) ); // or use init?
			add_action( 'init', array( $this, 'load_classes' ) );

		}

		/**
		 * Load the translation / textdomain files
		 */
		public function translations() {
			load_plugin_textdomain( 'wpo_wcpdf', false, dirname( self::$plugin_basename ) . '/languages' );
		}

		/**
		 * Load the main plugin classes and functions
		 */
		public function includes() {
			include_once( 'includes/class-wcpdf-settings.php' );
			include_once( 'includes/class-wcpdf-writepanels.php' );
			include_once( 'includes/class-wcpdf-export.php' );
		}
		

		/**
		 * Instantiate classes when woocommerce is activated
		 */
		public function load_classes() {
			if ( $this->is_woocommerce_activated() ) {
				$this->includes();
				$this->settings = new WooCommerce_PDF_Invoices_Settings();
				$this->writepanels = new WooCommerce_PDF_Invoices_Writepanels();
				$this->export = new WooCommerce_PDF_Invoices_Export();
			} else {
				// display notice instead
				add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
			}

		}

		/**
		 * Check if woocommerce is activated
		 */
		public function is_woocommerce_activated() {
			$blog_plugins = get_option( 'active_plugins', array() );
			$site_plugins = get_site_option( 'active_sitewide_plugins', array() );

			if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * WooCommerce not active notice.
		 *
		 * @return string Fallack notice.
		 */
		 
		public function need_woocommerce() {
			$error = sprintf( __( 'WooCommerce PDF Invoices & Packing Slips requires %sWooCommerce%s to be installed & activated!' , 'wpo_wcpdf' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
			
			$message = '<div class="error"><p>' . $error . '</p></div>';
		
			echo $message;
		}

		/***********************************************************************/
		/********************** GENERAL TEMPLATE FUNCTIONS *********************/
		/***********************************************************************/

		/**
		 * Output template styles
		 */
		public function template_styles() {
			$css = apply_filters( 'wpo_wcpdf_template_styles', $this->export->template_path. '/' .'style.css' );

			ob_start();
			if (file_exists($css)) {
				include($css);
			}
			$html = ob_get_clean();			
			$html = apply_filters( 'wpo_wcpdf_template_styles', $html );
			
			echo $html;
		}				

		/**
		 * Return logo id
		 */
		public function get_header_logo_id() {
			if (isset($this->settings->template_settings['header_logo'])) {
				return apply_filters( 'wpo_wcpdf_header_logo_id', $this->settings->template_settings['header_logo'] );
			}
		}
	
		/**
		 * Show logo html
		 */
		public function header_logo() {
			if ($this->get_header_logo_id()) {
				$attachment_id = $this->get_header_logo_id();
				$company = isset($this->settings->template_settings['shop_name'])? $this->settings->template_settings['shop_name'] : '';
				if( $attachment_id ) {
					$attachment = wp_get_attachment_image_src( $attachment_id, 'full', false );
					
					$attachment_src = $attachment[0];
					$attachment_width = $attachment[1];
					$attachment_height = $attachment[2];

					$attachment_path = get_attached_file( $attachment_id );

					if ( apply_filters('wpo_wcpdf_use_path', true) && file_exists($attachment_path) ) {
						$src = $attachment_path;
					} else {
						$src = $attachment_src;
					}
					
					printf('<img src="%1$s" width="%2$d" height="%3$d" alt="%4$s" />', $src, $attachment_width, $attachment_height, esc_attr( $company ) );
				}
			}
		}
	
		/**
		 * Return/Show custom company name or default to blog name
		 */
		public function get_shop_name() {
			if (!empty($this->settings->template_settings['shop_name'])) {
				$name = trim( $this->settings->template_settings['shop_name'] );
				return apply_filters( 'wpo_wcpdf_shop_name', wptexturize( $name ) );
			} else {
				return apply_filters( 'wpo_wcpdf_shop_name', get_bloginfo( 'name' ) );
			}
		}
		public function shop_name() {
			echo $this->get_shop_name();
		}
		
		/**
		 * Return/Show shop/company address if provided
		 */
		public function get_shop_address() {
			if (isset($this->settings->template_settings['shop_address']))
				return apply_filters( 'wpo_wcpdf_shop_address', wpautop( wptexturize( $this->settings->template_settings['shop_address'] ) ) );
		}
		public function shop_address() {
			echo $this->get_shop_address();
		}
		
		/**
		 * Return/Show billing address
		 */
		public function get_billing_address() {
			$address = $this->export->order->get_formatted_billing_address();
			if( !$address ) {
				$address = __('N/A', 'wpo_wcpdf');
			}
			return apply_filters( 'wpo_wcpdf_billing_address', $address );
		}
		public function billing_address() {
			echo $this->get_billing_address();
		}

		/**
		 * Return/Show billing email
		 */
		public function get_billing_email() {
			$billing_email =$this->export->order->billing_email;
			return apply_filters( 'wpo_wcpdf_billing_email', $billing_email );
		}
		public function billing_email() {
			echo $this->get_billing_email();
		}
		
		/**
		 * Return/Show billing phone
		 */
		public function get_billing_phone() {
			$billing_phone =$this->export->order->billing_phone;
			return apply_filters( 'wpo_wcpdf_billing_phone', $billing_phone );
		}
		public function billing_phone() {
			echo $this->get_billing_phone();
		}
		
		/**
		 * Return/Show shipping address
		 */
		public function get_shipping_address() {
			$address = $this->export->order->get_formatted_shipping_address();
			if( !$address ) {
				$address = __('N/A', 'wpo_wcpdf');
			}
			return apply_filters( 'wpo_wcpdf_shipping_address', $address );
		}
		public function shipping_address() {
			echo $this->get_shipping_address();
		}

		/**
		 * Return/Show a custom field
		 */		
		public function custom_field( $field_name, $field_label = '', $display_empty = false ) {
			$custom_field = get_post_meta($this->export->order->id,$field_name,true);
			if (!empty($field_label)){
				// add a a trailing space to the label
				$field_label .= ' ';
			}

			if (!empty($custom_field) || $display_empty) {
				echo $field_label . nl2br ($custom_field);
			}
		}
	
		/**
		 * Return/Show the current date
		 */
		public function get_current_date() {
			return apply_filters( 'wpo_wcpdf_date', date_i18n( get_option( 'date_format' ) ) );
		}
		public function current_date() {
			echo $this->get_current_date();
		}

		/**
		 * Return/Show payment method  
		 */
		public function get_payment_method() {
			$Payment_method_label = __( 'Payment method', 'wpo_wcpdf' );
			return apply_filters( 'wpo_wcpdf_payment_method', __( $this->export->order->payment_method_title, 'woocommerce' ) );
		}
		public function payment_method() {
			echo $this->get_payment_method();
		}

		/**
		 * Return/Show shipping method  
		 */
		public function get_shipping_method() {
			$shipping_method_label = __( 'Shipping method', 'wpo_wcpdf' );
			return apply_filters( 'wpo_wcpdf_shipping_method', __( $this->export->order->get_shipping_method(), 'woocommerce' ) );
		}
		public function shipping_method() {
			echo $this->get_shipping_method();
		}

		/**
		 * Return/Show order number (or invoice number)
		 */
		public function get_order_number() {
			// Trim the hash to have a clean number but still 
			// support any filters that were applied before.
			$order_number = ltrim($this->export->order->get_order_number(), '#');
			return apply_filters( 'wpo_wcpdf_order_number', $order_number);
		}
		public function order_number() {
			echo $this->get_order_number();
		}

		/**
		 * Return/Show invoice number 
		 */
		public function get_invoice_number() {
			$invoice_number = $this->export->get_invoice_number( $this->export->order->id );
			return $invoice_number;
		}
		public function invoice_number() {
			echo $this->get_invoice_number();
		}

		/**
		 * Return/Show the order date
		 */
		public function get_order_date() {
			$date = date_i18n( get_option( 'date_format' ), strtotime( $this->export->order->order_date ) );
			return apply_filters( 'wpo_wcpdf_order_date', $date );
		}
		public function order_date() {
			echo $this->get_order_date();
		}

		/**
		 * Return/Show the invoice date
		 */
		public function get_invoice_date() {
			$invoice_date = get_post_meta($this->export->order->id,'_wcpdf_invoice_date',true);

			// add invoice date if it doesn't exist
			if ( empty($invoice_date) || !isset($invoice_date) ) {
				$invoice_date = current_time('mysql');
				update_post_meta( $this->export->order->id, '_wcpdf_invoice_date', $invoice_date );
			}

			$formatted_invoice_date = date_i18n( get_option( 'date_format' ), strtotime( $invoice_date ) );

			return apply_filters( 'wpo_wcpdf_invoice_date', $formatted_invoice_date, $invoice_date );
		}
		public function invoice_date() {
			echo $this->get_invoice_date();
		}

		/**
		 * Return the order items
		 */
		public function get_order_items() {
			return apply_filters( 'wpo_wcpdf_order_items', $this->export->get_order_items() );
		}
	
		/**
		 * Return the order totals listing
		 */
		public function get_woocommerce_totals() {
			// get totals and remove the semicolon
			$totals = apply_filters( 'wpo_wcpdf_raw_order_totals', $this->export->order->get_order_item_totals() );
			
			// remove the colon for every label
			foreach ( $totals as $key => $total ) {
				$label = $total['label'];
				$colon = strrpos( $label, ':' );
				if( $colon !== false ) {
					$label = substr_replace( $label, '', $colon, 1 );
				}		
				$totals[$key]['label'] = $label;
			}
	
			return apply_filters( 'wpo_wcpdf_woocommerce_totals', $totals );
		}
		
		/**
		 * Return/show the order subtotal
		 */
		public function get_order_subtotal( $tax = 'excl', $discount = 'incl' ) { // set $tax to 'incl' to include tax, same for $discount
			//$compound = ($discount == 'incl')?true:false;
			
			$subtotal = $this->export->order->get_subtotal_to_display( false, $tax );
			
			$subtotal = ($pos = strpos($subtotal, ' <small>')) ? substr($subtotal, 0, $pos) : $subtotal; //removing the 'excluding tax' text			
			
			$subtotal = array (
				'label'	=> __('Subtotal', 'wpo_wcpdf'),
				'value'	=> $subtotal, 
			);
			
			return apply_filters( 'wpo_wcpdf_order_subtotal', $subtotal );
		}
		public function order_subtotal( $tax = 'excl', $discount = 'incl' ) {
			$subtotal = $this->get_order_subtotal( $tax, $discount );
			echo $subtotal['value'];
		}
	
		/**
		 * Return/show the order shipping costs
		 */
		public function get_order_shipping( $tax = 'excl' ) { // set $tax to 'incl' to include tax
			if ($tax == 'excl' ) {
				$shipping_costs = woocommerce_price ( $this->export->order->order_shipping );
			} else {
				$shipping_costs = woocommerce_price ( $this->export->order->order_shipping + $this->export->order->order_shipping_tax );
			}

			$shipping = array (
				'label'	=> __('Shipping', 'wpo_wcpdf'),
				'value'	=> $shipping_costs,
			);
			return apply_filters( 'wpo_wcpdf_order_shipping', $shipping );
		}
		public function order_shipping( $tax = 'excl' ) {
			$shipping = $this->get_order_shipping( $tax );
			echo $shipping['value'];
		}

		/**
		 * Return/show the total discount
		 */
		public function get_order_discount( $type = 'total' ) {
			switch ($type) {
				case 'cart':
					// Cart Discount - pre-tax discounts.
					$discount_value = $this->export->order->get_cart_discount();
					break;
				case 'order':
					// Order Discount - post-tax discounts.
					$discount_value = $this->export->order->get_order_discount();
					break;
				case 'total':
					// Total Discount - Cart & Order Discounts combined
					$discount_value = $this->export->order->get_total_discount();
					break;
				default:
					// Total Discount - Cart & Order Discounts combined
					$discount_value = $this->export->order->get_total_discount();
					break;
			}

			$discount = array (
				'label'	=> __('Discount', 'wpo_wcpdf'),
				'value'	=> $this->export->wc_price($discount_value),
			);

			if ( $discount_value > 0 ) {
				return apply_filters( 'wpo_wcpdf_order_discount', $discount );
			}
		}
		public function order_discount( $type = 'total' ) {
			$discount = $this->get_order_discount( $type );
			echo $discount['value'];
		}

		/**
		 * Return the order fees
		 */
		public function get_order_fees( $tax = 'excl' ) {
			if ( $wcfees = $this->export->order->get_fees() ) {
				foreach( $wcfees as $id => $fee ) {
					if ($tax == 'excl' ) {
						$fee_price = woocommerce_price( $fee['line_total'] );
					} else {
						$fee_price = woocommerce_price( $fee['line_total'] + $fee['line_tax'] );
					}


					$fees[ $id ] = array(
						'label' => $fee['name'],
						'value'	=> $fee_price
					);
				}
				return $fees;
			}
		}
		
		/**
		 * Return the order taxes
		 */
		public function get_order_taxes() {
			$tax_label = __( 'VAT', 'wpo_wcpdf' ); // register alternate label translation
			$tax_rate_ids = $this->export->get_tax_rate_ids();
			if ($this->export->order->get_taxes()) {
				foreach ( $this->export->order->get_taxes() as $key => $tax ) {
					$taxes[ $key ] = array(
						'label'					=> isset( $tax[ 'label' ] ) ? $tax[ 'label' ] : $tax[ 'name' ],
						'value'					=> woocommerce_price( ( $tax[ 'tax_amount' ] + $tax[ 'shipping_tax_amount' ] ) ),
						'rate_id'				=> $tax['rate_id'],
						'tax_amount'			=> $tax['tax_amount'],
						'shipping_tax_amount'	=> $tax['shipping_tax_amount'],
						'rate'					=> isset( $tax_rate_ids[ $tax['rate_id'] ] ) ? ( (float) $tax_rate_ids[$tax['rate_id']]['tax_rate'] ) . ' %': '',
					);
				}
				
				return apply_filters( 'wpo_wcpdf_order_taxes', $taxes );
			}
		}

		/**
		 * Return/show the order grand total
		 */
		public function get_order_grand_total( $tax = 'incl' ) {
			if ($tax == 'excl' ) {
				$total_tax = 0;
				foreach ( $this->export->order->get_taxes() as $tax ) {
					$total_tax += ( $tax[ 'tax_amount' ] + $tax[ 'shipping_tax_amount' ] );
				}
				
				if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
					// WC 2.1 or newer is used
					$total_unformatted = $this->export->order->get_total();
				} else {
					// Backwards compatibility
					$total_unformatted = $this->export->order->get_order_total();
				}

				$total = woocommerce_price( ( $total_unformatted - $total_tax ) );
				$label = __('Total ex. VAT');
			} else {
				$total = $this->export->order->get_formatted_order_total();
				$label = __('Total');
			}
			
			$grand_total = array(
				'label' => $label,
				'value'	=> $total,
			);			

			return apply_filters( 'wpo_wcpdf_order_grand_total', $grand_total );
		}
		public function order_grand_total( $tax = 'incl' ) {
			$grand_total = $this->get_order_grand_total( $tax );
			echo $grand_total['value'];
		}


		/**
		 * Return/Show shipping notes
		 */
		public function get_shipping_notes() {
			$shipping_notes = wpautop( wptexturize( $this->export->order->customer_note ) );
			return apply_filters( 'wpo_wcpdf_shipping_notes', $shipping_notes );
		}
		public function shipping_notes() {
			echo $this->get_shipping_notes();
		}
		
	
		/**
		 * Return/Show shop/company footer imprint, copyright etc.
		 */
		public function get_footer() {
			if (isset($this->settings->template_settings['footer'])) {
				$footer = wpautop( wptexturize( $this->settings->template_settings[ 'footer' ] ) );
				return apply_filters( 'wpo_wcpdf_footer', $footer );
			}
		}
		public function footer() {
			echo $this->get_footer();
		}

		/**
		 * Return/Show Extra field 1
		 */
		public function get_extra_1() {
			if (isset($this->settings->template_settings['extra_1'])) {
				$extra_1 = nl2br( wptexturize( $this->settings->template_settings[ 'extra_1' ] ) );
				return apply_filters( 'wpo_wcpdf_extra_1', $extra_1 );
			}
		}
		public function extra_1() {
			echo $this->get_extra_1();
		}

		/**
		 * Return/Show Extra field 2
		 */
		public function get_extra_2() {
			if (isset($this->settings->template_settings['extra_2'])) {
				$extra_2 = nl2br( wptexturize( $this->settings->template_settings[ 'extra_2' ] ) );
				return apply_filters( 'wpo_wcpdf_extra_2', $extra_2 );
			}
		}
		public function extra_2() {
			echo $this->get_extra_2();
		}

				/**
		 * Return/Show Extra field 3
		 */
		public function get_extra_3() {
			if (isset($this->settings->template_settings['extra_3'])) {
				$extra_3 = nl2br( wptexturize( $this->settings->template_settings[ 'extra_3' ] ) );
				return apply_filters( 'wpo_wcpdf_extra_3', $extra_3 );
			}
		}
		public function extra_3() {
			echo $this->get_extra_3();
		}				
	}
}

// Load main plugin class
$wpo_wcpdf = new WooCommerce_PDF_Invoices();
