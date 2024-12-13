<?php
namespace WPO\IPS\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsUpgrade' ) ) :

class SettingsUpgrade {

	public           $extensions;
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->extensions = array( 'pro', 'templates' );

		add_action( 'wpo_wcpdf_before_settings_page', array( $this, 'extensions_license_cache_notice' ), 10, 2 );
		add_action( 'wpo_wcpdf_after_settings_page', array( $this, 'extension_overview' ), 10, 2 );
		add_action( 'wpo_wcpdf_schedule_extensions_license_cache_clearing', array( $this, 'clear_extensions_license_cache' ) );
	}

	public function extensions_license_cache_notice( $tab, $active_section ) {
		if ( 'upgrade' === $tab && WPO_WCPDF()->settings->upgrade->get_extensions_license_data() ) {
			$message = sprintf(
				/* translators: 1. open anchor tag, 2. close anchor tag */
				__( 'Kindly be aware that the extensions\' license data is currently stored in cache, impeding the instant update of the information displayed below. To access the latest details, we recommend clearing the cache %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=tools' ) ) . '">',
				'</a>'
			);
			printf( '<div class="notice inline notice-warning"><p>%s</p></div>', $message );
		}
	}

	public function extension_overview( $tab, $section ) {
		if ( 'upgrade' === $tab ) {
			$features = array(
				array(
					'label'       => __( 'Proforma Invoice, Credit Note & Receipt', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Update your workflow and handle refunds. Both Proforma & Credit Note documents can either follow the main invoice numbering or have their own separate number sequence.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Attach to email', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Also attach the Packing Slip, Proforma Invoice and Credit Note to any of the outgoing emails.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Cloud storage upload', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Automatically upload your documents via FTP/SFTP or to Dropbox.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Bulk export', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Easily export documents for a specific date range.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Multilingual support', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Handle document translations with WPML, Polylang, Weglot, TranslatePress or GTranslate.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Attach static files', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Add up to three static files to your emails.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Custom document titles and filenames', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Customize document titles and filenames right in the plugin settings.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Custom address format', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Customize the address format of the billing and shipping addresses.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'Order notification email', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						__( 'Send a notification email to user specified addresses.', 'woocommerce-pdf-invoices-packing-slips' ),
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/configuring-the-order-notification-email/',
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' )
					),
					'extensions'  => array( 'pro', 'bundle' ),
				),
				array(
					'label'       => __( 'PDF Customizer', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						__( 'Fully customize the product table and totals table on your documents.', 'woocommerce-pdf-invoices-packing-slips' ),
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/using-the-customizer/',
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' )
					),
					'extensions'  => array( 'templates', 'bundle' ),
				),
				array(
					'label'       => __( 'Add custom data to your documents', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>',
						__( 'Display all sorts of data and apply conditional logic using Custom Blocks.', 'woocommerce-pdf-invoices-packing-slips' ),
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/using-custom-blocks/',
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' )
					),
					'extensions'  => array( 'templates', 'bundle' ),
				),
				array(
					'label'       => __( 'Additional PDF templates', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Make use of our Business or Modern template designs.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'templates', 'bundle' ),
				),
				array(
					'label'       => __( 'Add styling', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Easily change the look and feel of your documents by adding some custom CSS.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions'  => array( 'templates', 'bundle' ),
				),
			);

			$extension_license_infos = $this->get_extension_license_infos( true );

			$plugin_recommendations = array(
				array(
					'plugin_path' => 'wc-reminder-emails/wc-reminder-emails.php',
					'thumbnail'   => WPO_WCPDF()->plugin_url().'/assets/images/wc-reminder-emails-thumbnail-400x400.jpg',
					'title'       => __( 'WooCommerce Smart Reminder Emails', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Automatically schedule and send Reminder Emails for WooCommerce orders.', 'woocommerce-pdf-invoices-packing-slips' ),
					'url'         => 'https://wpovernight.com/downloads/woocommerce-reminder-emails-payment-reminders?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&content=reminder-emails-cross'
				),
				array(
					'plugin_path' => 'woocommerce-address-labels/woocommerce-address-labels.php',
					'thumbnail'   => WPO_WCPDF()->plugin_url().'/assets/images/woocommerce-address-labels-thumbnail-400x400.jpg',
					'title'       => __( 'WooCommerce Print Address Labels', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Print out address labels for selected orders straight from WooCommerce.', 'woocommerce-pdf-invoices-packing-slips' ),
					'url'         => 'https://wpovernight.com/downloads/woocommerce-print-address-labels?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&content=address-labels-cross'
				),
				array(
					'plugin_path' => 'woocommerce-printnode/print-orders.php',
					'thumbnail'   => WPO_WCPDF()->plugin_url().'/assets/images/woocommerce-printnode-thumbnail-400x400.jpg',
					'title'       => __( 'WooCommerce Automatic Printing - PrintNode', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'A plugin to automatically print completed orders via PrintNode.', 'woocommerce-pdf-invoices-packing-slips' ),
					'url'         => 'https://wpovernight.com/downloads/woocommerce-automatic-order-printing-printnode?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&content=order-printing-cross'
				),
				array(
					'plugin_path' => 'woocommerce-ultimate-barcodes/woocommerce-ultimate-barcodes.php',
					'thumbnail'   => WPO_WCPDF()->plugin_url().'/assets/images/woocommerce-ultimate-barcodes-thumbnail-400x400.jpg',
					'title'       => __( 'WooCommerce Ultimate Barcodes', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Generate barcodes (ZATCA, QR-codes, C128, EAN-13 and more) for your orders, products and even invoices & packing slips.', 'woocommerce-pdf-invoices-packing-slips' ),
					'url'         => 'https://wpovernight.com/downloads/woocommerce-ultimate-barcodes?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&content=ultimate-barcodes-cross'
				),
				array(
					'plugin_path' => 'woocommerce-order-list/woocommerce-order-list.php',
					'thumbnail'   => WPO_WCPDF()->plugin_url().'/assets/images/woocommerce-order-list-thumbnail-400x400.jpg',
					'title'       => __( 'WooCommerce Print Order List', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'This plugin lets you quickly print a list of your WooCommerce orders. Great for order picking.', 'woocommerce-pdf-invoices-packing-slips' ),
					'url'         => 'https://wpovernight.com/downloads/woocommerce-print-order-list?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&content=order-list-cross'
				),
				array(
					'plugin_path' => 'wp-menu-cart-pro/wp-menu-cart-pro.php',
					'thumbnail'   => WPO_WCPDF()->plugin_url().'/assets/images/wp-menu-cart-pro-thumbnail-400x400.jpg',
					'title'       => __( 'Menu Cart Pro', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Integrates seamlessly with WooCommerce to add a shopping cart to your menu.', 'woocommerce-pdf-invoices-packing-slips' ),
					'url'         => 'https://wpovernight.com/downloads/menu-cart-pro?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&content=menu-cart-pro-cross'
				),
			);

			// Sort recommendations based on if the plugin is installed
			$installed_plugins             = get_plugins();
			$sorted_plugin_recommendations = array();

			foreach ( array_reverse( $plugin_recommendations ) as $plugin ) {
				if ( isset( $installed_plugins[ $plugin['plugin_path'] ] ) ) {
					$plugin['installed']             = true;
					$sorted_plugin_recommendations[] = $plugin;
				} else {
					array_unshift( $sorted_plugin_recommendations, $plugin );
				}
			}

			include( WPO_WCPDF()->plugin_path() . '/views/upgrade-table.php' );
		}
	}

	/**
	 * Check if a PDF extension is enabled
	 *
	 * @param  string  $extension  can be 'pro' or 'templates'
	 * @return boolean
	 */
	public function extension_is_enabled( $extension ) {
		$is_enabled = false;

		if ( ! empty( $extension ) || ! in_array( $extension, $this->extensions ) ) {
			$extension_main_function = "WPO_WCPDF_".ucfirst( $extension );
			if ( function_exists( $extension_main_function ) ) {
				$is_enabled = true;
			}
		}

		return $is_enabled;
	}

	/**
	 * Get PDF extensions license info
	 *
	 * @param  bool  $ignore_cache
	 * @return array
	 */
	public function get_extension_license_infos( $ignore_cache = false ) {
		$extensions   = $this->extensions;
		$license_info = ! $ignore_cache ? $this->get_extensions_license_data( 'cached' ) : array();

		if ( ! empty( $license_info ) ) {
			return $license_info;
		}

		foreach ( $extensions as $extension ) {
			$license_info[ $extension ] = array();
			$args                       = array();
			$request                    = null;
			$license_key                = '';
			$updater                    = null;

			if ( $this->extension_is_enabled( $extension ) ) {
				$extension_main_function = "WPO_WCPDF_" . ucfirst( $extension );
				$updater                 = $extension_main_function()->updater;

				if ( 'templates' === $extension && version_compare( $extension_main_function()->version, '2.20.0', '<=' ) ) { // 'updater' property had 'private' visibility
					continue;
				}

				if ( is_null( $updater ) ) {
					continue;
				}

				// built-in updater
				if ( is_callable( array( $updater, 'get_license_key' ) ) ) {
					$license_key = $updater->get_license_key();
				}

				if ( ! empty( $license_key ) ) {
					$args['edd_action']  = 'check_license';
					$args['license_key'] = $license_info[ $extension ]['license_key'] = trim( $license_key );
				} else {
					continue;
				}

				if ( $updater && is_callable( array( $updater, 'remote_license_actions' ) ) && ! empty( $args ) ) {
					$request = $updater->remote_license_actions( $args );

					if ( is_wp_error( $request ) ) {
						wcpdf_log_error( 'Unable to retrieve license data from the remote server for the extension ' . $extension . '. Error: ' . $response->get_error_message() );
						continue;
					}
					
					$license_info[ $extension ]['status']         = isset( $request->license )        ? $request->license              : 'inactive';
					$license_info[ $extension ]['license_limit']  = isset( $request->license_limit )  ? $request->license_limit        : 1;
					$license_info[ $extension ]['license_id']     = isset( $request->license_id )     ? absint( $request->license_id ) : null;
					$license_info[ $extension ]['bundle_license'] = isset( $request->bundle_license ) ? $request->bundle_license       : false;
				}
			}
		}
		
		$extensions[]       = 'bundle';
		$default_utm_tags   = 'utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab';
		$bundle_upgrade_url = '';
		$upgrade_tiers      = array(
			// license limit => upgrade ID
			'pro' => array(
				1  => 3,
				3  => 4,
				25 => 5,
			),
			'templates' => array(
				1  => 4,
				3  => 5,
				25 => 6,
			),
		);
		
		foreach ( $extensions as $extension ) {
			// set default URL
			switch ( $extension ) {
				case 'pro':
					$pro_utm_tags                      = $default_utm_tags . '&utm_content=ips-pro-upgrade';
					$license_info[ $extension ]['url'] = "https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/?{$pro_utm_tags}";
					break;
				case 'templates':
				case 'bundle':
					$bundle_utm_tags                   = $default_utm_tags . '&utm_content=ips-plus-bundle-upgrade';
					$license_info[ $extension ]['url'] = "https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/?{$bundle_utm_tags}";
					break;
			}
			
			// if bundle, no upgrade needed
			if ( isset( $license_info[ $extension ]['bundle_license'] ) && $license_info[ $extension ]['bundle_license'] ) {
				continue;
			}
			
			// there's no license ID, can't be upgraded
			if ( empty( $license_info[ $extension ]['license_id'] ) ) {
				continue;
			}
			
			// check if the license is activated and valid
			if ( empty( $license_info[ $extension ]['status'] ) || 'valid' !== $license_info[ $extension ]['status'] ) {
				continue;
			}
			
			// if bundle upgrade URL is already set, skip
			if ( ! empty( $bundle_upgrade_url ) ) {
				continue;
			}
			
			// create upgrade URL
			$license_id    = $license_info[ $extension ]['license_id'];
			$license_limit = $license_info[ $extension ]['license_limit'];
			$upgrade_id    = isset( $upgrade_tiers[ $extension ][ $license_limit ] ) ? $upgrade_tiers[ $extension ][ $license_limit ] : 0;
			
			if ( 0 === $upgrade_id ) {
				continue;
			}
			
			$upgrade_utm_tags   = $default_utm_tags . '&utm_content=ips-plus-bundle-upgrade+upgrade-from-' . $extension;
			$bundle_upgrade_url = "https://wpovernight.com/checkout/?edd_action=sl_license_upgrade&license_id={$license_id}&upgrade_id={$upgrade_id}&{$upgrade_utm_tags}";
		}
		
		// set bundle upgrade URL
		if ( ! empty( $bundle_upgrade_url ) ) {
			$license_info['bundle']['url'] = $bundle_upgrade_url;
		}

		update_option( 'wpo_wcpdf_extensions_license_cache', $license_info );

		if ( as_next_scheduled_action( 'wpo_wcpdf_schedule_extensions_license_cache_clearing' ) ) {
			as_unschedule_action( 'wpo_wcpdf_schedule_extensions_license_cache_clearing' );
		}

		as_schedule_single_action( strtotime( "+1 week" ), 'wpo_wcpdf_schedule_extensions_license_cache_clearing' );

		return $license_info;
	}

	/**
	 * Clear extensions license cache
	 *
	 * @return void
	 */
	public function clear_extensions_license_cache() {
		delete_option( 'wpo_wcpdf_extensions_license_cache' );
	}

	/**
	 * Get extensions license data
	 *
	 * @param string $type can be 'cached' or 'live'
	 * @return array
	 */
	public function get_extensions_license_data( string $type = 'cached' ): array {
		$option_key = 'wpo_wcpdf_extensions_license_cache';

		// default to fetching cached data
		$data = get_option( $option_key, array() );

		// if type is 'live' or cached data is empty, fetch live data
		if ( 'live' === $type || empty( $data ) ) {
			$data = $this->get_extension_license_infos( true );

			if ( 'cached' === $type ) {
				update_option( $option_key, $data );
			}
		}

		return $data;
	}

	/**
	 * Check if are any extensions installed
	 *
	 * @return bool
	 */
	public function are_any_extensions_installed() {
		$installed = false;

		foreach ( $this->extensions as $extension ) {
			if ( $this->extension_is_enabled( $extension ) ) {
				$installed = true;
				break;
			}
		}

		return $installed;
	}

}

endif; // class_exists
