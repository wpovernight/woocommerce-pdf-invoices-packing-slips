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
		
		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'save_taxes_on_order_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_taxes_on_checkout' ), 10, 3 );
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
	
	public function save_taxes_on_order_totals( $and_taxes, $order ) {
		// it seems $and taxes is mostly false, meaning taxes are calculated separately,
		// but we still update just in case anything changed
		if ( ! empty( $order ) ) {
			$this->save_order_taxes( $order );
		}
	}
	
	public function save_taxes_on_checkout( $order_id, $posted_data, $order ) {
		if ( empty( $order ) && ! empty( $order_id ) ) {
			$order = wc_get_order( $order_id );
		}
		
		if ( $order ) {
			$this->save_order_taxes( $order );
		}
	}
	
	public function save_order_taxes( $order ) {
		foreach ( $order->get_taxes() as $item_id => $tax_item ) {
			if ( is_a( $tax_item, '\WC_Order_Item_Tax' ) && is_callable( array( $tax_item, 'get_rate_id' ) ) ) {
				// get tax rate id from item
				$tax_rate_id = $tax_item->get_rate_id();
				
				// read tax rate data from db
				if ( class_exists( '\WC_TAX' ) && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
					$tax_rate = \WC_Tax::_get_tax_rate( $tax_rate_id, OBJECT );
					if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
						// store percentage in tax item meta
						wc_update_order_item_meta( $item_id, '_wcpdf_rate_percentage', $tax_rate->tax_rate );

						$ubl_tax_settings = get_option('wpo_wcpdf_settings_ubl_taxes');

						$category       = isset( $ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['category'] ) ? $ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['category'] : '';
						$scheme         = isset( $ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['scheme'] ) ? $ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['scheme'] : '';
						$tax_rate_class = $tax_rate->tax_rate_class;
						
						if ( empty( $tax_rate_class ) ) {
							$tax_rate_class = 'standard';
						}

						if ( empty( $category ) ) {
							$category = isset( $ubl_tax_settings['class'][$tax_rate_class]['category'] ) ? $ubl_tax_settings['class'][$tax_rate_class]['category'] : '';
						}

						if ( empty( $scheme ) ) {
							$scheme = isset( $ubl_tax_settings['class'][$tax_rate_class]['scheme'] ) ? $ubl_tax_settings['class'][$tax_rate_class]['scheme'] : '';
						}

						if ( ! empty( $category ) ) {
							wc_update_order_item_meta( $item_id, '_wcpdf_ubl_tax_category', $category );
						}

						if ( ! empty( $scheme ) ) {
							wc_update_order_item_meta( $item_id, '_wcpdf_ubl_tax_scheme', $scheme );
						}
					}
				}
			}
		}
	}

}

endif; // class_exists

return new Settings_UBL();