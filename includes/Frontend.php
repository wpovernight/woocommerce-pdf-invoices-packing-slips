<?php
namespace WPO\IPS;

use WPO\IPS\EDI\Standards\EN16931;

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
		add_filter( 'woocommerce_api_order_response', array( $this, 'woocommerce_api_invoice_number' ), 10, 2 );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'edi_peppol_account_menu_item' ), 10, 2 );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'open_my_account_pdf_link_on_new_tab' ), 999 );
		add_action( 'template_redirect', array( $this, 'edi_save_peppol_settings' ) );
		add_action( 'woocommerce_account_peppol_endpoint', array( $this, 'edi_peppol_settings_account_page' ) );
		
		add_shortcode( 'wcpdf_download_invoice', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_download_pdf', array( $this, 'generate_document_shortcode' ) );
		add_shortcode( 'wcpdf_document_link', array( $this, 'generate_document_shortcode' ) );
		
		add_rewrite_endpoint( 'peppol', EP_PAGES );
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

		$invoice         = wcpdf_get_invoice( $order );
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
	 * Open PDF on My Account page in a new browser tab/window
	 */
	public function open_my_account_pdf_link_on_new_tab() {
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			if ( $general_settings = get_option( 'wpo_wcpdf_settings_general' ) ) {
				if ( isset( $general_settings['download_display'] ) && $general_settings['download_display'] == 'display' ) {
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
	
	/**
	 * Add EDI Peppol Settings user account menu item
	 * 
	 * @param array $items
	 * @param array $endpoints
	 * @return array
	 */
	public function edi_peppol_account_menu_item( array $items, array $endpoints ): array {
		if ( ! wpo_ips_edi_peppol_is_available() ) {
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
		if ( ! wpo_ips_edi_peppol_is_available() ) {
			echo '<p>' . esc_html__( 'EDI Peppol is not available.', 'woocommerce-pdf-invoices-packing-slips' ) . '</p>';
			return;
		}

		$user_id              = get_current_user_id();
		
		$endpoint_id          = get_user_meta( $user_id, 'peppol_endpoint_id', true );
		$endpoint_eas         = get_user_meta( $user_id, 'peppol_endpoint_eas', true );
		$legal_identifier_icd = get_user_meta( $user_id, 'peppol_legal_identifier_icd', true );
		$legal_identifier     = get_user_meta( $user_id, 'peppol_legal_identifier', true );
		
		$eas_options_raw      = EN16931::get_eas();
		$eas_options          = array();

		foreach ( $eas_options_raw as $code => $label ) {
			$eas_options[ $code ] = "[$code] $label";
		}

		$icd_options_raw      = EN16931::get_icd();
		$icd_options          = array();

		foreach ( $icd_options_raw as $code => $label ) {
			$icd_options[ $code ] = "[$code] $label";
		}
		?>
		<form method="post">
			<h3><?php esc_html_e( 'Peppol Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="peppol_endpoint_id"><?php esc_html_e( 'Peppol Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
				<input type="text" class="woocommerce-Input input-text" name="peppol_endpoint_id" id="peppol_endpoint_id" value="<?php echo esc_attr( $endpoint_id ); ?>" />
				<small>
					<?php
						echo wp_kses_post( sprintf(
							'%s<br>%s',
							__( 'Specify the Peppol Endpoint ID.', 'woocommerce-pdf-invoices-packing-slips' ),
							sprintf(
								/* translators: %1$s: open link anchor, %2$s: close link anchor */
								__( 'If you don\'t know the ID, you can search for it in the %1$sPeppol Directory%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<a href="https://directory.peppol.eu/public" target="_blank" rel="noopener noreferrer">',
								'</a>'
							)
						) );
					?>
				</small>
			</p>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="peppol_endpoint_eas"><?php esc_html_e( 'Peppol Endpoint Scheme (EAS)', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
				<select name="peppol_endpoint_eas" id="peppol_endpoint_eas" class="woocommerce-Input input-select">
					<option value=""><?php esc_html_e( 'Select...', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
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
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="peppol_legal_identifier"><?php esc_html_e( 'Peppol Legal Identifier', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
				<input type="text" class="woocommerce-Input input-text" name="peppol_legal_identifier" id="peppol_legal_identifier" value="<?php echo esc_attr( $legal_identifier ); ?>" />
				<small>
					<?php
						echo wp_kses_post( sprintf(
							'%s<br>%s',
							__( 'Specify the Peppol Legal Identifier.', 'woocommerce-pdf-invoices-packing-slips' ),
							sprintf(
								/* translators: %1$s: open link, %2$s: close link */
								__( 'If you don\'t know the Identifier, you can search for it in the %1$sPeppol Directory%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<a href="https://directory.peppol.eu/public" target="_blank" rel="noopener noreferrer">',
								'</a>'
							)
						) );
					?>
				</small>
			</p>
			
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="peppol_legal_identifier_icd"><?php esc_html_e( 'Peppol Legal Identifier Scheme (ICD)', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
				<select name="peppol_legal_identifier_icd" id="peppol_legal_identifier_icd" class="woocommerce-Input input-select">
					<option value=""><?php esc_html_e( 'Select...', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
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
			<p>
				<input type="hidden" name="wc_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpo_ips_edi_user_save_peppol_settings' ) ); ?>">
				<button type="submit" name="save_peppol_settings" class="woocommerce-Button button"><?php _e( 'Save changes', 'woocommerce-pdf-invoices-packing-slips' ); ?></button>
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
		if (
			isset( $_POST['save_peppol_settings'] ) &&
			is_user_logged_in() &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc_nonce'] ) ), 'wpo_ips_edi_user_save_peppol_settings' )
		) {
			$request = stripslashes_deep( $_POST );
			$user_id = get_current_user_id();
			
			if ( isset( $request['peppol_endpoint_id'] ) ) {
				update_user_meta( $user_id, 'peppol_endpoint_id', sanitize_text_field( wp_unslash( $request['peppol_endpoint_id'] ) ) );
			}
			
			if ( isset( $request['peppol_endpoint_eas'] ) ) {
				update_user_meta( $user_id, 'peppol_endpoint_eas', sanitize_text_field( wp_unslash( $request['peppol_endpoint_eas'] ) ) );
			}
			
			if ( isset( $request['peppol_legal_identifier'] ) ) {
				update_user_meta( $user_id, 'peppol_legal_identifier', sanitize_text_field( wp_unslash( $request['peppol_legal_identifier'] ) ) );
			}
			
			if ( isset( $request['peppol_legal_identifier_icd'] ) ) {
				update_user_meta( $user_id, 'peppol_legal_identifier_icd', sanitize_text_field( wp_unslash( $request['peppol_legal_identifier_icd'] ) ) );
			}
			
			wc_add_notice( __( 'Peppol settings saved.', 'woocommerce-pdf-invoices-packing-slips' ), 'success' );
			wp_redirect( wc_get_account_endpoint_url( 'peppol' ) );
			exit;
		}
	}
	
}

endif; // class_exists
