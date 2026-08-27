<?php
namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Frontend' ) ) :

class Frontend {

	protected static ?self $_instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Shortcodes
		add_shortcode( 'wcpdf_download_invoice', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_download_pdf', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_document_link', array( $this, 'generate_document_shortcode' ) );

		// REST
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			add_filter( 'woocommerce_api_order_response', array( $this, 'add_invoice_number_to_wc_legacy_order_api' ), 10, 2 );
			add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'add_invoice_number_to_wc_order_api' ), 10, 3 );
		}

		// Account
		if ( wpo_ips_is_account_page() ) {
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_invoice_actions' ), 999, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'open_my_account_link_on_new_tab' ), 999 );
		}
	}

	/**
	 * Display My Account invoice actions.
	 *
	 * @param array              $actions
	 * @param \WC_Abstract_Order $order
	 * @return array
	 */
	public function my_account_invoice_actions( array $actions, \WC_Abstract_Order $order ): array {
		$this->disable_storing_document_settings();

		$document_type  = 'invoice';
		$document_title = __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' );
		$invoice        = wcpdf_get_document( $document_type, $order );

		if ( ! $invoice ) {
			return (array) apply_filters(
				'wpo_wcpdf_myaccount_actions',
				$actions,
				$order
			);
		}

		$invoice_allowed = $invoice->is_allowed_in_my_account( 'available' );

		// Backward compatibility with the existing status-based filter.
		if ( ! $invoice_allowed ) {
			$invoice_allowed = in_array(
				$order->get_status(),
				apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ),
				true
			);
		}

		if ( $invoice_allowed ) {
			$name = is_callable( array( $invoice, 'get_title' ) )
				? $invoice->get_title()
				: $document_title;

			$endpoint_instance = WPO_WCPDF()->get_instance( 'endpoint' );

			$actions[ $document_type ] = array(
				'url'  => $endpoint_instance->get_document_link( $order, $document_type, array( 'my-account' => 'true' ) ),
				'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', $name, $invoice ),
			);

			if ( $invoice->is_enabled( 'xml' ) && wpo_ips_edi_is_available() ) {
				$actions[ $document_type . '_xml' ] = array(
					'url'  => $endpoint_instance->get_document_link( $order, $document_type, array( 'output' => 'xml', 'my-account' => 'true' ) ),
					'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', "E-{$name}", $invoice ),
				);
			}
		}

		return (array) apply_filters(
			'wpo_wcpdf_myaccount_actions',
			$actions,
			$order
		);
	}

	/**
	 * Open link links in a new browser tab/window on the My Account and Thank You (Order Received) pages
	 *
	 * @return void
	 */
	public function open_my_account_link_on_new_tab(): void {
		$is_account        = \wpo_ips_is_account_page();
		$is_order_received = \wpo_ips_is_order_received_page();

		if ( $is_account || $is_order_received ) {
			$general_settings = get_option( 'wpo_wcpdf_settings_general', array() );

			if ( isset( $general_settings['download_display'] ) && 'display' === $general_settings['download_display'] ) {
				$suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				$file_path            = WPO_WCPDF()->plugin_path() . '/assets/js/my-account-link' . $suffix . '.js';
				$file_system_instance = WPO_WCPDF()->get_instance( 'file_system' );

				if ( $file_system_instance->exists( $file_path ) ) {
					$script            = $file_system_instance->get_contents( $file_path );
					$endpoint_instance = WPO_WCPDF()->get_instance( 'endpoint' );

					if ( $script && $endpoint_instance->pretty_links_enabled() ) {
						$script = str_replace( 'generate_wpo_wcpdf', $endpoint_instance->get_identifier(), $script );
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
	 * @param \WC_Abstract_Order $order
	 * @return string
	 */
	private function get_invoice_number( \WC_Abstract_Order $order ): string {
		$invoice_number = '';

		$this->disable_storing_document_settings();

		try {
			$invoice = wcpdf_get_document( 'invoice', $order );

			if ( ! $invoice || ! is_callable( array( $invoice, 'get_number' ) ) ) {
				return '';
			}

			$number = $invoice->get_number();

			if ( ! empty( $number ) ) {
				$invoice_number = is_callable( array( $number, 'get_formatted' ) )
					? $number->get_formatted()
					: (string) $number;
			}

		} finally {
			$this->restore_storing_document_settings();
		}

		return $invoice_number;
	}

	/**
	 * Generate a document download link via shortcode
	 *
	 * @param array $atts
	 * @param string|null $content
	 * @param string $shortcode_tag
	 * @return string
	 */
	public function generate_document_shortcode( array $atts, ?string $content = null, string $shortcode_tag = '' ): string {
		global $wp;

		if ( is_admin() ) {
			return '';
		}

		// Default values
		$values = shortcode_atts( array(
			'order_id'      => '',
			'link_text'     => '',
			'id'            => '',
			'class'         => 'wpo_wcpdf_document_link',
			'document_type' => 'invoice',
		), $atts );

		$values['order_id']      = absint( $values['order_id'] );
		$values['document_type'] = sanitize_key( $values['document_type'] );
		$has_explicit_order_id   = ! empty( $values['order_id'] );

		$is_document_type_valid = false;
		$documents              = WPO_WCPDF()->get_instance( 'documents' )->get_documents();

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
			return '';
		}

		// Get $order
		$order = null;

		if ( ! $has_explicit_order_id ) {
			if ( is_checkout() && is_wc_endpoint_url( 'order-received' ) && isset( $wp->query_vars['order-received'] ) ) {
				$order = wc_get_order( $wp->query_vars['order-received'] );
			} elseif ( \wpo_ips_is_account_page() && is_wc_endpoint_url( 'view-order' ) && isset( $wp->query_vars['view-order'] ) ) {
				$order = wc_get_order( $wp->query_vars['view-order'] );
			}
		} else {
			$order = wc_get_order( $values['order_id'] );
		}

		if ( empty( $order ) || ! is_object( $order ) ) {
			return '';
		}

		if ( $has_explicit_order_id && ! $this->current_user_can_access_shortcode_order( $order, $values['document_type'] ) ) {
			return '';
		}

		$document = wcpdf_get_document( $values['document_type'], $order );

		if ( ! $document || ! $document->is_allowed() ) {
			return '';
		}

		$pdf_url = WPO_WCPDF()->get_instance( 'endpoint' )->get_document_link( $order, $values['document_type'], [ 'shortcode' => 'true' ] );

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
	 *
	 * @return void
	 */
	public function disable_storing_document_settings(): void {
		add_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'prevent_storing_document_settings' ), 9999 );
	}

	/**
	 * Restore the original document settings storing behavior.
	 * This should be called after disabling storing settings to avoid affecting other parts of the code.
	 *
	 * @return void
	 */
	public function restore_storing_document_settings(): void {
		remove_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'prevent_storing_document_settings' ), 9999 );
	}

	/**
	 * Prevent document settings from being stored during temporary document checks.
	 *
	 * @return bool
	 */
	public function prevent_storing_document_settings(): bool {
		return false;
	}

	/**
	 * Check whether the current user can use a shortcode with an explicit order ID.
	 *
	 * @param \WC_Abstract_Order $order
	 * @param string             $document_type
	 * @return bool
	 */
	protected function current_user_can_access_shortcode_order( \WC_Abstract_Order $order, string $document_type ): bool {
		if ( WPO_WCPDF()->admin->user_can_manage_document( $document_type ) ) {
			return true;
		}

		return is_user_logged_in() && current_user_can( 'view_order', $order->get_id() );
	}

}

endif; // class_exists
