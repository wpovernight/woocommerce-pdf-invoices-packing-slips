<?php
namespace WPO\IPS\Settings;

use WPO\IPS\EDI\TaxesSettings as EdiTaxSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsEDI' ) ) :

class SettingsEDI {

	public $sections;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function __construct()	{
		$this->sections = [
			'settings'    => __( 'Settings', 'woocommerce-pdf-invoices-packing-slips' ),
			'identifiers' => __( 'Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'taxes'       => __( 'Taxes', 'woocommerce-pdf-invoices-packing-slips' ),
		];

		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_edi', array( $this, 'output' ), 10, 2 );

		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'save_taxes_on_order_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_taxes_on_checkout' ), 10, 3 );
		
		// Admin notices
		add_action( 'admin_notices', array( $this, 'vat_coc_required') );
		add_action( 'admin_notices', array( '\\WPO\\IPS\\EDI\\TaxesSettings', 'standard_update_notice' ) );
		
		// AJAX
		add_action( 'wp_ajax_wpo_ips_edi_save_taxes', array( '\\WPO\\IPS\\EDI\\TaxesSettings', 'ajax_save_taxes' ) );
		add_action( 'wp_ajax_wpo_ips_edi_reload_tax_table', array( '\\WPO\\IPS\\EDI\\TaxesSettings', 'ajax_reload_tax_table' ) );
	}

	public function output( $active_section, $nonce ) {
		if ( ! wp_verify_nonce( $nonce, 'wp_wcpdf_settings_page_nonce' ) ) {
			return;
		}
		
		$active_section = ! empty( $active_section ) ? $active_section : 'settings';
		?>
		<div class="wcpdf_settings_sections">
			<?php if ( count( $this->sections ) > 1 ) : ?>
				<h2 class="nav-tab-wrapper">
					<?php
						foreach ( $this->sections as $section => $title ) {
							$active = ( $section == $active_section ) ? 'nav-tab-active' : '';
							printf( '<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', esc_url( add_query_arg( 'section', $section ) ), esc_attr( $section ), esc_attr( $active ), esc_html( $title ) );
						}
					?>
				</h2>
			<?php else : ?>
				<h3><?php echo esc_html( $this->sections[ $active_section ] ); ?></h3>
			<?php endif; ?>
		</div>
		<?php
			switch ( $active_section ) {
				case 'settings':
					settings_fields( 'wpo_ips_edi_settings' );
					do_settings_sections( 'wpo_ips_edi_settings' );
					submit_button();
					break;
				case 'identifiers':
					$this->display_company_identifiers();
					break;
				case 'taxes':
					$settings = new EdiTaxSettings();
					$settings->output();
					break;
			}
	}

	public function init_settings() {
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
					'options'     => array_merge(
						array(
							'' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...',
						),
						wpo_ips_edi_syntaxes()
					),
					'description' => __( 'Choose the preferred XML syntax standard for electronic documents.', 'woocommerce-pdf-invoices-packing-slips' ),
				),
			),
		);
		
		$settings_format = array();
		foreach ( wpo_ips_edi_syntaxes() as $syntax => $name ) {
			$formats = wpo_ips_edi_formats( $syntax );
			
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
					'options'           =>  array_merge(
						array(
							'' => __( 'Select', 'woocommerce-pdf-invoices-packing-slips' ) . '...',
						),
						array_combine(
							array_keys( $formats ),
							array_column( $formats, 'name' )
						)
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

		$settings_fields = apply_filters( 'wpo_ips_edi_settings', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

	public function save_taxes_on_order_totals( $and_taxes, $order ) {
		// it seems $and taxes is mostly false, meaning taxes are calculated separately,
		// but we still update just in case anything changed
		if ( ! empty( $order ) ) {
			wpo_ips_edi_save_order_taxes( $order );
		}
	}

	public function save_taxes_on_checkout( $order_id, $posted_data, $order ) {
		if ( empty( $order ) && ! empty( $order_id ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( $order ) {
			wpo_ips_edi_save_order_taxes( $order );
		}
	}

	public function vat_coc_required() {
		if (
			wpo_ips_edi_is_available() &&
			(
				! isset( WPO_WCPDF()->settings->general_settings['vat_number'] ) ||
				! isset( WPO_WCPDF()->settings->general_settings['coc_number'] )
			)
		) {
			$message = sprintf(
				/* translators: 1. General Settings, 2. EDI Settings  */
				__( 'You\'ve enabled E-Documents output, but some essential details are missing. Please ensure you\'ve added your VAT and CoC numbers in the %1$s. Also, specify your tax rates in the %2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) ) . '">' . __( 'General settings', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>',
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=edi' ) ) . '">' . __( 'E-Documents settings', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
			);

			echo '<div class="notice notice-warning"><p>' . wp_kses_post( $message ) . '</p></div>';
		}
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
	 * Display company identifiers.
	 *
	 * @return void
	 */
	private function display_company_identifiers(): void {
		$general_settings = WPO_WCPDF()->settings->general;
		$languages_data   = wpo_wcpdf_get_multilingual_languages();
		$languages        = $languages_data ? array_keys( $languages_data ) : array( 'default' );
		
		echo '<p>' . esc_html__( 'Please fill in your company identifiers. These details are essential for generating electronic documents.', 'woocommerce-pdf-invoices-packing-slips' ) . '</p>';
		
		if ( count( $languages ) > 1 ) {
			echo '<label for="wcpdf-language-selector"><strong>' . esc_html__( 'Select language', 'woocommerce-pdf-invoices-packing-slips' ) . ':</strong></label> ';
			echo '<select id="wcpdf-language-selector" class="wcpdf-language-selector" style="margin-bottom: 10px;">';
			foreach ( $languages as $language ) {
				echo '<option value="' . esc_attr( $language ) . '">' . esc_html( $language ) . '</option>';
			}
			echo '</select>';
		}

		foreach ( $languages as $language ) {
			$company_name = $general_settings->get_setting( 'shop_name', $language ) ?? '';
			$line_1       = $general_settings->get_setting( 'shop_address_line_1', $language ) ?? '';
			$postcode     = $general_settings->get_setting( 'shop_address_postcode', $language ) ?? '';
			$city         = $general_settings->get_setting( 'shop_address_city', $language ) ?? '';
			$country      = $general_settings->get_setting( 'shop_address_country', $language ) ?? '';
			$states       = wpo_wcpdf_get_country_states( $country );
			$state        = ! empty( $states ) ? $general_settings->get_setting( 'shop_address_state', $language ) : '';
			$vat_number   = $general_settings->get_setting( 'vat_number', $language ) ?? '';
			$reg_number   = $general_settings->get_setting( 'coc_number', $language ) ?? '';
			$email        = $general_settings->get_setting( 'shop_email_address', $language ) ?? '';

			echo '<div class="language-block" id="lang-' . esc_attr( $language ) . '" style="display:none;">';
			echo '<table class="widefat striped">';
			echo '<thead><tr><th>' . esc_html__( 'Field', 'woocommerce-pdf-invoices-packing-slips' ) . '</th><th>' . esc_html__( 'Value', 'woocommerce-pdf-invoices-packing-slips' ) . '</th></tr></thead>';
			echo '<tbody>';

			$rows = array(
				__( 'Company name', 'woocommerce-pdf-invoices-packing-slips' )        => array( $company_name, true ),
				__( 'Street address', 'woocommerce-pdf-invoices-packing-slips' )      => array( $line_1, true ),
				__( 'Postcode', 'woocommerce-pdf-invoices-packing-slips' )            => array( $postcode, true ),
				__( 'City', 'woocommerce-pdf-invoices-packing-slips' )                => array( $city, true ),
				__( 'State', 'woocommerce-pdf-invoices-packing-slips' )               => array( $state, false ),
				__( 'Country', 'woocommerce-pdf-invoices-packing-slips' )             => array( $country, true ),
				__( 'VAT number', 'woocommerce-pdf-invoices-packing-slips' )          => array( $vat_number, true ),
				__( 'Registration number', 'woocommerce-pdf-invoices-packing-slips' ) => array( $reg_number, false ),
				__( 'Email', 'woocommerce-pdf-invoices-packing-slips' )               => array( $email, true ),
			);

			foreach ( $rows as $label => $data ) {
				$value    = $data[0];
				$required = $data[1];

				$display = $value ?: sprintf(
					'<span style="color:%s">%s</span>',
					$required ? '#d63638' : '#2271b1',
					$required ? esc_html__( 'Missing', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'Optional', 'woocommerce-pdf-invoices-packing-slips' ),
				);

				echo '<tr>';
				echo '<td>' . esc_html( $label ) . '</td>';
				echo '<td>' . $display . '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
			echo '</div>';
		}
	}

}

endif; // class_exists
