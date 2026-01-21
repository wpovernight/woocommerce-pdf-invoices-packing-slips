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
		// PDF download link on My Account page
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_invoice_actions' ), 999, 2 ); // needs to be triggered later because of Jetpack query string: https://github.com/Automattic/jetpack/blob/1a062c5388083c7f15b9a3e82e61fde838e83047/projects/plugins/jetpack/modules/woocommerce-analytics/classes/class-jetpack-woocommerce-analytics-my-account.php#L235
		add_action( 'wp_enqueue_scripts', array( $this, 'open_my_account_link_on_new_tab' ), 999 );

		// REST API
		add_filter( 'woocommerce_api_order_response', array( $this, 'add_invoice_number_to_wc_legacy_order_api' ), 10, 2 ); // support for legacy WC REST API
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( $this, 'add_invoice_number_to_wc_order_api' ), 10, 3 );

		// Shortcodes
		add_shortcode( 'wcpdf_download_invoice', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_download_pdf', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_document_link', array( $this, 'generate_document_shortcode' ) );
		
		// Optional Checkout field (General Settings).
		if ( $this->checkout_field_is_enabled() ) {
			if ( wpo_wcpdf_checkout_is_block() ) {
				$this->checkout_field_display_checkout_block_field();
				$this->checkout_field_set_checkout_block_field_value();

				add_action( 'woocommerce_set_additional_field_value', array( $this, 'checkout_field_save_checkout_block_field' ), 10, 4 );
				add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'checkout_field_remove_order_checkout_block_field_meta' ), 10, 1 );
			} else {
				add_filter( 'woocommerce_checkout_fields', array( $this, 'checkout_field_display_classic_checkout_field' ), 10, 1 );
				add_filter( 'woocommerce_checkout_get_value', array( $this, 'checkout_field_set_classic_checkout_field_value' ), 10, 2 );
				add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_field_validate_classic_checkout_field_value' ), 10, 2 );
				add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'checkout_field_save_classic_checkout_field' ), 10, 2 );
			}
			
			if ( $this->checkout_field_is_vat_number() ) {
				add_filter( 'wpo_wcpdf_order_customer_vat_number_meta_keys', array( $this, 'checkout_field_add_vat_meta_key' ), 10, 2 );
			}
			
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'checkout_field_display_admin_billing' ), 10, 1 );
			
			// My Account (Account details).
			if ( $this->checkout_field_is_my_account_enabled() ) {
				add_action( 'woocommerce_edit_account_form', array( $this, 'account_details_display_checkout_field' ), 20 );
				add_filter( 'woocommerce_save_account_details_errors', array( $this, 'account_details_validate_checkout_field' ), 20, 2 );
				add_action( 'woocommerce_save_account_details', array( $this, 'account_details_save_checkout_field' ), 20, 1 );
			}
		}
	}

	/**
	 * Display My Account invoice actions.
	 *
	 * @param array $actions
	 * @param \WC_Abstract_Order $order
	 * @return array
	 */
	public function my_account_invoice_actions( array $actions, \WC_Abstract_Order $order ): array {
		$this->disable_storing_document_settings();

		$document_type   = 'invoice';
		$document_title  = __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' );
		$invoice         = wcpdf_get_document( $document_type, $order );
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
				$name                      = is_callable( array( $invoice, 'get_title' ) ) ? $invoice->get_title() : $document_title;
				$actions[ $document_type ] = array(
					'url'  => WPO_WCPDF()->endpoint->get_document_link( $order, $document_type, array( 'my-account' => 'true' ) ),
					'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', $name, $invoice )
				);

				if ( $invoice->is_enabled( 'xml' ) && wpo_ips_edi_is_available() ) {
					$actions[ $document_type . '_xml' ] = array(
						'url'  => WPO_WCPDF()->endpoint->get_document_link( $order, $document_type, array( 'output' => 'xml', 'my-account' => 'true' ) ),
						'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', "E-{$name}", $invoice ),
					);
				}
			}
		}

		return apply_filters( 'wpo_wcpdf_myaccount_actions', $actions, $order );
	}

	/**
	 * Open link links in a new browser tab/window on the My Account and Thank You (Order Received) pages
	 *
	 * @return void
	 */
	public function open_my_account_link_on_new_tab(): void {
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
			return '';
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
			return '';
		}

		$document = wcpdf_get_document( $values['document_type'], $order );

		if ( ! $document || ! $document->is_allowed() ) {
			return '';
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
	 *
	 * @return void
	 */
	public function disable_storing_document_settings(): void {
		add_filter( 'wpo_wcpdf_document_store_settings', '__return_false', 9999 );
	}

	/**
	 * Restore the original document settings storing behavior.
	 * This should be called after disabling storing settings to avoid affecting other parts of the code.
	 *
	 * @return void
	 */
	public function restore_storing_document_settings(): void {
		remove_filter( 'wpo_wcpdf_document_store_settings', '__return_false', 9999 );
	}
	
	/**
	 * Display optional checkout field in the Checkout Block.
	 *
	 * @return void
	 */
	public function checkout_field_display_checkout_block_field(): void {
		if ( ! $this->checkout_field_is_enabled() ) {
			return;
		}

		$field_id = 'wpo-ips/checkout-field';

		$args = array(
			'id'                => $field_id,
			'label'             => $this->checkout_field_get_label(),
			'location'          => 'order',
			'type'              => 'text',
			'sanitize_callback' => static function ( $val ) {
				$val = sanitize_text_field( (string) $val );
				return (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );
			},
			'validate_callback' => function ( $val ) {
				$val = (string) $val;

				// If not treated as VAT, keep the existing flexible hook.
				if ( ! $this->checkout_field_is_vat_number() ) {
					$result = apply_filters( 'wpo_ips_checkout_field_validate', true, $val );
					return ( $result instanceof \WP_Error ) ? $result : true;
				}

				$result = apply_filters( 'wpo_ips_checkout_field_validate', $this->checkout_field_validate_vat_number_value( $val ), $val );
				return ( $result instanceof \WP_Error ) ? $result : true;
			},
		);

		$args = apply_filters( 'wpo_ips_checkout_field_block_args', $args );

		woocommerce_register_additional_checkout_field( $args );
	}
	
	/**
	 * Set default value for the optional checkout field in the Checkout Block.
	 *
	 * @return void
	 */
	public function checkout_field_set_checkout_block_field_value(): void {
		$field_id = 'wpo-ips/checkout-field';

		add_filter(
			"woocommerce_get_default_value_for_{$field_id}",
			static function ( $value, string $group, \WC_Data $wc_object ) {
				// Our field is in 'order' location, so group is typically 'other'.
				if ( ! $wc_object instanceof \WC_Customer ) {
					return (string) $value;
				}

				$user_id = $wc_object->get_id();
				if ( ! $user_id ) {
					return (string) $value;
				}

				$stored = (string) get_user_meta( $user_id, 'wpo_ips_checkout_field', true );

				return (string) apply_filters( 'wpo_ips_checkout_field_default_value', $stored, $value, $group, $wc_object );
			},
			10,
			3
		);
	}
	
	/**
	 * Save optional checkout field from the Checkout Block.
	 *
	 * @param string $key Field key.
	 * @param mixed $value Field value.
	 * @param string $group Group name.
	 * @param object $wc_object WC object (e.g. order).
	 * @return void
	 */
	public function checkout_field_save_checkout_block_field( string $key, $value, string $group, object $wc_object ): void {
		if ( ! $this->checkout_field_is_enabled() ) {
			return;
		}

		$field_id = 'wpo-ips/checkout-field';

		if ( $key !== $field_id ) {
			return;
		}

		if ( ! ( $wc_object instanceof \WC_Order ) ) {
			return;
		}

		$val = sanitize_text_field( (string) wp_unslash( $value ) );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		// Save on order.
		$order_meta_key = '_wpo_ips_checkout_field';

		if ( '' === trim( $val ) ) {
			$wc_object->delete_meta_data( $order_meta_key );
		} else {
			$wc_object->update_meta_data( $order_meta_key, $val );
		}

		$wc_object->save_meta_data();

		// Save on customer (if available).
		$customer_id = is_callable( array( $wc_object, 'get_customer_id' ) ) ? absint( $wc_object->get_customer_id() ) : 0;
		if ( $customer_id > 0 ) {
			if ( '' === trim( $val ) ) {
				delete_user_meta( $customer_id, 'wpo_ips_checkout_field' );
			} else {
				update_user_meta( $customer_id, 'wpo_ips_checkout_field', $val );
			}
		}
	}
	
	/**
	 * Remove optional checkout field from order meta after checkout.
	 *
	 * @param \WC_Abstract_Order $order
	 * @return void
	 */
	public function checkout_field_remove_order_checkout_block_field_meta( \WC_Abstract_Order $order ): void {
		$field_id = 'wpo-ips/checkout-field';

		$order->delete_meta_data( '_wc_other/' . $field_id );
		$order->save_meta_data();
	}
	
	/**
	 * Display optional checkout field in the Classic Checkout page.
	 *
	 * @param mixed $fields
	 * @return array
	 */
	public function checkout_field_display_classic_checkout_field( $fields ): array {
		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		if ( ! $this->checkout_field_is_enabled() ) {
			return $fields;
		}

		$fields['order'] = $fields['order'] ?? array();

		$key = 'wpo_ips_checkout_field';

		$args = array(
			'type'     => 'text',
			'label'    => $this->checkout_field_get_label(),
			'required' => false,
			'class'    => array( 'form-row-wide' ),
		);

		$fields['order'][ $key ] = apply_filters( 'wpo_ips_checkout_field_classic_args', $args );

		return $fields;
	}
	
	/**
	 * Set default value for the optional checkout field in the Classic Checkout page.
	 *
	 * @param mixed $value
	 * @param string $input
	 * @return mixed
	 */
	public function checkout_field_set_classic_checkout_field_value( $value, string $input ) {
		if ( 'wpo_ips_checkout_field' !== $input ) {
			return $value;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $value;
		}

		$stored = (string) get_user_meta( $user_id, 'wpo_ips_checkout_field', true );

		return (string) apply_filters( 'wpo_ips_checkout_field_default_value', $stored, $value, 'classic', null );
	}
	
	/**
	 * Validate optional checkout field from the Classic Checkout page.
	 *
	 * @param mixed $data
	 * @param mixed $errors
	 * @return void
	 */
	public function checkout_field_validate_classic_checkout_field_value( $data, $errors ): void {
		if ( ! $this->checkout_field_is_enabled() || ! $errors instanceof \WP_Error ) {
			return;
		}
		
		if ( ! is_array( $data ) ) {
			return;
		}

		$key = 'wpo_ips_checkout_field';
		$raw = isset( $data[ $key ] ) ? (string) $data[ $key ] : '';
		$val = sanitize_text_field( $raw );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );
		
		if ( '' === trim( $val ) ) {
			return;
		}

		if ( $this->checkout_field_is_vat_number() ) {
			$result = $this->checkout_field_validate_vat_number_value( $val );

			if ( $result instanceof \WP_Error ) {
				$errors->add( $result->get_error_code(), $result->get_error_message(), array( 'id' => $key ) );
			}

			return;
		}

		// Non-VAT mode: keep existing flexibility.
		$result = apply_filters( 'wpo_ips_checkout_field_validate', true, $val );

		if ( $result instanceof \WP_Error ) {
			$errors->add( $result->get_error_code(), $result->get_error_message(), array( 'id' => $key ) );
		}
	}
	
	/**
	 * Save optional checkout field from the Classic Checkout page.
	 *
	 * @param int $order_id
	 * @param array $data
	 * @return void
	 */
	public function checkout_field_save_classic_checkout_field( int $order_id, array $data ): void {
		if ( ! $this->checkout_field_is_enabled() ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( empty( $order ) ) {
			return;
		}

		$key            = 'wpo_ips_checkout_field';
		$order_meta_key = '_wpo_ips_checkout_field';

		$raw = isset( $data[ $key ] ) ? (string) $data[ $key ] : '';
		$val = sanitize_text_field( $raw );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		// Order meta
		if ( '' === trim( $val ) ) {
			$order->delete_meta_data( $order_meta_key );
		} else {
			$order->update_meta_data( $order_meta_key, $val );
		}
		$order->save_meta_data();

		// Customer meta (if available)
		$customer_id = is_callable( array( $order, 'get_customer_id' ) ) ? absint( $order->get_customer_id() ) : 0;
		if ( $customer_id > 0 ) {
			if ( '' === trim( $val ) ) {
				delete_user_meta( $customer_id, 'wpo_ips_checkout_field' );
			} else {
				update_user_meta( $customer_id, 'wpo_ips_checkout_field', $val );
			}
		}
	}
	
	/**
	 * Add our checkout field meta key to the list of VAT meta keys.
	 *
	 * @param array              $vat_meta_keys
	 * @param \WC_Abstract_Order $order
	 * @return array
	 */
	public function checkout_field_add_vat_meta_key( array $vat_meta_keys, \WC_Abstract_Order $order ): array {
		$meta_key = '_wpo_ips_checkout_field';

		// Prefer our value early (so it's picked before other plugins), but keep it safe.
		if ( ! in_array( $meta_key, $vat_meta_keys, true ) ) {
			array_unshift( $vat_meta_keys, $meta_key );
		}

		return $vat_meta_keys;
	}
	
	/**
	 * Display the optional checkout field under the Billing address in wp-admin.
	 *
	 * @param \WC_Order $order
	 * @return void
	 */
	public function checkout_field_display_admin_billing( \WC_Order $order ): void {
		// If your setting disables the field, don't show it.
		if ( ! $this->checkout_field_is_enabled() ) {
			return;
		}

		$value = (string) $order->get_meta( '_wpo_ips_checkout_field', true );
		$value = trim( $value );

		if ( '' === $value ) {
			return;
		}

		$label = $this->checkout_field_get_label();

		echo '<p><strong>' . esc_html( $label ) . ':</strong><br>' . esc_html( $value ) . '</p>';
	}
	
	/**
	 * Display the optional checkout field in My Account > Account details.
	 *
	 * @return void
	 */
	public function account_details_display_checkout_field(): void {
		if ( ! $this->checkout_field_is_my_account_enabled() ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$key   = 'wpo_ips_checkout_field';
		$value = (string) get_user_meta( $user_id, $key, true );
		$value = (string) apply_filters( 'wpo_ips_checkout_field_default_value', $value, $value, 'my-account', null );

		$label       = $this->checkout_field_get_label();
		$description = '';

		if ( $this->checkout_field_is_vat_number() ) {
			$description = __( 'Please include the country prefix (for example NL123456789).', 'woocommerce-pdf-invoices-packing-slips' );
		}

		echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">';
		echo '<label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label>';
		echo '<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		if ( '' !== $description ) {
			echo '<span class="description">' . esc_html( $description ) . '</span>';
		}
		echo '</p>';
	}

	/**
	 * Validate the My Account field.
	 *
	 * @param \WP_Error $errors
	 * @param \WP_User  $user
	 * @return \WP_Error
	 */
	public function account_details_validate_checkout_field( \WP_Error $errors, $user ): \WP_Error {
		if ( ! $this->checkout_field_is_my_account_enabled() ) {
			return $errors;
		}

		$key = 'wpo_ips_checkout_field';

		// Field is optional: if missing, don't block save.
		if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $errors;
		}

		$raw = (string) wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$val = sanitize_text_field( $raw );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		if ( '' === trim( $val ) ) {
			return $errors;
		}

		// VAT mode.
		if ( $this->checkout_field_is_vat_number() ) {
			$result = $this->checkout_field_validate_vat_number_value( $val );

			if ( $result instanceof \WP_Error ) {
				$errors->add( $result->get_error_code(), $result->get_error_message() );
			}

			return $errors;
		}

		// Non-VAT mode: keep your flexible validation hook.
		$result = apply_filters( 'wpo_ips_checkout_field_validate', true, $val );
		if ( $result instanceof \WP_Error ) {
			$errors->add( $result->get_error_code(), $result->get_error_message() );
		}

		return $errors;
	}

	/**
	 * Save the My Account field (user meta only).
	 *
	 * @param int $user_id
	 * @return void
	 */
	public function account_details_save_checkout_field( int $user_id ): void {
		if ( ! $this->checkout_field_is_my_account_enabled() ) {
			return;
		}

		$key = 'wpo_ips_checkout_field';

		if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// If the field isn't present in the form submission, do nothing.
			return;
		}

		$raw = (string) wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$val = sanitize_text_field( $raw );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		if ( '' === trim( $val ) ) {
			delete_user_meta( $user_id, $key );
		} else {
			update_user_meta( $user_id, $key, $val );
		}
	}
	
	/**
	 * Check if the checkout field is enabled in settings.
	 *
	 * @return bool
	 */
	private function checkout_field_is_enabled(): bool {
		$general_settings = WPO_WCPDF()->settings->general;
		return ! empty( $general_settings->get_setting( 'checkout_field_enable' ) ?? '' );
	}
	
	/**
	 * Check if the My Account field is enabled in settings.
	 *
	 * @return bool
	 */
	private function checkout_field_is_my_account_enabled(): bool {
		if ( ! $this->checkout_field_is_enabled() ) {
			return false;
		}

		$general_settings = WPO_WCPDF()->settings->general;
		return ! empty( $general_settings->get_setting( 'checkout_field_enable_my_account' ) );
	}

	/**
	 * Get the checkout field label from settings.
	 *
	 * @return string
	 */
	private function checkout_field_get_label(): string {
		$default          = __( 'Customer identification', 'woocommerce-pdf-invoices-packing-slips' );
		$general_settings = WPO_WCPDF()->settings->general;
		$label            = trim( $general_settings->get_setting( 'checkout_field_label' ) );

		if ( '' === $label ) {
			$label = $default;
		}

		return (string) apply_filters( 'wpo_ips_checkout_field_label', $label );
	}
	
	/**
	 * Check if the checkout field should be treated as a VAT number.
	 *
	 * @return bool
	 */
	private function checkout_field_is_vat_number(): bool {
		$general_settings = WPO_WCPDF()->settings->general;
		$enabled          = ! empty( $general_settings->get_setting( 'checkout_field_as_vat_number' ) );

		if ( ! $enabled ) {
			return false;
		}

		// Prevent conflicts with VAT plugins.
		if ( \wpo_ips_has_vat_plugin_active() ) {
			return false;
		}

		return true;
	}
	
	/**
	 * Validate the checkout field value when treated as a VAT number.
	 *
	 * @param string $raw_value
	 * @return true|\WP_Error
	 */
	private function checkout_field_validate_vat_number_value( string $raw_value ) {
		$vat = strtoupper( preg_replace( '/\s+/', '', trim( $raw_value ) ) );

		// Optional field: empty is always OK.
		if ( '' === $vat ) {
			return true;
		}

		/**
		 * Allow other code to normalize the VAT number before validation.
		 *
		 * @param string $vat
		 * @param string $raw_value
		 */
		$vat = (string) apply_filters( 'wpo_ips_checkout_field_vat_normalize', $vat, $raw_value );

		// Must start with a valid country prefix.
		if ( ! wpo_ips_edi_vat_number_has_country_prefix( $vat ) ) {
			$error = new \WP_Error(
				'invalid_vat_prefix',
				__( 'Please enter a VAT number including a valid country prefix (for example PT123456789).', 'woocommerce-pdf-invoices-packing-slips' )
			);

			/**
			 * Allow overriding the error returned by the base validation.
			 *
			 * @param \WP_Error $error
			 * @param string   $vat
			 */
			return apply_filters( 'wpo_ips_checkout_field_vat_prefix_error', $error, $vat );
		}

		/**
		 * Allow additional VAT checks (format, length, VIES, etc).
		 * Return true to accept, or WP_Error to reject.
		 *
		 * @param true|\WP_Error $result
		 * @param string         $vat
		 * @param string         $raw_value
		 */
		return apply_filters( 'wpo_ips_checkout_field_vat_validate', true, $vat, $raw_value );
	}

}

endif; // class_exists
