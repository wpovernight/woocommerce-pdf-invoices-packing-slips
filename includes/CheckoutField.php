<?php

namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\CheckoutField' ) ) :

class CheckoutField {
	public const ORDER_META_KEY = '_wpo_ips_checkout_field';
	public const USER_META_KEY  = 'wpo_ips_checkout_field';
	public const FIELD_KEY      = 'wpo_ips_checkout_field';
	public const BLOCK_FIELD_ID = 'wpo-ips/checkout-field';

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
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Blocks/store-api hooks
		$this->display_checkout_block_field();
		$this->set_checkout_block_field_value();

		add_action( 'woocommerce_set_additional_field_value', array(
			$this,
			'save_checkout_block_field'
		), 10, 4 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array(
			$this,
			'remove_order_checkout_block_field_meta'
		), 10, 1 );

		// Classic checkout hooks
		add_filter( 'woocommerce_checkout_fields', array(
			$this,
			'display_classic_checkout_field'
		), 10, 1 );
		add_filter( 'woocommerce_checkout_get_value', array(
			$this,
			'set_classic_checkout_field_value'
		), 10, 2 );
		add_action( 'woocommerce_after_checkout_validation', array(
			$this,
			'validate_classic_checkout_field_value'
		), 10, 2 );
		add_action( 'woocommerce_checkout_update_order_meta', array(
			$this,
			'save_classic_checkout_field'
		), 10, 2 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array(
			$this,
			'display_admin_billing'
		), 10, 1 );

		// My Account (Account details).
		if ( $this->is_my_account_enabled() ) {
			add_action( 'woocommerce_edit_account_form', array( $this, 'account_details_display_checkout_field' ), 20 );
			add_filter( 'woocommerce_save_account_details_errors', array(
				$this,
				'account_details_validate_checkout_field'
			), 20, 2 );
			add_action( 'woocommerce_save_account_details', array(
				$this,
				'account_details_save_checkout_field'
			), 20, 1 );
		}
	}

	/**
	 * Display optional checkout field in the Checkout Block.
	 *
	 * @return void
	 */
	public function display_checkout_block_field(): void {
		$args = array(
			'id'                => self::BLOCK_FIELD_ID,
			'label'             => $this->get_label(),
			'location'          => 'order',
			'type'              => 'text',
			'sanitize_callback' => static function ( $val ) {
				$val = sanitize_text_field( (string) $val );
				return (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );
			},
			'validate_callback' => function ( $val ) {
				$val = (string) $val;

				// If not treated as VAT, keep the existing flexible hook.
				if ( ! $this->is_vat_number() ) {
					$result = apply_filters( 'wpo_ips_checkout_field_validate', true, $val );
					return ( $result instanceof \WP_Error ) ? $result : true;
				}

				$result = apply_filters( 'wpo_ips_checkout_field_validate', $this->validate_vat_number_value( $val ), $val );
				return ( $result instanceof \WP_Error ) ? $result : true;
			},
		);

		$args = apply_filters( 'wpo_ips_checkout_field_block_args', $args );

		wpo_ips_register_additional_checkout_field( $args );
	}

	/**
	 * Set default value for the optional checkout field in the Checkout Block.
	 *
	 * @return void
	 */
	public function set_checkout_block_field_value(): void {
		add_filter(
			'woocommerce_get_default_value_for_' . self::BLOCK_FIELD_ID,
			static function ( $value, string $group, \WC_Data $wc_object ) {
				// Our field is in 'order' location, so group is typically 'other'.
				if ( ! $wc_object instanceof \WC_Customer ) {
					return (string) $value;
				}

				$user_id = $wc_object->get_id();
				if ( ! $user_id ) {
					return (string) $value;
				}

				$stored = (string) get_user_meta( $user_id, self::USER_META_KEY, true );

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
	public function save_checkout_block_field( string $key, $value, string $group, object $wc_object ): void {
		if ( $key !== self::BLOCK_FIELD_ID ) {
			return;
		}

		if ( ! ( $wc_object instanceof \WC_Order ) ) {
			return;
		}

		$val = sanitize_text_field( (string) wp_unslash( $value ) );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		if ( '' === trim( $val ) ) {
			$wc_object->delete_meta_data( self::ORDER_META_KEY );
		} else {
			$wc_object->update_meta_data( self::ORDER_META_KEY, $val );
		}

		$wc_object->save_meta_data();

		// Save on customer (if available).
		$customer_id = is_callable( array( $wc_object, 'get_customer_id' ) ) ? absint( $wc_object->get_customer_id() ) : 0;
		if ( $customer_id > 0 ) {
			if ( '' === trim( $val ) ) {
				delete_user_meta( $customer_id, self::USER_META_KEY );
			} else {
				update_user_meta( $customer_id, self::USER_META_KEY, $val );
			}
		}
	}

	/**
	 * Remove optional checkout field from order meta after checkout.
	 *
	 * @param \WC_Abstract_Order $order
	 * @return void
	 */
	public function remove_order_checkout_block_field_meta( \WC_Abstract_Order $order ): void {
		$order->delete_meta_data( '_wc_other/' . self::BLOCK_FIELD_ID );
		$order->save_meta_data();
	}

	/**
	 * Display optional checkout field in the Classic Checkout page.
	 *
	 * @param mixed $fields
	 * @return array
	 */
	public function display_classic_checkout_field( $fields ): array {
		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		$fields['order'] = $fields['order'] ?? array();

		$args = array(
			'type'     => 'text',
			'label'    => $this->get_label(),
			'required' => false,
			'class'    => array( 'form-row-wide' ),
		);

		$fields['order'][ self::FIELD_KEY ] = apply_filters( 'wpo_ips_checkout_field_classic_args', $args );

		return $fields;
	}

	/**
	 * Set default value for the optional checkout field in the Classic Checkout page.
	 *
	 * @param mixed $value
	 * @param string $input
	 * @return mixed
	 */
	public function set_classic_checkout_field_value( $value, string $input ) {
		if ( self::FIELD_KEY !== $input ) {
			return $value;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $value;
		}

		$stored = (string) get_user_meta( $user_id, self::USER_META_KEY, true );

		return (string) apply_filters( 'wpo_ips_checkout_field_default_value', $stored, $value, 'classic', null );
	}

	/**
	 * Validate optional checkout field from the Classic Checkout page.
	 *
	 * @param mixed $data
	 * @param mixed $errors
	 * @return void
	 */
	public function validate_classic_checkout_field_value( $data, $errors ): void {
		if ( ( ! $errors instanceof \WP_Error ) || ! is_array( $data ) ) {
			return;
		}

		$raw = isset( $data[ self::FIELD_KEY ] ) ? (string) $data[ self::FIELD_KEY ] : '';
		$val = sanitize_text_field( $raw );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		if ( '' === trim( $val ) ) {
			return;
		}

		if ( $this->is_vat_number() ) {
			$result = $this->validate_vat_number_value( $val );

			if ( $result instanceof \WP_Error ) {
				$errors->add( $result->get_error_code(), $result->get_error_message(), array( 'id' => self::FIELD_KEY ) );
			}

			return;
		}

		// Non-VAT mode: keep existing flexibility.
		$result = apply_filters( 'wpo_ips_checkout_field_validate', true, $val );

		if ( $result instanceof \WP_Error ) {
			$errors->add( $result->get_error_code(), $result->get_error_message(), array( 'id' => self::FIELD_KEY ) );
		}
	}

	/**
	 * Save optional checkout field from the Classic Checkout page.
	 *
	 * @param int $order_id
	 * @param array $data
	 * @return void
	 */
	public function save_classic_checkout_field( int $order_id, array $data ): void {
		$order = wc_get_order( $order_id );
		if ( empty( $order ) ) {
			return;
		}

		$raw = isset( $data[ self::FIELD_KEY ] ) ? (string) $data[ self::FIELD_KEY ] : '';
		$val = sanitize_text_field( $raw );
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		// Order meta
		if ( '' === trim( $val ) ) {
			$order->delete_meta_data( self::ORDER_META_KEY );
		} else {
			$order->update_meta_data( self::ORDER_META_KEY, $val );
		}
		$order->save_meta_data();

		// Customer meta (if available)
		$customer_id = is_callable( array( $order, 'get_customer_id' ) ) ? absint( $order->get_customer_id() ) : 0;
		if ( $customer_id > 0 ) {
			if ( '' === trim( $val ) ) {
				delete_user_meta( $customer_id, self::USER_META_KEY );
			} else {
				update_user_meta( $customer_id, self::USER_META_KEY, $val );
			}
		}
	}

	/**
	 * Display the optional checkout field under the Billing address in wp-admin.
	 *
	 * @param \WC_Order $order
	 * @return void
	 */
	public function display_admin_billing( \WC_Order $order ): void {
		$value = (string) $order->get_meta( self::ORDER_META_KEY, true );
		$value = trim( $value );

		if ( '' === $value ) {
			return;
		}

		$label = $this->get_label();

		echo '<p><strong>' . esc_html( $label ) . ':</strong><br>' . esc_html( $value ) . '</p>';
	}

	/**
	 * Display the optional checkout field in My Account > Account details.
	 *
	 * @return void
	 */
	public function account_details_display_checkout_field(): void {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$value = (string) get_user_meta( $user_id, self::USER_META_KEY, true );
		$value = (string) apply_filters( 'wpo_ips_checkout_field_default_value', $value, $value, 'my-account', null );

		$label       = $this->get_label();
		$description = '';

		if ( $this->is_vat_number() ) {
			$description = __( 'Please include the country prefix (for example NL123456789).', 'woocommerce-pdf-invoices-packing-slips' );
		}

		echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">';
		echo '<label for="' . esc_attr( self::FIELD_KEY ) . '">' . esc_html( $label ) . '</label>';
		echo '<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="' . esc_attr( self::FIELD_KEY ) . '" id="' . esc_attr( self::FIELD_KEY ) . '" value="' . esc_attr( $value ) . '" />';
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
		// Field is optional: if missing, don't block save.
		if ( ! isset( $_POST[ self::FIELD_KEY ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $errors;
		}

		$val = (string) sanitize_text_field( wp_unslash( $_POST[ self::FIELD_KEY ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		if ( '' === trim( $val ) ) {
			return $errors;
		}

		// VAT mode.
		if ( $this->is_vat_number() ) {
			$result = $this->validate_vat_number_value( $val );

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
		if ( ! isset( $_POST[ self::FIELD_KEY ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// If the field isn't present in the form submission, do nothing.
			return;
		}

		$val = (string) sanitize_text_field( wp_unslash( $_POST[ self::FIELD_KEY ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$val = (string) apply_filters( 'wpo_ips_checkout_field_sanitize', $val );

		if ( '' === trim( $val ) ) {
			delete_user_meta( $user_id, self::USER_META_KEY );
		} else {
			update_user_meta( $user_id, self::USER_META_KEY, $val );
		}
	}

	/**
	 * Validate the checkout field value when treated as a VAT number.
	 *
	 * @param string $raw_value
	 * @return true|\WP_Error
	 */
	private function validate_vat_number_value( string $raw_value ) {
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

	/**
	 * Check if the custom checkout field is enabled in general settings.
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		$general_settings = get_option( 'wpo_wcpdf_settings_general', array() );
		return ! empty( $general_settings['checkout_field_enable'] ?? '' );
	}

	/**
	 * Check if the custom checkout field is editable on the My Account page.
	 *
	 * @return bool
	 */
	public function is_my_account_enabled(): bool {
		$general_settings = get_option( 'wpo_wcpdf_settings_general', array() );
		return ! empty( $general_settings['checkout_field_enable_my_account'] ?? '' );
	}

	/**
	 * Check if the custom checkout field should be treated as a VAT number.
	 *
	 * @return bool
	 */
	public function is_vat_number(): bool {
		if ( ! empty( WPO_WCPDF()->vat_plugins ) && WPO_WCPDF()->vat_plugins->has_active() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the configured label for the custom checkout field.
	 *
	 * @return string
	 */
	public function get_label(): string {
		$default          = __( 'Customer identification', 'woocommerce-pdf-invoices-packing-slips' );
		$general_settings = get_option( 'wpo_wcpdf_settings_general', array() );
		$label            = trim( $general_settings['checkout_field_label'] ?? '' );

		if ( '' === $label ) {
			$label = $default;
		}

		return (string) apply_filters( 'wpo_ips_checkout_field_label', $label );
	}

}

endif;
