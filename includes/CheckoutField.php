<?php
namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WPO\IPS\CheckoutField' ) ) :

	class CheckoutField {

		public const TYPE_CUSTOM                   = 'custom';
		public const TYPE_VAT_NUMBER               = 'vat_number';
		public const TYPE_REGISTRATION_NUMBER      = 'registration_number';

		private const ORDER_META_PREFIX            = '_wpo_ips_checkout_field_';
		private const USER_META_PREFIX             = 'wpo_ips_checkout_field_';

		public const LEGACY_ORDER_META_KEY         = '_wpo_ips_checkout_field';
		private const LEGACY_USER_META_KEY         = 'wpo_ips_checkout_field';

		public const CLASSIC_FIELD_KEY             = 'wpo_ips_checkout_field';
		public const BLOCK_FIELD_ID                = 'wpo-ips/checkout-field';

		public const ALTERNATIVE_CLASSIC_FIELD_KEY = 'wpo_ips_checkout_field_alternative';
		public const ALTERNATIVE_BLOCK_FIELD_ID    = 'wpo-ips/checkout-field-alternative';

		protected static ?self $_instance = null;

		/**
		 * Get the singleton instance.
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
		 * Get the supported field types.
		 *
		 * @return array
		 */
		public function get_types(): array {
			return array_keys( $this->get_type_options() );
		}

		/**
		 * Get the supported field types with their translated labels.
		 *
		 * @return array
		 */
		public function get_type_options(): array {
			return array(
				self::TYPE_CUSTOM              => __( 'Custom', 'woocommerce-pdf-invoices-packing-slips' ),
				self::TYPE_VAT_NUMBER          => __( 'VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
				self::TYPE_REGISTRATION_NUMBER => __( 'Company registration number', 'woocommerce-pdf-invoices-packing-slips' ),
			);
		}

		/**
		 * Get the configured field type.
		 *
		 * @return string
		 */
		public function get_type(): string {
			$general_settings = WPO_WCPDF()
				->get_instance( 'settings' )
				->get_instance( 'general' );

			$type = (string) $general_settings->get_setting( 'checkout_field_type' );

			// Checkout can run before the admin request that upgrades the settings.
			if ( '' === $type ) {
				$type = $general_settings->get_setting( 'checkout_field_as_vat_number' )
					? self::TYPE_VAT_NUMBER
					: self::TYPE_CUSTOM;
			}

			return $this->normalize_type( $type );
		}

		/**
		 * Get the optional field type for billing countries outside the shop country.
		 *
		 * @return string Empty means use the primary type everywhere.
		 */
		public function get_alternative_type(): string {
			$general = \WPO_WCPDF()->get_instance( 'settings' )->get_instance( 'general' );
			if ( ! $general->get_setting( 'checkout_field_alternative_enable' ) ) {
				return '';
			}

			$type = $general->get_setting( 'checkout_field_alternative_type' ) ?: self::TYPE_CUSTOM;

			return $this->get_shop_country() && $type !== $this->get_type() && in_array( $type, $this->get_types(), true )
				? $type
				: '';
		}

		/**
		 * Get the shop country used for checkout labels and type selection.
		 *
		 * @return string
		 */
		public function get_shop_country(): string {
			return \WPO_WCPDF()->get_instance( 'settings' )->get_instance( 'general' )->get_setting( 'shop_address_country' );
		}

		/**
		 * Resolve the field type for a billing country.
		 *
		 * @param string $country Billing country code.
		 * @return string
		 */
		public function get_checkout_type( string $country ): string {
			$alternative = $this->get_alternative_type();

			return $alternative && '' !== $country && $country !== $this->get_shop_country()
				? $alternative
				: $this->get_type();
		}

		/**
		 * Map checkout field IDs to their types.
		 *
		 * @param bool $block Whether to return Checkout Block IDs.
		 * @return array
		 */
		public function get_field_types( bool $block = false ): array {
			$fields      = array( ( $block ? self::BLOCK_FIELD_ID : self::CLASSIC_FIELD_KEY ) => $this->get_type() );
			$alternative = $this->get_alternative_type();

			if ( $alternative && ( ! $block || ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.9', '>=' ) ) ) ) {
				$fields[ $block ? self::ALTERNATIVE_BLOCK_FIELD_ID : self::ALTERNATIVE_CLASSIC_FIELD_KEY ] = $alternative;
			}

			return $fields;
		}

		/**
		 * Check whether the configured field has a specific type.
		 *
		 * @param string $type Field type.
		 * @return bool
		 */
		public function is_type( string $type ): bool {
			return $this->get_type() === $this->normalize_type( $type );
		}

		/**
		 * Get the order meta key for a field type.
		 *
		 * @param string $type Field type. Defaults to the configured type.
		 * @return string
		 */
		public function get_order_meta_key( string $type = '' ): string {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			return self::ORDER_META_PREFIX . $type;
		}

		/**
		 * Get the user meta key for a field type.
		 *
		 * @param string $type Field type. Defaults to the configured type.
		 * @return string
		 */
		public function get_user_meta_key( string $type = '' ): string {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			return self::USER_META_PREFIX . $type;
		}

		/**
		 * Get the billing countries where the checkout field is displayed.
		 *
		 * @return array Empty means all countries.
		 */
		public function get_countries(): array {
			$settings  = \WPO_WCPDF()->get_instance( 'settings' )->general_settings;
			$countries = $settings['checkout_field_countries'] ?? array();

			return is_array( $countries ) ? array_values( array_filter( $countries ) ) : array();
		}

		/**
		 * Check whether the checkout field is displayed for a billing country.
		 *
		 * @param string $country Billing country code.
		 * @return bool
		 */
		public function is_allowed_country( string $country ): bool {
			$countries = $this->get_countries();

			return empty( $countries ) || in_array( $country, $countries, true );
		}

		/**
		 * Get the field label, defaulting to the shop country label.
		 *
		 * @param string $type Field type. Defaults to the primary type.
		 * @return string
		 */
		public function get_label( string $type = '' ): string {
			$general_settings = WPO_WCPDF()
				->get_instance( 'settings' )
				->get_instance( 'general' );

			$type  = $type ?: $this->get_type();
			$label = $type === $this->get_type()
				? trim( (string) $general_settings->get_setting( 'checkout_field_label' ) )
				: '';

			if ( '' === $label ) {
				$label = $this->get_default_label(
					$type,
					$this->get_shop_country()
				);
			}

			return (string) apply_filters( 'wpo_ips_checkout_field_label', $label );
		}

		/**
		 * Get the default label for a field type.
		 *
		 * @param string $type    Field type.
		 * @param string $country Country code in ISO 3166-1 alpha-2 format.
		 * @return string
		 */
		public function get_default_label( string $type = '', string $country = '' ): string {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			switch ( $type ) {
				case self::TYPE_VAT_NUMBER:
					return __( 'VAT number', 'woocommerce-pdf-invoices-packing-slips' );

				case self::TYPE_REGISTRATION_NUMBER:
					$label = function_exists( 'wpo_ips_edi_get_identifier_mappings' )
						? wpo_ips_edi_get_identifier_mappings( $country, 'registration_number', 'label' )
						: '';

					return ! empty( $label )
						? (string) $label
						: __( 'Company registration number', 'woocommerce-pdf-invoices-packing-slips' );

				case self::TYPE_CUSTOM:
				default:
					return __( 'Customer identification', 'woocommerce-pdf-invoices-packing-slips' );
			}
		}

		/**
		 * Get an order value for a field type.
		 *
		 * @param \WC_Abstract_Order $order Order object.
		 * @param string             $type  Field type.
		 * @return string|null
		 */
		public function get_order_value( \WC_Abstract_Order $order, string $type = '' ): ?string {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			$value = trim(
				(string) $order->get_meta(
					$this->get_order_meta_key( $type ),
					true
				)
			);

			if ( '' !== $value ) {
				return $value;
			}

			return $this->get_legacy_order_value( $order, $type );
		}

		/**
		 * Save an order value.
		 *
		 * @param \WC_Abstract_Order $order Order object.
		 * @param string             $value Field value.
		 * @param string             $type  Field type.
		 * @return void
		 */
		public function save_order_value( \WC_Abstract_Order $order, string $value, string $type = '' ): void {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			$meta_key = $this->get_order_meta_key( $type );
			$value    = trim( $value );

			if ( '' === $value ) {
				$order->delete_meta_data( $meta_key );
			} else {
				$order->update_meta_data( $meta_key, $value );
			}

			if ( $type === $this->get_legacy_type() ) {
				$order->delete_meta_data( self::LEGACY_ORDER_META_KEY );
			}

			$order->save_meta_data();
		}

		/**
		 * Get a user value for a field type.
		 *
		 * @param int    $user_id User ID.
		 * @param string $type    Field type.
		 * @return string|null
		 */
		public function get_user_value( int $user_id, string $type = '' ): ?string {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			$value = trim(
				(string) get_user_meta(
					$user_id,
					$this->get_user_meta_key( $type ),
					true
				)
			);

			if ( '' !== $value ) {
				return $value;
			}

			return $this->get_legacy_user_value( $user_id, $type );
		}

		/**
		 * Save a user value.
		 *
		 * @param int    $user_id User ID.
		 * @param string $value   Field value.
		 * @param string $type    Field type.
		 * @return void
		 */
		public function save_user_value( int $user_id, string $value, string $type = '' ): void {
			$type = '' !== $type
				? $this->normalize_type( $type )
				: $this->get_type();

			$meta_key = $this->get_user_meta_key( $type );
			$value    = trim( $value );

			if ( '' === $value ) {
				delete_user_meta( $user_id, $meta_key );
			} else {
				update_user_meta( $user_id, $meta_key, $value );
			}

			if ( $type === $this->get_legacy_type() ) {
				delete_user_meta( $user_id, self::LEGACY_USER_META_KEY );
			}
		}

		/**
		 * Check whether the field should be treated as a VAT number.
		 *
		 * @param string $type Field type. Defaults to the primary type.
		 * @return bool
		 */
		public function is_vat_number( string $type = '' ): bool {
			if ( ( $type ?: $this->get_type() ) !== self::TYPE_VAT_NUMBER ) {
				return false;
			}

			return ! WPO_WCPDF()
				->get_instance( 'vat_plugins' )
				->has_active();
		}

		/**
		 * Check whether the checkout field is enabled.
		 *
		 * @param string $type Field type. Empty checks both configured types.
		 * @return bool
		 */
		public function is_enabled( string $type = '' ): bool {
			$general_settings = WPO_WCPDF()
				->get_instance( 'settings' )
				->get_instance( 'general' );

			if ( empty( $general_settings->get_setting( 'checkout_field_enable' ) ) ) {
				return false;
			}

			$types = $type ? array( $type ) : array_values( $this->get_field_types() );
			foreach ( $types as $field_type ) {
				if ( self::TYPE_VAT_NUMBER !== $field_type || $this->is_vat_number( $field_type ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check whether the checkout field is enabled in My Account.
		 *
		 * @return bool
		 */
		public function is_my_account_enabled(): bool {
			if ( ! $this->is_enabled() ) {
				return false;
			}

			$general_settings = WPO_WCPDF()
				->get_instance( 'settings' )
				->get_instance( 'general' );

			return ! empty(
				$general_settings->get_setting( 'checkout_field_enable_my_account' )
			);
		}

		/**
		 * Get the editable field type for a customer's saved billing country.
		 *
		 * @param int $user_id Customer ID.
		 * @return string Empty when the account field should not be displayed.
		 */
		public function get_account_type( int $user_id ): string {
			if ( ! $user_id || ! $this->is_my_account_enabled() ) {
				return '';
			}

			$customer = new \WC_Customer( $user_id );
			$country  = $customer->get_billing_country();
			$type     = $this->get_checkout_type( $country );

			return $this->is_allowed_country( $country ) && $this->is_enabled( $type ) ? $type : '';
		}

		/**
		 * Normalize a field type.
		 *
		 * @param string $type Field type.
		 * @return string
		 */
		private function normalize_type( string $type ): string {
			$type = sanitize_key( $type );

			return in_array( $type, $this->get_types(), true )
			? $type
			: self::TYPE_CUSTOM;
		}

		/**
		 * Get the original type of untyped values, independently of the current field.
		 *
		 * @return string
		 */
		private function get_legacy_type(): string {
			$type = get_option( 'wpo_ips_checkout_field_legacy_type', false );

			if ( false === $type ) {
				$settings = get_option( 'wpo_wcpdf_settings_general', array() );
				$type     = ! empty( $settings['checkout_field_as_vat_number'] )
					? self::TYPE_VAT_NUMBER
					: self::TYPE_CUSTOM;
			}

			return $this->normalize_type( (string) $type );
		}

		/**
		 * Get the legacy order checkout field value.
		 *
		 * @param \WC_Abstract_Order $order Order object.
		 * @param string             $type  Field type.
		 * @return string|null
		 */
		private function get_legacy_order_value( \WC_Abstract_Order $order, string $type ): ?string {
			// Legacy values retain their original type when the checkout setting changes.
			if ( $type !== $this->get_legacy_type() ) {
				return null;
			}

			$value = trim(
				(string) $order->get_meta(
					self::LEGACY_ORDER_META_KEY,
					true
				)
			);

			if ( '' === $value ) {
				return null;
			}

			return $value;
		}

		/**
		 * Get the legacy user checkout field value.
		 *
		 * @param int    $user_id User ID.
		 * @param string $type    Field type.
		 * @return string|null
		 */
		private function get_legacy_user_value( int $user_id, string $type ): ?string {
			if ( $type !== $this->get_legacy_type() ) {
				return null;
			}

			$value = trim(
				(string) get_user_meta(
					$user_id,
					self::LEGACY_USER_META_KEY,
					true
				)
			);

			if ( '' === $value ) {
				return null;
			}

			return $value;
		}

	}

	endif;
