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
			'edi' => __( 'E-Documents', 'woocommerce-pdf-invoices-packing-slips' ),
		];

		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_edi', array( $this, 'output' ), 10, 2 );

		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'save_taxes_on_order_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_taxes_on_checkout' ), 10, 3 );

		// VAT number or COC number is empty
		add_action( 'admin_notices', array( $this, 'vat_coc_required_for_ubl_invoice') );
		add_action( 'admin_notices', array( '\\WPO\\IPS\\EDI\\TaxesSettings', 'standard_update_notice' ) );
	}

	public function output( $active_section, $nonce ) {
		if ( ! wp_verify_nonce( $nonce, 'wp_wcpdf_settings_page_nonce' ) ) {
			return;
		}
		
		$active_section = ! empty( $active_section ) ? $active_section : 'edi';
		?>
		<div class="wcpdf_ubl_settings_sections">
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
				default:
				case 'edi':
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

	public function vat_coc_required_for_ubl_invoice() {
		$invoice_ubl_settings = WPO_WCPDF()->settings->get_document_settings( 'invoice', 'ubl' );

		if ( isset( $invoice_ubl_settings['enabled'] ) && ( ! isset( WPO_WCPDF()->settings->general_settings['vat_number'] ) || ! isset( WPO_WCPDF()->settings->general_settings['coc_number'] ) ) ) {
			$message = sprintf(
				/* translators: 1. General Settings, 2. UBL Settings  */
				__( 'You\'ve enabled UBL output for a document, but some essential details are missing. Please ensure you\'ve added your VAT and CoC numbers in the %1$s. Also, specify your tax rates in the %2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page' ) ) . '">' . __( 'General settings', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>',
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=ubl' ) ) . '">' . __( 'UBL settings', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
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

}

endif; // class_exists
