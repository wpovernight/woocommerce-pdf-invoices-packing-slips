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
	}

	/**
	 * Display download link on My Account page
	 */
	public function my_account_pdf_link( $actions, $order ) {
		$invoice = wcpdf_get_invoice( $order );
		if ( $invoice && $invoice->is_enabled() ) {
			$pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&document_type=invoice&order_ids=' . WCX_Order::get_id( $order ) . '&my-account'), 'generate_wpo_wcpdf' );

			// check my account button settings
			$button_setting = $invoice->get_setting('my_account_buttons', 'available');
			switch ($button_setting) {
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
					$allowed_statuses = $button_setting = $invoice->get_setting('my_account_restrict', array());
					if ( !empty( $allowed_statuses ) && in_array( WCX_Order::get_status( $order ), array_keys( $allowed_statuses ) ) ) {
						$invoice_allowed = true;
					} else {
						$invoice_allowed = false;
					}
					break;
			}

			// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
			if ( $invoice_allowed || in_array(WCX_Order::get_status( $order ), apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) {
				$document_title = array_filter( $invoice->get_setting( 'title', array() ) );
				if ( !empty($document_title) ) {
					$button_text = sprintf ( __( 'Download %s (PDF)', 'woocommerce-pdf-invoices-packing-slips' ), $invoice->get_title() );
				} else {
					$button_text = __( 'Download invoice (PDF)', 'woocommerce-pdf-invoices-packing-slips' );
				}
				$actions['invoice'] = array(
					'url'  => $pdf_url,
					'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', $button_text, $invoice )
				);
			}
		}

		return apply_filters( 'wpo_wcpdf_myaccount_actions', $actions, $order );
	}

	/**
	 * Add invoice number to WC REST API
	 */
	public function woocommerce_api_invoice_number ( $data, $order ) {
		$data['wpo_wcpdf_invoice_number'] = '';
		if ( $invoice = wcpdf_get_invoice( $order ) ) {
			$invoice_number = $invoice->get_number();
			if ( !empty( $invoice_number ) ) {
				$data['wpo_wcpdf_invoice_number'] = $invoice_number->get_formatted();
			}
		}

		return $data;
	}

}

endif; // class_exists

return new Frontend();