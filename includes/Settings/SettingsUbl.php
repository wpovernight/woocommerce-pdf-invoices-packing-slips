<?php
namespace WPO\IPS\Settings;

use WPO\IPS\UBL\Settings\TaxesSettings as UblTaxSettings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsUbl' ) ) :

class SettingsUbl {

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
			'taxes' => __( 'Taxes classification', 'woocommerce-pdf-invoices-packing-slips' ),
		];

		add_action( 'admin_init', array( $this, 'init_tax_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_ubl', array( $this, 'output' ), 10, 1 );

		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'save_taxes_on_order_totals' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'save_taxes_on_checkout' ), 10, 3 );

		// VAT number or COC number is empty
		add_action( 'admin_notices', array( $this, 'vat_coc_required_for_ubl_invoice') );
	}

	public function output( $active_section ) {
		$active_section = ! empty( $active_section ) ? $active_section : 'taxes';
		?>
		<div class="wcpdf_ubl_settings_sections">
			<?php if ( count( $this->sections ) > 1 ) : ?>
			<h2 class="nav-tab-wrapper">
				<?php
					foreach ( $this->sections as $section => $title ) {
						$active = ( $section == $active_section ) ? 'nav-tab-active' : '';
						printf( '<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', esc_url( add_query_arg( 'section', $section ) ), esc_attr( $section ), $active, esc_html( $title ) );
					}
				?>
			</h2>
			<?php else : ?>
			<h3><?php echo $this->sections[ $active_section ]; ?></h3>
			<?php endif; ?>
		</div>
		<?php

		switch ( $active_section ) {
			default:
			case 'taxes':
				echo '<p>' . esc_html__( 'To ensure compliance with e-invoicing requirements, please complete the Taxes Classification. This information is essential for accurately generating legally compliant invoices.', 'woocommerce-pdf-invoices-packing-slips' ) . '</p>';
				echo '<p><strong>' . esc_html__( 'Note', 'woocommerce-pdf-invoices-packing-slips' ) . ':</strong> ' . esc_html__( 'Each rate line allows you to configure the tax scheme, category, and reason. If these values are set to "Default," they will automatically inherit the settings selected in the "Tax class default" dropdowns at the bottom of the table.', 'woocommerce-pdf-invoices-packing-slips' ) . '</p>';
				$setting = new UblTaxSettings();
				$setting->output();
				break;
		}
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
		);

		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_ubl_taxes', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

	public function save_taxes_on_order_totals( $and_taxes, $order ) {
		// it seems $and taxes is mostly false, meaning taxes are calculated separately,
		// but we still update just in case anything changed
		if ( ! empty( $order ) ) {
			wpo_ips_ubl_save_order_taxes( $order );
		}
	}

	public function save_taxes_on_checkout( $order_id, $posted_data, $order ) {
		if ( empty( $order ) && ! empty( $order_id ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( $order ) {
			wpo_ips_ubl_save_order_taxes( $order );
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

			echo '<div class="notice notice-warning"><p>' . $message . '</p></div>';
		}
	}

}

endif; // class_exists
