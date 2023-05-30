<?php
namespace WPO\WC\PDF_Invoices;

use \WPO\WC\UBL\Settings\TaxesSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings_UBL' ) ) :

class Settings_UBL {
	
	public $sections;

	function __construct()	{
		$this->sections = [
			'general' => __( 'General', 'woocommerce-pdf-invoices-packing-slips' ),
			'taxes'   => __( 'Taxes', 'woocommerce-pdf-invoices-packing-slips' ),
		];
		
		add_action( 'admin_init', array( $this, 'init_general_settings' ) );
		add_action( 'admin_init', array( $this, 'init_tax_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_ubl', array( $this, 'output' ), 10, 1 );
	}

	public function output( $active_section ) {
		$active_section = ! empty( $active_section ) ? $active_section : 'general';
		?>
		<div class="wcpdf_ubl_settings_sections">
			<h2 class="nav-tab-wrapper">
				<?php
					foreach ( $this->sections as $section => $title ) {
						$active = ( $section == $active_section ) ? 'nav-tab-active' : '';
						printf( '<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', esc_url( add_query_arg( 'section', $section ) ), esc_attr( $section ), $active, esc_html( $title ) );
					}
				?>
			</h2>
		</div>
		<?php
		
		switch ( $active_section ) {
			default:
			case 'general':
				settings_fields( 'wpo_wcpdf_settings_ubl' );
				do_settings_sections( 'wpo_wcpdf_settings_ubl' );

				submit_button();
				break;
			case 'taxes':
				$setting = new TaxesSettings();
				$setting->output();
				break;
		}
	}

	public function init_general_settings() {
		$page = $option_group = $option_name = 'wpo_wcpdf_settings_ubl';

		$settings_fields = array(
			array(
				'type'     => 'section',
				'id'       => 'general',
				'title'    => '',
				'callback' => 'section',
			),
			array(
				'type'     => 'setting',
				'id'       => 'company_name',
				'title'    => __( 'Company Name', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'text_input',
				'section'  => 'general',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'company_name',
					'size'        => '42',
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'vat_number',
				'title'    => __( 'VAT Number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'text_input',
				'section'  => 'general',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'vat_number',
					'size'        => '42',
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'coc_number',
				'title'    => __( 'Chamber of Commerce Number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'text_input',
				'section'  => 'general',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'coc_number',
					'size'        => '42',
				)
			),
		);

		// load invoice to reuse method to get wc emails
		$invoice = wcpdf_get_invoice( null );

		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'attach_to_email_ids',
			'title'    => __( 'Attach UBL to:', 'woocommerce-pdf-invoices-packing-slips' ),
			'callback' => 'multiple_checkboxes',
			'section'  => 'general',
			'args'     => array(
				'option_name' => $option_name,
				'id'          => 'attach_to_email_ids',
				'fields'      => $invoice->get_wc_emails(),
			)
		);
		
		$settings_fields[] = array(
			'type'     => 'setting',
			'id'       => 'include_encrypted_pdf',
			'title'    => __( 'Include encrypted PDF:', 'woocommerce-pdf-invoices-packing-slips' ),
			'callback' => 'checkbox',
			'section'  => 'general',
			'args'     => array(
				'option_name' => $option_name,
				'id'          => 'include_encrypted_pdf',
				'description' => __( 'Include the PDF Invoice file encrypted in the UBL file.', 'woocommerce-pdf-invoices-packing-slips' ),
			)
		);

		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}
	
	public function init_tax_settings() {
		$page = $option_group = $option_name = 'wpo_wcpdf_settings_ubl_taxes';

		$settings_fields = array(
			array(
				'type'     => 'section',
				'id'       => 'taxes',
				'title'    => '',
				'callback' => 'section',
			),
			array(
				'type'     => 'setting',
				'id'       => 'ubl_wc_taxes',
				'title'    => __( 'Taxes settings for UBL', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'string',
				'section'  => 'taxes',
				'args'     => [],
			),
		);

		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

}

endif; // class_exists

return new Settings_UBL();