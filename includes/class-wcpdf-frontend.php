<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Frontend' ) ) :

class Frontend {
	
	function __construct()	{
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_pdf_link' ), 10, 2 );
		add_filter( 'woocommerce_api_order_response', array( $this, 'woocommerce_api_invoice_number' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'open_my_account_pdf_link_on_new_tab' ), 999 );
		add_shortcode( 'wcpdf_download_invoice', array( $this, 'download_invoice_shortcode' ) );
	}

	/**
	 * Display download link on My Account page
	 */
	public function my_account_pdf_link( $actions, $order ) {
		$this->disable_storing_document_settings();

		$invoice = wcpdf_get_invoice( $order );
		if ( $invoice && $invoice->is_enabled() ) {
			$pdf_url = wp_nonce_url( add_query_arg( array(
				'action'        => 'generate_wpo_wcpdf',
				'document_type' => 'invoice',
				'order_ids'     => WCX_Order::get_id( $order ),
				'my-account'    => true,
			), admin_url( 'admin-ajax.php' ) ), 'generate_wpo_wcpdf' );

			// check my account button settings
			$button_setting = $invoice->get_setting( 'my_account_buttons', 'available' );
			switch ( $button_setting ) {
				case 'available':
					$invoice_allowed = $invoice->exists();
					break;
				case 'always':
					$invoice_allowed = true;
					break;
				case 'never':
					$invoice_allowed = false;
					break;
				case 'custom':
					$allowed_statuses = $button_setting = $invoice->get_setting( 'my_account_restrict', array() );
					if ( !empty( $allowed_statuses ) && in_array( WCX_Order::get_status( $order ), array_keys( $allowed_statuses ) ) ) {
						$invoice_allowed = true;
					} else {
						$invoice_allowed = false;
					}
					break;
			}

			// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
			if ( $invoice_allowed || in_array( WCX_Order::get_status( $order ), apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) {
				$actions['invoice'] = array(
					'url'  => $pdf_url,
					'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', $invoice->get_title(), $invoice )
				);
			}
		}

		return apply_filters( 'wpo_wcpdf_myaccount_actions', $actions, $order );
	}

	/**
	 * Open PDF on My Account page in a new browser tab/window
	 */
	public function open_my_account_pdf_link_on_new_tab() {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			if ( $general_settings = get_option( 'wpo_wcpdf_settings_general' ) ) {
				if ( isset( $general_settings['download_display'] ) && $general_settings['download_display'] == 'display' ) {
					$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

					if ( function_exists( 'file_get_contents' ) && $script = file_get_contents( WPO_WCPDF()->plugin_path() . '/assets/js/my-account-link'.$suffix.'.js' ) ) {
						
						wp_add_inline_script( 'jquery', $script );
					}
				}
			}
		}
	}

	/**
	 * Add invoice number to WC REST API
	 */
	public function woocommerce_api_invoice_number ( $data, $order ) {
		$this->disable_storing_document_settings();
		$data['wpo_wcpdf_invoice_number'] = '';
		if ( $invoice = wcpdf_get_invoice( $order ) ) {
			$invoice_number = $invoice->get_number();
			if ( !empty( $invoice_number ) ) {
				$data['wpo_wcpdf_invoice_number'] = $invoice_number->get_formatted();
			}
		}

		return $data;
		$this->restore_storing_document_settings();
	}

	/**
	 * Download invoice frontend shortcode
	 */
	public function download_invoice_shortcode( $atts ) {
		global $wp;

		if ( is_admin() ) {
			return;
		}

		// Default values
		$values = shortcode_atts (array(
			'order_id'		=> '',
			'link_text'		=> ''
		), $atts );

		if ( empty( $values ) ) {
			return;
		}

		// Get $order
		if ( empty( $values['order_id'] ) ) {
			if( is_checkout() && is_wc_endpoint_url( 'order-received' ) && isset( $wp->query_vars['order-received'] ) ) {
				$order = wc_get_order( $wp->query_vars['order-received'] );
			} elseif( is_account_page() && is_wc_endpoint_url( 'view-order' ) && isset( $wp->query_vars['view-order'] ) ) {
				$order = wc_get_order( $wp->query_vars['view-order'] );
			}
		} else {
			$order = wc_get_order( $values['order_id'] );
		}

		if ( empty( $order ) || ! is_object( $order ) ) {
			return;
		}

		// Link text
		if( ! empty( $values['link_text'] ) ) {
			$link_text = $values['link_text'];
		} else {
			$link_text = __( 'Download invoice (PDF)', 'woocommerce-pdf-invoices-packing-slips' );
		}

		// Basic query args for PDF	
		$query_args = array(
			'action'        => 'generate_wpo_wcpdf',
			'document_type' => 'invoice',
			'order_ids'     => $order->get_id(),
		);
		
		// Add query args based on user permissions and guest access setting
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );
		if( is_user_logged_in() ) {
			$query_args['my-account'] = true;
		} elseif( ! is_user_logged_in() && isset( $debug_settings['guest_access'] ) ) {
			$query_args['order_key'] = $order->get_order_key();
		} else {
			return; // no business here
		}
	
		$pdf_url = wp_nonce_url( add_query_arg( $query_args, admin_url( 'admin-ajax.php' ) ), 'generate_wpo_wcpdf' );
		$text = sprintf( '<p><a href="%s" target="_blank">%s</a></p>', esc_attr( $pdf_url ), esc_html( $link_text ) );

		return $text;
	}

	/**
	 * Document objects are created in order to check for existence and retrieve data,
	 * but we don't want to store the settings for uninitialized documents.
	 * Only use in frontend/backed (page requests), otherwise settings will never be stored!
	 */
	public function disable_storing_document_settings() {
		add_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function restore_storing_document_settings() {
		remove_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function return_false(){
		return false;
	}
}

endif; // class_exists

return new Frontend();