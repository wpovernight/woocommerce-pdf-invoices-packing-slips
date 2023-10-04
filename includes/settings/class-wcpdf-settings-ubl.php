<?php
namespace WPO\WC\PDF_Invoices\Settings;

use WPO\WC\UBL\Settings\TaxesSettings as UBL_Tax_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_UBL' ) ) :

class Settings_UBL {
	
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
				$setting = new UBL_Tax_Settings();
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

						$ubl_tax_settings = get_option( 'wpo_wcpdf_settings_ubl_taxes' );

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
