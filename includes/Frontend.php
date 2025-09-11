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
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_invoice_pdf_link' ), 999, 2 ); // needs to be triggered later because of Jetpack query string: https://github.com/Automattic/jetpack/blob/1a062c5388083c7f15b9a3e82e61fde838e83047/projects/plugins/jetpack/modules/woocommerce-analytics/classes/class-jetpack-woocommerce-analytics-my-account.php#L235
		add_filter( 'woocommerce_api_order_response', array( $this, 'add_invoice_number_to_wc_legacy_order_api' ), 10, 2 ); // support for legacy WC REST API
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'add_invoice_number_to_wc_order_api' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'open_my_account_pdf_link_on_new_tab' ), 999 );
		add_shortcode( 'wcpdf_download_invoice', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_download_pdf', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_document_link', array( $this, 'generate_document_shortcode' ) );
	}

	/**
	 * Display Invoice download link on My Account page
	 *
	 * @param array $actions
	 * @param \WC_Abstract_Order $order
	 * @return array
	 */
	public function my_account_invoice_pdf_link( array $actions, \WC_Abstract_Order $order ): array {
		$this->disable_storing_document_settings();

		$invoice         = wcpdf_get_document( 'invoice', $order );
		$invoice_allowed = false;

		if ( $invoice && $invoice->is_enabled() ) {
			// check my account button settings
			$button_setting = $invoice->get_setting( 'my_account_buttons', 'available' );

			switch ( $button_setting ) {
				case 'available':
					$invoice_allowed = $invoice->exists();
					break;
				case 'always':
					$invoice_allowed = true;
					break;
				case 'custom':
					$allowed_statuses = $invoice->get_setting( 'my_account_restrict', array() );

					if ( ! empty( $allowed_statuses ) && in_array( $order->get_status(), array_keys( $allowed_statuses ), true ) ) {
						$invoice_allowed = true;
					}
					break;
				case 'never':
				default:
					break;
			}

			// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
			if ( $invoice_allowed || in_array( $order->get_status(), apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) {
				$actions['invoice'] = array(
					'url'  => WPO_WCPDF()->endpoint->get_document_link( $order, 'invoice', array( 'my-account' => 'true' ) ),
					'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', $invoice->get_title(), $invoice )
				);
			}
		}

		return apply_filters( 'wpo_wcpdf_myaccount_actions', $actions, $order );
	}

	/**
	 * Open PDF links in a new browser tab/window on the My Account and Thank You (Order Received) pages
	 */
	public function open_my_account_pdf_link_on_new_tab() {
		$is_account        = function_exists( 'is_account_page' )        && is_account_page();
		$is_order_received = function_exists( 'is_order_received_page' ) && is_order_received_page();
		
		if ( $is_account || $is_order_received ) {
			$general_settings = get_option( 'wpo_wcpdf_settings_general', array() );
			
			if ( isset( $general_settings['download_display'] ) && 'display' === $general_settings['download_display'] ) {
				$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				$file_path = WPO_WCPDF()->plugin_path() . '/assets/js/my-account-link' . $suffix . '.js';

				if ( WPO_WCPDF()->file_system->exists( $file_path ) ) {
					$script = WPO_WCPDF()->file_system->get_contents( $file_path );

					if ( $script && WPO_WCPDF()->endpoint->pretty_links_enabled() ) {
						$script = str_replace( 'generate_wpo_wcpdf', WPO_WCPDF()->endpoint->get_identifier(), $script );
					}

					wp_add_inline_script( 'jquery', $script );
				}
			}
		}
	}

	/**
	 * Add invoice number to WC Legacy REST API.
	 *
	 * @param array $data
	 * @param \WC_Abstract_Order $order
	 *
	 * @return array
	 */
	public function add_invoice_number_to_wc_legacy_order_api( array $data, \WC_Abstract_Order $order ): array {
		$data['wpo_wcpdf_invoice_number'] = $this->get_invoice_number( $order );

		return $data;
	}

	/**
	 * Add invoice number to WC REST API.
	 *
	 * @param \WP_REST_Response $response
	 * @param \WC_Data $order
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function add_invoice_number_to_wc_order_api( \WP_REST_Response $response, \WC_Data $order, \WP_REST_Request $request ): \WP_REST_Response {
		$data                             = $response->get_data();
		$data['wpo_wcpdf_invoice_number'] = $this->get_invoice_number( $order );
		$response->set_data( $data );

		return $response;
	}

	/**
	 * Retrieve formatted invoice number for a given order
	 *
	 * @param \WC_Abstract_Order|\WC_Order $order
	 *
	 * @return string
	 */
	private function get_invoice_number( $order ): string {
		$this->disable_storing_document_settings();
		$invoice        = wcpdf_get_document( 'invoice', $order );
		$invoice_number = '';

		if ( $invoice ) {
			$number = $invoice->get_number();
			if ( ! empty( $number ) ) {
				$invoice_number = $number->get_formatted();
			}
		}

		$this->restore_storing_document_settings();

		return $invoice_number;
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
