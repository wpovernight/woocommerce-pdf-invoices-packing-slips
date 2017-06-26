<?php
/**
 * Plugin Name: WooCommerce PDF Invoices & Packing Slips
 * Plugin URI: http://www.wpovernight.com
 * Description: Create, print & email PDF invoices & packing slips for WooCommerce orders.
 * Version: 1.6.6
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: wpo_wcpdf
 */
defined( 'ABSPATH' ) or exit;

if ( !class_exists( 'WooCommerce_PDF_Invoices' ) ) {

	class WooCommerce_PDF_Invoices {
	
		public static $plugin_prefix;
		public static $plugin_url;
		public static $plugin_path;
		public static $plugin_basename;
		public static $version;
		
		public $writepanels;
		public $settings;
		public $export;

		protected static $_instance = null;

		/**
		 * Main Plugin Instance
		 *
		 * Ensures only one instance of plugin is loaded or can be loaded.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			self::$plugin_prefix = 'wpo_wcpdf_';
			self::$plugin_basename = plugin_basename(__FILE__);
			self::$plugin_url = plugin_dir_url(self::$plugin_basename);
			self::$plugin_path = trailingslashit(dirname(__FILE__));
			self::$version = '1.6.6';
			
			// load the localisation & classes
			add_action( 'plugins_loaded', array( $this, 'translations' ) ); // or use init?
			add_action( 'init', array( $this, 'load_classes' ) );
			add_action( 'in_plugin_update_message-'.self::$plugin_basename, array( $this, 'in_plugin_update_message' ) );

			// run lifecycle methods
			if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
				// check if upgrading from versionless (1.4.14 and older)
				if ( get_option('wpo_wcpdf_general_settings') && get_option('wpo_wcpdf_version') === false ) {
					// tag 'versionless', so that we can apply necessary upgrade settings
					add_option( 'wpo_wcpdf_version', 'versionless' );
				}

				add_action( 'wp_loaded', array( $this, 'do_install' ) );
			}
		}

		/**
		 * Load the translation / textdomain files
		 * 
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present
		 */
		public function translations() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'wpo_wcpdf' );
			$dir    = trailingslashit( WP_LANG_DIR );

			/**
			 * Frontend/global Locale. Looks in:
			 *
			 * 		- WP_LANG_DIR/woocommerce-pdf-invoices-packing-slips/wpo_wcpdf-LOCALE.mo
			 * 	 	- WP_LANG_DIR/plugins/wpo_wcpdf-LOCALE.mo
			 * 	 	- woocommerce-pdf-invoices-packing-slips/languages/wpo_wcpdf-LOCALE.mo (which if not found falls back to:)
			 * 	 	- WP_LANG_DIR/plugins/wpo_wcpdf-LOCALE.mo
			 */
			load_textdomain( 'wpo_wcpdf', $dir . 'woocommerce-pdf-invoices-packing-slips/wpo_wcpdf-' . $locale . '.mo' );
			load_textdomain( 'wpo_wcpdf', $dir . 'plugins/wpo_wcpdf-' . $locale . '.mo' );
			load_plugin_textdomain( 'wpo_wcpdf', false, dirname( self::$plugin_basename ) . '/languages' );
		}

		/**
		 * Load the main plugin classes and functions
		 */
		public function includes() {
			include_once( 'includes/class-wcpdf-settings.php' );
			include_once( 'includes/class-wcpdf-writepanels.php' );
			include_once( 'includes/class-wcpdf-export.php' );
			include_once( 'includes/class-wcpdf-functions.php' );

			// compatibility classes
			include_once( 'includes/compatibility/abstract-wc-data-compatibility.php' );
			include_once( 'includes/compatibility/class-wc-date-compatibility.php' );
			include_once( 'includes/compatibility/class-wc-core-compatibility.php' );
			include_once( 'includes/compatibility/class-wc-order-compatibility.php' );
			include_once( 'includes/compatibility/class-wc-product-compatibility.php' );
		}
		

		/**
		 * Instantiate classes when woocommerce is activated
		 */
		public function load_classes() {
			if ( $this->is_woocommerce_activated() === false ) {
				add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
				return;
			}

			if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
				add_action( 'admin_notices', array ( $this, 'required_php_version' ) );
				return;
			}

			// all systems ready - GO!
			$this->includes();
			$this->settings = new WooCommerce_PDF_Invoices_Settings();
			$this->writepanels = new WooCommerce_PDF_Invoices_Writepanels();
			$this->export = new WooCommerce_PDF_Invoices_Export();
			$this->functions = new WooCommerce_PDF_Invoices_Functions();
		}

		/**
		 * Check if woocommerce is activated
		 */
		public function is_woocommerce_activated() {
			$blog_plugins = get_option( 'active_plugins', array() );
			$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

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

		/**
		 * PHP version requirement notice
		 */
		
		public function required_php_version() {
			$error = __( 'WooCommerce PDF Invoices & Packing Slips requires PHP 5.3 or higher (5.6 or higher recommended).', 'wpo_wcpdf' );
			$how_to_update = __( 'How to update your PHP version', 'wpo_wcpdf' );
			$message = sprintf('<div class="error"><p>%s</p><p><a href="%s">%s</a></p></div>', $error, 'http://docs.wpovernight.com/general/how-to-update-your-php-version/', $how_to_update);
		
			echo $message;
		}

		/** Lifecycle methods *******************************************************
		 * Because register_activation_hook only runs when the plugin is manually
		 * activated by the user, we're checking the current version against the
		 * version stored in the database
		****************************************************************************/

		/**
		 * Handles version checking
		 */
		public function do_install() {
			// only install when woocommerce is active and PHP version 5.3 or higher
			if ( !$this->is_woocommerce_activated() || version_compare( PHP_VERSION, '5.3', '<' ) ) {
				return;
			}

			$version_setting = 'wpo_wcpdf_version';
			$installed_version = get_option( $version_setting );

			// installed version lower than plugin version?
			if ( version_compare( $installed_version, self::$version, '<' ) ) {

				if ( ! $installed_version ) {
					$this->install();
				} else {
					$this->upgrade( $installed_version );
				}

				// new version number
				update_option( $version_setting, self::$version );
			} elseif ( $installed_version && version_compare( $installed_version, self::$version, '>' ) ) {
				$this->downgrade( $installed_version );
				// downgrade version number
				update_option( $version_setting, self::$version );
			}
		}


		/**
		 * Plugin install method. Perform any installation tasks here
		 */
		protected function install() {
			// Create temp folders
			$tmp_base = $this->export->get_tmp_base();

			// check if tmp folder exists => if not, initialize 
			if ( !@is_dir( $tmp_base ) ) {
				$this->export->init_tmp( $tmp_base );
			}

		}


		/**
		 * Plugin upgrade method.  Perform any required upgrades here
		 *
		 * @param string $installed_version the currently installed version
		 */
		protected function upgrade( $installed_version ) {
			if ( $installed_version == 'versionless') { // versionless = 1.4.14 and older
				// We're upgrading from an old version, so we're enabling the option to use the plugin tmp folder.
				// This is not per se the 'best' solution, but the good thing is that nothing is changed
				// and nothing will be broken (that wasn't broken before)
				$default = array( 'old_tmp' => 1 );
				update_option( 'wpo_wcpdf_debug_settings', $default );
			}

			// sync fonts on every upgrade!
			$debug_settings = get_option( 'wpo_wcpdf_debug_settings' ); // get temp setting

			// do not copy if old_tmp function active! (double check for slow databases)
			if ( !( isset($debug_settings['old_tmp']) || $installed_version == 'versionless' ) ) {
				$tmp_base = $this->export->get_tmp_base();

				// check if tmp folder exists => if not, initialize 
				if ( !@is_dir( $tmp_base ) ) {
					$this->export->init_tmp( $tmp_base );
				}

				$font_path = $tmp_base . 'fonts/';
				$this->export->copy_fonts( $font_path );
			}
			
			// 1.5.28 update: copy next invoice number to separate setting
			if ( $installed_version == 'versionless' || version_compare( $installed_version, '1.5.28', '<' ) ) {
				$template_settings = get_option( 'wpo_wcpdf_template_settings' );
				$next_invoice_number = isset($template_settings['next_invoice_number'])?$template_settings['next_invoice_number']:'';
				update_option( 'wpo_wcpdf_next_invoice_number', $next_invoice_number );
			}
		}

		/**
		 * Plugin downgrade method.  Perform any required downgrades here
		 *
		 * @param string $installed_version the currently installed ('old') version (actually higher since this is a downgrade)
		 */
		protected function downgrade( $installed_version ) {
			// sync fonts on downgrade, fixing incompatibility with 2.0
			$debug_settings = get_option( 'wpo_wcpdf_debug_settings' ); // get temp setting

			// do not copy if old_tmp function active! (double check for slow databases)
			if ( !isset($debug_settings['old_tmp']) ) {
				$tmp_base = $this->export->get_tmp_base();

				// check if tmp folder exists => if not, initialize 
				if ( !@is_dir( $tmp_base ) ) {
					$this->export->init_tmp( $tmp_base );
				}

				$font_path = $tmp_base . 'fonts/';
				$this->export->copy_fonts( $font_path );
			}

		}

		/**
		 * Show plugin changes. Code adapted from W3 Total Cache.
		 */
		public function in_plugin_update_message( $args ) {
			$transient_name = 'wpo_wcpdf_upgrade_notice_' . $args['Version'];
			if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
				$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/woocommerce-pdf-invoices-packing-slips/trunk/readme.txt' );
				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
					set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
				}
			}
			echo wp_kses_post( $upgrade_notice );
		}
		/**
		 * Parse update notice from readme file.
		 *
		 * @param  string $content
		 * @param  string $new_version
		 * @return string
		 */
		private function parse_update_notice( $content, $new_version ) {
			// Output Upgrade Notice.
			$matches        = null;
			$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
			$upgrade_notice = '';
			if ( preg_match( $regexp, $content, $matches ) ) {
				$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );
				// Convert the full version strings to minor versions.
				$notice_version_parts  = explode( '.', trim( $matches[1] ) );
				$current_version_parts = explode( '.', self::$version );
				if ( 3 !== sizeof( $notice_version_parts ) ) {
					return;
				}
				$notice_version  = $notice_version_parts[0] . '.' . $notice_version_parts[1];
				$current_version = $current_version_parts[0] . '.' . $current_version_parts[1];
				// Check the latest stable version and ignore trunk.
				if ( version_compare( $current_version, $notice_version, '<' ) ) {
					$upgrade_notice .= '</p><p class="wpo_wcpdf_upgrade_notice">';
					foreach ( $notices as $index => $line ) {
						$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
					}
				}
			}
			return wp_kses_post( $upgrade_notice );
		}

		/***********************************************************************/
		/********************** GENERAL TEMPLATE FUNCTIONS *********************/
		/***********************************************************************/

		/**
		 * Get template name from slug
		 */
		public function get_template_name ( $template_type ) {
			return $this->functions->get_template_name( $template_type );
		}

		/**
		 * Output template styles
		 */
		public function template_styles() {
			$this->functions->template_styles();
		}				

		/**
		 * Return logo id
		 */
		public function get_header_logo_id() {
			return $this->functions->get_header_logo_id();
		}
	
		/**
		 * Show logo html
		 */
		public function header_logo() {
			$this->functions->header_logo();
		}
	
		/**
		 * Return/Show custom company name or default to blog name
		 */
		public function get_shop_name() {
			return $this->functions->get_shop_name();
		}
		public function shop_name() {
			$this->functions->shop_name();
		}
		
		/**
		 * Return/Show shop/company address if provided
		 */
		public function get_shop_address() {
			return $this->functions->get_shop_address();
		}
		public function shop_address() {
			$this->functions->shop_address();
		}

		/**
		 * Check if billing address and shipping address are equal
		 */
		public function ships_to_different_address() {
			return $this->functions->ships_to_different_address();
		}
		
		/**
		 * Return/Show billing address
		 */
		public function get_billing_address() {
			return $this->functions->get_billing_address();
		}
		public function billing_address() {
			$this->functions->billing_address();
		}

		/**
		 * Return/Show billing email
		 */
		public function get_billing_email() {
			return $this->functions->get_billing_email();
		}
		public function billing_email() {
			$this->functions->billing_email();
		}
		
		/**
		 * Return/Show billing phone
		 */
		public function get_billing_phone() {
			return $this->functions->get_billing_phone();
		}
		public function billing_phone() {
			$this->functions->billing_phone();
		}
		
		/**
		 * Return/Show shipping address
		 */
		public function get_shipping_address() {
			return $this->functions->get_shipping_address();
		}
		public function shipping_address() {
			$this->functions->shipping_address();
		}

		/**
		 * Return/Show a custom field
		 */		
		public function get_custom_field( $field_name ) {
			return $this->functions->get_custom_field( $field_name );
		}
		public function custom_field( $field_name, $field_label = '', $display_empty = false ) {
			$this->functions->custom_field( $field_name, $field_label, $display_empty );
		}

		/**
		 * Return/Show order notes
		 */		
		public function get_order_notes( $filter = 'customer' ) {
			return $this->functions->get_order_notes( $filter );
		}
		public function order_notes( $filter = 'customer' ) {
			$this->functions->order_notes( $filter );
		}

		/**
		 * Return/Show the current date
		 */
		public function get_current_date() {
			return $this->functions->get_current_date();
		}
		public function current_date() {
			$this->functions->current_date();
		}

		/**
		 * Return/Show payment method  
		 */
		public function get_payment_method() {
			return $this->functions->get_payment_method();
		}
		public function payment_method() {
			$this->functions->payment_method();
		}

		/**
		 * Return/Show shipping method  
		 */
		public function get_shipping_method() {
			return $this->functions->get_shipping_method();
		}
		public function shipping_method() {
			$this->functions->shipping_method();
		}

		/**
		 * Return/Show order number
		 */
		public function get_order_number() {
			return $this->functions->get_order_number();
		}
		public function order_number() {
			$this->functions->order_number();
		}

		/**
		 * Return/Show invoice number 
		 */
		public function get_invoice_number() {
			return $this->functions->get_invoice_number();
		}
		public function invoice_number() {
			$this->functions->invoice_number();
		}

		/**
		 * Return/Show the order date
		 */
		public function get_order_date() {
			return $this->functions->get_order_date();
		}
		public function order_date() {
			$this->functions->order_date();
		}

		/**
		 * Return/Show the invoice date
		 */
		public function get_invoice_date() {
			return $this->functions->get_invoice_date();
		}
		public function invoice_date() {
			$this->functions->invoice_date();
		}

		/**
		 * Return the order items
		 */
		public function get_order_items() {
			return $this->functions->get_order_items();
		}

		/**
		 * Return/show product attribute
		 */
		public function get_product_attribute( $attribute_name, $product ) {
			return $this->functions->get_product_attribute( $attribute_name, $product );
		}
		public function product_attribute( $attribute_name, $product ) {
			$this->functions->product_attribute( $attribute_name, $product );
		}

	
		/**
		 * Return the order totals listing
		 */
		public function get_woocommerce_totals() {
			return $this->functions->get_woocommerce_totals();
		}
		
		/**
		 * Return/show the order subtotal
		 */
		public function get_order_subtotal( $tax = 'excl', $discount = 'incl' ) { // set $tax to 'incl' to include tax, same for $discount
			return $this->functions->get_order_subtotal( $tax, $discount );
		}
		public function order_subtotal( $tax = 'excl', $discount = 'incl' ) {
			$this->functions->order_subtotal( $tax, $discount );
		}
	
		/**
		 * Return/show the order shipping costs
		 */
		public function get_order_shipping( $tax = 'excl' ) { // set $tax to 'incl' to include tax
			return $this->functions->get_order_shipping( $tax );
		}
		public function order_shipping( $tax = 'excl' ) {
			$this->functions->order_shipping( $tax );
		}

		/**
		 * Return/show the total discount
		 */
		public function get_order_discount( $type = 'total', $tax = 'incl' ) {
			return $this->functions->get_order_discount( $type, $tax );
		}
		public function order_discount( $type = 'total', $tax = 'incl' ) {
			$this->functions->order_discount( $type, $tax );
		}

		/**
		 * Return the order fees
		 */
		public function get_order_fees( $tax = 'excl' ) {
			return $this->functions->get_order_fees( $tax );
		}
		
		/**
		 * Return the order taxes
		 */
		public function get_order_taxes() {
			return $this->functions->get_order_taxes();
		}

		/**
		 * Return/show the order grand total
		 */
		public function get_order_grand_total( $tax = 'incl' ) {
			return $this->functions->get_order_grand_total( $tax );
		}
		public function order_grand_total( $tax = 'incl' ) {
			$this->functions->order_grand_total( $tax );
		}


		/**
		 * Return/Show shipping notes
		 */
		public function get_shipping_notes() {
			return $this->functions->get_shipping_notes();
		}
		public function shipping_notes() {
			$this->functions->shipping_notes();
		}
		
	
		/**
		 * Return/Show shop/company footer imprint, copyright etc.
		 */
		public function get_footer() {
			return $this->functions->get_footer();
		}
		public function footer() {
			$this->functions->footer();
		}

		/**
		 * Return/Show Extra field 1
		 */
		public function get_extra_1() {
			return $this->functions->get_extra_1();
		}
		public function extra_1() {
			$this->functions->extra_1();
		}

		/**
		 * Return/Show Extra field 2
		 */
		public function get_extra_2() {
			return $this->functions->get_extra_2();
		}
		public function extra_2() {
			$this->functions->extra_2();
		}

		/**
		 * Return/Show Extra field 3
		 */
		public function get_extra_3() {
			return $this->functions->get_extra_3();
		}
		public function extra_3() {
			$this->functions->extra_3();
		}
	}
}

/**
 * Returns the main instance of WooCommerce PDF Invoices & Packing Slips to prevent the need to use globals.
 *
 * @since  1.6
 * @return WPO_WCPDF
 */
function WPO_WCPDF() {
	return WooCommerce_PDF_Invoices::instance();
}

// Global for backwards compatibility.
$GLOBALS['wpo_wcpdf'] = WPO_WCPDF();