<?php
namespace WPO\IPS\Settings;

use WPO\IPS\EDI\Standards\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsEDI' ) ) :

class SettingsEDI {

	public array $sections;
	protected static ?self $_instance = null;

	/**
	 * Get the singleton instance of the SettingsEDI class.
	 *
	 * @return SettingsEDI
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
	public function __construct()	{
		$this->sections = apply_filters( 'wpo_ips_edi_settings_sections', array(
			'settings'    => __( 'Settings', 'woocommerce-pdf-invoices-packing-slips' ),
			'identifiers' => __( 'Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'taxes'       => __( 'Taxes', 'woocommerce-pdf-invoices-packing-slips' ),
			'info'        => __( 'Info', 'woocommerce-pdf-invoices-packing-slips' ),
			'network'     => __( 'Network', 'woocommerce-pdf-invoices-packing-slips' ),
		) );

		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_edi', array( $this, 'output_settings' ), 10, 2 );
		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'save_taxes_on_calculate_order_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_taxes_on_checkout' ), 10, 3 );

		// AJAX
		add_action( 'wp_ajax_wpo_ips_edi_save_taxes', array( $this, 'ajax_save_taxes' ) );
		add_action( 'wp_ajax_wpo_ips_edi_reload_tax_table', array( $this, 'ajax_reload_tax_table' ) );
		add_action( 'wp_ajax_wpo_ips_edi_load_customer_order_identifiers', array( $this, 'ajax_load_customer_order_identifiers' ) );
	}

	/**
	 * Output the settings for EDI.
	 *
	 * @param string $active_section
	 * @param string $nonce
	 *
	 * @return void
	 */
	public function output_settings( string $active_section, string $nonce ): void {
		if ( ! wp_verify_nonce( $nonce, 'wp_wcpdf_settings_page_nonce' ) ) {
			return;
		}

		$active_section = ! empty( $active_section ) ? $active_section : 'settings';
		?>
		<div class="wcpdf-settings-sub-sections">
			<?php if ( count( $this->sections ) > 1 ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php
						foreach ( $this->sections as $section => $title ) {
							$active = ( $section === $active_section ) ? 'nav-tab-active' : '';
							printf(
								'<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>',
								esc_url( add_query_arg( 'section', $section ) ),
								esc_attr( $section ),
								esc_attr( $active ),
								wp_kses_post( $title )
							);
						}
					?>
				</h2>
			<?php else : ?>
				<h3><?php echo esc_html( $this->sections[ $active_section ] ); ?></h3>
			<?php endif; ?>
		</div>
		<?php
			switch ( $active_section ) {
				default:
				case 'settings':
					settings_fields( 'wpo_ips_edi_settings' );
					do_settings_sections( 'wpo_ips_edi_settings' );
					submit_button();
					break;
				case 'identifiers':
					$this->output_supplier_identifiers();
					$this->output_customer_identifiers();
					break;
				case 'taxes':
					$this->output_taxes();
					break;
				case 'info':
					$this->output_info();
					break;
				case 'network':
					$this->output_network();
					break;
			}

			do_action( 'wpo_ips_edi_settings_after_output', $active_section, $this->sections );
	}

	/**
	 * Initialize the settings for EDI.
	 *
	 * @return void
	 */
	public function init_settings(): void {
		$page    = $option_group = $option_name = 'wpo_ips_edi_settings';
		$section = 'edi';

		$settings_fields = array(
			array(
				'type'     => 'section',
				'id'       => $section,
				'title'    => '',
				'callback' => 'section',
			),
			array(
				'type'     => 'setting',
				'id'       => 'enabled',
				'title'    => __( 'Enable Electronic Documents', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => $section,
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'enabled',
					'description' => __( 'Allow your store to generate and send electronic documents.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'document_types',
				'title'			=> __( 'Document Types', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> $section,
				'args'			=> array(
					'option_name'     => $option_name,
					'id'              => 'document_types',
					'options'         => $this->get_document_types(),
					'multiple'        => true,
					'enhanced_select' => true,
					'placeholder'     => __( 'Select one or more document types with electronic format.', 'woocommerce-pdf-invoices-packing-slips' ),
					'description'     => __( 'E-Documents require the correspondent PDF to be generated first.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'send_attachments',
				'title'    => __( 'Send Attachments', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => $section,
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'send_attachments',
					'description' => __( 'When sending a document by e-mail, automatically include the electronic version attachment along with the PDF.', 'woocommerce-pdf-invoices-packing-slips' ),
				),
			),
			array(
				'type'     => 'setting',
				'id'       => 'syntax',
				'title'    => __( 'Preferred Syntax', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'select',
				'section'  => $section,
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'syntax',
					'options'     => wpo_ips_edi_syntaxes(),
					'description' => __( 'Choose the preferred XML syntax standard for electronic documents.', 'woocommerce-pdf-invoices-packing-slips' ),
				),
			),
		);

		$settings_format = array();
		foreach ( wpo_ips_edi_syntax_formats() as $syntax => $data ) {
			$settings_format[] = array(
				'type'     => 'setting',
				'id'       => "{$syntax}_format",
				'title'    => sprintf(
					/* translators: %s syntax */
					__( '%s Format', 'woocommerce-pdf-invoices-packing-slips' ),
					strtoupper( trim( $syntax ) )
				),
				'callback' => 'select',
				'section'  => $section,
				'args'     => array(
					'option_name'       => $option_name,
					'id'                => "{$syntax}_format",
					'options'           => array_combine(
						array_keys( $data['formats'] ),
						array_column( $data['formats'], 'name' )
					),
					'description'       => sprintf(
						/* translators: %s syntax */
						__( 'Choose the preferred %s format.', 'woocommerce-pdf-invoices-packing-slips' ),
						strtoupper( trim( $syntax ) )
					),
					'custom_attributes' => array(
						'data-show_for_option_name'   => $option_name . '[syntax]',
						'data-show_for_option_values' => json_encode( array( $syntax ) ),
					),
				),
			);
		}

		if ( ! empty( $settings_format ) ) {
			$settings_fields = array_merge( $settings_fields, $settings_format );
		}

		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_endpoint_id',
			'title'    => '',
			'callback' => 'text_input',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Peppol Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'peppol_endpoint_id',
				'description'       => sprintf(
					'%s<br>%s',
					__( 'Specify the Peppol Endpoint ID for the supplier.', 'woocommerce-pdf-invoices-packing-slips' ),
					sprintf(
						/* translators: %1$s: open link anchor, %2$s: close link anchor */
						__( 'If you don\'t know the ID, you can search for it in the %1$sPeppol Directory%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="https://directory.peppol.eu/public" target="_blank" rel="noopener noreferrer">',
						'</a>'
					)
				),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
				),
			),
		);

		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_endpoint_eas',
			'title'    => '',
			'callback' => 'select',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Peppol Endpoint Scheme (EAS)', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'peppol_endpoint_eas',
				'options'           => ( function () {
					$options = array( '' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...' );
					foreach ( EN16931::get_eas() as $code => $label ) {
						$options[ $code ] = "[$code] $label";
					}
					return $options;
				} )(),
				'description'       => sprintf(
					'%s<br>%s',
					__( 'Specify the Electronic Address Scheme (EAS) for the supplier Endpoint above.', 'woocommerce-pdf-invoices-packing-slips' ),
					sprintf(
						/* translators: %1$s: open link anchor, %2$s: close link anchor */
						__( 'For more information on each Endpoint Address Scheme (EAS), refer to the %1$sofficial Peppol EAS list%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/eas/" target="_blank">',
						'</a>'
					)
				),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
				),
			),
		);

		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_legal_identifier',
			'title'    => '',
			'callback' => 'select',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Peppol Legal Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'peppol_legal_identifier',
				'options'           => array(
					''           => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...',
					'vat_number' => __( 'Shop VAT Number', 'woocommerce-pdf-invoices-packing-slips' ),
					'coc_number' => __( 'Shop COC Number', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'description'       => sprintf(
					'%s<br>%s',
					__( 'Specify the Peppol Legal Identifier for the supplier.', 'woocommerce-pdf-invoices-packing-slips' ),
					sprintf(
						/* translators: %1$s: open link anchor, %2$s: close link anchor */
						__( 'Please make sure the field is filled out in the %1$sGeneral Settings%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_ips_edi_settings&tab=general' ) ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					)
				),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
				),
			),
		);

		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_legal_identifier_icd',
			'title'    => '',
			'callback' => 'select',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Peppol Legal Identifier Scheme (ICD)', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'peppol_legal_identifier_icd',
				'options'           => ( function () {
					$options = array( '' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...' );
					foreach ( EN16931::get_icd() as $code => $label ) {
						$options[ $code ] = "[$code] $label";
					}
					return $options;
				} )(),
				'description'       => sprintf(
					'%s<br>%s',
					__( 'Specify the Peppol Legal Identifier Scheme (ICD) for the supplier.', 'woocommerce-pdf-invoices-packing-slips' ),
					sprintf(
						/* translators: %1$s: open link anchor, %2$s: close link anchor */
						__( 'For detailed information on each Identification Code (ICD), see the %1$sofficial Peppol ICD list%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="https://docs.peppol.eu/poacc/billing/3.0/codelist/ICD/" target="_blank">',
						'</a>'
					)
				),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
				),
			),
		);
		
		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_customer_identifiers_input_mode',
			'title'    => '',
			'callback' => 'select',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Customer Peppol Identifiers Input Mode', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'peppol_customer_identifiers_input_mode',
				'options'           => array(
					'select' => __( 'Customer selects scheme and enters identifier separately', 'woocommerce-pdf-invoices-packing-slips' ),
					'full'   => __( 'Customer enters full ID (e.g., 0088:123456789)', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'description'       => __( 'Determines how the customer provides their Peppol Endpoint ID and Legal Entity Identifier. This applies to both fields.', 'woocommerce-pdf-invoices-packing-slips' ),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
				),
			),
		);
		
		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_customer_identifier_fields_location',
			'title'    => '',
			'callback' => 'select',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Customer Peppol Identifier Fields Location', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'peppol_customer_identifier_fields_location',
				'options'           => array(
					'checkout'   => __( 'Checkout only', 'woocommerce-pdf-invoices-packing-slips' ),
					'my_account' => __( 'My Account only', 'woocommerce-pdf-invoices-packing-slips' ),
					'both'       => __( 'Both Checkout and My Account', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'description'       => __( 'Choose where customers can provide their Peppol identifiers (both Endpoint ID and Legal Entity Identifier).', 'woocommerce-pdf-invoices-packing-slips' ),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
				),
			),
		);
		
		$languages = wpo_wcpdf_get_multilingual_languages();
		
		if ( count( $languages ) > 0 ) {
			$settings_fields[] = array(
				'type'     => 'setting',
				'id'       => 'supplier_identifiers_language',
				'title'    => __( 'Supplier Identifiers Language', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'select',
				'section'  => $section,
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'supplier_identifiers_language',
					'options'     => $languages,
					'description' => sprintf(
						/* translators: %1$s: open link anchor, %2$s: close link anchor */
						__( 'Select the language for the supplier identifiers data. This option is available because multilingual support is enabled. You can check the currently available data %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=edi&section=identifiers' ) ) . '" target="_blank">',
						'</a>'
					),
				)
			);
		}

		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'embed_encrypted_pdf',
			'title'    => __( 'Embed Encrypted PDF', 'woocommerce-pdf-invoices-packing-slips' ),
			'callback' => 'checkbox',
			'section'  => $section,
			'args'     => array(
				'option_name' => $option_name,
				'id'          => 'embed_encrypted_pdf',
				'description' => __( 'Embed the encrypted PDF invoice file within the e-document. Note that this option may not be valid for all formats.', 'woocommerce-pdf-invoices-packing-slips' ),
			)
		);

		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'enabled_preview',
			'title'    => __( 'Enable XML Preview', 'woocommerce-pdf-invoices-packing-slips' ),
			'callback' => 'checkbox',
			'section'  => $section,
			'args'     => array(
				'option_name' => $option_name,
				'id'          => 'enabled_preview',
				'description' => __( 'Enable the XML preview for electronic documents.', 'woocommerce-pdf-invoices-packing-slips' ),
			)
		);
		
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'enabled_logs',
			'title'    => __( 'Enable Debug Logs', 'woocommerce-pdf-invoices-packing-slips' ),
			'callback' => 'checkbox',
			'section'  => $section,
			'args'     => array(
				'option_name' => $option_name,
				'id'          => 'enabled_logs',
				'description' => __( 'Enable logging for debugging purposes.', 'woocommerce-pdf-invoices-packing-slips' ),
			)
		);

		$settings_fields = apply_filters( 'wpo_ips_edi_settings', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

	/**
	 * Save taxes on calculate order totals.
	 *
	 * @param bool $and_taxes
	 * @param \WC_Abstract_Order $order
	 *
	 * @return void
	 */
	public function save_taxes_on_calculate_order_totals( bool $and_taxes, \WC_Abstract_Order $order ): void {
		// it seems $and_taxes is mostly false, meaning taxes are calculated separately,
		// but we still update just in case anything changed
		wpo_ips_edi_save_order_taxes( $order );
	}

	/**
	 * Save taxes on checkout.
	 *
	 * @param int $order_id
	 * @param array $posted_data
	 * @param \WC_Order $order
	 *
	 * @return void
	 */
	public function save_taxes_on_checkout( int $order_id, array $posted_data, \WC_Order $order ): void {
		wpo_ips_edi_save_order_taxes( $order );
		wpo_ips_edi_maybe_save_order_customer_peppol_data( $order );
	}

	/**
	 * Get setting value by key.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_setting( string $key, $default = null ) {
		$settings = get_option( 'wpo_ips_edi_settings', array() );

		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		return $default;
	}

	/**
	 * Get the available EDI document types.
	 *
	 * @return array
	 */
	private function get_document_types(): array {
		$xml_documents  = WPO_WCPDF()->documents->get_documents( 'enabled', 'xml' );
		$document_types = array();

		foreach ( $xml_documents as $document ) {
			if ( isset( $document->output_formats ) && is_array( $document->output_formats ) && in_array( 'xml', $document->output_formats ) ) {
				$document_types[ $document->get_type() ] = $document->get_title();
			}
		}

		return apply_filters( 'wpo_ips_edi_document_types', $document_types );
	}
	
	/**
	 * Output supplier identifiers.
	 *
	 * @return void
	 */
	private function output_supplier_identifiers(): void {
		$identifiers_data = wpo_ips_edi_get_supplier_identifiers_data();

		foreach ( $identifiers_data as $language_slug => $language_data ) :
			?>
			<div class="edi-supplier-identifier" id="lang-<?php echo esc_attr( $language_slug ); ?>">
				<table class="widefat striped">
					<caption>
						<?php esc_html_e( 'Supplier', 'woocommerce-pdf-invoices-packing-slips' ); ?>
						<?php if ( 'default' !== $language_slug ) : ?>
							<small>[<?php echo esc_attr( $language_slug ); ?>]</small>
						<?php endif; ?>
					</caption>
					<tbody>
						<?php
						foreach ( $language_data as $key => $data ) {
							$value    = $data['value'];
							$required = $data['required'];
							$display  = $value ?: sprintf(
								'<span style="color:%s">%s</span>',
								$required ? '#d63638' : '#996800',
								$required
									? esc_html__( 'Missing', 'woocommerce-pdf-invoices-packing-slips' )
									: esc_html__( 'Optional', 'woocommerce-pdf-invoices-packing-slips' )
							);
							?>
							<tr>
								<td><?php echo esc_html( $data['label'] ); ?></td>
								<td>
									<?php echo $display; ?>
									<?php if ( 'vat_number' === $key && ! empty( $value ) && ! wpo_ips_edi_vat_number_has_country_prefix( $value ) ) : ?>
										<br><small class="notice-warning" style="color:#996800;"><?php esc_html_e( 'VAT number is missing the country prefix', 'woocommerce-pdf-invoices-packing-slips' ); ?></small>
									<?php endif; ?>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2">
								<?php printf(
									/* translators: General settings link */
									esc_html__( 'Missing details can be completed in the %s.', 'woocommerce-pdf-invoices-packing-slips' ),
									'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=general#shop_information' ) ) . '" class="edi-complete-details">' . esc_html__( 'General settings', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
								); ?>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
			<?php
		endforeach;
	}
	
	/**
	 * Output customer identifiers.
	 *
	 * @return void
	 */
	private function output_customer_identifiers(): void {
		?>
		<div class="edi-customer-identifiers">
			<table class="widefat striped">
				<caption><?php esc_html_e( 'Customer', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
				<thead>
					<tr>
						<td colspan="2">
							<div class="edi-search-wrap">
								<input type="text" id="edi-customer-order-id" placeholder="<?php esc_html_e( 'Order ID', 'woocommerce-pdf-invoices-packing-slips' ); ?>" value="">
								<a href="#" class="button button-primary" id="edi-customer-order-id-search-button"><?php esc_html_e( 'Search', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
							</div>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Retrieve customer identifiers by loading an order.', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
	
	/**
	 * Load customer identifiers via AJAX.
	 *
	 * @return void
	 */
	public function ajax_load_customer_order_identifiers(): void {
		if ( ! check_ajax_referer( 'wpo_ips_edi_nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		$request  = stripslashes_deep( $_GET );
		$order_id = absint( $request['order_id'] );
		
		if ( empty( $order_id ) ) {
			wp_send_json_error( __( 'Order ID is required.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}
		
		$order = wc_get_order( $order_id );
		
		if ( empty( $order ) ) {
			wp_send_json_error( __( 'Order not found!', 'woocommerce-pdf-invoices-packing-slips' ) );
		}
		
		$data = wpo_ips_edi_get_order_customer_identifiers_data( $order );
		
		wp_send_json_success( compact( 'data' ) );
	}

	/**
	 * Save the tax settings
	 *
	 * @param array $tax_settings
	 * @return void
	 */
	public function save_tax_settings( array $tax_settings = array() ): void {
		update_option( 'wpo_ips_edi_tax_settings', $tax_settings );
	}

	/**
	 * Save taxes from AJAX request.
	 *
	 * @return void
	 */
	public function ajax_save_taxes(): void {
		if (
			! isset( $_POST['action'] ) ||
			'wpo_ips_edi_save_taxes' !== $_POST['action'] ||
			! wp_verify_nonce( $_POST['nonce'], 'edi_save_taxes' )
		) {
			wp_send_json_error( __( 'Invalid request.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		$request      = stripslashes_deep( $_POST );
		$tax_settings = isset( $request['wpo_ips_edi_tax_settings'] ) ? $request['wpo_ips_edi_tax_settings'] : array();

		$this->save_tax_settings( $tax_settings );

		wp_send_json_success( __( 'Tax settings saved successfully.', 'woocommerce-pdf-invoices-packing-slips' ) );
	}

	/**
	 * Reload the tax table via AJAX.
	 *
	 * @return void
	 */
	public function ajax_reload_tax_table(): void {
		if ( ! check_ajax_referer( 'wpo_ips_edi_nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		$request   = stripslashes_deep( $_GET );
		$tax_class = isset( $request['tax_class'] ) ? sanitize_text_field( $request['tax_class'] ) : '';

		ob_start();
		$this->output_table_for_tax_class( $tax_class );
		$html = ob_get_clean();

		echo $html;
		wp_die();
	}

	/**
	 * Output the settings page for UBL taxes.
	 *
	 * @return void
	 */
	public function output_taxes(): void {
		?>
		<p><?php esc_html_e( 'To ensure compliance with e-invoicing requirements, please complete the Taxes Classification. This information is essential for accurately generating legally compliant invoices.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<p>
			<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: strong tag with note */
						__( '%s: Each rate line allows you to configure the tax scheme, category, and reason. If these values are set to "Default," they will automatically inherit the settings selected in the "Tax class default" dropdowns at the bottom of the table.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<strong>' . esc_html__( 'Note', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>'
					)
				);
			?>
		</p>
		<p>
			<?php
				printf(
					/* translators: %1$s: open link anchor, %2$s: close link anchor */
					esc_html__( 'You can add custom tax schemes, categories or reasons by following the instructions in our %1$sdocumentation%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
					'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/ubl-tax-classification-filter-hooks/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			?>
		</p>
		<div id="edi-tax-save-notice" class="notice" style="display:none;"></div>
		<?php
			$rates                       = \WC_Tax::get_tax_rate_classes();
			$formatted_rates             = array();
			$formatted_rates['standard'] = __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' );

			foreach ( $rates as $rate ) {
				if ( empty( $rate->slug ) ) {
					continue;
				}

				$formatted_rates[ $rate->slug ] = ! empty( $rate->name ) ? esc_attr( $rate->name ) : esc_attr( $rate->slug );
			}

			// Output tax class selector and action button
			$this->output_tax_class_selector_and_action( $formatted_rates );

			// Output all tables wrapped in containers
			foreach ( $formatted_rates as $slug => $name ) {
				echo '<div class="edi-tax-class-table" data-tax-class="' . esc_attr( $slug ) . '" style="display:none;">';
				$this->output_table_for_tax_class( $slug );
				echo '</div>';
			}
	}

	/**
	 * Output the tax class selector and action button.
	 *
	 * @param array $formatted_rates An associative array of tax class slugs and names.
	 * @return void
	 */
	public function output_tax_class_selector_and_action( array $formatted_rates ): void {
		$nonce  = wp_create_nonce( 'edi_save_taxes' );
		$action = 'wpo_ips_edi_save_taxes';

		if ( empty( $current_slug ) && ! empty( $formatted_rates ) ) {
			$current_slug = (string) array_key_first( $formatted_rates );
		}
		?>
		<div class="edi-tax-class-group" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-action="<?php echo esc_attr( $action ); ?>">
			<div class="doc-output-toggle-group">
				<?php foreach ( $formatted_rates as $slug => $name ) :
					$is_active    = ( $slug === $current_slug );
					$active_class = $is_active ? 'active' : '';
					$href         = add_query_arg( 'edi_tax_class', $slug ); // graceful fallback
					?>
					<a
						href="<?php echo esc_url( $href ); ?>"
						class="doc-output-toggle <?php echo esc_attr( $active_class ); ?>"
						data-tax-class="<?php echo esc_attr( $slug ); ?>"
						aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
						<?php echo esc_html( $name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the table for a specific tax class.
	 *
	 * @param string $slug The slug of the tax class.
	 *
	 * @return void
	 */
	public function output_table_for_tax_class( string $slug ): void {
		global $wpdb;

		$edi_tax_settings = wpo_ips_edi_get_tax_settings();
		$table_name       = "{$wpdb->prefix}woocommerce_tax_rates";
		$slug             = sanitize_key( strtolower( $slug ) );
			
		$query = wpo_wcpdf_prepare_identifier_query(
			"SELECT * FROM %i WHERE tax_rate_class = %s;",
			array( $table_name ),
			array( ( 'standard' === $slug ) ? '' : $slug )
		);

		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$allowed_html = array(
			'select' => array(
				'name'         => true,
				'id'           => true,
				'class'        => true,
				'style'        => true,
				'data-current' => true
			),
			'option' => array(
				'value'        => true,
				'selected'     => true,
			)
		);
		?>

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Country code', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'State code', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Postcode / ZIP', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'City', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Rate', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Scheme', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th><?php esc_html_e( 'Category', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th width="10%"><?php esc_html_e( 'Reason', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
					<th width="15%"><?php esc_html_e( 'Remarks', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
				</tr>
			</thead>
			<tbody id="rates">
				<?php
					if ( ! empty( $results ) ) {
						foreach ( $results as $result ) {
							$locationResults = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
								$wpdb->prepare(
									"SELECT * FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %d;",
									$result->tax_rate_id
								)
							);
							$postcode = array();
							$city     = array();

							foreach ( $locationResults as $locationResult ) {
								if ( ! isset( $locationResult->location_type ) ) {
									continue;
								}

								switch ( $locationResult->location_type ) {
									case 'postcode':
										$postcode[] = $locationResult->location_code;
										break;
									case 'city':
										$city[] = $locationResult->location_code;
										break;
								}
							}

							$country          = empty( $result->tax_rate_country ) ? '*' : $result->tax_rate_country;
							$state            = empty( $result->tax_rate_state ) ? '*' : $result->tax_rate_state;
							$postcode         = empty( $postcode ) ? '*' : implode( '; ', $postcode );
							$city             = empty( $city ) ? '*' : implode( '; ', $city );

							$scheme           = isset( $edi_tax_settings['rate'][ $result->tax_rate_id ]['scheme'] )   ? $edi_tax_settings['rate'][ $result->tax_rate_id ]['scheme']   : 'default';
							$scheme_default   = isset( $edi_tax_settings['class'][ $slug ]['scheme'] ) ? $edi_tax_settings['class'][ $slug ]['scheme'] : 'default';
							$scheme_code      = ( 'default' === $scheme ) ? $scheme_default : $scheme;

							$category         = isset( $edi_tax_settings['rate'][ $result->tax_rate_id ]['category'] ) ? $edi_tax_settings['rate'][ $result->tax_rate_id ]['category'] : 'default';
							$category_default = isset( $edi_tax_settings['class'][ $slug ]['category'] ) ? $edi_tax_settings['class'][ $slug ]['category'] : 'default';
							$category_code    = ( 'default' === $category ) ? $category_default : $category;

							$reason           = isset( $edi_tax_settings['rate'][ $result->tax_rate_id ]['reason'] )   ? $edi_tax_settings['rate'][ $result->tax_rate_id ]['reason']   : 'default';
							$reason_default   = isset( $edi_tax_settings['class'][ $slug ]['reason'] ) ? $edi_tax_settings['class'][ $slug ]['reason'] : 'default';
							$reason_code      = ( 'default' === $reason ) ? $reason_default : $reason;

							echo '<tr>';
							echo '<td>' . esc_html( $country ) . '</td>';
							echo '<td>' . esc_html( $state ) . '</td>';
							echo '<td>' . esc_html( $postcode ) . '</td>';
							echo '<td>' . esc_html( $city ) . '</td>';
							echo '<td>' . esc_html( wc_round_tax_total( $result->tax_rate ) ) . '%</td>';
							echo '<td>';
							$select_for_scheme = $this->get_tax_select_for( 'scheme', 'rate', $result->tax_rate_id, $scheme );
							echo wp_kses( $select_for_scheme, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $scheme_code ) . '</code></div>';
							echo '</td>';
							echo '<td>';
							$select_for_category = $this->get_tax_select_for( 'category', 'rate', $result->tax_rate_id, $category );
							echo wp_kses( $select_for_category, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $category_code ) . '</code></div>';
							echo '</td>';
							echo '<td>';
							$select_for_reason = $this->get_tax_select_for( 'reason', 'rate', $result->tax_rate_id, $reason );
							echo wp_kses( $select_for_reason, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $reason_code ) . '</code></div>';
							echo '</td>';
							echo '<td class="remark">';

							foreach ( EN16931::get_vatex_remarks() as $field => $remarks ) {
								foreach ( array( 'scheme', 'category', 'reason' ) as $f ) {
									if ( isset( $remarks[ ${$f} ] ) ) {
										echo '<p><code>' . esc_html( ${$f} ) . '</code>: ' . esc_html( $remarks[ ${$f} ] ) . '</p>';
									}
								}
							}

							echo '</td>';
							echo '</tr>';
						}
					} else {
						echo '<tr><td colspan="9">' . esc_html__( 'No taxes found for this class.', 'woocommerce-pdf-invoices-packing-slips' ) . '</td></tr>';
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th colspan="5" style="text-align: right;"><?php esc_html_e( 'Tax class default', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</th>
					<?php
						$scheme   = isset( $edi_tax_settings['class'][ $slug ]['scheme'] )   ? $edi_tax_settings['class'][ $slug ]['scheme']   : 'default';
						$category = isset( $edi_tax_settings['class'][ $slug ]['category'] ) ? $edi_tax_settings['class'][ $slug ]['category'] : 'default';
						$reason   = isset( $edi_tax_settings['class'][ $slug ]['reason'] )   ? $edi_tax_settings['class'][ $slug ]['reason']   : 'default';
					?>
					<th>
						<?php
							$select_for_scheme = $this->get_tax_select_for( 'scheme', 'class', $slug, $scheme );
							echo wp_kses( $select_for_scheme, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $scheme ) . '</code></div>';
						?>
					</th>
					<th>
						<?php
							$select_for_category = $this->get_tax_select_for( 'category', 'class', $slug, $category );
							echo wp_kses( $select_for_category, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $category ) . '</code></div>';
						?>
					</th>
					<th>
						<?php
							$select_for_reason = $this->get_tax_select_for( 'reason', 'class', $slug, $reason );
							echo wp_kses( $select_for_reason, $allowed_html );
							echo '<div class="current" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $reason ) . '</code></div>';
						?>
					</th>
					<th class="remark">
						<?php
							foreach ( EN16931::get_vatex_remarks() as $field => $remarks ) {
								foreach ( array( 'scheme', 'category', 'reason' ) as $f ) {
									if ( isset( $remarks[ ${$f} ] ) ) {
										echo '<p><code>' . esc_html( ${$f} ) . '</code>: ' . esc_html( $remarks[ ${$f} ] ) . '</p>';
									}
								}
							}
						?>
					</th>
				</tr>
			</tfoot>
		</table>
		<?php
	}

	/**
	 * Get select field for tax rate
	 *
	 * @param string $for
	 * @param string $type
	 * @param string $id
	 * @param string $selected
	 *
	 * @return string
	 */
	public function get_tax_select_for( string $for, string $type, string $id, string $selected ): string {
		$defaults = array(
			'default' => __( 'Default', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		switch ( $for ) {
			case 'scheme':
				$options = EN16931::get_vat_cat();
				break;
			case 'category':
				$options = EN16931::get_5305();
				break;
			case 'reason':
				$defaults['none'] = __( 'None', 'woocommerce-pdf-invoices-packing-slips' );
				$options          = EN16931::get_vatex();
				break;
			default:
				$options = array();
		}

		$select  = '<select name="wpo_ips_edi_tax_settings[' . $type . '][' . $id . '][' . $for . ']" data-current="' . $selected . '" style="width:100%; box-sizing:border-box;">';

		foreach ( $defaults as $key => $value ) {
			if ( 'class' === $type && 'default' === $key ) {
				continue;
			}

			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}

		foreach ( $options as $key => $value ) {
			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}

		$select .= '</select>';

		return $select;
	}
	
	/**
	 * Output the info content.
	 * 
	 * @return void
	 */
	public function output_info(): void {
		?>
		<div>
			<h3><?php esc_html_e( 'Available Formats', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
			<table class="widefat striped" styles="margin-top:12px;">
				<thead>
					<tr>
						<td><?php esc_html_e( 'Format', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
						<td><?php esc_html_e( 'Syntax', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
						<td><?php esc_html_e( 'Hybrid', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
						<td><?php esc_html_e( 'Supported Documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( wpo_ips_edi_syntax_formats() as $syntax => $data ) : ?>
						<?php foreach ( $data['formats'] as $format ) : ?>
							<tr>
								<td><?php echo esc_html( $format['name'] ); ?></td>
								<td><?php echo esc_html( strtoupper( $syntax ) ); ?></td>
								<td><?php echo wp_kses_post( $format['hybrid'] ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-no"></span>' ); ?></td>
								<td>
									<?php
										echo esc_html( implode(
											', ',
											array_map(
												'ucfirst',
												array_keys( $format['documents'] )
											)
										) );
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4">
							<?php echo wp_kses_post( sprintf(
								/* translators: %1$s: open link anchor, %2$s: close link anchor */ 
								__( 'Hybrid formats require PDF/A-3 support, which is only available with our %1$smPDF extension%2$s version 2.6.0 or higher.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<a href="" target="_blank" rel="noopener noreferrer">',
								'</a>'
							) ); ?>
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<?php echo wp_kses_post( sprintf(
								/* translators: %s EN16931 */
								__( 'All supported formats are compliant with the European standard %s, ensuring semantic and structural consistency for cross-border electronic invoicing within the EU.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<strong>EN16931</strong>'
							) ); ?>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div>
			<h3><?php esc_html_e( 'Validation', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
			<table class="widefat striped" styles="margin-top:12px;">
				<thead>
					<tr>
						<td><?php esc_html_e( 'Type', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
						<td><?php esc_html_e( 'Validators', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php
						$validators = array(
							'xml' => array(
								'ecosio'  => 'https://ecosio.com/en/peppol-and-xml-document-validator/',
								'DIGIT'   => 'https://www.itb.ec.europa.eu/invoice/upload',
							),
							'pdfa3' => array(
								'veraPDF' => 'https://demo.verapdf.org/',
								'BFO'     => 'https://bfo.com/blog/2017/11/08/verify_pdfa_online/',
							),
						);
						
						foreach ( $validators as $type => $urls ) : ?>
							<tr>
								<td><?php echo esc_html( $type === 'xml' ? 'XML' : 'PDF/A-3' ); ?></td>
								<td>
									<?php
										$links = array();
										foreach ( $urls as $name => $url ) {
											$links[] = '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $name ) . '</a>';
										}
										echo implode( ', ', $links );
									?>
								</td>
							</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
	
	/**
	 * Output the network settings.
	 *
	 * @return void
	 */
	public function output_network(): void {
		ob_start();
		?>
		<p><?php esc_html_e( 'Send your documents through supported delivery networks directly from the plugin.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<div class="notice notice-info inline">
			<p>
				<?php esc_html_e( 'This feature is currently under development.', 'woocommerce-pdf-invoices-packing-slips' ); ?>
			</p>
		</div>
		<?php
		echo apply_filters( 'wpo_ips_edi_settings_output_network_html', ob_get_clean(), $this );
	}

}

endif; // class_exists
