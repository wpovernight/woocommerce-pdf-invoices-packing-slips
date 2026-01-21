<?php
namespace WPO\IPS\EDI;

use WPO\IPS\EDI\Standards\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\EDI\\Peppol' ) ) :

class Peppol {

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
		// Peppol My Account
		add_filter( 'woocommerce_account_menu_items', array( $this, 'peppol_account_menu_item' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'save_peppol_settings' ) );
		add_action( 'woocommerce_account_peppol_endpoint', array( $this, 'peppol_settings_account_page' ) );
		add_rewrite_endpoint( 'peppol', EP_PAGES );

		// Peppol Checkout
		if ( wpo_wcpdf_checkout_is_block() ) {
			$this->peppol_display_checkout_block_fields();
			$this->peppol_set_checkout_block_fields_value();
			add_action( 'woocommerce_set_additional_field_value', array( $this, 'peppol_save_checkout_block_fields' ), 10, 4 );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'peppol_remove_order_checkout_block_fields_meta' ), 10, 1 );
		} else {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'peppol_display_classic_checkout_fields' ), 10, 1 );
			add_filter( 'woocommerce_checkout_get_value', array( $this, 'peppol_set_classic_checkout_fields_value' ), 10, 2 );
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'peppol_validate_classic_checkout_field_values' ), 10, 2 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'peppol_save_classic_checkout_fields' ), 10, 2 );
			add_action( 'wp_enqueue_scripts', array( $this, 'peppol_enqueue_classic_checkout_script' ), 20 );
		}
	}

	/**
	 * Add EDI Peppol Settings user account menu item
	 *
	 * @param array $items
	 * @param array $endpoints
	 * @return array
	 */
	public function peppol_account_menu_item( array $items, array $endpoints ): array {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'my_account' ) ) {
			return $items;
		}

		$last_key = array_key_last( $items );
		$position = array_search( $last_key, array_keys( $items ), true );

		return array_slice( $items, 0, $position, true )
			+ array( 'peppol' => 'Peppol' )
			+ array_slice( $items, $position, null, true );
	}

	/**
	 * Add EDI Peppol Settings to user account page.
	 *
	 * @return void
	 */
	public function peppol_settings_account_page(): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'my_account' ) ) {
			echo '<p>' . esc_html__( 'Peppol is not available.', 'woocommerce-pdf-invoices-packing-slips' ) . '</p>';
			return;
		}

		$user_id                = get_current_user_id();
		$endpoint_id            = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
		$endpoint_eas           = (string) get_user_meta( $user_id, 'peppol_endpoint_eas', true );

		$input_mode             = wpo_ips_edi_peppol_identifier_input_mode();
		$endpoint_id_value      = $endpoint_id;
		$eas_options            = array();

		// In "full" mode we show scheme:identifier directly in the text inputs.
		if ( 'full' === $input_mode ) {
			if ( '' !== $endpoint_eas && '' !== $endpoint_id ) {
				$endpoint_id_value = "{$endpoint_eas}:{$endpoint_id}";
			}

		// In "select" mode we show the scheme in a dropdown and the identifier in a separate text input.
		} elseif ( 'select' === $input_mode ) {
			foreach ( EN16931::get_eas() as $code => $label ) {
				$eas_options[ $code ] = "[$code] $label";
			}
		}
		?>
		<form method="post">
			<h3><?php esc_html_e( 'Peppol Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="peppol_endpoint_id"><?php esc_html_e( 'Peppol Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
				<input type="text" class="woocommerce-Input input-text" name="peppol_endpoint_id" id="peppol_endpoint_id" value="<?php echo esc_attr( $endpoint_id_value ); ?>" />
				<small>
					<?php
						$description = __( 'Specify the Peppol Endpoint ID.', 'woocommerce-pdf-invoices-packing-slips' );

						// Add example if using full mode
						if ( 'select' !== $input_mode ) {
							$description .= ' <em>' . esc_html__( 'Example: 0088:123456789', 'woocommerce-pdf-invoices-packing-slips' ) . '</em>';
						}

						$description .= '<br>' . sprintf(
							/* translators: %1$s: open link anchor, %2$s: close link anchor */
							__( 'If you don\'t know the ID, you can search for it in the %1$sPeppol Directory%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://directory.peppol.eu/public" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);

						echo wp_kses_post( $description );
					?>
				</small>
			</p>

			<?php if ( 'select' === $input_mode ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="peppol_endpoint_eas"><?php esc_html_e( 'Peppol Endpoint Scheme (EAS)', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
					<select name="peppol_endpoint_eas" id="peppol_endpoint_eas" class="woocommerce-Input input-select">
						<option value=""><?php echo esc_html__( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...'; ?></option>
						<?php foreach ( $eas_options as $code => $label ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $endpoint_eas, $code ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<small>
						<?php
							echo wp_kses_post( sprintf(
								'%s<br>%s',
								__( 'Specify the Electronic Address Scheme (EAS) for the Endpoint above.', 'woocommerce-pdf-invoices-packing-slips' ),
								sprintf(
									/* translators: %1$s: open link anchor, %2$s: close link anchor */
									__( 'For more information on each Endpoint Address Scheme (EAS), refer to the %1$sofficial Peppol EAS list%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
									'<a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/eas/" target="_blank">',
									'</a>'
								)
							) );
						?>
					</small>
				</p>
			<?php endif; ?>
			
			<p>
				<input type="hidden" name="wc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpo_ips_edi_user_save_peppol_settings' ) ); ?>">
				<button type="submit" name="save_peppol_settings" class="woocommerce-Button button"><?php esc_html_e( 'Save changes', 'woocommerce-pdf-invoices-packing-slips' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Save EDI Peppol settings to user profile.
	 *
	 * @return void
	 */
	public function save_peppol_settings(): void {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
		if ( empty( $request_method ) || 'POST' !== $request_method || ! isset( $_POST['save_peppol_settings'] ) ) {
			return;
		}

		// Validate nonce and auth
		$nonce = isset( $_POST['wc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wc_nonce'] ) ) : '';
		if ( ! is_user_logged_in() || ! wp_verify_nonce( $nonce, 'wpo_ips_edi_user_save_peppol_settings' ) ) {
			wc_add_notice( __( 'Peppol settings could not be saved. Please try again.', 'woocommerce-pdf-invoices-packing-slips' ), 'error' );
			wp_safe_redirect( wc_get_account_endpoint_url( 'peppol' ) );
			exit;
		}

		$request = stripslashes_deep( $_POST );
		
		// Maybe validate
		if ( ! empty( $request['peppol_endpoint_id'] ) ) {
			$result = $this->peppol_validate_identifier_value( $request['peppol_endpoint_id'] );

			if ( is_wp_error( $result ) ) {
				wc_add_notice( $result->get_error_message(), 'error' );
				return;
			}
		}
		
		$user_id = get_current_user_id();

		wpo_ips_edi_peppol_save_customer_identifiers( $user_id, $request );

		wc_add_notice( __( 'Peppol settings saved.', 'woocommerce-pdf-invoices-packing-slips' ), 'success' );
		wp_safe_redirect( wc_get_account_endpoint_url( 'peppol' ) );
		exit;
	}

	/**
	 * Display EDI Peppol fields in the Checkout Block.
	 *
	 * @return void
	 */
	public function peppol_display_checkout_block_fields(): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return;
		}

		$input_mode      = wpo_ips_edi_peppol_identifier_input_mode();
		$visibility_mode = $this->peppol_checkout_visibility_mode();

		$can_use_hidden = ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.9.0', '>=' ) );

		// Register toggle field only when configured + supported.
		if ( $can_use_hidden && 'toggle' === $visibility_mode ) {
			woocommerce_register_additional_checkout_field(
				array(
					'id'       => 'wpo-ips-edi/peppol-invoice',
					'label'    => __( 'I need a Peppol invoice (business purchase)', 'woocommerce-pdf-invoices-packing-slips' ),
					'location' => 'order',
					'type'     => 'checkbox',
					'sanitize_callback' => static function ( $val ) {
						return (bool) $val;
					},
					'validate_callback' => static function ( $val ) {
						return true;
					},
				)
			);
		}

		$conditional_hidden = ( $can_use_hidden ) ? $this->peppol_checkout_block_hidden_condition() : array();

		// Endpoint ID
		$args = array(
			'id'                => 'wpo-ips-edi/peppol-endpoint-id',
			'label'             => __( 'Peppol identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'location'          => 'order',
			'type'              => 'text',
			'sanitize_callback' => static function ( $val ) {
				return preg_replace( '/\s+/', '', trim( (string) $val ) );
			},
			'validate_callback' => function ( $val ) {
				$result = $this->peppol_validate_identifier_value( (string) $val );
				return is_wp_error( $result ) ? $result : true;
			},
		);

		if ( ! empty( $conditional_hidden ) ) {
			$args['hidden'] = $conditional_hidden;
		}

		woocommerce_register_additional_checkout_field( $args );

		// EAS
		if ( 'select' === $input_mode ) {
			$eas = EN16931::get_eas();

			$args = array(
				'id'       => 'wpo-ips-edi/peppol-endpoint-eas',
				'label'    => __( 'Endpoint Scheme (EAS)', 'woocommerce-pdf-invoices-packing-slips' ),
				'location' => 'order',
				'type'     => 'select',
				'options'  => array_map(
					static fn ( $code, $label ) => array(
						'value' => $code,
						'label' => "[$code] $label",
					),
					array_keys( $eas ),
					$eas
				),
				'validate_callback' => static function ( $val ) use ( $eas ) {
					if ( $val && ! isset( $eas[ $val ] ) ) {
						return new \WP_Error( 'invalid_eas', __( 'Invalid Endpoint Scheme.', 'woocommerce-pdf-invoices-packing-slips' ) );
					}
					return true;
				},
			);

			if ( ! empty( $conditional_hidden ) ) {
				$args['hidden'] = $conditional_hidden;
			}

			woocommerce_register_additional_checkout_field( $args );
		}
	}

	/**
	 * Set default values for EDI Peppol fields in the Checkout Block.
	 *
	 * @return void
	 */
	public function peppol_set_checkout_block_fields_value(): void {
		$fields = array(
			'wpo-ips-edi/peppol-endpoint-id',
			'wpo-ips-edi/peppol-endpoint-eas',
		);

		$visibility_mode = $this->peppol_checkout_visibility_mode();
		$can_use_hidden  = ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.9.0', '>=' ) );

		if ( $can_use_hidden && 'toggle' === $visibility_mode ) {
			array_unshift( $fields, 'wpo-ips-edi/peppol-invoice' );
		}

		foreach ( $fields as $field ) {
			add_filter( "woocommerce_get_default_value_for_{$field}", array( $this, 'peppol_prefill_checkout_block_field_from_user_meta' ), 10, 3 );
		}
	}

	/**
	 * Provide a default value for the Checkout Block additional field
	 * using the value we store in user meta.
	 *
	 * @param null     $value     Current default (usually empty).
	 * @param string   $group     'billing' | 'shipping' | 'other'. Our field is in the 'order' location, so this will be 'other'.
	 * @param \WC_Data $wc_object Object for which the default is being requested.
	 *
	 * @return string
	 */
	public function peppol_prefill_checkout_block_field_from_user_meta( $value, string $group, \WC_Data $wc_object ): string {
		if ( 'other' !== $group || ! $wc_object instanceof \WC_Customer ) {
			return (string) $value;
		}

		$user_id = $wc_object->get_id();
		if ( ! $user_id ) {
			return (string) $value;
		}

		$key      = str_replace( 'woocommerce_get_default_value_for_', '', current_filter() );
		$meta_key = str_replace( '-', '_', substr( $key, strlen( 'wpo-ips-edi/' ) ) );

		// Auto-enable toggle if we already have a stored identifier.
		if ( 'peppol_invoice' === $meta_key ) {
			$id             = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
			$has_identifier = ( '' !== $id );

			return $has_identifier ? '1' : '';
		}

		$input_mode = wpo_ips_edi_peppol_identifier_input_mode();

		// If we’re in 'full' mode, compose scheme:identifier for the *text* fields.
		if ( 'full' === $input_mode ) {
			if ( 'peppol_endpoint_id' === $meta_key ) {
				$id  = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
				$eas = (string) get_user_meta( $user_id, 'peppol_endpoint_eas', true );
				return ( '' !== $eas && '' !== $id ) ? "{$eas}:{$id}" : (string) $value;
			}
		}

		// For other fields, just return the user meta value.
		return (string) get_user_meta( $user_id, $meta_key, true );
	}

	/**
	 * Save EDI Peppol fields from Checkout Block.
	 *
	 * @param string $key Field key.
	 * @param mixed $value Field value.
	 * @param string $group Group name.
	 * @param object $wc_object WC object (e.g. order).
	 * @return void
	 */
	public function peppol_save_checkout_block_fields( string $key, $value, string $group, object $wc_object ): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return;
		}

		$allowed = array(
			'wpo-ips-edi/peppol-endpoint-id',
			'wpo-ips-edi/peppol-endpoint-eas',
		);

		if ( ! in_array( $key, $allowed, true ) ) {
			return;
		}

		$meta_key = str_replace( '-', '_', substr( $key, strlen( 'wpo-ips-edi/' ) ) );
		$value    = trim( sanitize_text_field( wp_unslash( $value ) ) );

		$customer_id = is_callable( array( $wc_object, 'get_customer_id' ) )
			? absint( $wc_object->get_customer_id() )
			: 0;

		wpo_ips_edi_peppol_save_customer_identifiers( $customer_id, array( $meta_key => $value ) );
		
		if ( $wc_object instanceof \WC_Order ) {
			wpo_ips_edi_maybe_save_order_peppol_data( $wc_object, array( $meta_key => $value ) );
		}
	}

	/**
	 * Remove EDI Peppol fields from order meta after checkout.
	 *
	 * @param \WC_Abstract_Order $order
	 * @return void
	 */
	public function peppol_remove_order_checkout_block_fields_meta( \WC_Abstract_Order $order ): void {
		$fields = array(
			'wpo-ips-edi/peppol-endpoint-id',
			'wpo-ips-edi/peppol-endpoint-eas',
		);

		foreach ( $fields as $field ) {
			$order->delete_meta_data( '_wc_other/' . $field );
		}

		$order->save_meta_data();
	}

	/**
	 * Display EDI Peppol fields on the Classic Checkout page.
	 *
	 * @param mixed $fields Checkout fields.
	 * @return array Modified checkout fields with Peppol fields added.
	 */
	public function peppol_display_classic_checkout_fields( $fields ): array {
		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return $fields;
		}

		$input_mode       = wpo_ips_edi_peppol_identifier_input_mode();
		$visibility_mode  = $this->peppol_checkout_visibility_mode();

		$placeholder_endpoint = ( 'select' !== $input_mode ) ? '0088:123456789' : '123456789';

		$peppol_fields = array();

		// Toggle checkbox only in toggle mode.
		if ( 'toggle' === $visibility_mode ) {
			$peppol_fields['peppol_invoice'] = array(
				'type'     => 'checkbox',
				'label'    => __( 'I need a Peppol invoice (business purchase)', 'woocommerce-pdf-invoices-packing-slips' ),
				'required' => false,
				'class'    => array( 'form-row-wide' ),
			);
		}

		$conditional_class = array( 'form-row-wide' );

		if ( 'toggle' === $visibility_mode ) {
			$conditional_class[] = 'wpo-ips-peppol-conditional';
		} elseif ( 'company' === $visibility_mode ) {
			$conditional_class[] = 'wpo-ips-peppol-company-conditional';
		}

		$peppol_fields['peppol_endpoint_id'] = array(
			'type'        => 'text',
			'label'       => __( 'Peppol identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'required'    => false,
			'class'       => $conditional_class,
			'placeholder' => $placeholder_endpoint,
		);

		if ( 'select' === $input_mode ) {
			$peppol_fields['peppol_endpoint_eas'] = array(
				'type'     => 'select',
				'label'    => __( 'Peppol Endpoint Scheme (EAS)', 'woocommerce-pdf-invoices-packing-slips' ),
				'required' => false,
				'class'    => $conditional_class,
				'options'  => ( function () {
					$options = array( '' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...' );
					foreach ( EN16931::get_eas() as $code => $label ) {
						$options[ $code ] = "[$code] $label";
					}
					return $options;
				} )(),
			);
		}

		$fields['order'] = $peppol_fields + ( $fields['order'] ?? array() );

		return $fields;
	}

	/**
	 * Set EDI Peppol fields values in the Classic Checkout page.
	 *
	 * @param null $value Current value.
	 * @param string $input Input name.
	 * @return mixed Modified value.
	 */
	public function peppol_set_classic_checkout_fields_value( $value, string $input ) {
		if ( ! in_array( $input, array(
			'peppol_invoice',
			'peppol_endpoint_id',
			'peppol_endpoint_eas',
		), true ) ) {
			return $value;
		}

		$visibility_mode = $this->peppol_checkout_visibility_mode();

		if ( 'peppol_invoice' === $input && 'toggle' !== $visibility_mode ) {
			return $value;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $value;
		}

		$endpoint_id  = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
		$endpoint_eas = (string) get_user_meta( $user_id, 'peppol_endpoint_eas', true );

		$input_mode = wpo_ips_edi_peppol_identifier_input_mode();

		switch ( $input ) {
			case 'peppol_invoice':
				return ( '' !== $endpoint_id ) ? '1' : '';
			case 'peppol_endpoint_id':
				if ( 'full' === $input_mode && '' !== $endpoint_eas && '' !== $endpoint_id ) {
					return "{$endpoint_eas}:{$endpoint_id}";
				}
				return $endpoint_id;
			case 'peppol_endpoint_eas':
				return $endpoint_eas;
		}

		return $value;
	}

	/**
	 * Validate Peppol Endpoint / Legal‑ID pairs after WooCommerce
	 * has normalised and sanitised all checkout data.
	 *
	 * @param mixed $data   All posted checkout fields.
	 * @param mixed $errors Errors object to add validation errors to.
	 * @return void
	 */
	public function peppol_validate_classic_checkout_field_values( $data, $errors ): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) || ! $errors instanceof \WP_Error ) {
			return;
		}
		
		if ( ! is_array( $data ) ) {
			return;
		}

		// Endpoint ID
		if ( ! empty( $data['peppol_endpoint_id'] ) ) {
			$result = $this->peppol_validate_identifier_value( $data['peppol_endpoint_id'] );

			if ( is_wp_error( $result ) ) {
				$errors->add(
					$result->get_error_code(),
					$result->get_error_message(),
					array( 'id' => 'peppol_endpoint_id' )
				);
			}
		}
	}

	/**
	 * Save EDI Peppol fields from Classic Checkout page.
	 *
	 * @param int $order_id Order ID.
	 * @param array $data Checkout data.
	 * @return void
	 */
	public function peppol_save_classic_checkout_fields( int $order_id, array $data ): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return;
		}

		$visibility_mode = $this->peppol_checkout_visibility_mode();

		$has_company = ! empty( $data['billing_company'] );
		$has_id      = ! empty( $data['peppol_endpoint_id'] );

		if ( 'toggle' === $visibility_mode ) {
			$wants_peppol = ! empty( $data['peppol_invoice'] ) && $has_id;
		} elseif ( 'company' === $visibility_mode ) {
			$wants_peppol = $has_company && $has_id;
		} else {
			// always
			$wants_peppol = $has_id;
		}

		if ( ! $wants_peppol ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( empty( $order ) ) {
			return;
		}

		$customer_id = is_callable( array( $order, 'get_customer_id' ) )
			? absint( $order->get_customer_id() )
			: 0;

		wpo_ips_edi_peppol_save_customer_identifiers( $customer_id, $data );

		wpo_ips_edi_maybe_save_order_peppol_data( $order, $data );
	}
	
	/**
	 * Enqueue Peppol script for Classic Checkout page.
	 *
	 * @return void
	 */
	public function peppol_enqueue_classic_checkout_script(): void {
		$visibility_mode = $this->peppol_checkout_visibility_mode();

		if ( 'always' === $visibility_mode ) {
			return;
		}

		if (
			! function_exists( 'is_checkout' ) ||
			! is_checkout() ||
			! wpo_ips_edi_peppol_enabled_for_location( 'checkout' )
		) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'wpo-ips-peppol-checkout',
			WPO_WCPDF()->plugin_url() . '/assets/js/peppol-classic-checkout' . $suffix . '.js',
			array( 'jquery' ),
			WPO_WCPDF_VERSION,
			true
		);

		wp_localize_script(
			'wpo-ips-peppol-checkout',
			'wpoIpsPeppol',
			array(
				'visibilityMode' => $visibility_mode, // always|toggle|company
			)
		);
	}
	
	/**
	 * Get the configured visibility mode for the Peppol checkout fields.
	 *
	 * @return string One of: 'always', 'toggle', 'company'.
	 */
	private function peppol_checkout_visibility_mode(): string {
		$visibility_mode = (string) wpo_ips_edi_get_settings( 'peppol_endpoint_id_checkout_visibility' );
		$allowed         = array( 'always', 'toggle', 'company' );

		if ( ! in_array( $visibility_mode, $allowed, true ) ) {
			$visibility_mode = 'always';
		}

		return $visibility_mode;
	}
	
	/**
	 * Build the Checkout Block "hidden" condition for the Peppol fields.
	 * 
	 * @link https://developer.woocommerce.com/docs/block-development/tutorials/how-to-conditional-additional-fields/
	 *
	 * @return array
	 */
	private function peppol_checkout_block_hidden_condition(): array {
		$visibility_mode = $this->peppol_checkout_visibility_mode();

		if ( 'toggle' === $visibility_mode ) {
			return array(
				'checkout' => array(
					'properties' => array(
						'additional_fields' => array(
							'properties' => array(
								'wpo-ips-edi/peppol-invoice' => array(
									'not' => array(
										'const' => true,
									),
								),
							),
						),
					),
				),
			);
		}

		if ( 'company' === $visibility_mode ) {
			// Hide while company is empty.
			return array(
				'customer' => array(
					'properties' => array(
						'billing_address' => array(
							'properties' => array(
								'company' => array(
									'maxLength' => 0,
								),
							),
						),
					),
				),
			);
		}

		// always
		return array();
	}

	/**
	 * Validate a Peppol identifier value.
	 *
	 * @param string $raw_value Raw user input.
	 * @return true|\WP_Error True if valid or should be accepted, WP_Error if invalid.
	 */
	private function peppol_validate_identifier_value( string $raw_value ) {
		$val = preg_replace( '/\s+/', '', trim( (string) $raw_value ) );

		// Let "required" or other validation handle this elsewhere.
		if ( '' === $val ) {
			return true;
		}

		$input_mode               = wpo_ips_edi_peppol_identifier_input_mode();
		$has_scheme               = ( false !== strpos( $val, ':' ) );
		$use_directory_validation = (bool) wpo_ips_edi_get_settings( 'peppol_directory_validation' );
		$directory_url            = 'https://directory.peppol.eu/';

		// If input mode is not "full", we do not enforce "scheme:value" here.
		if ( 'full' !== $input_mode ) {
			return true;
		}

		// Directory validation disabled
		if ( ! $use_directory_validation ) {
			if ( ! $has_scheme ) {
				return new \WP_Error(
					'peppol_format_invalid',
					__( 'The identifier must be in "scheme:value" format (for example 0088:123456789).', 'woocommerce-pdf-invoices-packing-slips' )
				);
			}

			return true;
		}

		// Directory validation enabled
		$result = $this->peppol_directory_lookup( $val );

		if ( is_wp_error( $result ) ) {
			if ( 'peppol_empty_endpoint' === $result->get_error_code() ) {
				return new \WP_Error(
					'peppol_empty_endpoint',
					__( 'Peppol Endpoint ID is empty.', 'woocommerce-pdf-invoices-packing-slips' )
				);
			}

			// Network/response errors: do not block checkout.
			return true;
		}

		$matches     = isset( $result['matches'] ) && is_array( $result['matches'] ) ? $result['matches'] : array();
		$search_meta = isset( $result['search'] ) && is_array( $result['search'] ) ? $result['search'] : array();

		$used_fallback = ! empty( $search_meta['used_fallback'] );

		/**
		 * No scheme provided (no ":").
		 *
		 * We always warn that the scheme is required, but still show any found
		 * participants as hints.
		 */
		if ( ! $has_scheme ) {
			$message = sprintf(
				/* translators: 1: entered identifier, 2: Peppol Directory URL */
				__(
					'The identifier "%1$s" was found without a scheme. Please enter it in "scheme:value" format. You can search for the correct scheme and identifier in the %2$s.',
					'woocommerce-pdf-invoices-packing-slips'
				),
				esc_html( $val ),
				'<a href="' . esc_url( $directory_url ) . '" target="_blank" rel="noopener noreferrer">' . __( 'Peppol Directory', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
			);

			if ( ! empty( $matches ) ) {
				$message .= $this->peppol_directory_render_matches_list( $matches, $val );
			}

			return new \WP_Error(
				'peppol_directory_scheme_required',
				$message
			);
		}

		/**
		 * Scheme + value provided.
		 */

		// No matches at all (for full query and fallback).
		if ( empty( $matches ) ) {
			$message = sprintf(
				/* translators: 1: entered identifier, 2: Peppol Directory URL */
				__(
					'No Peppol participant was found for "%1$s". Please confirm the scheme and identifier in the %2$s.',
					'woocommerce-pdf-invoices-packing-slips'
				),
				esc_html( $val ),
				'<a href="' . esc_url( $directory_url ) . '" target="_blank" rel="noopener noreferrer">' . __( 'Peppol Directory', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
			);

			return new \WP_Error(
				'peppol_directory_no_match',
				$message
			);
		}

		// If we did not use fallback, the full "scheme:value" query found matches, accept silently.
		if ( ! $used_fallback ) {
			return true;
		}

		// We used fallback (value-only), so "scheme:value" had no hits.
		// Show alternatives based on value-only search and warn about possible wrong scheme.
		$message  = sprintf(
			/* translators: 1: entered identifier, 2: Peppol Directory URL */
			__(
				'We could not find a Peppol participant with this scheme and identifier. Please check the scheme or search in the %2$s. Below are participants found for this identifier value:',
				'woocommerce-pdf-invoices-packing-slips'
			),
			esc_html( $val ),
			'<a href="' . esc_url( $directory_url ) . '" target="_blank" rel="noopener noreferrer">' . __( 'Peppol Directory', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
		);
		$message .= $this->peppol_directory_render_matches_list( $matches, $val );

		return new \WP_Error(
			'peppol_directory_similar_found',
			$message
		);
	}

	/**
	 * Query the Peppol Directory for an endpoint.
	 *
	 * @param string $endpoint_id
	 * @return array|\WP_Error
	 */
	private function peppol_directory_lookup( string $endpoint_id ) {
		$endpoint_id = trim( (string) $endpoint_id );

		if ( '' === $endpoint_id ) {
			return new \WP_Error(
				'peppol_empty_endpoint',
				__( 'Peppol Endpoint ID is empty.', 'woocommerce-pdf-invoices-packing-slips' )
			);
		}

		$has_colon      = ( false !== strpos( $endpoint_id, ':' ) );
		$primary_query  = $endpoint_id;
		$fallback_query = '';
		$used_fallback  = false;

		// If we have "scheme:value", fallback query will be just "value".
		if ( $has_colon ) {
			list( , $fallback_query ) = explode( ':', $endpoint_id, 2 );
			$fallback_query = trim( $fallback_query );
		}

		// First attempt: full query (can be scheme:value, value, or name).
		$data = $this->peppol_directory_request( $primary_query );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$matches = isset( $data['matches'] ) && is_array( $data['matches'] ) ? $data['matches'] : array();

		// Fallback: if we had "scheme:value" and got no matches, try "value" only.
		if ( $has_colon && empty( $matches ) && '' !== $fallback_query ) {
			$data = $this->peppol_directory_request( $fallback_query );

			if ( is_wp_error( $data ) ) {
				return $data;
			}

			$matches      = isset( $data['matches'] ) && is_array( $data['matches'] ) ? $data['matches'] : array();
			$used_fallback = true;
		}

		$normalized_matches = array();

		foreach ( $matches as $match ) {
			$participant_value = $match['participantID']['value'] ?? '';

			$entity     = isset( $match['entities'][0] ) && is_array( $match['entities'][0] ) ? $match['entities'][0] : array();
			$name_entry = isset( $entity['name'][0] )    && is_array( $entity['name'][0] )    ? $entity['name'][0]    : array();

			$name     = $name_entry['name']     ?? '';
			$language = $name_entry['language'] ?? '';
			$country  = $entity['countryCode']  ?? '';
			$reg_date = $entity['regDate']      ?? '';

			// Collect all identifier values we can find (participant + entity identifiers).
			$identifier_values = array();

			if ( '' !== $participant_value ) {
				$identifier_values[] = $participant_value;
			}

			if ( ! empty( $entity['identifiers'] ) && is_array( $entity['identifiers'] ) ) {
				foreach ( $entity['identifiers'] as $identifier ) {
					if ( ! empty( $identifier['value'] ) ) {
						$identifier_values[] = $identifier['value'];
					}
				}
			}

			$identifier_values = array_values( array_unique( $identifier_values ) );

			$normalized_matches[] = array(
				'value'       => $participant_value,
				'identifiers' => $identifier_values,
				'name'        => $name,
				'language'    => $language,
				'country'     => $country,
				'reg_date'    => $reg_date,
			);
		}

		return array(
			'total'   => isset( $data['total-result-count'] ) ? (int) $data['total-result-count'] : count( $normalized_matches ),
			'matches' => $normalized_matches,
			'search'  => array(
				'query'          => $primary_query,
				'fallback_query' => $fallback_query,
				'used_fallback'  => $used_fallback,
			),
		);
	}

	/**
	 * Perform a Peppol Directory request using the generic "q" parameter.
	 *
	 * @param string $query
	 * @return array|\WP_Error
	 */
	private function peppol_directory_request( string $query ) {
		$base_url = 'https://directory.peppol.eu/search/1.0/json';

		$query_args = array(
			'q'        => $query,
			'beautify' => 'true',
		);

		$url = add_query_arg( $query_args, $base_url );

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 5,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'peppol_directory_request_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'Peppol Directory request failed: %s', 'woocommerce-pdf-invoices-packing-slips' ),
					$response->get_error_message()
				)
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return new \WP_Error(
				'peppol_directory_unexpected_status',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Peppol Directory returned an unexpected status code: %d', 'woocommerce-pdf-invoices-packing-slips' ),
					$code
				)
			);
		}

		$data = json_decode( $body, true );

		if ( null === $data || ! is_array( $data ) ) {
			return new \WP_Error(
				'peppol_directory_invalid_response',
				__( 'Peppol Directory returned an invalid JSON response.', 'woocommerce-pdf-invoices-packing-slips' )
			);
		}

		return $data;
	}

	/**
	 * Render Peppol Directory matches as a simple text list.
	 *
	 * @param array  $matches
	 * @param string $endpoint
	 * @return string HTML string.
	 */
	private function peppol_directory_render_matches_list( array $matches, string $endpoint ): string {
		if ( empty( $matches ) ) {
			return '';
		}

		ob_start();
		?>
		<p style="margin-top:5px;">
			<?php
				echo esc_html(
					sprintf(
						/* translators: %s: endpoint ID */
						__( 'We found the following participant(s) related to "%s":', 'woocommerce-pdf-invoices-packing-slips' ),
						$endpoint
					)
				);
			?>
		</p>
		<ul class="wpo-ips-edi-peppol-directory-result-list">
			<?php foreach ( $matches as $match ) : ?>
				<?php
					$name        = isset( $match['name'] ) ? (string) $match['name'] : '';
					$value       = isset( $match['value'] ) ? (string) $match['value'] : '';
					$identifiers = ( ! empty( $match['identifiers'] ) && is_array( $match['identifiers'] ) ) ? $match['identifiers'] : array();

					// Prefer the participantID value, fall back to the first identifier.
					$identifier = ( '' !== $value ) ? $value : ( ( ! empty( $identifiers ) ) ? (string) reset( $identifiers ) : '' );

					if ( '' !== $name && '' !== $identifier ) {
						$item = sprintf( '%1$s (%2$s)', $name, $identifier );
					} elseif ( '' !== $identifier ) {
						$item = $identifier;
					} elseif ( '' !== $name ) {
						$item = $name;
					} else {
						$item = '';
					}

					if ( '' === $item ) {
						continue;
					}
				?>
				<li><?php echo esc_html( $item ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php

		return (string) ob_get_clean();
	}

}

endif; // class_exists
