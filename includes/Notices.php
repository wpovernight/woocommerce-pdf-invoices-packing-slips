<?php
namespace WPO\IPS;

use MailPoet\Settings\SettingsController as MailPoetSettingsController;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Notices {

	protected static ?self $_instance = null;

	/**
	 * Get the singleton instance of the Notices class.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'setup_notices' ) );
	}

	/**
	 * Setup admin notices.
	 *
	 * @return void
	 */
	public function setup_notices(): void {
		if ( ! is_admin() ) {
			return;
		}

		$is_settings_page = \WPO_WCPDF()->is_settings_page();
		$is_plugins_page  = \WPO_WCPDF()->is_plugins_page();

		self::maybe_add_admin_notice( array( $this, 'nginx_detected_notice' ), $is_settings_page );
		self::maybe_add_admin_notice( array( $this, 'yearly_reset_action_missing_notice' ), $is_settings_page );
		self::maybe_add_admin_notice( array( $this, 'unstable_option_announcement_notice' ), $is_settings_page );
		self::maybe_add_admin_notice( array( $this, 'new_unstable_version_available_notice' ), $is_settings_page );
		self::maybe_add_admin_notice( array( $this, 'review_plugin_notice' ), $is_settings_page );
		self::maybe_add_admin_notice( array( $this, 'install_wizard_notice' ), $is_settings_page );
		self::maybe_add_admin_notice( array( $this, 'display_admin_notice_for_shop_address' ), $is_settings_page );

		self::maybe_add_admin_notice( array( $this, 'legacy_addon_notices' ), $is_settings_page || $is_plugins_page );

		self::maybe_add_admin_notice( array( $this, 'mailpoet_mta_detected_notice' ) );
		self::maybe_add_admin_notice( array( $this, 'rtl_detected_notice' ) );
		self::maybe_add_admin_notice( array( $this, 'no_dir_notice' ) );
	}
	
	/**
	 * Register an admin notice callback only when the given condition is true.
	 *
	 * @param callable $callback
	 * @param bool     $condition
	 * @param int      $priority
	 * @return void
	 */
	public static function maybe_add_admin_notice( callable $callback, bool $condition = true, int $priority = 10 ): void {
		if ( ! $condition ) {
			return;
		}

		add_action( 'admin_notices', $callback, $priority );
	}
	
	/**
	 * WooCommerce notice.
	 *
	 * @return void
	 */
	public static function need_woocommerce_notice(): void {
		$error_message = sprintf(
			/* translators: 1. open anchor tag, 2. close anchor tag, 3. Woo version */
			esc_html__( 'PDF Invoices & Packing Slips for WooCommerce requires %1$sWooCommerce%2$s version %3$s or higher to be installed & activated!' , 'woocommerce-pdf-invoices-packing-slips' ),
			'<a href="http://wordpress.org/extend/plugins/woocommerce/">',
			'</a>',
			esc_attr( \WPO_WCPDF()->version_woo )
		);

		$message  = '<div class="error">';
		$message .= sprintf( '<p>%s</p>', $error_message );
		$message .= '</div>';

		echo wp_kses_post( $message );
	}
	
	/**
	 * PHP version requirement notice
	 * 
	 * @return void
	 */
	public static function required_php_version_notice(): void {
		$error_message = sprintf(
			/* translators: PHP version */
			esc_html__( 'PDF Invoices & Packing Slips for WooCommerce requires PHP %s or higher.', 'woocommerce-pdf-invoices-packing-slips' ),
			esc_attr( \WPO_WCPDF()->version_php )
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
	 * Display a notice informing the user that the server requirements are not met.
	 *
	 * @return void
	 */
	public static function display_server_requirement_notice(): void {
		$status_page_url = admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=status' );
		$dismiss_url     = wp_nonce_url( add_query_arg( 'wpo_dismiss_requirements_notice', true ), 'dismiss_requirements_notice' );
		$notice_message  = sprintf(
			/* translators: 1: Plugin name, 2: Open anchor tag, 3: Close anchor tag */
			__( 'Your server does not meet the requirements for %1$s. Please check the %2$sStatus page%3$s for more information.', 'woocommerce-pdf-invoices-packing-slips' ),
			'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>',
			'<a href="' . esc_url( $status_page_url ) . '">',
			'</a>'
		);

		?>

		<div class="notice notice-warning">
			<p><?php echo wp_kses_post( $notice_message ); ?></p>
			<p><a href="<?php echo esc_url( $dismiss_url ); ?>" class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
		</div>

		<?php
	}
	
	/**
	 * NGINX detected notice
	 *
	 * @return void
	 */
	public function nginx_detected_notice(): void {
		$main = \WPO_WCPDF()->main;
		
		if ( empty( $main ) ) {
			$main = Main::instance();
		}
		
		$settings = \WPO_WCPDF()->settings;
		
		if (  empty( $settings ) ) {
			$settings = Settings::instance();
		}

		$tmp_path        = $main->get_tmp_path( 'attachments' );
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$random_string   = $main->get_random_string();

		if ( stristr( $server_software, 'nginx' ) && $settings->user_can_manage_settings() && ! get_option( 'wpo_wcpdf_hide_nginx_notice' ) && ! $random_string ) {
			ob_start();
			?>
			<div class="error">
				<img src="<?php echo esc_url( \WPO_WCPDF()->plugin_url() . '/assets/images/nginx.svg' ); ?>" style="margin-top:10px;">
				<?php /* translators: directory path */ ?>
				<p><?php printf( esc_html__( 'The PDF files in %s are not currently protected due to your site running on <strong>NGINX</strong>.', 'woocommerce-pdf-invoices-packing-slips' ), '<strong>' . wpo_wcpdf_escape_url_path_or_base64( $tmp_path ) . '</strong>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<p><?php esc_html_e( 'To protect them, you must click the button below.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_protect_pdf_directory', 'true' ), 'protect_pdf_directory_nonce' ) ); ?>"><?php esc_html_e( 'Generate random temporary folder name', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_nginx_notice', 'true' ), 'hide_nginx_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
			</div>
			<?php

			echo wp_kses_post( ob_get_clean() );
		}

		// protect PDF directory
		if ( isset( $_REQUEST['wpo_wcpdf_protect_pdf_directory'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'protect_pdf_directory_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_protect_pdf_directory' );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			} else {
				$main->generate_random_string();
				$old_path = $main->get_tmp_base( false );
				$new_path = $main->get_tmp_base();
				$main->copy_directory( $old_path, $new_path );
				// save option to hide nginx notice
				update_option( 'wpo_wcpdf_hide_nginx_notice', true );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			}
		}

		// save option to hide nginx notice
		if ( isset( $_REQUEST['wpo_wcpdf_hide_nginx_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'hide_nginx_notice_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_nginx_notice' );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			} else {
				update_option( 'wpo_wcpdf_hide_nginx_notice', true );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
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
		$settings = \WPO_WCPDF()->settings;
		
		if (  empty( $settings ) ) {
			$settings = Settings::instance();
		}

		if ( ! $settings->maybe_schedule_yearly_reset_numbers() ) {
			return;
		}

		if ( ! function_exists( '\\as_get_scheduled_actions' ) ) {
			wcpdf_log_error( 'Action Scheduler function not available. Cannot verify if the yearly numbering reset action is scheduled.', 'critical' );
			return;
		}

		$current_date   = new \DateTime();
		$end_of_year    = new \DateTime( 'last day of December' );
		$days_remaining = $current_date->diff( $end_of_year )->days;

		// Check if the current date is within the last 30 days of the year
		if ( $days_remaining <= 30 && ! $settings->yearly_reset_action_is_scheduled() ) {
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
				$settings->schedule_yearly_reset_numbers();
				wcpdf_log_error( 'Yearly reset numbering system rescheduled!', 'info' );
			}

			wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=status' ) );
			exit;
		}
	}
	
	/**
	 * Show a one-time notice about the new "Check for unstable versions" option.
	 *
	 * @return void
	 */
	public function unstable_option_announcement_notice(): void {
		$settings = \WPO_WCPDF()->settings;
		
		if (  empty( $settings ) ) {
			$settings = Settings::instance();
		}
		
		$dismiss_option = 'wpo_wcpdf_dismiss_unstable_option_announcement';
		$dismiss_arg    = 'wpo_wcpdf_dismiss_unstable_option_announcement';
		$nonce_action   = 'wcpdf_dismiss_unstable_option_announcement';

		// Fallback if wc_string_to_bool() is unavailable
		$dismiss_value = get_option( $dismiss_option, 'no' );
		if ( function_exists( 'wc_string_to_bool' ) ) {
			$already_dismissed = wc_string_to_bool( (string) $dismiss_value );
		} else {
			// simple string check as a fallback
			$already_dismissed = ( 'yes' === (string) $dismiss_value );
		}

		// Bail if already dismissed or user cannot manage settings
		if ( $already_dismissed || ! $settings->user_can_manage_settings() ) {
			return;
		}

		// Handle dismissal
		if ( isset( $_GET[ $dismiss_arg ] ) && isset( $_GET['_wpnonce'] ) ) {
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), $nonce_action ) ) {
				update_option( $dismiss_option, 'yes' );
			} else {
				wcpdf_log_error( 'Invalid nonce while dismissing unstable version feature notice.' );
			}

			wp_safe_redirect( remove_query_arg( array( $dismiss_arg, '_wpnonce' ) ) );
			exit;
		}

		// Build dismiss URL
		$dismiss_url = wp_nonce_url(
			add_query_arg( $dismiss_arg, '1' ),
			$nonce_action
		);
		?>
		<div class="notice notice-info">
			<p>
				<?php
					printf(
						/* translators: %s: Plugin name */
						esc_html__( 'We\'ve added a new option to %s that lets you check for beta and pre-release versions.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<strong>' . esc_html__( 'PDF Invoices & Packing Slips for WooCommerce', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
					);
				?>
			</p>
			<p>
				<?php esc_html_e( 'If you\'d like to help improve the plugin by testing early releases on a staging site, you can enable this feature from the advanced settings.', 'woocommerce-pdf-invoices-packing-slips' ); ?>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug#check_unstable_versions' ) ); ?>">
					<?php esc_html_e( 'Go to settings', 'woocommerce-pdf-invoices-packing-slips' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( $dismiss_url ); ?>">
					<?php esc_html_e( 'Dismiss this notice', 'woocommerce-pdf-invoices-packing-slips' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
	
	/**
	 * Display a notice when a new unstable version is available.
	 *
	 * @return void
	 */
	public function new_unstable_version_available_notice(): void {
		$settings = \WPO_WCPDF()->settings;
		
		if (  empty( $settings ) ) {
			$settings = Settings::instance();
		}
		
		$debug_settings         = $settings->debug_settings;
		$check_unstable_enabled = isset( $debug_settings['check_unstable_versions'] );
		$unstable_state         = get_option( 'wpo_wcpdf_unstable_version_state', array() );
		$current_tag            = isset( $unstable_state['tag'] ) ? $unstable_state['tag'] : '';
		$is_dismissed           = isset( $unstable_state['dismissed'] ) ? $unstable_state['dismissed'] : false;
		$hide_version_arg       = 'wpo_wcpdf_hide_unstable_version';

		// Don't show the notice if disabled or dismissed
		if ( ! $check_unstable_enabled || empty( $current_tag ) || $is_dismissed ) {
			return;
		}

		// Handle dismissal
		if ( isset( $_GET[ $hide_version_arg ], $_GET['_wpnonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );

			if ( wp_verify_nonce( $nonce, 'wcpdf_hide_unstable_version' ) ) {
				update_option( 'wpo_wcpdf_unstable_version_state', array(
					'tag'       => $current_tag,
					'dismissed' => true,
				) );
			} else {
				wcpdf_log_error( 'Invalid nonce while hiding unstable version notice.' );
			}

			$redirect_url = remove_query_arg( array( $hide_version_arg, '_wpnonce' ), wp_get_referer() );

			if ( ! $redirect_url ) {
				$redirect_url = admin_url(); // Fallback
			}

			wp_safe_redirect( $redirect_url );
			exit;
		}

		$hide_url = wp_nonce_url(
			add_query_arg( $hide_version_arg, 1, wp_get_referer() ?: admin_url() ),
			'wcpdf_hide_unstable_version'
		);

		// Display the notice
		?>
		<div class="notice notice-info">
			<p>
				<?php
					printf(
						/* translators: 1. new unstable version, 2. plugin name */
						esc_html__( 'A new unstable version (%1$s) of %2$s is available.', 'woocommerce-pdf-invoices-packing-slips' ),
						esc_html( $current_tag ),
						'<strong>' . esc_html__( 'PDF Invoices & Packing Slips for WooCommerce', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
					);
				?>
			</p>
			<p>
				<span class="dashicons dashicons-download"></span>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=status' ) ); ?>">
					<?php esc_html_e( 'Download from the status page', 'woocommerce-pdf-invoices-packing-slips' ); ?>
				</a>
			</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $hide_url ); ?>">
					<?php esc_html_e( 'Hide this version', 'woocommerce-pdf-invoices-packing-slips' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
	
	/**
	 * Show admin notices for legacy add-ons that were deactivated on plugin activation.
	 *
	 * @return void
	 */
	public function legacy_addon_notices(): void {
		foreach ( \WPO_WCPDF()->legacy_addons as $filename => $name ) {
			$transient_name = \WPO_WCPDF()->get_legacy_addon_transient_name( $filename );
			$query_arg      = "{$transient_name}_notice";

			if ( get_transient( $transient_name ) ) {
				ob_start();
				?>
				<div class="notice notice-warning">
					<p>
						<?php
							printf(
								/* translators: legacy addon name */
								esc_html__( 'While updating the PDF Invoices & Packing Slips for WooCommerce plugin we\'ve noticed our legacy %s add-on was active on your site. This functionality is now incorporated into the core plugin. We\'ve deactivated the add-on for you, and you are free to uninstall it.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<strong>' . esc_attr( $name ) . '</strong>'
							);
						?>
					</p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( $query_arg => true ) ), 'wcpdf_legacy_addon_notice' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<?php
				echo wp_kses_post( ob_get_clean() );
			}

			// save option to hide legacy addon notice
			if ( isset( $_REQUEST[ $query_arg ] ) && isset( $_REQUEST['_wpnonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'wcpdf_legacy_addon_notice' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: ' . $query_arg );
				} else {
					delete_transient( $transient_name );
				}

				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			}
		}
	}
	
	/**
	 * MailPoet MTA detected notice
	 * 
	 * @return void
	 */
	public function mailpoet_mta_detected_notice(): void {
		if ( is_callable( array( MailPoetSettingsController::class, 'getInstance' ) ) ) {
			$settings = MailPoetSettingsController::getInstance();
			
			if ( empty( $settings ) || ! method_exists( $settings, 'get' ) ) {
				return;
			}
			
			$send_transactional = $settings->get( 'send_transactional_emails', false );

			if ( $send_transactional && ! get_option( 'wpo_wcpdf_hide_mailpoet_notice', false ) ) {
				ob_start();
				?>
					<div class="error">
						<img src="<?php echo esc_url( \WPO_WCPDF()->plugin_url() . '/assets/images/mailpoet.svg' ); ?>" style="margin-top:10px;">
						<p>
							<?php
								printf(
									/* translators: 1: MailPoet version, 2: MailPoet sending method, 3: MailPoet sending method, 4: plugin name. */
									esc_html__(
										'When sending emails with %1$s and the active sending method is %2$s or %3$s, MailPoet does not include the %4$s attachments in the emails.',
										'woocommerce-pdf-invoices-packing-slips'
									),
									'MailPoet 3',
									'<strong>MailPoet Sending Service</strong>',
									'<strong>Your web host / web server</strong>',
									'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>'
								);
							?>
						</p>
						<p>
							<?php
								printf(
									/* translators: 1: The default WordPress sending method (default), 2: Advanced tab. */
									esc_html__( 'To fix this you should select %1$s on the %2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
									'<strong>The default WordPress sending method (default)</strong>',
									'<strong>' . esc_html__( 'Advanced tab', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
								);
							?>
						</p>
						<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=mailpoet-settings#/advanced' ) ); ?>"><?php esc_html_e( 'Change MailPoet sending method to WordPress (default)', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
						<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_mailpoet_notice', 'true' ), 'hide_mailpoet_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
					</div>
				<?php
				echo wp_kses_post( ob_get_clean() );
			}
		}

		// save option to hide mailpoet notice
		if ( isset( $_REQUEST['wpo_wcpdf_hide_mailpoet_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'hide_mailpoet_notice_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_mailpoet_notice' );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			} else {
				update_option( 'wpo_wcpdf_hide_mailpoet_notice', true );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			}
		}
	}
	
	/**
	 * RTL detected notice
	 *
	 * @return void
	 */
	public function rtl_detected_notice(): void {
		if ( ! is_super_admin() ) {
			return;
		}

		if ( is_rtl() && ! get_option( 'wpo_wcpdf_hide_rtl_notice', false ) ) {
			ob_start();
			?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'PDF Invoices & Packing Slips for WooCommerce detected that your current site locale is right-to-left (RTL) which the current PDF engine does not support it. Please consider installing our mPDF extension that is compatible.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p><a class="button" href="<?php echo esc_url( 'https://github.com/wpovernight/woocommerce-pdf-ips-mpdf/releases/latest' ); ?>" target="_blank"><?php esc_html_e( 'Download mPDF extension', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_rtl_notice', 'true' ), 'hide_rtl_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
			<?php
			echo wp_kses_post( ob_get_clean() );
		}

		// save option to hide notice
		if ( isset( $_REQUEST['wpo_wcpdf_hide_rtl_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'hide_rtl_notice_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_rtl_notice' );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			} else {
				update_option( 'wpo_wcpdf_hide_rtl_notice', true );
				wp_safe_redirect( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) );
				exit;
			}
		}
	}
	
	/**
	 * Review plugin notice
	 *
	 * @return void
	 */
	public function review_plugin_notice(): void {
		if ( get_option( 'wpo_wcpdf_review_notice_dismissed' ) !== false ) {
			return;
		}
		
		if ( isset( $_REQUEST['wpo_wcpdf_dismiss_review'] ) && isset( $_REQUEST['_wpdismissnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpdismissnonce'] ) ), 'dismiss_review_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_dismiss_review' );
				return;
			} else {
				update_option( 'wpo_wcpdf_review_notice_dismissed', true );
				return;
			}
		}

		// get invoice count to determine whether notice should be shown
		$invoice_count = \wpo_ips_get_invoice_count();
		
		if ( $invoice_count > 100 ) {
			// keep track of how many days this notice is show so we can remove it after 7 days
			$notice_shown_on = get_option( 'wpo_wcpdf_review_notice_shown', array() );
			$today           = gmdate( 'Y-m-d' );
			
			if ( ! in_array( $today, $notice_shown_on ) ) {
				$notice_shown_on[] = $today;
				update_option( 'wpo_wcpdf_review_notice_shown', $notice_shown_on );
			}
			// count number of days review is shown, dismiss forever if shown more than 7
			if ( count( $notice_shown_on ) > 7 ) {
				update_option( 'wpo_wcpdf_review_notice_dismissed', true );
				return;
			}

			$rounded_count = (int) substr( (string) $invoice_count, 0, 1 ) * pow( 10, strlen( (string) $invoice_count ) - 1);
			?>
			<div class="notice notice-info is-dismissible wpo-wcpdf-review-notice">
				<h3>
					<?php
						printf(
							/* translators: rounded count */
							esc_html__( 'Wow, you have created more than %d invoices with our plugin!', 'woocommerce-pdf-invoices-packing-slips' ),
							esc_html( $rounded_count )
						);
					?>
				</h3>
				<p><?php esc_html_e( 'It would mean a lot to us if you would quickly give our plugin a 5-star rating. Help us spread the word and boost our motivation!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<ul>
					<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/?rate=5#new-post" class="button"><?php esc_html_e( 'Yes you deserve it!', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></a></li>
					<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_review', true ), 'dismiss_review_nonce', '_wpdismissnonce' ) ); ?>" class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?> / <?php esc_html_e( 'Already did!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
					<li><a href="mailto:support@wpovernight.com?Subject=Here%20is%20how%20I%20think%20you%20can%20do%20better"><?php esc_html_e( 'Actually, I have a complaint...', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
				</ul>
			</div>
			<script type="text/javascript">
				jQuery( function( $ ) {
					$( '.wpo-wcpdf-review-notice' ).on( 'click', '.notice-dismiss', function( event ) {
						event.preventDefault();
						window.location.href = $( '.wpo-wcpdf-dismiss' ).attr('href');
					} );
				} );
			</script>
			<!-- Hide extensions ad if this is shown -->
			<style>.wcpdf-extensions-ad { display: none; }</style>
			<?php
		}
	}
	
	/**
	 * Install wizard notice
	 *
	 * @return void
	 */
	public function install_wizard_notice(): void {
		if ( get_option( 'wpo_wcpdf_install_notice_dismissed' ) !== false ) {
			return;
		}
		
		if ( isset( $_REQUEST['wpo_wcpdf_dismiss_install'] ) && isset( $_REQUEST['_wpdismissnonce'] ) ) {
			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpdismissnonce'] ) ), 'dismiss_install_nonce' ) ) {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_dismiss_install' );
				return;
			} else {
				update_option( 'wpo_wcpdf_install_notice_dismissed', true );
				return;
			}
		}

		if ( get_transient( 'wpo_wcpdf_new_install' ) !== false ) {
			?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-install-notice">
					<p><strong><?php esc_html_e( 'New to PDF Invoices & Packing Slips for WooCommerce?', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong> &#8211; <?php esc_html_e( 'Jumpstart the plugin by following our wizard!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo-wcpdf-setup' ) ); ?>" class="button-primary"><?php esc_html_e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a> <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_install', true ), 'dismiss_install_nonce', '_wpdismissnonce' ) ); ?>" class="wpo-wcpdf-dismiss-wizard"><?php esc_html_e( 'I am the wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<script type="text/javascript">
					jQuery( function( $ ) {
						$( '.wpo-wcpdf-install-notice' ).on( 'click', '.notice-dismiss', function( event ) {
							event.preventDefault();
							window.location.href = $( '.wpo-wcpdf-dismiss-wizard' ).attr( 'href' );
						} );
					} );
				</script>
			<?php
		}
	}
	
	/**
	 * No directory or not writable notice
	 *
	 * @return void
	 */
	public function no_dir_notice(): void {
		$main = \WPO_WCPDF()->main;
		
		if ( empty( $main ) ) {
			$main = Main::instance();
		}
	
		// if all folders exist and are writable delete the option
		if ( is_callable( array( $main, 'tmp_folders_exist_and_writable' ) ) && $main->tmp_folders_exist_and_writable() ) {
			delete_option( 'wpo_wcpdf_no_dir_error' );
			return;
		}
		
		$path = get_option( 'wpo_wcpdf_no_dir_error' );
		
		if ( $path ) {
			ob_start();
			?>
				<div class="error">
					<p>
						<?php
							echo wp_kses_post( sprintf(
								/* translators: 1. plugin name, 2. directory path */
								__( 'The %1$s directory %2$s couldn\'t be created or is not writable!', 'woocommerce-pdf-invoices-packing-slips' ),
								'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>',
								'<code>' . \wpo_wcpdf_escape_url_path_or_base64( $path ) . '</code>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							) );
						?>
					</p>
					<p><?php esc_html_e( 'Please check your directories write permissions or contact your hosting service provider.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_no_dir_notice', 'true' ), 'hide_no_dir_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
			<?php
			echo wp_kses_post( ob_get_clean() );

			// save option to hide notice
			if ( isset( $_REQUEST['wpo_wcpdf_hide_no_dir_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
				// validate nonce
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'hide_no_dir_notice_nonce' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_no_dir_notice' );
					wp_safe_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
					exit;
				} else {
					delete_option( 'wpo_wcpdf_no_dir_error' );
					wp_safe_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
					exit;
				}
			}
		}
	}
	
	/**
	 * Display admin notice for incomplete shop address.
	 *
	 * @return void
	 */
	public function display_admin_notice_for_shop_address(): void {
		// Return if the notice has been dismissed.
		if ( get_option( 'wpo_wcpdf_dismiss_shop_address_notice', false ) ) {
			return;
		}

		// Handle dismissal action.
		if ( isset( $_GET['wpo_dismiss_shop_address_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'dismiss_shop_address_notice' ) ) {
				update_option( 'wpo_wcpdf_dismiss_shop_address_notice', true );
				wp_safe_redirect( remove_query_arg( array( 'wpo_dismiss_shop_address_notice', '_wpnonce' ) ) );
				exit;
			} else {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_dismiss_requirements_notice' );
				return;
			}
		}

		$settings_instance = Settings::instance();

		if ( is_callable( array( $settings_instance, 'maybe_shop_address_is_incomplete' ) ) && $settings_instance->maybe_shop_address_is_incomplete() ) {
			$general_page_url = admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=general' );
			$dismiss_url      = wp_nonce_url( add_query_arg( 'wpo_dismiss_shop_address_notice', true ), 'dismiss_shop_address_notice' );
			$notice_message   = sprintf(
				/* translators: 1: Plugin name, 2: Open anchor tag, 3: Close anchor tag */
				__( '%1$s: Your shop address is incomplete. Please fill in the missing fields in the %2$sGeneral settings%3$s.', 'woocommerce-pdf-invoices-packing-slips' ),
				'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>',
				'<a href="' . esc_url( $general_page_url ) . '">',
				'</a>'
			);
			?>
				<div class="notice notice-warning">
					<p><?php echo wp_kses_post( $notice_message ); ?></p>
					<p><a href="<?php echo esc_url( $dismiss_url ); ?>"
						class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					</p>
				</div>
			<?php
		}
	}
	
}