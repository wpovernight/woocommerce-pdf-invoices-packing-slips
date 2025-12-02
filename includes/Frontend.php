<?php
namespace WPO\IPS;

use WPO\IPS\EDI\Standards\EN16931;

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
		
		// Peppol My Account
		add_filter( 'woocommerce_account_menu_items', array( $this, 'edi_peppol_account_menu_item' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'edi_save_peppol_settings' ) );
		add_action( 'woocommerce_account_peppol_endpoint', array( $this, 'edi_peppol_settings_account_page' ) );
		add_rewrite_endpoint( 'peppol', EP_PAGES );
		
		// Peppol Checkout
		if ( wpo_wcpdf_checkout_is_block() ) {
			$this->edi_peppol_display_checkout_block_fields();
			$this->edi_peppol_set_checkout_block_fields_value();
			add_action( 'woocommerce_set_additional_field_value', array( $this, 'edi_peppol_save_checkout_block_fields' ), 10, 4 );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'edi_peppol_remove_order_checkout_block_fields_meta' ), 10, 1 );
		} else {
			add_filter( 'woocommerce_checkout_fields', array( $this, 'edi_peppol_display_classic_checkout_fields' ), 10, 1 );
			add_filter( 'woocommerce_checkout_get_value', array( $this, 'edi_peppol_set_classic_checkout_fields_value' ), 10, 2 );
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'edi_peppol_validate_classic_checkout_field_values' ), 10, 2 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'edi_peppol_save_classic_checkout_fields' ), 10, 2 );
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
		add_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	/**
	 * Restore the original document settings storing behavior.
	 * This should be called after disabling storing settings to avoid affecting other parts of the code.
	 * 
	 * @return void
	 */
	public function restore_storing_document_settings(): void {
		remove_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	/**
	 * Callback function to return false, used to disable storing document settings.
	 * 
	 * @return bool
	 */
	public function return_false(): bool {
		return false;
	}
	
	/**
	 * Add EDI Peppol Settings user account menu item
	 * 
	 * @param array $items
	 * @param array $endpoints
	 * @return array
	 */
	public function edi_peppol_account_menu_item( array $items, array $endpoints ): array {
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
	public function edi_peppol_settings_account_page(): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'my_account' ) ) {
			echo '<p>' . esc_html__( 'Peppol is not available.', 'woocommerce-pdf-invoices-packing-slips' ) . '</p>';
			return;
		}

		$user_id                = get_current_user_id();
		$endpoint_id            = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
		$endpoint_eas           = (string) get_user_meta( $user_id, 'peppol_endpoint_eas', true );
		$legal_identifier       = (string) get_user_meta( $user_id, 'peppol_legal_identifier', true );
		$legal_identifier_icd   = (string) get_user_meta( $user_id, 'peppol_legal_identifier_icd', true );
		
		$input_mode             = wpo_ips_edi_peppol_identifier_input_mode();
		$endpoint_id_value      = $endpoint_id;
		$legal_identifier_value = $legal_identifier;
		$eas_options            = array();
		$icd_options            = array();
		
		// In "full" mode we show scheme:identifier directly in the text inputs.
		if ( 'full' === $input_mode ) {
			if ( '' !== $endpoint_eas && '' !== $endpoint_id ) {
				$endpoint_id_value = "{$endpoint_eas}:{$endpoint_id}";
			}
			if ( '' !== $legal_identifier_icd && '' !== $legal_identifier ) {
				$legal_identifier_value = "{$legal_identifier_icd}:{$legal_identifier}";
			}
		
		// In "select" mode we show the scheme in a dropdown and the identifier in a separate text input.
		} elseif ( 'select' === $input_mode ) {
			foreach ( EN16931::get_eas() as $code => $label ) {
				$eas_options[ $code ] = "[$code] $label";
			}
			foreach ( EN16931::get_icd() as $code => $label ) {
				$icd_options[ $code ] = "[$code] $label";
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
						<option value=""><?php esc_html_e( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...'; ?></option>
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
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="peppol_legal_identifier"><?php esc_html_e( 'Peppol Legal Identifier', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
				<input type="text" class="woocommerce-Input input-text" name="peppol_legal_identifier" id="peppol_legal_identifier" value="<?php echo esc_attr( $legal_identifier_value ); ?>" />
				<small>
					<?php
						$description = __( 'Specify the Peppol Legal Identifier.', 'woocommerce-pdf-invoices-packing-slips' );

						// Show example only in full mode
						if ( 'select' !== $input_mode ) {
							$description .= ' <em>' . esc_html__( 'Example: 0208:1234567890', 'woocommerce-pdf-invoices-packing-slips' ) . '</em>';
						}

						$description .= '<br>' . sprintf(
							/* translators: %1$s: open link, %2$s: close link */
							__( 'If you don\'t know the Identifier, you can search for it in the %1$sPeppol Directory%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://directory.peppol.eu/public" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);

						echo wp_kses_post( $description );
					?>
				</small>
			</p>
			
			<?php if ( 'select' === $input_mode ) : ?>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="peppol_legal_identifier_icd"><?php esc_html_e( 'Peppol Legal Identifier Scheme (ICD)', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
					<select name="peppol_legal_identifier_icd" id="peppol_legal_identifier_icd" class="woocommerce-Input input-select">
						<option value=""><?php echo esc_html__( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...'; ?></option>
						<?php foreach ( $icd_options as $code => $label ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $legal_identifier_icd, $code ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<small>
						<?php
							echo wp_kses_post( sprintf(
								'%s<br>%s',
								__( 'Specify the Peppol Legal Identifier Scheme (ICD) for the Identifier above.', 'woocommerce-pdf-invoices-packing-slips' ),
								sprintf(
									/* translators: %1$s: open link anchor, %2$s: close link anchor */
									__( 'For detailed information on each Identification Code (ICD), see the %1$sofficial Peppol ICD list%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
									'<a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/ICD/" target="_blank">',
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
	public function edi_save_peppol_settings(): void {
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
		$user_id = get_current_user_id();
		
		wpo_ips_edi_peppol_save_customer_identifiers( $user_id, $request );
		
		wc_add_notice( __( 'Peppol settings saved.', 'woocommerce-pdf-invoices-packing-slips' ), 'success' );
		wp_safe_redirect( wc_get_account_endpoint_url( 'peppol' ) );
		exit;
	}
	
	/**
	 * Display EDI Peppol fields in the Checkout Block.
	 * 
	 * @return void
	 */
	public function edi_peppol_display_checkout_block_fields(): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return;
		}

		$input_mode = wpo_ips_edi_peppol_identifier_input_mode();

		woocommerce_register_additional_checkout_field(
			array(
				'id'                => 'wpo-ips-edi/peppol-endpoint-id',
				'label'             => __( 'Peppol Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ),
				'location'          => 'order',
				'type'              => 'text',
				'sanitize_callback' => function ( $val ) {
					return preg_replace( '/\s+/', '', trim( (string) $val ) );
				},
				'validate_callback' => function ( $val ) {
					$result = $this->peppol_validate_identifier_value( $val );

					if ( is_wp_error( $result ) ) {
						return $result;
					}

					return true;
				},
			)
		);

		if ( 'select' === $input_mode ) {
			$eas = EN16931::get_eas();
			woocommerce_register_additional_checkout_field(
				array(
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
					'validate_callback' => function ( $val ) {
						if ( $val && ! isset( $eas[ $val ] ) ) {
							return new \WP_Error( 'invalid_eas', __( 'Invalid Endpoint Scheme.', 'woocommerce-pdf-invoices-packing-slips' ) );
						}
						return true;
					},
				)
			);
		}

		// // We chose not to display the Legal Identifier at Checkout; it's only shown in My Account for now. This may change later.
		// woocommerce_register_additional_checkout_field(
		// 	array(
		// 		'id'                => 'wpo-ips-edi/peppol-legal-identifier',
		// 		'label'             => __( 'Peppol Legal Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
		// 		'location'          => 'order',
		// 		'type'              => 'text',
		// 		'sanitize_callback' => function ( $val ) {
		// 			return preg_replace( '/\s+/', '', trim( (string) $val ) );
		// 		},
		// 		'validate_callback' => function ( $val ) use ( $input_mode ) {
		// 			if ( 'full' === $input_mode && false === strpos( $val, ':' ) ) {
		// 				return new \WP_Error( 'invalid_legal_id', __( 'Use scheme:identifier format.', 'woocommerce-pdf-invoices-packing-slips' ) );
		// 			}
		// 			return true;
		// 		},
		// 	)
		// );

		// if ( 'select' === $input_mode ) {
		// 	woocommerce_register_additional_checkout_field(
		// 		array(
		// 			'id'       => 'wpo-ips-edi/peppol-legal-identifier-icd',
		// 			'label'    => __( 'Legal Identifier Scheme (ICD)', 'woocommerce-pdf-invoices-packing-slips' ),
		// 			'location' => 'order',
		// 			'type'     => 'select',
		// 			'options'  => array_map(
		// 				static fn ( $code, $label ) => array(
		// 					'value' => $code,
		// 					'label' => "[$code] $label",
		// 				),
		// 				array_keys( EN16931::get_icd() ),
		// 				EN16931::get_icd()
		// 			),
		// 			'validate_callback' => function ( $val ) {
		// 				if ( $val && ! isset( EN16931::get_icd()[ $val ] ) ) {
		// 					return new \WP_Error( 'invalid_icd', __( 'Invalid Legal Identifier Scheme.', 'woocommerce-pdf-invoices-packing-slips' ) );
		// 				}
		// 				return true;
		// 			},
		// 		)
		// 	);
		// }
	}
	
	/**
	 * Set default values for EDI Peppol fields in the Checkout Block.
	 *
	 * @return void
	 */
	public function edi_peppol_set_checkout_block_fields_value(): void {
		$fields = array(
			'wpo-ips-edi/peppol-endpoint-id',
			'wpo-ips-edi/peppol-endpoint-eas',
			// 'wpo-ips-edi/peppol-legal-identifier',
			// 'wpo-ips-edi/peppol-legal-identifier-icd',
		);
		
		foreach ( $fields as $field ) {
			add_filter( "woocommerce_get_default_value_for_{$field}", array( $this, 'edi_peppol_prefill_checkout_block_field_from_user_meta' ), 10, 3 );
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
	public function edi_peppol_prefill_checkout_block_field_from_user_meta( $value, string $group, \WC_Data $wc_object ): string {
		if ( 'other' !== $group || ! $wc_object instanceof \WC_Customer ) {
			return (string) $value;
		}

		$user_id = $wc_object->get_id();
		if ( ! $user_id ) {
			return (string) $value;
		}

		$key        = str_replace( 'woocommerce_get_default_value_for_', '', current_filter() );
		$meta_key   = str_replace( '-', '_', substr( $key, strlen( 'wpo-ips-edi/' ) ) );
		$input_mode = wpo_ips_edi_peppol_identifier_input_mode();

		// If we’re in 'full' mode, compose scheme:identifier for the *text* fields.
		if ( 'full' === $input_mode ) {
			switch ( $meta_key ) {
				case 'peppol_endpoint_id': {
					$id  = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
					$eas = (string) get_user_meta( $user_id, 'peppol_endpoint_eas', true );
					return ( '' !== $eas && '' !== $id ) ? "{$eas}:{$id}" : (string) $value;
				}
				// case 'peppol_legal_identifier': {
				// 	$id  = (string) get_user_meta( $user_id, 'peppol_legal_identifier', true );
				// 	$icd = (string) get_user_meta( $user_id, 'peppol_legal_identifier_icd', true );
				// 	return ( '' !== $icd && '' !== $id ) ? "{$icd}:{$id}" : (string) $value;
				// }
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
	public function edi_peppol_save_checkout_block_fields( string $key, $value, string $group, object $wc_object ): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return;
		}

		$allowed = array(
			'wpo-ips-edi/peppol-endpoint-id',
			'wpo-ips-edi/peppol-endpoint-eas',
			// 'wpo-ips-edi/peppol-legal-identifier',
			// 'wpo-ips-edi/peppol-legal-identifier-icd',
		);
		
		if ( ! in_array( $key, $allowed, true ) ) {
			return;
		}

		$meta_key = str_replace( '-', '_', substr( $key, strlen( 'wpo-ips-edi/' ) ) );
		$value    = trim( sanitize_text_field( wp_unslash( $value ) ) );
		
		if ( $wc_object instanceof \WC_Order ) {
			wpo_ips_edi_maybe_save_order_customer_peppol_data( $wc_object );
		}

		$customer_id = is_callable( array( $wc_object, 'get_customer_id' ) )
			? absint( $wc_object->get_customer_id() )
			: 0;
			
		if ( empty( $customer_id ) ) {
			return;
		}

		wpo_ips_edi_peppol_save_customer_identifiers( $customer_id, array( $meta_key => $value ) );
	}
	
	/**
	 * Remove EDI Peppol fields from order meta after checkout.
	 * 
	 * @param \WC_Abstract_Order $order
	 * @return void
	 */
	public function edi_peppol_remove_order_checkout_block_fields_meta( \WC_Abstract_Order $order ): void {
		$fields = array(
			'wpo-ips-edi/peppol-endpoint-id',
			'wpo-ips-edi/peppol-endpoint-eas',
			// 'wpo-ips-edi/peppol-legal-identifier',
			// 'wpo-ips-edi/peppol-legal-identifier-icd',
		);
		
		foreach ( $fields as $field ) {
			$order->delete_meta_data( '_wc_other/' . $field );
		}
		
		$order->save_meta_data();
	}
	
	/**
	 * Display EDI Peppol fields on the Classic Checkout page.
	 *
	 * @param array $fields Checkout fields.
	 * @return array Modified checkout fields with Peppol fields added.
	 */
	public function edi_peppol_display_classic_checkout_fields( array $fields ): array {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return $fields;
		}

		$input_mode           = wpo_ips_edi_peppol_identifier_input_mode();
		$placeholder_endpoint = ( 'select' !== $input_mode )
			? '0088:123456789'
			: '123456789';
		$placeholder_legal    = ( 'select' !== $input_mode )
			? '0208:1234567890'
			: '1234567890';
		$peppol_fields        = array();

		$peppol_fields['peppol_endpoint_id'] = array(
			'type'        => 'text',
			'label'       => __( 'Peppol Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'required'    => false,
			'class'       => array( 'form-row-wide' ),
			'placeholder' => $placeholder_endpoint,
		);

		if ( 'select' === $input_mode ) {
			$peppol_fields['peppol_endpoint_eas'] = array(
				'type'        => 'select',
				'label'       => __( 'Peppol Endpoint Scheme (EAS)', 'woocommerce-pdf-invoices-packing-slips' ),
				'required'    => false,
				'class'       => array( 'form-row-wide' ),
				'options'     => ( function () {
					$options = array( '' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...' );
					foreach ( EN16931::get_eas() as $code => $label ) {
						$options[ $code ] = "[$code] $label";
					}
					return $options;
				} )(),
			);
		}

		// // We chose not to display the Legal Identifier at Checkout; it's only shown in My Account for now. This may change later.
		// $peppol_fields['peppol_legal_identifier'] = array(
		// 	'type'        => 'text',
		// 	'label'       => __( 'Peppol Legal Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
		// 	'required'    => false,
		// 	'class'       => array( 'form-row-wide' ),
		// 	'placeholder' => $placeholder_legal,
		// );

		// if ( 'select' === $input_mode ) {
		// 	$peppol_fields['peppol_legal_identifier_icd'] = array(
		// 		'type'        => 'select',
		// 		'label'       => __( 'Peppol Legal Identifier Scheme (ICD)', 'woocommerce-pdf-invoices-packing-slips' ),
		// 		'required'    => false,
		// 		'class'       => array( 'form-row-wide' ),
		// 		'options'     => ( function () {
		// 			$options = array( '' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...' );
		// 			foreach ( EN16931::get_icd() as $code => $label ) {
		// 				$options[ $code ] = "[$code] $label";
		// 			}
		// 			return $options;
		// 		} )(),
		// 	);
		// }

		// Prepend Peppol fields before existing 'order' fields
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
	public function edi_peppol_set_classic_checkout_fields_value( $value, string $input ) {
		if ( ! in_array( $input, array(
			'peppol_endpoint_id',
			'peppol_endpoint_eas',
			// 'peppol_legal_identifier',
			// 'peppol_legal_identifier_icd',
		), true ) ) {
			return $value;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $value;
		}

		$endpoint_id          = (string) get_user_meta( $user_id, 'peppol_endpoint_id', true );
		$endpoint_eas         = (string) get_user_meta( $user_id, 'peppol_endpoint_eas', true );
		// $legal_identifier     = (string) get_user_meta( $user_id, 'peppol_legal_identifier', true );
		// $legal_identifier_icd = (string) get_user_meta( $user_id, 'peppol_legal_identifier_icd', true );

		$input_mode = wpo_ips_edi_peppol_identifier_input_mode();

		switch ( $input ) {
			case 'peppol_endpoint_id':
				if ( 'full' === $input_mode && '' !== $endpoint_eas && '' !== $endpoint_id ) {
					return "{$endpoint_eas}:{$endpoint_id}";
				}
				return $endpoint_id;
			case 'peppol_endpoint_eas':
				return $endpoint_eas;
			// case 'peppol_legal_identifier':
			// 	if ( 'full' === $input_mode && '' !== $legal_identifier_icd && '' !== $legal_identifier ) {
			// 		return "{$legal_identifier_icd}:{$legal_identifier}";
			// 	}
			// 	return $legal_identifier;
			// case 'peppol_legal_identifier_icd':
			// 	return $legal_identifier_icd;
		}

		return $value;
	}
	
	/**
	 * Validate Peppol Endpoint / Legal‑ID pairs after WooCommerce
	 * has normalised and sanitised all checkout data.
	 *
	 * @param array $data   All posted checkout fields.
	 * @param mixed $errors Errors object to add validation errors to.
	 * @return void
	 */
	public function edi_peppol_validate_classic_checkout_field_values( array $data, $errors ): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) || ! $errors instanceof \WP_Error ) {
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
	public function edi_peppol_save_classic_checkout_fields( int $order_id, array $data ): void {
		if ( ! wpo_ips_edi_peppol_enabled_for_location( 'checkout' ) ) {
			return;
		}

		if ( ! isset( $data['peppol_endpoint_id'] ) ) {
			return; // No Peppol data submitted
		}
		
		// if ( ! isset( $data['peppol_legal_identifier'] ) ) {
		// 	return; // No Peppol data submitted
		// }

		$order = wc_get_order( $order_id );
		
		if ( empty( $order ) ) {
			return;
		}
		
		$user_id = absint( $order->get_customer_id() );
		
		if ( empty( $user_id ) ) {
			return;
		}
		
		wpo_ips_edi_peppol_save_customer_identifiers( $user_id, $data );
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
					$scheme = $match['scheme'] ?? '';
					$value  = $match['value']  ?? '';
					$name   = $match['name']   ?? '';
				
					$identifier = ! empty( $scheme )
						? "$scheme:$value"
						: $value;

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
