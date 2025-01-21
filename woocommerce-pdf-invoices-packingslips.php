<?php
/**
 * Plugin Name:          PDF Invoices & Packing Slips for WooCommerce
 * Requires Plugins:     woocommerce
 * Plugin URI:           https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/
 * Description:          Create, print & email PDF or UBL Invoices & PDF Packing Slips for WooCommerce orders.
 * Version:              3.9.6
 * Author:               WP Overnight
 * Author URI:           https://www.wpovernight.com
 * License:              GPLv2 or later
 * License URI:          https://opensource.org/licenses/gpl-license.php
 * Text Domain:          woocommerce-pdf-invoices-packing-slips
 * WC requires at least: 3.3
 * WC tested up to:      9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPO_WCPDF' ) ) :

class WPO_WCPDF {

	public $version              = '3.9.6';
	public $version_php          = '7.4';
	public $version_woo          = '3.3';
	public $version_wp           = '4.4';
	public $plugin_basename;
	public $legacy_addons;
	public $third_party_plugins;
	public $order_util;
	public $settings;
	public $documents;
	public $main;
	public $endpoint;
	public $assets;
	public $admin;
	public $frontend;
	public $install;
	public $font_synchronizer;

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
		require $this->plugin_path() . '/vendor/autoload.php';

		$this->plugin_basename = plugin_basename(__FILE__);
		$this->legacy_addons   = apply_filters( 'wpo_wcpdf_legacy_addons', array(
			'ubl-woocommerce-pdf-invoices.php'     => 'UBL Invoices for WooCommerce',
			'woocommerce-pdf-ips-number-tools.php' => 'PDF Invoices & Packing Slips for WooCommerce - Number Tools',
		) );

		$this->define( 'WPO_WCPDF_VERSION', $this->version );

		// load the localisation & classes
		add_action( 'init', array( $this, 'translations' ), 8 );
		add_action( 'init', array( $this, 'load_classes' ), 9 ); // Pro runs on default 10, if this runs after it will not work
		add_action( 'in_plugin_update_message-'.$this->plugin_basename, array( $this, 'in_plugin_update_message' ) );
		add_action( 'before_woocommerce_init', array( $this, 'woocommerce_hpos_compatible' ) );
		add_action( 'admin_notices', array( $this, 'nginx_detected' ) );
		add_action( 'admin_notices', array( $this, 'mailpoet_mta_detected' ) );
		add_action( 'admin_notices', array( $this, 'rtl_detected' ) );
		add_action( 'admin_notices', array( $this, 'yearly_reset_action_missing_notice' ) );
		add_action( 'admin_notices', array( $this, 'legacy_addon_notices' ) );
		add_action( 'init', array( '\\WPO\\IPS\\Semaphore', 'init_cleanup' ), 999 ); // wait AS to initialize

		// deactivate legacy extensions if activated
		register_activation_hook( __FILE__, array( $this, 'deactivate_legacy_addons' ) );
	}

	public function is_dependency_version_supported( $dependency ) {
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
		$locale = $this->determine_locale();
		$dir    = trailingslashit( WP_LANG_DIR );

		$textdomains = array( 'woocommerce-pdf-invoices-packing-slips' );

		/**
		 * Frontend/global Locale. Looks in:
		 *
		 * 		- WP_LANG_DIR/woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packing-slips-LOCALE.mo
		 * 	 	- WP_LANG_DIR/plugins/woocommerce-pdf-invoices-packing-slips-LOCALE.mo
		 * 	 	- woocommerce-pdf-invoices-packing-slips/languages/woocommerce-pdf-invoices-packing-slips-LOCALE.mo (which if not found falls back to:)
		 * 	 	- WP_LANG_DIR/plugins/woocommerce-pdf-invoices-packing-slips-LOCALE.mo
		 */
		foreach ( $textdomains as $textdomain ) {
			unload_textdomain( $textdomain );
			load_textdomain( $textdomain, $dir . 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packing-slips-' . $locale . '.mo' );
			load_textdomain( $textdomain, $dir . 'plugins/woocommerce-pdf-invoices-packing-slips-' . $locale . '.mo' );
			load_plugin_textdomain( $textdomain, false, dirname( plugin_basename(__FILE__) ) . '/languages' );
		}
	}

	/**
	 * Load the main plugin classes and functions
	 */
	public function includes() {
		// plugin legacy class mapping
		include_once $this->plugin_path() . '/wpo-ips-legacy-class-alias-mapping.php';

		// plugin functions
		include_once $this->plugin_path() . '/wpo-ips-functions.php';
		include_once $this->plugin_path() . '/wpo-ips-functions-ubl.php';

		// Third party compatibility
		$this->third_party_plugins = \WPO\IPS\Compatibility\ThirdPartyPlugins::instance();
		// WC OrderUtil compatibility
		$this->order_util          = \WPO\IPS\Compatibility\OrderUtil::instance();
		// Plugin classes
		$this->settings            = \WPO\IPS\Settings::instance();
		$this->documents           = \WPO\IPS\Documents::instance();
		$this->main                = \WPO\IPS\Main::instance();
		$this->endpoint            = \WPO\IPS\Endpoint::instance();
		$this->assets              = \WPO\IPS\Assets::instance();
		$this->admin               = \WPO\IPS\Admin::instance();
		$this->frontend            = \WPO\IPS\Frontend::instance();
		$this->install             = \WPO\IPS\Install::instance();
		$this->font_synchronizer   = \WPO\IPS\FontSynchronizer::instance();
	}

	/**
	 * Instantiate classes when woocommerce is activated
	 */
	public function load_classes() {
		if ( ! $this->is_woocommerce_activated() || ! $this->is_dependency_version_supported( 'woo' ) ) {
			add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
			return;
		}

		if ( ! has_filter( 'wpo_wcpdf_pdf_maker' ) && ! $this->is_dependency_version_supported( 'php' ) ) {
			add_filter( 'wpo_wcpdf_document_is_allowed', '__return_false', 99999 );
			add_action( 'admin_notices', array ( $this, 'required_php_version' ) );
		}

		add_action( 'admin_init', array( $this, 'deactivate_legacy_addons') );

		// all systems ready - GO!
		$this->includes();
	}

	/**
	 * WooCommerce notice.
	 *
	 * @return void
	 */
	public function need_woocommerce() {
		$error_message = sprintf(
			/* translators: 1. open anchor tag, 2. close anchor tag, 3. Woo version */
			esc_html__( 'PDF Invoices & Packing Slips for WooCommerce requires %1$sWooCommerce%2$s version %3$s or higher to be installed & activated!' , 'woocommerce-pdf-invoices-packing-slips' ),
			'<a href="http://wordpress.org/extend/plugins/woocommerce/">',
			'</a>',
			esc_attr( $this->version_woo )
		);

		$message  = '<div class="error">';
		$message .= sprintf( '<p>%s</p>', $error_message );
		$message .= '</div>';

		echo wp_kses_post( $message );
	}

	/**
	 * Check if woocommerce is activated
	 */
	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option('active_sitewide_plugins' ) ) : array();

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			$is_wc_activated = true;
		} else {
			$is_wc_activated = false;
		}
		return apply_filters( 'wpo_wcpdf_is_woocommerce_activated', $is_wc_activated );
	}

	/**
	 * Declares WooCommerce HPOS compatibility.
	 *
	 * @return void
	 */
	public function woocommerce_hpos_compatible() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}

	/**
	 * PHP version requirement notice
	 */
	public function required_php_version() {
		$error_message = sprintf(
			/* translators: PHP version */
			esc_html__( 'PDF Invoices & Packing Slips for WooCommerce requires PHP %s or higher.', 'woocommerce-pdf-invoices-packing-slips' ),
			esc_attr( $this->version_php )
		);

		$php_message = sprintf(
			/* translators: <a> tags */
			esc_html__( 'We strongly recommend to %1$supdate your PHP version%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
			'<a href="https://docs.wpovernight.com/general/how-to-update-your-php-version/" target="_blank">',
			'</a>'
		);

		$message  = '<div class="error">';
		$message .= sprintf( '<p>%s</p>', $error_message );
		$message .= sprintf( '<p>%s</p>', $php_message );
		$message .= '</div>';

		echo wp_kses_post( $message );
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

	public function nginx_detected()
	{
		if ( empty( $this->main ) ) {
			return;
		}

		$tmp_path        = $this->main->get_tmp_path( 'attachments' );
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$random_string   = $this->main->get_random_string();

		if ( stristr( $server_software, 'nginx' ) && $this->settings->user_can_manage_settings() && ! get_option( 'wpo_wcpdf_hide_nginx_notice' ) && ! $random_string ) {
			ob_start();
			?>
			<div class="error">
				<img src="<?php echo $this->plugin_url() . '/assets/images/nginx.svg'; ?>" style="margin-top:10px;">
				<?php /* translators: directory path */ ?>
				<p><?php printf( __( 'The PDF files in %s are not currently protected due to your site running on <strong>NGINX</strong>.', 'woocommerce-pdf-invoices-packing-slips' ), '<strong>' . $tmp_path . '</strong>' ); ?></p>
				<p><?php _e( 'To protect them, you must click the button below.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_protect_pdf_directory', 'true' ), 'protect_pdf_directory_nonce' ) ); ?>"><?php _e( 'Generate random temporary folder name', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_nginx_notice', 'true' ), 'hide_nginx_notice_nonce' ) ); ?>"><?php _e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
			</div>
			<?php

			echo wp_kses_post( ob_get_clean() );
		}

		// protect PDF directory
		if ( isset( $_REQUEST['wpo_wcpdf_protect_pdf_directory'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'protect_pdf_directory_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_protect_pdf_directory' );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			} else {
				$this->main->generate_random_string();
				$old_path = $this->main->get_tmp_base( false );
				$new_path = $this->main->get_tmp_base();
				$this->main->copy_directory( $old_path, $new_path );
				// save option to hide nginx notice
				update_option( 'wpo_wcpdf_hide_nginx_notice', true );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			}
		}

		// save option to hide nginx notice
		if ( isset( $_REQUEST['wpo_wcpdf_hide_nginx_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hide_nginx_notice_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_nginx_notice' );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			} else {
				update_option( 'wpo_wcpdf_hide_nginx_notice', true );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			}
		}
	}

	/**
	 * Detect MailPoet.
	 * @return void
	 */
	public function mailpoet_mta_detected() {
		if( is_callable( array( '\\MailPoet\\Settings\\SettingsController', 'getInstance' ) ) ) {
			$settings = \MailPoet\Settings\SettingsController::getInstance();
			if( empty($settings) ) return;
			$send_transactional = $settings->get( 'send_transactional_emails', false );

			if( $send_transactional && ! get_option('wpo_wcpdf_hide_mailpoet_notice') ) {
				ob_start();
				?>
				<div class="error">
					<img src="<?php echo $this->plugin_url() . "/assets/images/mailpoet.svg"; ?>" style="margin-top:10px;">
					<p><?php _e( 'When sending emails with MailPoet 3 and the active sending method is <strong>MailPoet Sending Service</strong> or <strong>Your web host / web server</strong>, MailPoet does not include the <strong>PDF Invoices & Packing Slips for WooCommerce</strong> attachments in the emails.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p><?php _e( 'To fix this you should select <strong>The default WordPress sending method (default)</strong> on the <strong>Advanced tab</strong>.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=mailpoet-settings#/advanced' ) ); ?>"><?php _e( 'Change MailPoet sending method to WordPress (default)', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_mailpoet_notice', 'true' ), 'hide_mailpoet_notice_nonce' ) ); ?>"><?php _e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<?php
				echo wp_kses_post( ob_get_clean() );
			}
		}

		// save option to hide mailpoet notice
		if ( isset( $_REQUEST['wpo_wcpdf_hide_mailpoet_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hide_mailpoet_notice_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_mailpoet_notice' );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			} else {
				update_option( 'wpo_wcpdf_hide_mailpoet_notice', true );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			}
		}
	}

	/**
	 * RTL detected notice
	 *
	 * @return void
	 */
	public function rtl_detected() {
		if ( ! is_super_admin() ) {
			return;
		}

		if ( is_rtl() && ! get_option( 'wpo_wcpdf_hide_rtl_notice' ) ) {
			ob_start();
			?>
			<div class="notice notice-warning">
				<p><?php _e( 'PDF Invoices & Packing Slips for WooCommerce detected that your current site locale is right-to-left (RTL) which the current PDF engine does not support it. Please consider installing our mPDF extension that is compatible.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( 'https://github.com/wpovernight/woocommerce-pdf-ips-mpdf/releases/latest' ); ?>" target="_blank"><?php _e( 'Download mPDF extension', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_rtl_notice', 'true' ), 'hide_rtl_notice_nonce' ) ); ?>"><?php _e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
			</div>
			<?php
			echo wp_kses_post( ob_get_clean() );
		}

		// save option to hide notice
		if ( isset( $_REQUEST['wpo_wcpdf_hide_rtl_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hide_rtl_notice_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_rtl_notice' );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			} else {
				update_option( 'wpo_wcpdf_hide_rtl_notice', true );
				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			}
		}
	}

	/**
	 * Yearly reset action missing notice
	 *
	 * @return void
	 */
	public function yearly_reset_action_missing_notice(): void {
		if ( ! $this->settings->maybe_schedule_yearly_reset_numbers() ) {
			return;
		}

		if ( ! function_exists( 'as_get_scheduled_actions' ) ) {
			return;
		}

		$current_date   = new \DateTime();
		$end_of_year    = new \DateTime( 'last day of December' );
		$days_remaining = $current_date->diff( $end_of_year )->days;

		// Check if the current date is within the last 30 days of the year
		if ( $days_remaining <= 30 && ! $this->settings->yearly_reset_action_is_scheduled() ) {
			ob_start();
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( "The year-end is approaching, and we noticed that your PDF Invoices & Packing Slips for WooCommerce plugin doesn't have the scheduled action to reset invoice numbers annually, even though you've explicitly enabled this setting in the document options. Click the button below to schedule the action before the year ends.", 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_schedule_yearly_reset_action', 'true' ), 'schedule_yearly_reset_action_nonce' ) ); ?>"><?php esc_html_e( 'Schedule the action now', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
			</div>
			<?php
			echo wp_kses_post( ob_get_clean() );
		}

		// Schedule yearly reset action
		if ( isset( $_REQUEST['wpo_wcpdf_schedule_yearly_reset_action'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'schedule_yearly_reset_action_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_schedule_yearly_reset_action' );
			} else {
				$this->settings->schedule_yearly_reset_numbers();
				wcpdf_log_error( 'Yearly reset numbering system rescheduled!', 'info' );
			}

			wp_redirect( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=status' );
			exit;
		}
	}

	/**
	 * Get an array of all active plugins, including multisite
	 * @return array active plugin paths
	 */
	public function get_active_plugins() {
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

	public function deactivate_legacy_addons() {
		foreach ( $this->legacy_addons as $filename => $name ) {
			$legacy_addon = $this->plugin_is_activated( $filename );

			if ( ! empty( $legacy_addon ) ) {
				deactivate_plugins( $legacy_addon );
				$transient_name = $this->get_legacy_addon_transient_name( $filename );
				set_transient( $transient_name, 'yes', DAY_IN_SECONDS );
			}
		}
	}

	public function plugin_is_activated( $filename ) {
		$active_plugins = $this->get_active_plugins();
		$active_plugin  = '';

		foreach ( $active_plugins as $plugin ) {
			if ( false !== strpos( $plugin, $filename ) ) {
				$active_plugin = $plugin;
				break;
			}
		}

		return $active_plugin;
	}

	public function get_legacy_addon_transient_name( $filename ) {
		$filename_without_ext = basename( $filename, '.php' );
		$legacy_addon_name    = str_replace( '-', '_', $filename_without_ext );

		return "wpo_wcpdf_legacy_addon_{$legacy_addon_name}";
	}

	public function legacy_addon_notices() {
		foreach ( $this->legacy_addons as $filename => $name ) {
			$transient_name = $this->get_legacy_addon_transient_name( $filename );
			$query_arg      = "{$transient_name}_notice";

			if ( get_transient( $transient_name ) ) {
				ob_start();
				?>
				<div class="notice notice-warning">
					<p>
						<?php
							printf(
								/* translators: legacy addon name */
								__( 'While updating the PDF Invoices & Packing Slips for WooCommerce plugin we\'ve noticed our legacy %s add-on was active on your site. This functionality is now incorporated into the core plugin. We\'ve deactivated the add-on for you, and you are free to uninstall it.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<strong>' . esc_attr( $name ) . '</strong>'
							);
						?>
					</p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( $query_arg => true ) ), 'wcpdf_legacy_addon_notice' ) ); ?>"><?php _e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<?php
				echo wp_kses_post( ob_get_clean() );
			}

			// save option to hide legacy addon notice
			if ( isset( $_REQUEST[ $query_arg ] ) && isset( $_REQUEST['_wpnonce'] ) ) {
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wcpdf_legacy_addon_notice' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: ' . $query_arg );
				} else {
					delete_transient( $transient_name );
				}

				wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
				exit;
			}
		}
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

	/**
	 * Determine the site locale
	 */
	public function determine_locale() {
		if ( function_exists( 'determine_locale' ) ) { // WP5.0+
			$locale = determine_locale();
		} else {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		}

		return apply_filters( 'plugin_locale', $locale, 'woocommerce-pdf-invoices-packing-slips' );
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
