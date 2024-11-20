<?php
namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Endpoint' ) ) :

class Endpoint {

	public $action_suffix = '_wpo_wcpdf';
	public $events        = array( 'generate', 'printed' );
	public $actions;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		if ( $this->pretty_links_enabled() ) {
			add_action( 'init', array( $this, 'add_endpoint' ) );
			add_action( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'parse_request', array( $this, 'handle_document_requests' ) );
		}

		$this->actions = $this->get_actions();
	}

	public function get_actions() {
		$actions = [];
		foreach ( $this->events as $event ) {
			$actions[ $event ] = $event . $this->action_suffix;
		}
		return $actions;
	}

	public function pretty_links_enabled() {
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );

		if ( isset( $debug_settings['pretty_document_links'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function get_identifier() {
		return apply_filters( 'wpo_wcpdf_pretty_document_link_identifier', 'wcpdf' );
	}

	public function add_endpoint() {
		add_rewrite_rule(
			'^' . $this->get_identifier() . '/([^/]*)/([^/]*)/([^/]*)/([^/]*)?',
			'index.php?action=' . $this->actions['generate'] . '&document_type=$matches[1]&order_ids=$matches[2]&access_key=$matches[3]&output=$matches[4]',
			'top'
		);
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'action';
		$vars[] = 'document_type';
		$vars[] = 'order_ids';
		$vars[] = 'access_key';
		$vars[] = 'output';
		return $vars;
	}

	public function handle_document_requests() {
		global $wp;

		if ( ! empty( $wp->query_vars['action'] ) && $this->actions['generate'] == $wp->query_vars['action'] ) {
			if ( ! empty( $wp->query_vars['document_type'] ) && ! empty( $wp->query_vars['order_ids'] ) && ! empty( $wp->query_vars['access_key'] ) && ! empty( $wp->query_vars['output'] ) ) {
				$_REQUEST['action']        = $this->actions['generate'];
				$_REQUEST['document_type'] = sanitize_text_field( $wp->query_vars['document_type'] );
				$_REQUEST['order_ids']     = sanitize_text_field( $wp->query_vars['order_ids'] );
				$_REQUEST['access_key']    = sanitize_text_field( $wp->query_vars['access_key'] );
				$_REQUEST['output']        = sanitize_text_field( $wp->query_vars['output'] );

				do_action( 'wp_ajax_' . $this->actions['generate'] );
			}
		}
	}

	public function get_document_link( $order, $document_type, $additional_vars = array() ) {
		if ( empty( $order ) || empty( $document_type ) ) {
			return '';
		}

		$access_type = $this->get_document_link_access_type();

		switch ( $access_type ) {
			case 'logged_in':
			default:
				$access_key = is_user_logged_in() ? wp_create_nonce( $this->actions['generate'] ) : '';
				break;
			case 'guest': // 'guest' is hybrid, it can behave as 'logged_in' if the user is logged in, but if not, behaves as 'full'
				$access_key = ! is_user_logged_in() ? $order->get_order_key() : wp_create_nonce( $this->actions['generate'] );
				break;
			case 'full':
				$access_key = $order->get_order_key();
				break;
		}

		if ( empty( $access_key ) ) {
			return '';
		}

		if ( $this->pretty_links_enabled() ) {
			$output     = isset( $additional_vars['output'] ) ? esc_attr( $additional_vars['output'] ) : 'pdf';
			$parameters = array(
				$this->get_identifier(),
				$document_type,
				$order->get_id(),
				$access_key,
				$output
			);
			$document_link = trailingslashit( get_home_url() ) . implode( '/', $parameters );
		} else {
			$document_link = add_query_arg( array(
				'action'        => $this->actions['generate'],
				'document_type' => $document_type,
				'order_ids'     => $order->get_id(),
				'access_key'    => $access_key,
			), admin_url( 'admin-ajax.php' ) );
		}

		// handle additional query vars
		$additional_vars = apply_filters( 'wpo_wcpdf_document_link_additional_vars', $additional_vars, $order, $document_type );
		if ( ! empty( $additional_vars ) && is_array( $additional_vars ) ) {
			if ( isset( $additional_vars['output'] ) && $this->pretty_links_enabled() ) {
				unset( $additional_vars['output'] );
			}
			$document_link = add_query_arg( $additional_vars, $document_link );
		}

		return esc_url( $document_link );
	}

	/**
	 * Get mark/unmark document printed link
	 *
	 * @param string $event          Can be 'mark' or 'unmark'
	 * @param object $order
	 * @param string $document_type
	 * @param string $trigger
	 * @return void
	 */
	public function get_document_printed_link( $event, $order, $document_type, $trigger = 'manually' ) {
		if ( empty( $event ) || ! in_array( $event, [ 'mark', 'unmark' ] ) ) {
			return '';
		}

		if ( empty( $order ) || empty( $document_type ) || ! is_admin() ) {
			return '';
		}

		$printed_link = add_query_arg( array(
			'action'        => $this->actions['printed'],
			'event'         => $event,
			'document_type' => $document_type,
			'order_id'      => $order->get_id(),
			'trigger'       => $trigger,
			'security'      => wp_create_nonce( $this->actions['printed'] ),
		), admin_url( 'admin-ajax.php' ) );

		return esc_url( $printed_link );
	}

	/**
	 * Get document link access type from debug settings
	 *
	 * @return string
	 */
	public function get_document_link_access_type() {
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );
		$access_type    = isset( $debug_settings['document_link_access_type'] ) ? $debug_settings['document_link_access_type'] : 'logged_in';

		return apply_filters( 'wpo_wcpdf_document_link_access_type', $access_type, $this );
	}

	/**
	 * Get document denied frontend redirect URL
	 *
	 * @return string
	 */
	public function get_document_denied_frontend_redirect_url() {
		$redirect_url   = '';
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );

		if ( isset( $debug_settings['document_access_denied_redirect_page'] ) ) {
			switch ( $debug_settings['document_access_denied_redirect_page'] ) {
				case 'login_page':
					$redirect_url = wp_sanitize_redirect( wp_login_url() );
					break;
				case 'myaccount_page':
					$redirect_url = wp_sanitize_redirect( wc_get_page_permalink( 'myaccount' ) );
					break;
				case 'custom_page':
					if ( isset( $debug_settings['document_custom_redirect_page'] ) && ! empty( $debug_settings['document_custom_redirect_page'] ) ) {
						$redirect_url = wp_sanitize_redirect( $debug_settings['document_custom_redirect_page'] );
					}
					break;
				case 'blank_page':
				default:
					break;
			}
		}

		return apply_filters( 'wpo_wcpdf_document_denied_frontend_redirect_url', $redirect_url, $debug_settings, $this );
	}

}

endif; // class_exists
