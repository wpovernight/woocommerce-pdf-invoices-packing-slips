<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Endpoint' ) ) :

class Endpoint {

	public $action = 'generate_wpo_wcpdf';

	public function __construct() {
		if ( $this->is_enabled() ) {
			add_action( 'init', array( $this, 'add_endpoint' ) );
			add_action( 'query_vars', array( $this, 'add_query_vars' ) );
			add_action( 'parse_request', array( $this, 'handle_document_requests' ) );
		}
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
			'index.php?action=generate_wpo_wcpdf&document_type=$matches[1]&order_ids=$matches[2]&_wpnonce=$matches[3]',
			'top'
		);
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'action';
		$vars[] = 'document_type';
		$vars[] = 'order_ids';
		$vars[] = '_wpnonce';
		return $vars;
	}

	public function handle_document_requests() {
		global $wp;

		if ( ! empty( $wp->query_vars['action'] ) && strpos( $this->action, $wp->query_vars['action'] ) !== false ) {
			if ( ! empty( $wp->query_vars['document_type'] ) && ! empty( $wp->query_vars['order_ids'] ) && ! empty( $wp->query_vars['_wpnonce'] ) ) {
				$_REQUEST['action']        = $this->action;
				$_REQUEST['document_type'] = sanitize_text_field( $wp->query_vars['document_type'] );
				$_REQUEST['order_ids']     = sanitize_text_field( $wp->query_vars['order_ids'] );
				$_REQUEST['_wpnonce']      = sanitize_text_field( $wp->query_vars['_wpnonce'] );
				
				do_action( 'wp_ajax_' . $this->action );
			}
		}
	}

	public function get_document_link( $order, $document_type, $additional_args = array() ) {
		if ( $this->is_enabled() ) {
			$parameters = array(
				$this->get_identifier(),
				$document_type,
				WCX_Order::get_id( $order ),
				wp_create_nonce( $this->action ),
			);
			$document_link = trailingslashit( get_home_url() ) . implode( '/', $parameters );
		} else {
			$document_link = wp_nonce_url( add_query_arg( array(
				'action'        => $this->action,
				'document_type' => $document_type,
				'order_ids'     => WCX_Order::get_id( $order ),
			), admin_url( 'admin-ajax.php' ) ), $this->action );
		}

		if ( ! empty( $additional_args ) && is_array( $additional_args ) ) {
			$document_link = add_query_arg( $additional_args, $document_link );
		}

		return $document_link;
	}
	
}

endif; // class_exists

return new Endpoint();