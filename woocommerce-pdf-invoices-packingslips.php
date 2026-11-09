<?php
/**
 * Plugin Name:          PDF Invoices & Packing Slips for WooCommerce
 * Requires Plugins:     woocommerce
 * Plugin URI:           https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/
 * Description:          Create, print & email PDF or Electronic Invoices & PDF Packing Slips for WooCommerce orders.
 * Version:              6.0.0
 * Requires PHP:         8.1
 * Author:               WP Overnight
 * Author URI:           https://www.wpovernight.com
 * License:              GPLv2 or later
 * License URI:          https://opensource.org/licenses/gpl-license.php
 * Text Domain:          woocommerce-pdf-invoices-packing-slips
 * WC requires at least: 4.0
 * WC tested up to:      11.2
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
use WPO\IPS\SetupWizard;
use WPO\IPS\CheckoutField;

if ( ! class_exists( 'WPO_WCPDF' ) ) :

class WPO_WCPDF {

	public string $version                         = '6.0.0';
	public string $version_php                     = '8.1';
	public string $version_woo                     = '4.0';
	public string $version_wp                      = '5.3';
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
	public ?SetupWizard $setup_wizard              = null;
	public ?CheckoutField $checkout_field          = null;

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
			// Incompatible versions
			'woocommerce-pdf-ips-pro/woocommerce-pdf-ips-pro.php'                           => array(
				'name'    => 'PDF Invoices & Packing Slips for WooCommerce - Professional',
				'version' => '2.22.0',
			),
			'woocommerce-order-proposal/woocommerce-order-proposal.php'                     => array(
				'name'    => 'WooCommerce Order Proposal',
				'version' => '2.3.1',
			),
			'dokan-invoice/dokan-invoice.php'                                               => array(
				'name'    => 'Dokan - PDF Invoice',
				'version' => '1.2.8',
			),
			'woocommerce-pdf-invoices-italian-add-on/woocommerce-pdf-italian-add-on.php'    => array(
				'name'    => 'PDF Invoices Italian Add-on for WooCommerce',
				'version' => '0.7.7.4',
			),
			'wc-donation-platform/wc-donation-platform.php'                                 => array(
				'name'    => 'Donation Platform for WooCommerce: Fundraising & Donation Management',
				'version' => '1.4.0',
			),
			'factupress-verifactu-for-woocommerce/factupress-verifactu-for-woocommerce.php' => array(
				'name'    => '«FactuPress» - Veri*Factu for WooCommerce',
				'version' => '2.6.0',
			),
			'woocommerce-deposits/woocommerce-deposits.php'                                 => array(
				'name'    => 'WooCommerce Deposits', // No longer maintained: https://github.com/Pepper-Ng/woocommerce-deposits
				'version' => '2.5.17',
				'author'  => 'Webtomizer', // protects the WooCommerce Deposits extension (by Woo), which shares this basename
			),
			// Legacy addons
			'ubl-woocommerce-pdf-invoices/ubl-woocommerce-pdf-invoices.php'                 => 'UBL Invoices for WooCommerce',
			'woocommerce-pdf-ips-number-tools/woocommerce-pdf-ips-number-tools.php'         => 'PDF Invoices & Packing Slips for WooCommerce - Number Tools',
			'woocommerce-pdf-ips-ubl-extender/woocommerce-pdf-ips-ubl-extender.php'         => 'PDF Invoices & Packing Slips for WooCommerce - UBL Extender',
			'wpo-ips-factur-x/wpo-ips-factur-x.php'                                         => 'PDF Invoices & Packing Slips for WooCommerce - Factur-X',
			'wpo-ips-cius-ro/wpo-ips-cius-ro.php'                                           => 'PDF Invoices & Packing Slips for WooCommerce - CIUS-RO',
			'wpo-ips-xrechnung/wpo-ips-xrechnung.php'                                       => 'PDF Invoices & Packing Slips for WooCommerce - XRechnung',
			'wpo-ips-fatturapa/wpo-ips-fatturapa.php'                                       => 'PDF Invoices & Packing Slips for WooCommerce - FatturaPA',
			'wcpdf-quotation/wcpdf-quotation.php'                                           => 'PDF Invoices & Packing Slips for WooCommerce - Quotation Document',
		) );

		$this->define( 'WPO_WCPDF_VERSION', $this->version );

		// load the localisation & classes
		add_action( 'init', array( $this, 'translations' ), 8 );
		add_action( 'init', array( $this, 'load_classes' ), 9 ); // Pro runs on default 10, if this runs after it will not work
		add_action( 'in_plugin_update_message-' . $this->plugin_basename, array( $this, 'in_plugin_update_message' ) );
		add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatible' ) );
		add_action( 'wpo_wcpdf_new_github_prerelease_available', array( $this, 'set_new_unstable_version_available_option' ), 10, 3 );
		add_action( 'init', array( '\\WPO\\IPS\\Semaphore', 'init_cleanup' ), 999 ); // wait AS to initialize

		// Deactivate legacy extensions when the plugin is activated.
		register_activation_hook(
			__FILE__,
			array( $this, 'deactivate_legacy_addons' )
		);

		// Deactivate legacy extensions that were already active after an update.
		add_action(
			'plugins_loaded',
			array( $this, 'deactivate_legacy_addons' ),
			PHP_INT_MIN
		);

		// Block regular activation attempts.
		add_action(
			'activate_plugin',
			array( $this, 'block_legacy_addon_activation' ),
			PHP_INT_MIN,
			1
		);

		// Prevent legacy extensions from being added to the active plugins option.
		add_filter(
			'pre_update_option_active_plugins',
			array( $this, 'filter_legacy_addons_from_active_plugin_option' ),
			PHP_INT_MAX,
			3
		);

		// Prevent legacy extensions from being network activated.
		add_filter(
			'pre_update_site_option_active_sitewide_plugins',
			array( $this, 'filter_legacy_addons_from_active_plugin_option' ),
			PHP_INT_MAX,
			3
		);

		// Remove activation links from the regular Plugins screen.
		add_filter(
			'plugin_action_links',
			array( $this, 'filter_legacy_addon_action_links' ),
			10,
			4
		);

		// Remove activation links from the Network Plugins screen.
		add_filter(
			'network_admin_plugin_action_links',
			array( $this, 'filter_legacy_addon_action_links' ),
			10,
			4
		);
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

		$is_admin_like       = is_admin() || wp_doing_ajax();
		$is_document_context = wpo_ips_is_document_context_request();
		$is_checkout_context = wpo_ips_is_checkout_request();

		// Lightweight/common services
		$this->get_instance( 'settings' );
		$this->get_instance( 'documents' );
		$this->get_instance( 'order_util' );
		$this->get_instance( 'vat_plugins' );
		$this->get_instance( 'main' );

		// Register document routing on every request so rewrite rebuilds retain its rule.
		$this->get_instance( 'endpoint' );

		// Document-related runtime
		if ( $is_document_context ) {
			$this->get_instance( 'file_system' );
			$this->get_instance( 'third_party_plugins' );
			$this->get_instance( 'font_synchronizer' );
		}

		// Admin/UI/runtime AJAX
		if ( $is_admin_like ) {
			$this->get_instance( 'admin' );
			$this->get_instance( 'assets' );
			$this->get_instance( 'install' );
			$this->get_instance( 'notices' );
		}

		// Shortcodes can appear on any page; load their handler only when rendered.
		foreach ( array( 'wcpdf_download_invoice', 'wcpdf_download_pdf', 'wcpdf_document_link' ) as $shortcode ) {
			add_shortcode( $shortcode, function ( $atts, $content = null, $shortcode_tag = '' ) {
				return $this->get_instance( 'frontend' )->generate_document_shortcode(
					is_array( $atts ) ? $atts : array(),
					$content,
					$shortcode_tag
				);
			} );
		}

		// Frontend only where useful
		if (
			wpo_ips_is_account_page()        ||
			wpo_ips_is_order_received_page() ||
			$is_checkout_context             ||
			wpo_ips_is_wc_api_request()      ||
			( defined( 'REST_REQUEST' ) && REST_REQUEST )
		) {
			$this->get_instance( 'frontend' );
		}

		// REST_REQUEST is not defined during normal init. Load services before REST routes.
		add_action( 'rest_api_init', function () {
			$this->get_instance( 'frontend' );

			if ( wpo_ips_edi_peppol_is_available() ) {
				$this->get_instance( 'peppol' );
			}
		}, 5 );

		// Peppol only when enabled and relevant
		if (
			function_exists( 'wpo_ips_edi_peppol_is_available' ) &&
			wpo_ips_edi_peppol_is_available() &&
			(
				$is_checkout_context       ||
				wpo_ips_is_account_page()  ||
				$is_admin_like             ||
				( defined( 'REST_REQUEST' ) && REST_REQUEST )
			)
		) {
			$this->get_instance( 'peppol' );
		}
	}
	
	/**
	 * Get a plugin class instance by slug.
	 *
	 * @param string $property
	 *
	 * @return object|null
	 */
	public function get_instance( string $property ): ?object {
		$map = apply_filters(
			'wpo_ips_plugin_instance_map',
			array(
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
				'checkout_field'      => CheckoutField::class,
			),
			$this
		);

		if ( ! isset( $map[ $property ] ) ) {
			if ( function_exists( 'wcpdf_log_error' ) ) {
				wcpdf_log_error(
					sprintf(
						'%s: Unknown plugin instance property requested: %s.',
						__METHOD__,
						$property
					),
					'critical'
				);
			}

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
	 * Load translations from WordPress.org language packs, with custom
	 * translations overriding them through the textdomain folder
	 * (WP_LANG_DIR/<textdomain>/<textdomain>-<locale>.mo).
	 * 
	 * @param bool $force_reload Reload translations after a locale change, such as when generating email attachments.
	 * @return void
	 */
	public function translations( bool $force_reload = false ): void {
		static $loaded = false;

		$textdomain = 'woocommerce-pdf-invoices-packing-slips';

		if ( $loaded && ! $force_reload ) {
			return;
		}

		$locale     = $this->determine_locale();
		$dir        = trailingslashit( WP_LANG_DIR );

		// Custom translation folder, e.g. to override WordPress.org language packs.
		// WordPress can load a sibling .l10n.php file (6.5+) without a .mo file.
		$custom_mo      = $dir . $textdomain . '/' . $textdomain . '-' . $locale . '.mo';
		$custom_l10nphp = $dir . $textdomain . '/' . $textdomain . '-' . $locale . '.l10n.php';

		// The domain may already be loaded on demand (e.g. a translation
		// request before 'init', or WordPress implicit loading for language
		// packs), skipping the custom folder: only skip when the custom
		// translations could not change or add anything.
		if ( is_textdomain_loaded( $textdomain ) && ! $force_reload && ! is_readable( $custom_mo ) && ! is_readable( $custom_l10nphp ) ) {
			$loaded = true;
			return;
		}

		if ( is_textdomain_loaded( $textdomain ) ) {
			unload_textdomain( $textdomain );
		}

		$loaded = load_textdomain( $textdomain, $custom_mo );

		// Keep explicit loading to support the plugin_locale filter when reloading email attachment translations.
		$loaded = load_textdomain( $textdomain, $dir . 'plugins/' . $textdomain . '-' . $locale . '.mo' ) || $loaded;
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
		$active_legacy_addons = array_filter(
			array_keys( $this->legacy_addons ),
			function ( string $plugin_basename ): bool {
				return $this->plugin_is_activated( $plugin_basename )
					&& $this->is_legacy_addon( $plugin_basename );
			}
		);

		if ( empty( $active_legacy_addons ) ) {
			return;
		}

		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins( $active_legacy_addons );

		foreach ( $active_legacy_addons as $plugin_basename ) {
			$this->set_legacy_addon_notice( $plugin_basename );
		}
	}

	/**
	 * Block activation of legacy add-ons.
	 *
	 * @param string $plugin Plugin basename.
	 * @return void
	 */
	public function block_legacy_addon_activation( string $plugin ): void {
		if ( ! $this->is_legacy_addon( $plugin ) ) {
			return;
		}

		$name               = $this->get_legacy_addon_name( $plugin );
		$deprecated_version = $this->get_legacy_addon_deprecated_version( $plugin );

		$this->set_legacy_addon_notice( $plugin );

		if ( null !== $deprecated_version ) {
			$message = sprintf(
				/* translators: 1: legacy add-on name, 2: last deprecated add-on version */
				__( 'The %1$s add-on cannot be activated because the installed version is not compatible with this version of PDF Invoices & Packing Slips for WooCommerce. Please update the add-on to a version newer than %2$s first.', 'woocommerce-pdf-invoices-packing-slips' ),
				$name,
				$deprecated_version
			);
		} else {
			$message = sprintf(
				/* translators: %s: legacy add-on name */
				__( 'The legacy %s add-on cannot be activated because it is not compatible with this version of PDF Invoices & Packing Slips for WooCommerce.', 'woocommerce-pdf-invoices-packing-slips' ),
				$name
			);
		}

		if ( wp_doing_ajax() ) {
			wp_send_json_error(
				array(
					'plugin'       => $plugin,
					'pluginName'   => $name,
					'errorCode'    => 'wpo_wcpdf_legacy_addon',
					'errorMessage' => $message,
				),
				409
			);
		}

		wp_die(
			esc_html( $message ),
			esc_html__( 'Plugin activation blocked', 'woocommerce-pdf-invoices-packing-slips' ),
			array(
				'response'  => 409,
				'back_link' => true,
			)
		);
	}

	/**
	 * Prevent legacy add-ons from being added to an active plugins option.
	 *
	 * @param mixed  $plugins     New option value.
	 * @param mixed  $old_plugins Previous option value.
	 * @param string $option      Option name.
	 * @return array
	 */
	public function filter_legacy_addons_from_active_plugin_option( mixed $plugins, mixed $old_plugins, string $option ): array {
		$plugins             = is_array( $plugins ) ? $plugins : array();
		$old_plugins         = is_array( $old_plugins ) ? $old_plugins : array();
		$legacy_addons       = array_filter(
			array_keys( $this->legacy_addons ),
			function ( string $plugin_basename ): bool {
				return $this->is_legacy_addon( $plugin_basename );
			}
		);
		$is_networkwide_list = 'active_sitewide_plugins' === $option;

		if ( $is_networkwide_list ) {
			$activation_attempts = array_intersect_key(
				array_diff_key( $plugins, $old_plugins ),
				array_fill_keys( $legacy_addons, true )
			);

			foreach ( array_keys( $activation_attempts ) as $plugin_basename ) {
				$this->set_legacy_addon_notice( $plugin_basename );
			}

			return array_diff_key( $plugins, array_fill_keys( $legacy_addons, true ) );
		}

		$activation_attempts = array_intersect(
			array_diff( $plugins, $old_plugins ),
			$legacy_addons
		);

		foreach ( $activation_attempts as $plugin_basename ) {
			$this->set_legacy_addon_notice( $plugin_basename );
		}

		return array_values(
			array_diff( $plugins, $legacy_addons )
		);
	}

	/**
	 * Remove activation links from legacy add-ons.
	 *
	 * @param array  $actions     Plugin action links.
	 * @param string $plugin_file Plugin basename.
	 * @param array  $plugin_data Plugin data.
	 * @param string $context     Plugins list context.
	 * @return array
	 */
	public function filter_legacy_addon_action_links( array $actions, string $plugin_file, array $plugin_data, string $context ): array {
		if ( ! $this->is_legacy_addon( $plugin_file ) ) {
			return $actions;
		}

		unset(
			$actions['activate'],
			$actions['network_activate']
		);

		$deprecated_version = $this->get_legacy_addon_deprecated_version( $plugin_file );

		if ( null !== $deprecated_version ) {
			$label = esc_html__( 'Outdated version — update to activate', 'woocommerce-pdf-invoices-packing-slips' );
		} else {
			$label = esc_html__( 'Legacy add-on — activation unavailable', 'woocommerce-pdf-invoices-packing-slips' );
		}

		$actions['wpo_wcpdf_legacy_addon'] = sprintf(
			'<span aria-disabled="true">%s</span>',
			$label
		);

		return $actions;
	}
	
	/**
	 * Get the display name of a legacy add-on.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return string
	 */
	public function get_legacy_addon_name( string $plugin_basename ): string {
		$addon = $this->legacy_addons[ $plugin_basename ] ?? '';

		if ( is_array( $addon ) ) {
			return (string) ( $addon['name'] ?? '' );
		}

		return (string) $addon;
	}

	/**
	 * Get the last add-on version still treated as legacy.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return string|null Null when the add-on is always legacy.
	 */
	public function get_legacy_addon_deprecated_version( string $plugin_basename ): ?string {
		$addon = $this->legacy_addons[ $plugin_basename ] ?? null;

		if ( is_array( $addon ) && ! empty( $addon['version'] ) ) {
			return (string) $addon['version'];
		}

		return null;
	}

	/**
	 * Get the author an add-on must match to be treated as legacy.
	 *
	 * Distinguishes different plugins that share a basename (e.g. the
	 * unmaintained Webtomizer deposits plugin and the WooCommerce Deposits
	 * extension). Null when the entry applies to any author.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return string|null
	 */
	public function get_legacy_addon_author( string $plugin_basename ): ?string {
		$addon = $this->legacy_addons[ $plugin_basename ] ?? null;

		if ( is_array( $addon ) && ! empty( $addon['author'] ) ) {
			return (string) $addon['author'];
		}

		return null;
	}

	/**
	 * Get the author of a plugin from its file header.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return string Empty string when the author cannot be determined.
	 */
	private function get_plugin_author( string $plugin_basename ): string {
		static $authors = array();

		if ( ! isset( $authors[ $plugin_basename ] ) ) {
			$file = WP_PLUGIN_DIR . '/' . $plugin_basename;
			$data = file_exists( $file )
				? get_file_data( $file, array( 'Author' => 'Author' ) )
				: array();

			$authors[ $plugin_basename ] = (string) ( $data['Author'] ?? '' );
		}

		return $authors[ $plugin_basename ];
	}

	/**
	 * Get the installed version of a plugin from its file header.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return string Empty string when the version cannot be determined.
	 */
	private function get_plugin_version( string $plugin_basename ): string {
		static $versions = array();

		if ( ! isset( $versions[ $plugin_basename ] ) ) {
			$file = WP_PLUGIN_DIR . '/' . $plugin_basename;
			$data = file_exists( $file )
				? get_file_data( $file, array( 'Version' => 'Version' ) )
				: array();

			$versions[ $plugin_basename ] = (string) ( $data['Version'] ?? '' );
		}

		return $versions[ $plugin_basename ];
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
		$debug_settings = $this->get_instance( 'settings' )->get_settings( 'debug' );
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
	 * Define constant if not already set
	 * 
	 * @param  string $name
	 * @param  mixed $value
	 * @return void
	 */
	private function define( string $name, mixed $value ): void {
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

		return (string) apply_filters(
			'plugin_locale',
			$locale,
			'woocommerce-pdf-invoices-packing-slips'
		);
	}
	
	/**
	 * Parse update notice from readme file.
	 *
	 * Shows the Upgrade Notice of the newest entry that is not newer than
	 * the version being offered ($new_version); the block stops at the next
	 * version heading, so older entries below it are not appended.
	 *
	 * @param  string $content
	 * @param  string $new_version
	 * @return string
	 */
	private function parse_update_notice( string $content, string $new_version ): string {
		// Output Upgrade Notice.
		// The notice block stops at the next version heading (any `= 1.2.3 =`)
		// or at the end of the section, so older entries below the newest one
		// are not appended to the notice of the update being offered.
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*\d+(?:\.\d+)*\s*=|$)~Uis';
		$upgrade_notice = '';


		if ( preg_match( $regexp, $content, $matches ) ) {

			// Ignore notices for versions newer than the update being offered.
			if ( version_compare( trim( (string) $matches[1] ), $new_version, '>' ) ) {
				return $upgrade_notice;
			}

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
	 * Check whether a plugin is active.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return bool
	 */
	private function plugin_is_activated( string $plugin_basename ): bool {
		return in_array(
			$plugin_basename,
			$this->get_active_plugins(),
			true
		);
	}

	/**
	 * Check whether a plugin is a legacy add-on.
	 *
	 * Add-ons without a version are always legacy. Add-ons with a version
	 * (the last legacy release) are only legacy when the installed version is
	 * that version or lower, or when the installed version cannot be determined.
	 * Dev suffixes in the installed version (e.g. -i782, -pr781, -beta.1 or
	 * -rc.1) are stripped, so every build of a release line is compared as
	 * that release. Entries with an author only apply to installed plugins
	 * whose Author header matches, so plugins sharing a basename stay
	 * unaffected. A missing add-on file is never treated as legacy: WordPress
	 * skips missing files when loading plugins, so a temporarily absent file
	 * (deployment, maintenance) must not disable a compatible add-on.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return bool
	 */
	public function is_legacy_addon( string $plugin_basename ): bool {
		if ( ! isset( $this->legacy_addons[ $plugin_basename ] ) ) {
			return false;
		}

		$deprecated_version = $this->get_legacy_addon_deprecated_version( $plugin_basename );

		if ( null === $deprecated_version ) {
			return true;
		}

		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_basename ) ) {
			return false;
		}

		$author = $this->get_legacy_addon_author( $plugin_basename );

		if (
			null !== $author &&
			false === stripos( $this->get_plugin_author( $plugin_basename ), $author )
		) {
			return false;
		}

		$installed_version = $this->get_plugin_version( $plugin_basename );

		// Strip the dev suffix to compare base versions only.
		if ( ! preg_match( '/^v?(\d+(?:\.\d+)*)/', $installed_version, $matches ) ) {
			return true; // Unrecognized version format: treat as legacy.
		}

		return version_compare( $matches[1], $deprecated_version, '<=' );
	}

	/**
	 * Store the notice state for a legacy add-on.
	 *
	 * Versioned add-ons can be deactivated at any time (at plugins_loaded,
	 * on activation of this plugin, including blocked activation attempts),
	 * so their notice has no expiry: the deactivation warning must not
	 * silently disappear, and it is removed by legacy_addon_notices() once
	 * a compatible add-on version is installed. Always-legacy add-ons
	 * keep the 24h expiry.
	 *
	 * @param string $plugin_basename Plugin basename.
	 * @return void
	 */
	private function set_legacy_addon_notice( string $plugin_basename ): void {
		$expiry = null !== $this->get_legacy_addon_deprecated_version( $plugin_basename )
			? 0
			: DAY_IN_SECONDS;

		set_transient(
			$this->get_legacy_addon_transient_name( $plugin_basename ),
			'yes',
			$expiry
		);
	}

} // class WPO_WCPDF

endif; // class_exists

/**
 * Returns the main instance of PDF Invoices & Packing Slips for WooCommerce to prevent the need to use globals.
 *
 * @return WPO_WCPDF
 */
function WPO_WCPDF(): WPO_WCPDF {
	return WPO_WCPDF::instance();
}

WPO_WCPDF(); // load plugin
