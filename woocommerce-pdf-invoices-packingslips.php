<?php
/**
 * Plugin Name:          PDF Invoices & Packing Slips for WooCommerce
 * Requires Plugins:     woocommerce
 * Plugin URI:           https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/
 * Description:          Create, print & email PDF or Electronic Invoices & PDF Packing Slips for WooCommerce orders.
 * Version:              6.0.0-i1484.1
 * Author:               WP Overnight
 * Author URI:           https://www.wpovernight.com
 * License:              GPLv2 or later
 * License URI:          https://opensource.org/licenses/gpl-license.php
 * Text Domain:          woocommerce-pdf-invoices-packing-slips
 * WC requires at least: 3.3
 * WC tested up to:      10.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use WPO\IPS\Compatibility\ThirdPartyPlugins;
use WPO\IPS\Compatibility\VatPlugins;
use WPO\IPS\Compatibility\OrderUtil;
use WPO\IPS\Compatibility\FileSystem;
use WPO\IPS\Settings;
use WPO\IPS\Documents;
use WPO\IPS\Main;
use WPO\IPS\Endpoint;
use WPO\IPS\Assets;
use WPO\IPS\Admin;
use WPO\IPS\Frontend;
use WPO\IPS\Install;
use WPO\IPS\FontSynchronizer;
use WPO\IPS\EDI\Peppol;
use WPO\IPS\Notices;

if ( ! class_exists( 'WPO_WCPDF' ) ) :

class WPO_WCPDF {

	public string $version                         = '5.9.2';
	public string $version_php                     = '7.4';
	public string $version_woo                     = '3.3';
	public string $version_wp                      = '4.4';
	public ?string $plugin_basename                = null;
	public array $legacy_addons                    = array();
	
	public ?ThirdPartyPlugins $third_party_plugins = null;
	public ?VatPlugins $vat_plugins                = null;
	public ?OrderUtil $order_util                  = null;
	public ?FileSystem $file_system                = null;
	public ?Settings $settings                     = null;
	public ?Documents $documents                   = null;
	public ?Main $main                             = null;
	public ?Endpoint $endpoint                     = null;
	public ?Assets $assets                         = null;
	public ?Admin $admin                           = null;
	public ?Frontend $frontend                     = null;
	public ?Install $install                       = null;
	public ?FontSynchronizer $font_synchronizer    = null;
	public ?Peppol $peppol                         = null;
	public ?Notices $notices                       = null;

	protected ?bool $dependencies_ready            = null;
	protected ?bool $woocommerce_activated         = null;
	
	protected static ?self $_instance              = null;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 * 
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		require $this->plugin_path() . '/vendor/autoload.php';
		require $this->plugin_path() . '/vendor/strauss/autoload.php';

		$this->plugin_basename = plugin_basename( __FILE__ );
		$this->legacy_addons   = apply_filters( 'wpo_wcpdf_legacy_addons', array(
			'ubl-woocommerce-pdf-invoices.php'     => 'UBL Invoices for WooCommerce',
			'woocommerce-pdf-ips-number-tools.php' => 'PDF Invoices & Packing Slips for WooCommerce - Number Tools',
			'woocommerce-pdf-ips-ubl-extender.php' => 'PDF Invoices & Packing Slips for WooCommerce - UBL Extender',
			'wpo-ips-factur-x.php'                 => 'PDF Invoices & Packing Slips for WooCommerce - Factur-X',
			'wpo-ips-cius-ro.php'                  => 'PDF Invoices & Packing Slips for WooCommerce - CIUS-RO',
			'wpo-ips-xrechnung.php'                => 'PDF Invoices & Packing Slips for WooCommerce - XRechnung',
			'wpo-ips-fatturapa.php'                => 'PDF Invoices & Packing Slips for WooCommerce - FatturaPA',
		) );

		$this->define( 'WPO_WCPDF_VERSION', $this->version );

		// load the localisation & classes
		add_action( 'init', array( $this, 'translations' ), 8 );
		add_action( 'init', array( $this, 'load_classes' ), 9 ); // Pro runs on default 10, if this runs after it will not work
		add_action( 'in_plugin_update_message-' . $this->plugin_basename, array( $this, 'in_plugin_update_message' ) );
		add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatible' ) );
		add_action( 'wpo_wcpdf_new_github_prerelease_available', array( $this, 'set_new_unstable_version_available_option' ), 10, 3 );
		add_action( 'init', array( '\\WPO\\IPS\\Semaphore', 'init_cleanup' ), 999 ); // wait AS to initialize

		// deactivate legacy extensions if activated
		register_activation_hook( __FILE__, array( $this, 'deactivate_legacy_addons' ) );
	}
	
	/**
	 * Load the main plugin classes and functions
	 * 
	 * @return void
	 */
	public function includes(): void {
		// plugin legacy class mapping
		include_once $this->plugin_path() . '/wpo-ips-legacy-class-alias-mapping.php';

		// deprecated
		include_once $this->plugin_path() . '/wpo-ips-deprecated-hooks.php';
		include_once $this->plugin_path() . '/wpo-ips-deprecated-functions.php';

		// plugin functions
		include_once $this->plugin_path() . '/wpo-ips-functions.php';
		include_once $this->plugin_path() . '/wpo-ips-functions-edi.php';
		
		// plugin classes
		$this->get_instance( 'third_party_plugins' );
		$this->get_instance( 'vat_plugins' );
		$this->get_instance( 'order_util' );
		$this->get_instance( 'file_system' );
		$this->get_instance( 'settings' );
		$this->get_instance( 'documents' );
		$this->get_instance( 'main' );
		$this->get_instance( 'endpoint' );
		$this->get_instance( 'assets' );
		$this->get_instance( 'admin' );
		$this->get_instance( 'frontend' );
		$this->get_instance( 'install' );
		$this->get_instance( 'font_synchronizer' );
		$this->get_instance( 'peppol' );
		$this->get_instance( 'notices' );
	}
	
	/**
	 * Get a plugin class instance by slug.
	 *
	 * @param string $property
	 *
	 * @return object|null
	 */
	public function get_instance( string $property ) {
		$map = array(
			'third_party_plugins' => ThirdPartyPlugins::class,
			'vat_plugins'         => VatPlugins::class,
			'order_util'          => OrderUtil::class,
			'file_system'         => FileSystem::class,
			'settings'            => Settings::class,
			'documents'           => Documents::class,
			'main'                => Main::class,
			'endpoint'            => Endpoint::class,
			'assets'              => Assets::class,
			'admin'               => Admin::class,
			'frontend'            => Frontend::class,
			'install'             => Install::class,
			'font_synchronizer'   => FontSynchronizer::class,
			'peppol'              => Peppol::class,
			'notices'             => Notices::class,
			'setup_wizard'        => SetupWizard::class,
		);

		if ( ! isset( $map[ $property ] ) ) {
			return null;
		}

		if ( null === $this->{$property} ) {
			$class_name = $map[ $property ];
			$this->{$property} = $class_name::instance();
		}

		return $this->{$property};
	}

	/**
	 * Is the dependency version supported?
	 * 
	 * @param string $dependency
	 * @return bool
	 */
	public function is_dependency_version_supported( string $dependency ): bool {
		switch ( $dependency ) {
			case 'php':
				return defined( 'PHP_VERSION' ) && version_compare( PHP_VERSION, $this->version_php, '>=' );
			case 'woo':
				return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $this->version_woo, '>=' );
			case 'wp':
				global $wp_version;
				return version_compare( $wp_version, $this->version_wp, '>=' );
		}

		return false;
	}

	/**
	 * Load the translation / textdomain files
	 * 
	 * @return void
	 */
	public function translations(): void {
		static $loaded = false;

		if ( $loaded ) {
			return;
		}

		$textdomain = 'woocommerce-pdf-invoices-packing-slips';

		if ( is_textdomain_loaded( $textdomain ) ) {
			$loaded = true;
			return;
		}

		$locale = $this->determine_locale();
		$dir    = trailingslashit( WP_LANG_DIR );

		load_textdomain( $textdomain, $dir . 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packing-slips-' . $locale . '.mo' );
		load_textdomain( $textdomain, $dir . 'plugins/woocommerce-pdf-invoices-packing-slips-' . $locale . '.mo' );
		load_plugin_textdomain( $textdomain, false, dirname( $this->plugin_basename ) . '/languages' );

		$loaded = true;
	}

	/**
	 * Instantiate classes when woocommerce is activated
	 * 
	 * @return void
	 */
	public function load_classes(): void {
		if ( ! $this->dependencies_are_ready() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'deactivate_legacy_addons') );

		// all systems ready - GO!
		$this->includes();
	}

	/**
	 * Check if WooCommerce and PHP dependencies are met.
	 * If not, show the appropriate admin notices.
	 *
	 * @return bool
	 */
	public function dependencies_are_ready(): bool {
		if ( null !== $this->dependencies_ready ) {
			return $this->dependencies_ready;
		}
	
		// Check if WooCommerce is activated and meets the minimum version
		if ( ! $this->is_woocommerce_activated() || ! $this->is_dependency_version_supported( 'woo' ) ) {
			Notices::maybe_add_admin_notice( array( Notices::class, 'need_woocommerce_notice' ) );
			return $this->dependencies_ready = false;
		}

		// Check if PHP version is supported
		if ( ! has_filter( 'wpo_wcpdf_pdf_maker' ) && ! $this->is_dependency_version_supported( 'php' ) ) {
			add_filter( 'wpo_wcpdf_document_is_allowed', '__return_false', 99999 );
			Notices::maybe_add_admin_notice( array( Notices::class, 'required_php_version_notice' ) );
			return $this->dependencies_ready = false;
		}

		return $this->dependencies_ready = true;
	}

	/**
	 * Check if woocommerce is activated
	 *
	 * @return bool
	 */
	public function is_woocommerce_activated(): bool {
		if ( null !== $this->woocommerce_activated ) {
			return $this->woocommerce_activated;
		}

		$blog_plugins    = (array) get_option( 'active_plugins', array() );
		$site_plugins    = is_multisite() ? (array) get_site_option( 'active_sitewide_plugins', array() ) : array();
		$is_wc_activated = in_array( 'woocommerce/woocommerce.php', $blog_plugins, true ) || isset( $site_plugins['woocommerce/woocommerce.php'] );

		$this->woocommerce_activated = (bool) apply_filters( 'wpo_wcpdf_is_woocommerce_activated', $is_wc_activated );

		return $this->woocommerce_activated;
	}

	/**
	 * Declares WooCommerce HPOS compatibility.
	 *
	 * @return void
	 */
	public function woocommerce_hpos_compatible(): void {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	/**
	 * Show plugin changes. Code adapted from W3 Total Cache.
	 * 
	 * @param array $args Update message args.
	 * @return void
	 */
	public function in_plugin_update_message( array $args ): void {
		$transient_name = 'wpo_wcpdf_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/woocommerce-pdf-invoices-packing-slips/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'], $args['new_version'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Deactivate legacy add-ons that are still active.
	 *
	 * @return void
	 */
	public function deactivate_legacy_addons(): void {
		foreach ( $this->legacy_addons as $filename => $name ) {
			$legacy_addon = $this->plugin_is_activated( $filename );

			if ( ! empty( $legacy_addon ) ) {
				deactivate_plugins( $legacy_addon );
				$transient_name = $this->get_legacy_addon_transient_name( $filename );
				set_transient( $transient_name, 'yes', DAY_IN_SECONDS );
			}
		}
	}
	
	/**
	 * Get transient name for legacy addon notice based on the addon filename.
	 *
	 * @param string $filename
	 * @return string
	 */
	public function get_legacy_addon_transient_name( string $filename ): string {
		$filename_without_ext = basename( $filename, '.php' );
		$legacy_addon_name    = str_replace( '-', '_', $filename_without_ext );

		return "wpo_wcpdf_legacy_addon_{$legacy_addon_name}";
	}

	/**
	 * Store the new unstable version if version checking is enabled.
	 *
	 * @param array  $unstable The unstable version data.
	 * @param string $owner    GitHub repo owner.
	 * @param string $repo     GitHub repo name.
	 * @return void
	 */
	public function set_new_unstable_version_available_option( array $unstable, string $owner, string $repo ): void {
		$debug_settings = $this->settings->debug_settings;
		$enabled        = isset( $debug_settings['check_unstable_versions'] );
		$new_tag        = sanitize_text_field( $unstable['tag'] );

		if (
			$enabled &&
			! empty( $new_tag ) &&
			'wpovernight' === $owner &&
			'woocommerce-pdf-invoices-packing-slips' === $repo
		) {
			$current = get_option( 'wpo_wcpdf_unstable_version_state', array() );

			if ( ! isset( $current['tag'] ) || $current['tag'] !== $new_tag ) {
				update_option( 'wpo_wcpdf_unstable_version_state', array(
					'tag'       => $new_tag,
					'dismissed' => false,
				) );
			}
		}
	}

	/**
	 * Get the plugin url.
	 * 
	 * @return string
	 */
	public function plugin_url(): string {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * 
	 * @return string
	 */
	public function plugin_path(): string {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}
	
	/**
	 * Check if the current admin page is the plugin settings page.
	 *
	 * @return bool
	 */
	public function is_settings_page(): bool {
		if ( isset( $_GET['page'] ) && 'wpo_wcpdf_options_page' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
			return true;
		}

		global $pagenow;

		if ( 'options.php' === $pagenow ) {
			$option_page = isset( $_POST['option_page'] )
				? sanitize_text_field( wp_unslash( $_POST['option_page'] ) )
				: '';

			return 0 === strpos( $option_page, 'wpo_wcpdf_' );
		}

		return false;
	}

	/**
	 * Check if the current admin page is the plugins page.
	 *
	 * @return bool
	 */
	public function is_plugins_page(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! empty( $screen ) && isset( $screen->id ) ) {
			return 'plugins' === $screen->id;
		}

		return isset( $GLOBALS['pagenow'] ) && 'plugins.php' === $GLOBALS['pagenow'];
	}
	
	/**
	 * Check if this is a shop_order page (edit or list)
	 * 
	 * @return bool
	 */
	public function is_order_page(): bool {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		
		if ( ! is_null( $screen ) && in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ) ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if this is the My Account page
	 * 
	 * @return bool
	 */
	public function is_account_page(): bool {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return true;
		}
		
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return false;
		}
		
		$page_id = wc_get_page_id( 'myaccount' );

		return ( $page_id && is_page( $page_id ) ) || wc_post_content_has_shortcode( 'woocommerce_my_account' ) || apply_filters( 'woocommerce_is_account_page', false );
	}
	
	/**
	 * Check if this is a frontend page request (not admin, ajax, cron, rest or wp-cli)
	 * 
	 * @return bool
	 */
	public function is_frontend_page_request(): bool {
		return ! is_admin()
			&& ! wp_doing_ajax()
			&& ! wp_doing_cron()
			&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
			&& ! ( defined( 'WP_CLI' ) && WP_CLI );
	}
	
	/**
	 * Define constant if not already set
	 * 
	 * @param  string $name
	 * @param  string|bool $value
	 * @return void
	 */
	private function define( string $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
	
	/**
	 * Determine the site locale
	 * 
	 * @return string
	 */
	private function determine_locale(): string {
		if ( function_exists( 'determine_locale' ) ) { // WP5.0+
			$locale = determine_locale();
		} else {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		}

		return apply_filters( 'plugin_locale', $locale, 'woocommerce-pdf-invoices-packing-slips' );
	}
	
	/**
	 * Parse update notice from readme file.
	 *
	 * @param  string $content
	 * @param  string $new_version
	 * @return string
	 */
	private function parse_update_notice( string $content, string $new_version ): string {
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
				return $upgrade_notice;
			}

			$notice_version  = $notice_version_parts[0] . '.' . $notice_version_parts[1];
			$current_version = $current_version_parts[0] . '.' . $current_version_parts[1];

			// Check the latest stable version and ignore trunk.
			if ( version_compare( $current_version, $notice_version, '<' ) ) {

				$upgrade_notice .= '</p><p class="wpo_wcpdf_upgrade_notice">';

				foreach ( $notices as $index => $line ) {
					if ( empty( $line ) ) {
						continue;
					}
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				}
			}
		}

		return wp_kses_post( $upgrade_notice );
	}
	
	/**
	 * Get an array of all active plugins, including multisite
	 * 
	 * @return array active plugin paths
	 */
	private function get_active_plugins(): array {
		$active_plugins = (array) apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ( is_multisite() ) {
			// get_site_option( 'active_sitewide_plugins', array() ) returns a 'reversed list'
			// like [hello-dolly/hello.php] => 1369572703 so we do array_keys to make the array
			// compatible with $active_plugins
			$active_sitewide_plugins = (array) array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
			// merge arrays and remove doubles
			$active_plugins = (array) array_unique( array_merge( $active_plugins, $active_sitewide_plugins ) );
		}

		return $active_plugins;
	}
	
	/**
	 * Check if a plugin with the given filename is activated.
	 *
	 * @param string $filename The filename to check for (e.g., 'wcpdf-legacy-addon.php').
	 * @return string The full plugin path if activated, or an empty string if not.
	 */
	private function plugin_is_activated( string $filename ): string {
		$active_plugins = $this->get_active_plugins();
		$active_plugin  = '';

		foreach ( $active_plugins as $plugin ) {
			if ( ! empty( $plugin ) && false !== strpos( $plugin, $filename ) ) {
				$active_plugin = $plugin;
				break;
			}
		}

		return $active_plugin;
	}

} // class WPO_WCPDF

endif; // class_exists

/**
 * Returns the main instance of PDF Invoices & Packing Slips for WooCommerce to prevent the need to use globals.
 *
 * @since  1.6
 * @return WPO_WCPDF
 */
function WPO_WCPDF() {
	return WPO_WCPDF::instance();
}

WPO_WCPDF(); // load plugin
