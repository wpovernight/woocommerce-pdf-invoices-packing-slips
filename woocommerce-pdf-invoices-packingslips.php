<?php
/**
 * Plugin Name: WooCommerce PDF Invoices & Packing Slips
 * Plugin URI: http://www.wpovernight.com
 * Description: Create, print & email PDF invoices & packing slips for WooCommerce orders.
 * Version: 2.0-beta-5
 * Author: Ewout Fernhout
 * Author URI: http://www.wpovernight.com
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-pdf-invoices-packing-slips
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'WPO_WCPDF' ) ) :

class WPO_WCPDF {

	public $version = '2.0-beta-5';
	public $plugin_basename;
	public $legacy_mode;

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
		$this->plugin_basename = plugin_basename(__FILE__);

		$this->define( 'WPO_WCPDF_VERSION', $this->version );

		// load the localisation & classes
		add_action( 'plugins_loaded', array( $this, 'translations' ) );
		add_filter( 'load_textdomain_mofile', array( $this, 'textdomain_fallback' ), 10, 2 );
		add_action( 'init', array( $this, 'load_classes' ) );
		add_action( 'in_plugin_update_message-'.$this->plugin_basename, array( $this, 'in_plugin_update_message' ) );

		// run lifecycle methods
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			add_action( 'wp_loaded', array( $this, 'do_install' ) );
		}
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}


	/**
	 * Load the translation / textdomain files
	 * 
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function translations() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-pdf-invoices-packing-slips' );
		$dir    = trailingslashit( WP_LANG_DIR );

		$textdomains = array( 'woocommerce-pdf-invoices-packing-slips' );
		if ( $this->legacy_mode_enabled() === true ) {
			$textdomains[] = 'wpo_wcpdf';
		}

		/**
		 * Frontend/global Locale. Looks in:
		 *
		 * 		- WP_LANG_DIR/woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packing-slips-LOCALE.mo
		 * 	 	- WP_LANG_DIR/plugins/woocommerce-pdf-invoices-packing-slips-LOCALE.mo
		 * 	 	- woocommerce-pdf-invoices-packing-slips-pro/languages/woocommerce-pdf-invoices-packing-slips-LOCALE.mo (which if not found falls back to:)
		 * 	 	- WP_LANG_DIR/plugins/woocommerce-pdf-invoices-packing-slips-LOCALE.mo
		 */
		foreach ($textdomains as $textdomain) {
			load_textdomain( $textdomain, $dir . 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packing-slips-' . $locale . '.mo' );
			load_textdomain( $textdomain, $dir . 'plugins/woocommerce-pdf-invoices-packing-slips-' . $locale . '.mo' );
			load_plugin_textdomain( $textdomain, false, dirname( plugin_basename(__FILE__) ) . '/languages' );
		}
	}

	/**
	 * Maintain backwards compatibility with old translation files
	 * Uses old .mo file if it exists in any of the override locations
	 */
	public function textdomain_fallback( $mofile, $textdomain ) {
		$plugin_domain = 'woocommerce-pdf-invoices-packing-slips';
		$old_domain = 'wpo_wcpdf';

		if ($textdomain == $old_domain) {
			$textdomain = $plugin_domain;
			$mofile = str_replace( "{$old_domain}-", "{$textdomain}-", $mofile ); // with trailing dash to target file and not folder
		}

		if ( $textdomain === $plugin_domain ) {
			$old_mofile = str_replace( "{$textdomain}-", "{$old_domain}-", $mofile ); // with trailing dash to target file and not folder
			if ( file_exists( $old_mofile ) ) {
				// we have an old override - use it
				return $old_mofile;
			}

			// prevent loading outdated language packs
			$pofile = str_replace('.mo', '.po', $mofile);
			if ( file_exists( $pofile ) ) {
				// load po file
				$podata = file_get_contents($pofile);
				// set revision date threshold
				$block_before = strtotime( '2017-05-15' );
				// read revision date
				preg_match('~PO-Revision-Date: (.*?)\\\n~s',$podata,$matches);
				if (isset($matches[1])) {
					$revision_date = $matches[1];
					if ( $revision_timestamp = strtotime($revision_date) ) {
						// check if revision is before threshold date
						if ( $revision_timestamp < $block_before ) {
							// delete po & mo file if possible
							@unlink($pofile);
							@unlink($mofile);
							return '';
						}
					}
				}
			}
		}

		return $mofile;
	}

	/**
	 * Load the main plugin classes and functions
	 */
	public function includes() {
		// WooCommerce compatibility classes
		include_once( 'includes/compatibility/abstract-wc-data-compatibility.php' );
		include_once( 'includes/compatibility/class-wc-date-compatibility.php' );
		include_once( 'includes/compatibility/class-wc-core-compatibility.php' );
		include_once( 'includes/compatibility/class-wc-order-compatibility.php' );
		include_once( 'includes/compatibility/class-wc-product-compatibility.php' );
		include_once( 'includes/compatibility/wc-datetime-functions-compatibility.php' );

		// Third party compatibility
		include_once( 'includes/compatibility/class-wcpdf-compatibility-third-party-plugins.php' );

		// Plugin classes
		include_once( 'includes/wcpdf-functions.php' );
		$this->settings = include_once( 'includes/class-wcpdf-settings.php' );
		$this->documents = include_once( 'includes/class-wcpdf-documents.php' );
		$this->main = include_once( 'includes/class-wcpdf-main.php' );
		include_once( 'includes/class-wcpdf-assets.php' );
		include_once( 'includes/class-wcpdf-admin.php' );
		include_once( 'includes/class-wcpdf-frontend.php' );

		// Backwards compatibility with self
		include_once( 'includes/legacy/class-wcpdf-legacy.php' );
		include_once( 'includes/legacy/class-wcpdf-legacy-deprecated-hooks.php' );
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
	}

	/**
	 * Check if legacy mode is enabled
	 */
	public function legacy_mode_enabled() {
		if (!isset($this->legacy_mode)) {
			$debug_settings = get_option( 'wpo_wcpdf_settings_debug' );
			$this->legacy_mode = isset($debug_settings['legacy_mode']);
		}
		return $this->legacy_mode;
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
		$error = sprintf( __( 'WooCommerce PDF Invoices & Packing Slips requires %sWooCommerce%s to be installed & activated!' , 'woocommerce-pdf-invoices-packing-slips' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );
		
		$message = '<div class="error"><p>' . $error . '</p></div>';
	
		echo $message;
	}

	/**
	 * PHP version requirement notice
	 */
	
	public function required_php_version() {
		$error = __( 'WooCommerce PDF Invoices & Packing Slips requires PHP 5.3 or higher (5.6 or higher recommended).', 'woocommerce-pdf-invoices-packing-slips' );
		$how_to_update = __( 'How to update your PHP version', 'woocommerce-pdf-invoices-packing-slips' );
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
		// only install when woocommerce is active
		if ( !$this->is_woocommerce_activated() ) {
			return;
		}

		$version_setting = 'wpo_wcpdf_version';
		$installed_version = get_option( $version_setting );

		// installed version lower than plugin version?
		if ( version_compare( $installed_version, WPO_WCPDF_VERSION, '<' ) ) {

			if ( ! $installed_version ) {
				$this->install();
			} else {
				$this->upgrade( $installed_version );
			}

			// new version number
			update_option( $version_setting, WPO_WCPDF_VERSION );
		}
	}


	/**
	 * Plugin install method. Perform any installation tasks here
	 */
	protected function install() {
		// check if upgrading from versionless (1.4.14 and older)
		if ( get_option('wpo_wcpdf_general_settings') ) {
			$this->upgrade( 'versionless' );
			return;
		}

		// Create temp folders
		$tmp_base = $this->main->get_tmp_base();

		// check if tmp folder exists => if not, initialize 
		if ( !@is_dir( $tmp_base ) ) {
			$this->main->init_tmp( $tmp_base );
		}

		// set default settings
		$settings_defaults = array(
			'wpo_wcpdf_settings_general' => array(
				'download_display'			=> 'display',
				'template_path'				=> WPO_WCPDF()->plugin_path() . '/templates/Simple',
				// 'currency_font'				=> '',
				'paper_size'				=> 'a4',
				// 'header_logo'				=> '',
				// 'shop_name'					=> array(),
				// 'shop_address'				=> array(),
				// 'footer'					=> array(),
				// 'extra_1'					=> array(),
				// 'extra_2'					=> array(),
				// 'extra_3'					=> array(),
			),
			'wpo_wcpdf_documents_settings_invoice' => array(
				'enabled'					=> 1,
				// 'attach_to_email_ids'		=> array(),
				// 'display_shipping_address'	=> '',
				// 'display_email'				=> '',
				// 'display_phone'				=> '',
				// 'display_date'				=> '',
				// 'display_number'			=> '',
				// 'number_format'				=> array(),
				// 'reset_number_yearly'		=> '',
				// 'my_account_buttons'		=> '',
				// 'invoice_number_column'		=> '',
				// 'disable_free'				=> '',
			),
			'wpo_wcpdf_documents_settings_packing-slip' => array(
				'enabled'					=> 1,
				// 'display_billing_address'	=> '',
				// 'display_email'				=> '',
				// 'display_phone'				=> '',
			),
			// 'wpo_wcpdf_settings_debug' => array(
			// 	'legacy_mode'				=> '',
			// 	'enable_debug'				=> '',
			// 	'html_output'				=> '',
			// ),
		);
		foreach ($settings_defaults as $option => $defaults) {
			update_option( $option, $defaults );
		}
	}

	/**
	 * Plugin upgrade method.  Perform any required upgrades here
	 *
	 * @param string $installed_version the currently installed ('old') version
	 */
	protected function upgrade( $installed_version ) {
		// sync fonts on every upgrade!
		$tmp_base = $this->main->get_tmp_base();

		// check if tmp folder exists => if not, initialize 
		if ( !@is_dir( $tmp_base ) ) {
			$this->main->init_tmp( $tmp_base );
		} else {
			$font_path = $this->main->get_tmp_path( 'fonts' );
			$this->main->copy_fonts( $font_path );
		}
		
		// 1.5.28 update: copy next invoice number to separate setting
		if ( $installed_version == 'versionless' || version_compare( $installed_version, '1.5.28', '<' ) ) {
			$template_settings = get_option( 'wpo_wcpdf_template_settings' );
			$next_invoice_number = isset($template_settings['next_invoice_number'])?$template_settings['next_invoice_number']:'';
			update_option( 'wpo_wcpdf_next_invoice_number', $next_invoice_number );
		}

		// 2.0-dev update: reorganize settings
		if ( $installed_version == 'versionless' || version_compare( $installed_version, '2.0-dev', '<' ) ) {
			$old_settings = array(
				'wpo_wcpdf_general_settings'	=> get_option( 'wpo_wcpdf_general_settings' ),
				'wpo_wcpdf_template_settings'	=> get_option( 'wpo_wcpdf_template_settings' ),
				'wpo_wcpdf_debug_settings'		=> get_option( 'wpo_wcpdf_debug_settings' ),
			);

			// combine invoice number formatting in array
			$old_settings['wpo_wcpdf_template_settings']['invoice_number_formatting'] = array();
			$format_option_keys = array('padding','suffix','prefix');
			foreach ($format_option_keys as $format_option_key) {
				if (isset($old_settings['wpo_wcpdf_template_settings']["invoice_number_formatting_{$format_option_key}"])) {
					$old_settings['wpo_wcpdf_template_settings']['invoice_number_formatting'][$format_option_key] = $old_settings['wpo_wcpdf_template_settings']["invoice_number_formatting_{$format_option_key}"];
				}
			}

			// convert abbreviated email_ids
			foreach ($old_settings['wpo_wcpdf_general_settings']['email_pdf'] as $email_id => $value) {
				if ($email_id == 'completed' || $email_id == 'processing') {
					$old_settings['wpo_wcpdf_general_settings']['email_pdf']["customer_{$email_id}_order"] = $value;
					unset($old_settings['wpo_wcpdf_general_settings']['email_pdf'][$email_id]);
				}
			}

			// Migrate template path
			// forward slash for consistency/compatibility
			$template_path = str_replace('\\','/', $old_settings['wpo_wcpdf_template_settings']['template_path']);
			// strip abspath (forward slashed) if included
			$template_path = str_replace( str_replace('\\','/', ABSPATH), '', $template_path );
			// strip pdf subfolder from templates path
			$template_path = str_replace( '/templates/pdf/', '/templates/', $template_path );
			$old_settings['wpo_wcpdf_template_settings']['template_path'] = $template_path;

			// map new settings to old
			$settings_map = array(
				'wpo_wcpdf_settings_general' => array(
					'download_display'			=> array( 'wpo_wcpdf_general_settings' => 'download_display' ),
					'template_path'				=> array( 'wpo_wcpdf_template_settings' => 'template_path' ),
					'currency_font'				=> array( 'wpo_wcpdf_template_settings' => 'currency_font' ),
					'paper_size'				=> array( 'wpo_wcpdf_template_settings' => 'paper_size' ),
					'header_logo'				=> array( 'wpo_wcpdf_template_settings' => 'header_logo' ),
					'shop_name'					=> array( 'wpo_wcpdf_template_settings' => 'shop_name' ),
					'shop_address'				=> array( 'wpo_wcpdf_template_settings' => 'shop_address' ),
					'footer'					=> array( 'wpo_wcpdf_template_settings' => 'footer' ),
					'extra_1'					=> array( 'wpo_wcpdf_template_settings' => 'extra_1' ),
					'extra_2'					=> array( 'wpo_wcpdf_template_settings' => 'extra_2' ),
					'extra_3'					=> array( 'wpo_wcpdf_template_settings' => 'extra_3' ),
				),
				'wpo_wcpdf_documents_settings_invoice' => array(
					'attach_to_email_ids'		=> array( 'wpo_wcpdf_general_settings' => 'email_pdf' ),
					'display_shipping_address'	=> array( 'wpo_wcpdf_template_settings' => 'invoice_shipping_address' ),
					'display_email'				=> array( 'wpo_wcpdf_template_settings' => 'invoice_email' ),
					'display_phone'				=> array( 'wpo_wcpdf_template_settings' => 'invoice_phone' ),
					'display_date'				=> array( 'wpo_wcpdf_template_settings' => 'display_date' ),
					'display_number'			=> array( 'wpo_wcpdf_template_settings' => 'display_number' ),
					'number_format'				=> array( 'wpo_wcpdf_template_settings' => 'invoice_number_formatting' ),
					'reset_number_yearly'		=> array( 'wpo_wcpdf_template_settings' => 'yearly_reset_invoice_number' ),
					'my_account_buttons'		=> array( 'wpo_wcpdf_general_settings' => 'my_account_buttons' ),
					'invoice_number_column'		=> array( 'wpo_wcpdf_general_settings' => 'invoice_number_column' ),
					'disable_free'				=> array( 'wpo_wcpdf_general_settings' => 'disable_free' ),
				),
				'wpo_wcpdf_documents_settings_packing-slip' => array(
					'display_billing_address'	=> array( 'wpo_wcpdf_template_settings' => 'packing_slip_billing_address' ),
					'display_email'				=> array( 'wpo_wcpdf_template_settings' => 'packing_slip_email' ),
					'display_phone'				=> array( 'wpo_wcpdf_template_settings' => 'packing_slip_phone' ),
				),
				'wpo_wcpdf_settings_debug' => array(
					'enable_debug'				=> array( 'wpo_wcpdf_debug_settings' => 'enable_debug' ),
					'html_output'				=> array( 'wpo_wcpdf_debug_settings' => 'html_output' ),
				),
			);
			
			// walk through map
			foreach ($settings_map as $new_option => $new_settings_keys) {
				${$new_option} = array();
				foreach ($new_settings_keys as $new_key => $old_setting ) {
					$old_key = reset($old_setting);
					$old_option = key($old_setting);
					if (!empty($old_settings[$old_option][$old_key])) {
						// turn translatable fields into array
						$translatable_fields = array('shop_name','shop_address','footer','extra_1','extra_2','extra_3');
						if (in_array($new_key, $translatable_fields)) {
							${$new_option}[$new_key] = array( 'default' => $old_settings[$old_option][$old_key] );
						} else {
							${$new_option}[$new_key] = $old_settings[$old_option][$old_key];
						}
					}
				}

				// auto enable invoice & packing slip
				$enabled = array( 'wpo_wcpdf_documents_settings_invoice', 'wpo_wcpdf_documents_settings_packing-slip' );
				if ( in_array( $new_option, $enabled ) ) {
					${$new_option}['enabled'] = 1;
				}

				// auto enable legacy mode
				if ( $new_option == 'wpo_wcpdf_settings_debug' ) {
					${$new_option}['legacy_mode'] = 1;
				}

				// merge with existing settings
				${$new_option."_old"} = get_option( $new_option, ${$new_option} ); // second argument loads new as default in case the settings did not exist yet
				${$new_option} = ${$new_option} + ${$new_option."_old"}; // duplicate options take new options as default

				// store new option values
				update_option( $new_option, ${$new_option} );
			}
		}

		// 2.0-beta-2 update: copy next number to separate db store
		if ( version_compare( $installed_version, '2.0-beta-2', '<' ) ) {
			// load number store class (just in case)
			include_once( WPO_WCPDF()->plugin_path() . '/includes/documents/class-wcpdf-sequential-number-store.php' );

			$next_number = get_option( 'wpo_wcpdf_next_invoice_number' );
			if (!empty($next_number)) {
				$number_store = new \WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store( 'invoice_number' );
				$number_store->set_next( (int) $next_number );
			}
			delete_option( 'wpo_wcpdf_next_invoice_number' ); // clean up after ourselves
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
			$current_version_parts = explode( '.', $this->version );

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

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

} // class WPO_WCPDF

endif; // class_exists

/**
 * Returns the main instance of WooCommerce PDF Invoices & Packing Slips to prevent the need to use globals.
 *
 * @since  1.6
 * @return WPO_WCPDF
 */
function WPO_WCPDF() {
	return WPO_WCPDF::instance();
}

WPO_WCPDF(); // load plugin

// legacy class for plugin detecting
if ( !class_exists( 'WooCommerce_PDF_Invoices' ) ) {
	class WooCommerce_PDF_Invoices{
		public static $version;

		public function __construct() {
			self::$version = WPO_WCPDF()->version;
		}
	}
	new WooCommerce_PDF_Invoices();
}
