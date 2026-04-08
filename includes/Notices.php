<?php
namespace WPO\IPS;

use MailPoet\Settings\SettingsController as MailPoetSettingsController;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Notices {

	protected ?\WPO_WCPDF $plugin     = null;
	protected ?Settings $settings     = null;
	protected ?Main $main             = null;
	
	protected static ?self $_instance = null;

	/**
	 * Get the singleton instance of the class.
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
	protected function __construct() {
		$this->plugin   = \WPO_WCPDF();
		$this->settings = $this->plugin->get_instance( 'settings' );
		$this->main     = $this->plugin->get_instance( 'main' );
		
		add_action( 'admin_init', array( $this, 'handle_notice_actions' ) );
		add_action( 'admin_init', array( $this, 'setup_notices' ) );
	}

	/**
	 * Handle actions triggered by admin notices.
	 *
	 * @return void
	 */
	public function handle_notice_actions(): void {
		self::handle_notice_action(
			'wpo_wcpdf_hide_mailpoet_notice',
			'hide_mailpoet_notice_nonce',
			function (): void {
				update_option( 'wpo_wcpdf_hide_mailpoet_notice', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_hide_rtl_notice',
			'hide_rtl_notice_nonce',
			function (): void {
				update_option( 'wpo_wcpdf_hide_rtl_notice', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_hide_no_dir_notice',
			'hide_no_dir_notice_nonce',
			function (): void {
				delete_option( 'wpo_wcpdf_no_dir_error' );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_protect_pdf_directory',
			'protect_pdf_directory_nonce',
			function (): void {
				$this->main->regenerate_random_string( false );
				update_option( 'wpo_wcpdf_hide_nginx_notice', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_hide_nginx_notice',
			'hide_nginx_notice_nonce',
			function (): void {
				update_option( 'wpo_wcpdf_hide_nginx_notice', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_schedule_yearly_reset_action',
			'schedule_yearly_reset_action_nonce',
			function (): void {
				$this->settings->schedule_yearly_reset_numbers();
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=status' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_dismiss_unstable_option_announcement',
			'wcpdf_dismiss_unstable_option_announcement',
			function (): void {
				update_option( 'wpo_wcpdf_dismiss_unstable_option_announcement', 'yes' );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_hide_unstable_version',
			'wcpdf_hide_unstable_version',
			function (): void {
				$unstable_state = get_option( 'wpo_wcpdf_unstable_version_state', array() );
				$unstable_state['dismissed'] = true;

				update_option( 'wpo_wcpdf_unstable_version_state', $unstable_state );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		foreach ( $this->plugin->legacy_addons as $filename => $name ) {
			$transient_name = $this->plugin->get_legacy_addon_transient_name( $filename );
			$query_arg      = "{$transient_name}_notice";

			self::handle_notice_action(
				$query_arg,
				'wcpdf_legacy_addon_notice',
				function () use ( $transient_name ): void {
					delete_transient( $transient_name );
				},
				admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
			);
		}
		
		self::handle_notice_action(
			'wpo_wcpdf_dismiss_review',
			'dismiss_review_nonce',
			function (): void {
				update_option( 'wpo_wcpdf_review_notice_dismissed', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_wcpdf_dismiss_install',
			'dismiss_install_nonce',
			function (): void {
				update_option( 'wpo_wcpdf_install_notice_dismissed', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
		
		self::handle_notice_action(
			'wpo_dismiss_shop_address_notice',
			'dismiss_shop_address_notice',
			function (): void {
				update_option( 'wpo_wcpdf_dismiss_shop_address_notice', true );
			},
			admin_url( 'admin.php?page=wpo_wcpdf_options_page' )
		);
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

		$is_settings_page = $this->plugin->is_settings_page();
		$is_plugins_page  = $this->plugin->is_plugins_page();

		$notices = array(
			array( array( $this, 'nginx_detected_notice' ), $is_settings_page ),
			array( array( $this, 'yearly_reset_action_missing_notice' ), $is_settings_page ),
			array( array( $this, 'unstable_option_announcement_notice' ), $is_settings_page ),
			array( array( $this, 'new_unstable_version_available_notice' ), $is_settings_page ),
			array( array( $this, 'review_plugin_notice' ), $is_settings_page ),
			array( array( $this, 'install_wizard_notice' ), $is_settings_page ),
			array( array( $this, 'display_admin_notice_for_shop_address' ), $is_settings_page ),
			array( array( $this, 'legacy_addon_notices' ), $is_settings_page || $is_plugins_page ),
			array( array( $this, 'mailpoet_mta_detected_notice' ), true ),
			array( array( $this, 'rtl_detected_notice' ), true ),
			array( array( $this, 'no_dir_notice' ), true ),
		);

		foreach ( $notices as $notice ) {
			self::maybe_add_admin_notice( $notice[0], $notice[1] );
		}
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
	 * Helper method to handle admin notice actions with nonce verification and redirection.
	 *
	 * @param string $query_arg
	 * @param string $nonce_action
	 * @param callable $callback
	 * @param string|null $redirect_url
	 * @return void
	 */
	public static function handle_notice_action( string $query_arg, string $nonce_action, callable $callback, ?string $redirect_url = null ): void {
		if ( ! isset( $_GET[ $query_arg ], $_GET['_wpnonce'] ) ) {
			return;
		}

		$redirect_url = $redirect_url ?: admin_url( 'admin.php?page=wpo_wcpdf_options_page' );

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), $nonce_action ) ) {
			wcpdf_log_error( 'Invalid nonce for action: ' . $query_arg );
			wp_safe_redirect( $redirect_url );
			exit;
		}

		$callback();

		wp_safe_redirect( $redirect_url );
		exit;
	}
	
	/**
	 * WooCommerce notice.
	 * 
	 * - Called from WPO_WCPDF::dependencies_are_ready()
	 *
	 * @return void
	 */
	public static function need_woocommerce_notice(): void {
		$error_message = sprintf(
			/* translators: 1. open anchor tag, 2. close anchor tag, 3. Woo version */
			__( 'PDF Invoices & Packing Slips for WooCommerce requires %1$sWooCommerce%2$s version %3$s or higher to be installed & activated!' , 'woocommerce-pdf-invoices-packing-slips' ),
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
	 * - Called from WPO_WCPDF::dependencies_are_ready()
	 * 
	 * @return void
	 */
	public static function required_php_version_notice(): void {
		$error_message = sprintf(
			/* translators: PHP version */
			__( 'PDF Invoices & Packing Slips for WooCommerce requires PHP %s or higher.', 'woocommerce-pdf-invoices-packing-slips' ),
			esc_attr( \WPO_WCPDF()->version_php )
		);

		$php_message = sprintf(
			/* translators: <a> tags */
			__( 'We strongly recommend to %1$supdate your PHP version%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
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
	 * - Called from \WPO\IPS\Settings\SettingsDebug::handle_server_requirement_notice()
	 * - Handling of the dismissal action is done in \WPO\IPS\Settings\SettingsDebug::handle_server_requirement_notice() via self::handle_notice_action()
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
		$tmp_path        = $this->main->get_tmp_path( 'attachments' );
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$random_string   = $this->main->get_random_string();

		if ( stristr( $server_software, 'nginx' ) && $this->settings->user_can_manage_settings() && ! get_option( 'wpo_wcpdf_hide_nginx_notice' ) && ! $random_string ) {
			ob_start();
			?>
			<div class="error">
				<img src="<?php echo esc_url( $this->plugin->plugin_url() . '/assets/images/nginx.svg' ); ?>" style="margin-top:10px;">
				<?php /* translators: directory path */ ?>
				<p><?php echo wp_kses_post( sprintf(
					/* translators: 1. directory path, 2. NGINX */
					__( 'The PDF files in %1$s are not currently protected due to your site running on %2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
					'<strong>' . \wpo_wcpdf_escape_url_path_or_base64( $tmp_path ) . '</strong>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<strong>NGINX</strong>'
				) ); ?></p>
				<p><?php esc_html_e( 'To protect them, you must click the button below.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_protect_pdf_directory', 'true' ), 'protect_pdf_directory_nonce' ) ); ?>"><?php esc_html_e( 'Generate random temporary folder name', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_nginx_notice', 'true' ), 'hide_nginx_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
			</div>
			<?php

			echo wp_kses_post( ob_get_clean() );
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

		if ( ! function_exists( '\\as_get_scheduled_actions' ) ) {
			wcpdf_log_error( 'Action Scheduler function not available. Cannot verify if the yearly numbering reset action is scheduled.', 'critical' );
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
	}
	
	/**
	 * Show a one-time notice about the new "Check for unstable versions" option.
	 *
	 * @return void
	 */
	public function unstable_option_announcement_notice(): void {
		// Fallback if wc_string_to_bool() is unavailable
		$dismiss_value = get_option( 'wpo_wcpdf_dismiss_unstable_option_announcement', 'no' );
		
		if ( function_exists( 'wc_string_to_bool' ) ) {
			$already_dismissed = wc_string_to_bool( (string) $dismiss_value );
		} else {
			// simple string check as a fallback
			$already_dismissed = ( 'yes' === (string) $dismiss_value );
		}

		// Bail if already dismissed or user cannot manage settings
		if ( $already_dismissed || ! $this->settings->user_can_manage_settings() ) {
			return;
		}

		// Build dismiss URL
		$dismiss_url = wp_nonce_url(
			add_query_arg( 'wpo_wcpdf_dismiss_unstable_option_announcement', '1' ),
			'wcpdf_dismiss_unstable_option_announcement'
		);
		?>
		<div class="notice notice-info">
			<p>
				<?php
					echo wp_kses_post( sprintf(
						/* translators: %s: Plugin name */
						__( 'We\'ve added a new option to %s that lets you check for beta and pre-release versions.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>'
					) );
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
		$debug_settings         = $this->settings->debug_settings;
		$check_unstable_enabled = isset( $debug_settings['check_unstable_versions'] );
		$unstable_state         = get_option( 'wpo_wcpdf_unstable_version_state', array() );
		$current_tag            = isset( $unstable_state['tag'] ) ? $unstable_state['tag'] : '';
		$is_dismissed           = isset( $unstable_state['dismissed'] ) ? $unstable_state['dismissed'] : false;

		// Don't show the notice if disabled or dismissed
		if ( ! $check_unstable_enabled || empty( $current_tag ) || $is_dismissed ) {
			return;
		}

		$hide_url = wp_nonce_url(
			add_query_arg( 'wpo_wcpdf_hide_unstable_version', 1 ),
			'wcpdf_hide_unstable_version'
		);

		// Display the notice
		?>
		<div class="notice notice-info">
			<p>
				<?php
					echo wp_kses_post( sprintf(
						/* translators: 1. new unstable version, 2. plugin name */
						__( 'A new unstable version (%1$s) of %2$s is available.', 'woocommerce-pdf-invoices-packing-slips' ),
						esc_html( $current_tag ),
						'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>'
					) );
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
		foreach ( $this->plugin->legacy_addons as $filename => $name ) {
			$transient_name = $this->plugin->get_legacy_addon_transient_name( $filename );
			$query_arg      = "{$transient_name}_notice";

			if ( get_transient( $transient_name ) ) {
				ob_start();
				?>
				<div class="notice notice-warning">
					<p>
						<?php
							echo wp_kses_post( sprintf(
								/* translators: legacy addon name */
								__( 'While updating the PDF Invoices & Packing Slips for WooCommerce plugin we\'ve noticed our legacy %s add-on was active on your site. This functionality is now incorporated into the core plugin. We\'ve deactivated the add-on for you, and you are free to uninstall it.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<strong>' . esc_attr( $name ) . '</strong>'
							) );
						?>
					</p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( $query_arg => true ) ), 'wcpdf_legacy_addon_notice' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<?php
				echo wp_kses_post( ob_get_clean() );
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
						<img src="<?php echo esc_url( $this->plugin->plugin_url() . '/assets/images/mailpoet.svg' ); ?>" style="margin-top:10px;">
						<p>
							<?php
								echo wp_kses_post( sprintf(
									/* translators: 1: MailPoet version, 2: MailPoet sending method, 3: MailPoet sending method, 4: plugin name. */
									__( 'When sending emails with %1$s and the active sending method is %2$s or %3$s, MailPoet does not include the %4$s attachments in the emails.', 'woocommerce-pdf-invoices-packing-slips' ),
									'MailPoet 3',
									'<strong>MailPoet Sending Service</strong>',
									'<strong>Your web host / web server</strong>',
									'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>'
								) );
							?>
						</p>
						<p>
							<?php
								echo wp_kses_post( sprintf(
									/* translators: 1: The default WordPress sending method (default), 2: Advanced tab. */
									__( 'To fix this you should select %1$s on the %2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
									'<strong>The default WordPress sending method (default)</strong>',
									'<strong>' . __( 'Advanced tab', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
								) );
							?>
						</p>
						<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=mailpoet-settings#/advanced' ) ); ?>"><?php esc_html_e( 'Change MailPoet sending method to WordPress (default)', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
						<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_mailpoet_notice', 'true' ), 'hide_mailpoet_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
					</div>
				<?php
				echo wp_kses_post( ob_get_clean() );
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
					<p><?php echo wp_kses_post( sprintf(
						/* translators: 1. plugin name, 2. compatible PDF engine */
						__( '%1$s detected that your current site locale is right-to-left (RTL) which the current PDF engine does not support it. Please consider installing our %2$s extension that is compatible.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>',
						'<strong>mPDF</strong>'
					) ); ?></p>
					<p><a class="button" href="<?php echo esc_url( 'https://github.com/wpovernight/woocommerce-pdf-ips-mpdf/releases/latest' ); ?>" target="_blank"><?php echo wp_kses_post( __( 'Download mPDF extension', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></a></p>
					<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_rtl_notice', 'true' ), 'hide_rtl_notice_nonce' ) ); ?>"><?php echo wp_kses_post( __( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></a></p>
				</div>
			<?php
			echo wp_kses_post( ob_get_clean() );
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
					<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_review', true ), 'dismiss_review_nonce' ) ); ?>" class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?> / <?php esc_html_e( 'Already did!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
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

		if ( get_transient( 'wpo_wcpdf_new_install' ) !== false ) {
			?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-install-notice">
					<p><strong><?php echo wp_kses_post( sprintf(
						/* translators: plugin name */
						__( 'New to %s?', 'woocommerce-pdf-invoices-packing-slips' ),
						'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>'
					) ); ?></strong> &#8211; <?php esc_html_e( 'Jumpstart the plugin by following our wizard!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo-wcpdf-setup' ) ); ?>" class="button-primary"><?php esc_html_e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a> <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_install', true ), 'dismiss_install_nonce' ) ); ?>" class="wpo-wcpdf-dismiss-wizard"><?php esc_html_e( 'I am the wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
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
		// if all folders exist and are writable delete the option
		if ( is_callable( array( $this->main, 'tmp_folders_exist_and_writable' ) ) && $this->main->tmp_folders_exist_and_writable() ) {
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

		if ( is_callable( array( $this->settings, 'maybe_shop_address_is_incomplete' ) ) && $this->settings->maybe_shop_address_is_incomplete() ) {
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
