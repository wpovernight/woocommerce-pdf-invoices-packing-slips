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
			'network'     => __( 'Network', 'woocommerce-pdf-invoices-packing-slips' ),
		) );

		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_edi', array( $this, 'output_settings' ), 10, 2 );
		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'save_taxes_on_calculate_order_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_taxes_on_checkout' ), 10, 3 );
		add_filter( 'pre_update_option_wpo_ips_edi_settings', array( $this, 'preserve_peppol_settings' ), 10, 3 );
		
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
				'type'     => 'setting',
				'id'       => 'syntax',
				'title'    => __( 'Preferred Syntax', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'select',
				'section'  => $section,
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'syntax',
					'options'     => wpo_ips_edi_syntaxes(),
					'description' => sprintf(
						/* translators: %s: link to documentation */
						__( 'Choose the preferred XML syntax standard for electronic documents. Need help deciding? %s', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/supported-edocument-formats/" target="_blank">' . __( 'See the format comparison guide', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
					),
				),
			),
		);

		$settings_format = array();
		foreach ( wpo_ips_edi_syntax_formats() as $syntax => $data ) {
			$description = sprintf(
				/* translators: %s syntax */
				__( 'Choose the preferred %s format.', 'woocommerce-pdf-invoices-packing-slips' ),
				strtoupper( trim( $syntax ) )
			);

			if ( 'cii' === strtolower( trim( $syntax ) ) && ! class_exists( 'WCPDF_Custom_PDF_Maker_mPDF' ) ) {
				$description .= ' ' . sprintf(
					/* translators: %1$s: open link anchor, %2$s: close link anchor */
					__( 'The %1$smPDF extension%2$s is required for hybrid formats. Please install or enable it.', 'woocommerce-pdf-invoices-packing-slips' ),
					'<a href="https://github.com/wpovernight/woocommerce-pdf-ips-mpdf/releases/latest" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
			}

			$settings_format[] = array(
				'type'     => 'setting',
				'id'       => "{$syntax}_format",
				'title'    => '',
				'callback' => 'select',
				'section'  => $section,
				'args'     => array(
					'title'             => __( 'Format', 'woocommerce-pdf-invoices-packing-slips' ),
					'option_name'       => $option_name,
					'id'                => "{$syntax}_format",
					'options'           => array_combine(
						array_keys( $data['formats'] ),
						array_column( $data['formats'], 'name' )
					),
					'description'       => wp_kses_post( $description ),
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
					__( 'Specify the Peppol Endpoint ID for the supplier. Do not include the scheme (e.g., "0208:"), as it can be selected below.', 'woocommerce-pdf-invoices-packing-slips' ),
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
					'data-keep_current_value'     => true,
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
					'data-keep_current_value'     => true,
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
						__( 'Please make sure the field is filled out in the %1$sShop Information%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=general#shop_information' ) ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					)
				),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
					'data-keep_current_value'     => true,
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
					'data-keep_current_value'     => true,
				),
			),
		);

		// // Peppol specific field (Default is full mode; may change in the future.)
		// $settings_fields[] = array(
		// 	'type'     => 'setting',
		// 	'id'       => 'peppol_customer_identifiers_input_mode',
		// 	'title'    => '',
		// 	'callback' => 'select',
		// 	'section'  => $section,
		// 	'args'     => array(
		// 		'title'             => __( 'Customer Peppol Identifiers Input Mode', 'woocommerce-pdf-invoices-packing-slips' ),
		// 		'option_name'       => $option_name,
		// 		'id'                => 'peppol_customer_identifiers_input_mode',
		// 		'options'           => array(
		// 			'select' => __( 'Customer selects scheme and enters identifier separately', 'woocommerce-pdf-invoices-packing-slips' ),
		// 			'full'   => __( 'Customer enters full ID (e.g., 0088:123456789)', 'woocommerce-pdf-invoices-packing-slips' ),
		// 		),
		// 		'description'       => __( 'Determines how the customer provides their Peppol Endpoint ID and Legal Entity Identifier. This applies to both fields.', 'woocommerce-pdf-invoices-packing-slips' ),
		// 		'custom_attributes' => array(
		// 			'data-show_for_option_name'   => $option_name . '[ubl_format]',
		// 			'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
		// 			'data-keep_current_value'     => true,
		// 		),
		// 	),
		// );

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
				'default'           => 'both',
				'options'           => array(
					'checkout'   => __( 'Checkout only', 'woocommerce-pdf-invoices-packing-slips' ),
					'my_account' => __( 'My Account only', 'woocommerce-pdf-invoices-packing-slips' ),
					'both'       => __( 'Both Checkout and My Account', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'description'       => __( 'The Legal Entity Identifier is shown in My Account only. The Endpoint Identifier follows the option selected above.', 'woocommerce-pdf-invoices-packing-slips' ),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => json_encode( array( 'peppol-bis-3p0' ) ),
					'data-keep_current_value'     => true,
				),
			),
		);
		
		// Peppol specific field
		if ( ! wpo_wcpdf_checkout_is_block() || ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.9.0', '>=' ) ) ) {
			$settings_fields[] = array(
				'type'     => 'setting',
				'id'       => 'peppol_endpoint_id_checkout_visibility',
				'title'    => '',
				'callback' => 'select',
				'section'  => $section,
				'args'     => array(
					'title'             => __( 'Endpoint ID field visibility at checkout', 'woocommerce-pdf-invoices-packing-slips' ),
					'option_name'       => $option_name,
					'id'                => 'peppol_endpoint_id_checkout_visibility',
					'default'           => 'always',
					'options'           => array(
						'always'  => __( 'Always', 'woocommerce-pdf-invoices-packing-slips' ),
						'toggle'  => __( 'On business purchase selection', 'woocommerce-pdf-invoices-packing-slips' ),
						'company' => __( 'When company name is present', 'woocommerce-pdf-invoices-packing-slips' ),
					),
					'description'       => __( 'Controls when the customer Peppol Endpoint ID field is shown at checkout.', 'woocommerce-pdf-invoices-packing-slips' ),
					'custom_attributes' => array(
						'data-show_for_option_name'   => $option_name . '[ubl_format]',
						'data-show_for_option_values' => wp_json_encode( array( 'peppol-bis-3p0' ) ),
						'data-keep_current_value'     => true,
					),
				),
			);
		}
		
		// Peppol specific field
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'peppol_directory_validation',
			'title'    => '',
			'callback' => 'checkbox',
			'section'  => $section,
			'args'     => array(
				'title'       => __( 'Validate customer Endpoint ID', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name' => $option_name,
				'id'          => 'peppol_directory_validation',
				'description' => __(
					'When enabled, the customer Peppol Endpoint ID entered at checkout or in the My Account area is validated against the Peppol Directory. If no matching participant is found, an error is shown so the value can be corrected.',
					'woocommerce-pdf-invoices-packing-slips'
				),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[ubl_format]',
					'data-show_for_option_values' => wp_json_encode( array( 'peppol-bis-3p0' ) ),
					'data-keep_current_value'     => true,
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
			'title'    => '',
			'callback' => 'checkbox',
			'section'  => $section,
			'args'     => array(
				'title'             => __( 'Embed Encrypted PDF', 'woocommerce-pdf-invoices-packing-slips' ),
				'option_name'       => $option_name,
				'id'                => 'embed_encrypted_pdf',
				'description'       => __( 'Embed the encrypted PDF invoice file within the e-document.', 'woocommerce-pdf-invoices-packing-slips' ),
				'custom_attributes' => array(
					'data-show_for_option_name'   => $option_name . '[syntax]',
					'data-show_for_option_values' => json_encode( array( 'ubl' ) ),
				),
			)
		);
		
		$settings_fields[] = array(
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
		);
		
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'include_item_meta',
			'title'    =>  __( 'Include Item Meta Data', 'woocommerce-pdf-invoices-packing-slips' ),
			'callback' => 'checkbox',
			'section'  => $section,
			'args'     => array(
				'option_name' => $option_name,
				'id'          => 'include_item_meta',
				'description' => sprintf(
					'%s %s',
					__( 'Include item meta data in the e-document.', 'woocommerce-pdf-invoices-packing-slips' ),
					sprintf(
						/* translators: 1: opening link tag, 2: closing link tag */
						__( 'Advanced customization is possible, see the %1$sdocumentation page%2$s for details.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/customize-the-item-meta-included-in-e-documents/" target="_blank" rel="noopener noreferrer">',
						'</a>'
					)
				),
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
	 * Preserve Peppol settings on update.
	 *
	 * @param mixed $value
	 * @param mixed $old_value
	 * @param string $option
	 *
	 * @return array
	 */
	public function preserve_peppol_settings( $value, $old_value, string $option ): array {
		$new = is_array( $value )     ? $value     : array();
		$old = is_array( $old_value ) ? $old_value : array();
		
		foreach ( $new as $key => $val ) {
			if ( false !== strpos( $key, 'peppol_' ) ) {
				// preserve old value on empty new value
				if ( empty( $val ) && ! empty( $old[ $key ] ) ) {
					$new[ $key ] = $old[ $key ];
				// normalize endpoint ID
				} elseif ( ! empty( $val ) && 'peppol_endpoint_id' === $key ) {
					$new[ $key ] = preg_replace( '/^[^:]+:/', '', trim( $val ) );
				}
			}
		}
		
		return $new;
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
		wpo_ips_edi_maybe_save_order_peppol_data( $order, $posted_data );
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
	 * Save taxes from AJAX request.
	 *
	 * @return void
	 */
	public function ajax_save_taxes(): void {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if (
			! isset( $_POST['action'] ) ||
			'wpo_ips_edi_save_taxes' !== $_POST['action'] ||
			! wp_verify_nonce( $nonce, 'edi_save_taxes' )
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

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
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
									<?php echo wp_kses_post( $display ); ?>
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
									'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=general#shop_information' ) ) . '" class="edi-complete-details" target="_blank">' . esc_html__( 'Shop Information', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
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
								<button type="button" class="button button-primary" id="edi-customer-order-id-search-button"><?php esc_html_e( 'Search', 'woocommerce-pdf-invoices-packing-slips' ); ?></button>
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
	 * Save the tax settings
	 *
	 * @param array $tax_settings
	 * @return void
	 */
	private function save_tax_settings( array $tax_settings = array() ): void {
		update_option( 'wpo_ips_edi_tax_settings', $tax_settings );
	}

	/**
	 * Output the settings page for UBL taxes.
	 *
	 * @return void
	 */
	private function output_taxes(): void {
		$formatted_rates = array(
			'standard' => __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' )
		);

		// Woo 5.2+
		if ( is_callable( array( '\WC_Tax', 'get_tax_rate_classes' ) ) ) {
			$rates = \WC_Tax::get_tax_rate_classes();

			foreach ( $rates as $rate ) {
				if ( empty( $rate->slug ) ) {
					continue;
				}

				$formatted_rates[ $rate->slug ] = ! empty( $rate->name ) ? $rate->name : $rate->slug;
			}

		// Older Woo versions
		} else {
			$slugs = \WC_Tax::get_tax_class_slugs();
			$names = \WC_Tax::get_tax_classes();

			foreach ( $slugs as $i => $slug ) {
				if ( empty( $slug ) ) {
					continue;
				}

				$name                     = ! empty( $names[ $i ] ) ? $names[ $i ] : $slug;
				$formatted_rates[ $slug ] = $name;
			}
		}
		?>
			<p><?php esc_html_e( 'To ensure compliance with e-invoicing requirements, please complete the Taxes Classification. This information is essential for accurately generating legally compliant invoices.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<?php $this->output_tax_class_selector_and_action( $formatted_rates ); // Output tax class selector and action button ?>
			<?php foreach ( $formatted_rates as $slug => $name ) : ?>
				<div class="edi-tax-class-table" data-tax-class="<?php echo esc_attr( $slug ); ?>" style="display:none;">
					<?php $this->output_table_for_tax_class( $slug ); ?>
				</div>
			<?php endforeach; ?>
			<button
				type="button"
				class="button button-primary button-wpo button-edi-save-taxes"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'edi_save_taxes' ) ); ?>"
				data-action="wpo_ips_edi_save_taxes">
				<?php esc_html_e( 'Save Taxes', 'woocommerce-pdf-invoices-packing-slips' ); ?>
			</button>
			<div id="edi-tax-save-notice" class="notice inline" style="display:none;"></div>
		<?php
	}

	/**
	 * Output the tax class selector and action button.
	 *
	 * @param array $formatted_rates An associative array of tax class slugs and names.
	 * @return void
	 */
	private function output_tax_class_selector_and_action( array $formatted_rates ): void {
		$nonce        = wp_create_nonce( 'edi_save_taxes' );
		$action       = 'wpo_ips_edi_save_taxes';
		$current_slug = isset( $_GET['edi_tax_class'] ) ? sanitize_text_field( wp_unslash( $_GET['edi_tax_class'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $current_slug ) && ! empty( $formatted_rates ) ) {
			$current_slug = (string) array_key_first( $formatted_rates );
		}
		?>
		<div class="edi-tax-class-group" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-action="<?php echo esc_attr( $action ); ?>">
			<ul class="doc-output-toggle-group">
				<?php foreach ( $formatted_rates as $slug => $name ) :
					$is_active    = ( $slug === $current_slug );
					$active_class = $is_active ? 'active' : '';
					$href         = add_query_arg( 'edi_tax_class', $slug ); // graceful fallback
					?>
					<li class="doc-output-toggle-item">
						<a
							href="<?php echo esc_url( $href ); ?>"
							class="doc-output-toggle <?php echo esc_attr( $active_class ); ?>"
							data-tax-class="<?php echo esc_attr( $slug ); ?>">
							<?php echo esc_html( $name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Output the default tax classification panel.
	 *
	 * @param string $slug The slug of the tax class.
	 *
	 * @return void
	 */
	private function output_default_tax_classification_panel( string $slug ): void {
		$edi_tax_settings = wpo_ips_edi_get_tax_settings();

		$scheme   = $edi_tax_settings['class'][ $slug ]['scheme']   ?? '';
		$category = $edi_tax_settings['class'][ $slug ]['category'] ?? '';
		$reason   = $edi_tax_settings['class'][ $slug ]['reason']   ?? '';
		?>
			<div class="edi-tax-defaults-card" aria-labelledby="edi-tax-defaults-title">
				<h3><?php esc_html_e( 'Configure default tax classifications', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
				<p class="description">
					<?php
						esc_html_e(
							'These defaults will be applied when "Default" is selected for individual tax rates.',
							'woocommerce-pdf-invoices-packing-slips'
						);
					?>
				</p>
				<p class="description">
					<?php
						printf(
							/* translators: %1$s: open link anchor, %2$s: close link anchor */
							esc_html__( 'For more information on setting up the tax table, please see our %1$sarticle%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/e-documents/configuring-tax-classifications-for-e-documents/" target="_blank" rel="noopener noreferrer">',
							'</a>'
						);
					?>
				</p>
				<div class="edi-tax-defaults-grid">
					<div class="edi-tax-defaults-field">
						<h4><?php esc_html_e( 'Tax Scheme', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
						<div class="edi-tax-defaults-control">
							<?php $this->output_tax_selector_for( 'scheme', 'class', $slug, $scheme ); ?>
							<div class="current<?php echo empty( $scheme ) ? ' hidden' : ''; ?>">
								<?php esc_html_e( 'Code', 'woocommerce-pdf-invoices-packing-slips' ); ?>:
								<code><?php echo esc_html( $scheme ); ?></code>
							</div>
						</div>
					</div>
					<div class="edi-tax-defaults-field">
						<h4><?php esc_html_e( 'Tax Category', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
						<div class="edi-tax-defaults-control">
							<?php $this->output_tax_selector_for( 'category', 'class', $slug, $category ); ?>
							<div class="current<?php echo empty( $category ) ? ' hidden' : ''; ?>">
								<?php esc_html_e( 'Code', 'woocommerce-pdf-invoices-packing-slips' ); ?>:
								<code><?php echo esc_html( $category ); ?></code>
							</div>
						</div>
					</div>
					<div class="edi-tax-defaults-field">
						<h4><?php esc_html_e( 'Reason', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
						<div class="edi-tax-defaults-control">
							<?php $this->output_tax_selector_for( 'reason', 'class', $slug, $reason ); ?>
							<div class="current<?php echo empty( $reason ) ? ' hidden' : ''; ?>">
								<?php esc_html_e( 'Code', 'woocommerce-pdf-invoices-packing-slips' ); ?>:
								<code><?php echo esc_html( $reason ); ?></code>
							</div>
						</div>
					</div>
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
	private function output_table_for_tax_class( string $slug ): void {
		global $wpdb;

		$edi_tax_settings = wpo_ips_edi_get_tax_settings();
		$table_name       = "{$wpdb->prefix}woocommerce_tax_rates";
		$slug             = sanitize_key( strtolower( $slug ) );

		$query = wpo_wcpdf_prepare_identifier_query(
			"SELECT * FROM %i WHERE tax_rate_class = %s ORDER BY tax_rate_country ASC, tax_rate_state ASC;",
			array( $table_name ),
			array( ( 'standard' === $slug ) ? '' : $slug )
		);

		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		$locations_by_rate = $this->get_locations_by_rate_ids( $results );

		$this->output_default_tax_classification_panel( $slug );
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
								$location_results = isset( $locations_by_rate[ $result->tax_rate_id ] ) ? $locations_by_rate[ $result->tax_rate_id ] : array();
								$postcode         = array();
								$city             = array();

								foreach ( $location_results as $location_result ) {
									if ( ! isset( $location_result->location_type ) ) {
										continue;
									}

									switch ( $location_result->location_type ) {
										case 'postcode':
											$postcode[] = $location_result->location_code;
											break;
										case 'city':
											$city[] = $location_result->location_code;
											break;
									}
								}

								$country          = empty( $result->tax_rate_country ) ? '*' : $result->tax_rate_country;
								$state            = empty( $result->tax_rate_state ) ? '*' : $result->tax_rate_state;
								$postcode         = empty( $postcode ) ? '*' : implode( '; ', $postcode );
								$city             = empty( $city ) ? '*' : implode( '; ', $city );

								$scheme           = isset( $edi_tax_settings['rate'][ $result->tax_rate_id ]['scheme'] )   ? $edi_tax_settings['rate'][ $result->tax_rate_id ]['scheme']   : 'default';
								$scheme_default   = isset( $edi_tax_settings['class'][ $slug ]['scheme'] ) ? $edi_tax_settings['class'][ $slug ]['scheme'] : '';
								$scheme_code      = ( 'default' === $scheme ) ? $scheme_default : $scheme;

								$category         = isset( $edi_tax_settings['rate'][ $result->tax_rate_id ]['category'] ) ? $edi_tax_settings['rate'][ $result->tax_rate_id ]['category'] : 'default';
								$category_default = isset( $edi_tax_settings['class'][ $slug ]['category'] ) ? $edi_tax_settings['class'][ $slug ]['category'] : '';
								$category_code    = ( 'default' === $category ) ? $category_default : $category;

								$reason           = isset( $edi_tax_settings['rate'][ $result->tax_rate_id ]['reason'] )   ? $edi_tax_settings['rate'][ $result->tax_rate_id ]['reason']   : 'default';
								$reason_default   = isset( $edi_tax_settings['class'][ $slug ]['reason'] ) ? $edi_tax_settings['class'][ $slug ]['reason'] : '';
								$reason_code      = ( 'default' === $reason ) ? $reason_default : $reason;

								echo '<tr>';
								echo '<td>' . esc_html( $country ) . '</td>';
								echo '<td>' . esc_html( $state ) . '</td>';
								echo '<td>' . esc_html( $postcode ) . '</td>';
								echo '<td>' . esc_html( $city ) . '</td>';
								echo '<td>' . esc_html( wc_round_tax_total( $result->tax_rate ) ) . '%</td>';
								echo '<td>';
								$this->output_tax_selector_for( 'scheme', 'rate', $result->tax_rate_id, $scheme );
								echo '<div class="current' . ( empty( $scheme_code ) ? ' hidden' : '' ) . '" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $scheme_code ) . '</code></div>';
								echo '</td>';
								echo '<td>';
								$this->output_tax_selector_for( 'category', 'rate', $result->tax_rate_id, $category );
								echo '<div class="current' . ( empty( $category_code ) ? ' hidden' : '' ) . '" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $category_code ) . '</code></div>';
								echo '</td>';
								echo '<td>';
								$this->output_tax_selector_for( 'reason', 'rate', $result->tax_rate_id, $reason );
								echo '<div class="current' . ( empty( $reason_code ) ? ' hidden' : '' ) . '" style="margin-top:6px;">' . esc_html__( 'Code', 'woocommerce-pdf-invoices-packing-slips' ) . ': <code>' . esc_html( $reason_code ) . '</code></div>';
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
			</table>
		<?php
	}

	/**
	 * Output the network settings.
	 *
	 * @return void
	 */
	private function output_network(): void {
		ob_start();
		?>
		<p><?php esc_html_e( 'Send your documents through supported delivery networks directly from the plugin.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<div id="plugin-recommendations">
			<h1><?php esc_html_e( 'Network Products', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
			<div class="card-container">
				<div class="recommendation-card">
					<img src="<?php echo WPO_WCPDF()->plugin_url() . '/assets/images/wpo-ips-edocs-network-peppol-400x400.jpg' ?>" alt="Peppol">
					<div class="card-content">
						<h5>Peppol</h5>
						<p><?php esc_html_e( 'Peppol is a network for electronic document exchange, enabling businesses to send and receive electronic invoices and other documents securely and efficiently.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
						<?php printf( '<a class="upgrade_button" target="_blank" href="%s">%s</a>', esc_url( 'https://wpovernight.com/downloads/woocommerce-edocuments-peppol/?utm_medium=plugin&utm_source=ips&utm_campaign=upgrade-tab&utm_content=peppol-network-cross' ), esc_html__( 'Buy now', 'woocommerce-pdf-invoices-packing-slips' ) ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		echo apply_filters( 'wpo_ips_edi_settings_output_network_html', ob_get_clean(), $this ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	
	/**
	 * Output a tax selector for a specific context.
	 *
	 * @param string $for
	 * @param string $type
	 * @param string $id
	 * @param string $selected
	 *
	 * @return void
	 */
	private function output_tax_selector_for( string $for, string $type, string $id, string $selected ): void {
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
				$key   = '';
				$value = __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...';
			}

			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}

		foreach ( $options as $key => $value ) {
			$select .= '<option ' . selected( $key, $selected, false ) . ' value="' . $key . '">' . $value . '</option>';
		}

		$select .= '</select>';

		echo wp_kses( $select, $allowed_html );
	}
	
	/**
	 * Get tax rate locations grouped by tax_rate_id for a given set of tax rate rows.
	 *
	 * @param array $results
	 * @return array
	 */
	private function get_locations_by_rate_ids( array $results ): array {
		global $wpdb;

		$locations_by_rate = array();

		if ( empty( $results ) ) {
			return $locations_by_rate;
		}

		$rate_ids = array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( $row ) {
							return isset( $row->tax_rate_id ) ? (int) $row->tax_rate_id : 0;
						},
						$results
					)
				)
			)
		);

		if ( empty( $rate_ids ) ) {
			return $locations_by_rate;
		}

		$placeholders = implode( ',', array_fill( 0, count( $rate_ids ), '%d' ) );
		$sql          = "SELECT tax_rate_id, location_type, location_code
			FROM {$wpdb->prefix}woocommerce_tax_rate_locations
			WHERE tax_rate_id IN ( {$placeholders} )";

		$loc_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( $sql, $rate_ids ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		if ( empty( $loc_rows ) ) {
			return $locations_by_rate;
		}

		foreach ( $loc_rows as $loc_row ) {
			$id = (int) $loc_row->tax_rate_id;

			if ( ! isset( $locations_by_rate[ $id ] ) ) {
				$locations_by_rate[ $id ] = array();
			}

			$locations_by_rate[ $id ][] = $loc_row;
		}

		return $locations_by_rate;
	}

}

endif; // class_exists
