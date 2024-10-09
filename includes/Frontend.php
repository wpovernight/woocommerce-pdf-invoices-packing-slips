<?php
namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Frontend' ) ) :

class Frontend {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()	{
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_pdf_link' ), 999, 2 ); // needs to be triggered later because of Jetpack query string: https://github.com/Automattic/jetpack/blob/1a062c5388083c7f15b9a3e82e61fde838e83047/projects/plugins/jetpack/modules/woocommerce-analytics/classes/class-jetpack-woocommerce-analytics-my-account.php#L235
		add_filter( 'woocommerce_api_order_response', array( $this, 'woocommerce_api_invoice_number' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'open_my_account_pdf_link_on_new_tab' ), 999 );
		add_shortcode( 'wcpdf_download_invoice', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_download_pdf', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_document_link', array( $this, 'generate_document_shortcode' ) );
	}

	/**
	 * Display download link on My Account page
	 */
	public function my_account_pdf_link( $actions, $order ) {
		$this->disable_storing_document_settings();

		$invoice = wcpdf_get_invoice( $order );
		if ( $invoice && $invoice->is_enabled() ) {
			$pdf_url = WPO_WCPDF()->endpoint->get_document_link( $order, 'invoice', array( 'my-account' => 'true' ) );

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
					if ( !empty( $allowed_statuses ) && in_array( $order->get_status(), array_keys( $allowed_statuses ) ) ) {
						$invoice_allowed = true;
					} else {
						$invoice_allowed = false;
					}
					break;
			}

			// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
			if ( $invoice_allowed || in_array( $order->get_status(), apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) {
				$actions['invoice'] = array(
					'url'  => esc_url( $pdf_url ),
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

						if ( WPO_WCPDF()->endpoint->pretty_links_enabled() ) {
							$script = str_replace( 'generate_wpo_wcpdf', WPO_WCPDF()->endpoint->get_identifier(), $script );
						}

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

		$this->restore_storing_document_settings();
		return $data;
	}

	public function generate_document_shortcode( $atts, $content = null, $shortcode_tag = '' ) {
		global $wp;

		if ( is_admin() ) {
			return;
		}

		// Default values
		$values = shortcode_atts( array(
			'order_id'      => '',
			'link_text'     => '',
			'id'            => '',
			'class'         => 'wpo_wcpdf_document_link',
			'document_type' => 'invoice',
		), $atts );

		$is_document_type_valid = false;
		$documents              = WPO_WCPDF()->documents->get_documents();
		foreach ( $documents as $document ) {
			if ( $document->get_type() === $values['document_type'] ) {
				$is_document_type_valid = true;

				if ( ! empty( $values['link_text'] ) ) {
					$link_text = $values['link_text'];
				} else {
					$link_text = sprintf(
						/* translators: %s: Document type */
						__( 'Download %s (PDF)', 'woocommerce-pdf-invoices-packing-slips' ),
						wp_kses_post( $document->get_type() )
					);
				}

				break;
			}
		}

		if ( ! $is_document_type_valid ) {
			return;
		}

		// Get $order
		if ( empty( $values['order_id'] ) ) {
			if ( is_checkout() && is_wc_endpoint_url( 'order-received' ) && isset( $wp->query_vars['order-received'] ) ) {
				$order = wc_get_order( $wp->query_vars['order-received'] );
			} elseif ( is_account_page() && is_wc_endpoint_url( 'view-order' ) && isset( $wp->query_vars['view-order'] ) ) {
				$order = wc_get_order( $wp->query_vars['view-order'] );
			}
		} else {
			$order = wc_get_order( $values['order_id'] );
		}

		if ( empty( $order ) || ! is_object( $order ) ) {
			return;
		}

		$document = wcpdf_get_document( $values['document_type'], $order );

		if ( ! $document || ! $document->is_allowed() ) {
			return;
		}

		$pdf_url = WPO_WCPDF()->endpoint->get_document_link( $order, $values['document_type'], [ 'shortcode' => 'true' ] );

		if ( 'wcpdf_document_link' === $shortcode_tag ) {
			return esc_url( $pdf_url );
		}

		return sprintf(
			'<p><a %s class="%s" href="%s" target="_blank">%s</a></p>',
			( ! empty( $values['id'] ) ? 'id="' . esc_attr( $values['id'] ) . '"' : '' ),
			esc_attr( $values['class'] ),
			esc_url( $pdf_url ),
			esc_html( $link_text )
		);
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
