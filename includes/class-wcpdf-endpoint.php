<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Endpoint' ) ) :

class Endpoint {

	public $action_suffix = '_wpo_wcpdf';
	public $events        = [ 'generate', 'printed' ];
	public $actions;

	public function __construct() {
		if ( $this->is_enabled() ) {
			add_action( 'init', array( $this, 'add_endpoint' ) );
			add_action( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'parse_request', array( $this, 'handle_document_requests' ) );
		}
		
		$this->actions = $this->get_actions();
	}
	
	public function get_actions() {
		$actions = [];
		foreach ( $this->events as $event ) {
			$actions[$event] = $event.$this->action_suffix;
		}
		return $actions;
	}

	public function is_enabled() {
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
			'^'.$this->get_identifier().'/([^/]*)/([^/]*)/([^/]*)?',
			'index.php?action='.$this->actions['generate'].'&document_type=$matches[1]&order_ids=$matches[2]&access_key=$matches[3]',
			'top'
		);
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'action';
		$vars[] = 'document_type';
		$vars[] = 'order_ids';
		$vars[] = 'access_key';
		return $vars;
	}

	public function handle_document_requests() {
		global $wp;

		if ( ! empty( $wp->query_vars['action'] ) && $this->actions['generate'] == $wp->query_vars['action'] ) {
			if ( ! empty( $wp->query_vars['document_type'] ) && ! empty( $wp->query_vars['order_ids'] ) && ! empty( $wp->query_vars['access_key'] ) ) {
				$_REQUEST['action']        = $this->actions['generate'];
				$_REQUEST['document_type'] = sanitize_text_field( $wp->query_vars['document_type'] );
				$_REQUEST['order_ids']     = sanitize_text_field( $wp->query_vars['order_ids'] );
				$_REQUEST['access_key']    = sanitize_text_field( $wp->query_vars['access_key'] );
				
				do_action( 'wp_ajax_' . $this->actions['generate'] );
			}
		}
	}

	public function get_document_link( $order, $document_type, $additional_vars = array() ) {
		if ( empty( $order ) || empty( $document_type ) ) {
			return '';
		}

		// handle access key
		if ( is_user_logged_in() ) {
			$access_key = wp_create_nonce( $this->actions['generate'] );
		} elseif ( ! is_user_logged_in() && WPO_WCPDF()->settings->is_guest_access_enabled() ) {
			$access_key = $order->get_order_key();
		} else {
			return '';
		}

		if ( $this->is_enabled() ) {
			$parameters = array(
				$this->get_identifier(),
				$document_type,
				$order->get_id(),
				$access_key,
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
	
}

endif; // class_exists

return new Endpoint();