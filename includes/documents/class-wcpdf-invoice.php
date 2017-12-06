<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Invoice' ) ) :

/**
 * Invoice Document
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Invoice
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

class Invoice extends Order_Document_Methods {
	/**
	 * Init/load the order object.
	 *
	 * @param  int|object|WC_Order $order Order to init.
	 */
	public function __construct( $order = 0 ) {
		// set properties
		$this->type		= 'invoice';
		$this->title	= __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' );
		$this->icon		= WPO_WCPDF()->plugin_url() . "/assets/images/invoice.png";

		// Call parent constructor
		parent::__construct( $order );
	}

	public function get_title() {
		// override/not using $this->title to allow for language switching!
		return apply_filters( "wpo_wcpdf_{$this->slug}_title", __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' ), $this );
	}

	public function init() {
		$this->set_date( current_time( 'timestamp', true ) );
		$this->init_number();
	}

	public function init_number() {
		global $wpdb;
		// If a third-party plugin claims to generate invoice numbers, trigger this instead
		if ( apply_filters( 'woocommerce_invoice_number_by_plugin', false ) || apply_filters( 'wpo_wcpdf_external_invoice_number_enabled', false, $this ) ) {
			$invoice_number = apply_filters( 'woocommerce_generate_invoice_number', null, $this->order );
			$invoice_number = apply_filters( 'wpo_wcpdf_external_invoice_number', $invoice_number, $this );
			if ( is_numeric($invoice_number) || $invoice_number instanceof Document_Number ) {
				$this->set_number( $invoice_number );
			} else {
				// invoice number is not numeric, treat as formatted
				// try to extract meaningful number data
				$formatted_number = $invoice_number;
				$number = (int) preg_replace('/\D/', '', $invoice_number);
				$invoice_number = compact( 'number', 'formatted_number' );
				$this->set_number( $invoice_number );				
			}
			return $invoice_number;
		}

		$number_store_method = WPO_WCPDF()->settings->get_sequential_number_store_method();
		$number_store = new Sequential_Number_Store( 'invoice_number', $number_store_method );
		// reset invoice number yearly
		if ( isset( $this->settings['reset_number_yearly'] ) ) {
			$current_year = date("Y");
			$last_number_year = $number_store->get_last_date('Y');
			// check if we need to reset
			if ( $current_year != $last_number_year ) {
				$number_store->set_next( 1 );
			}
		}

		$invoice_date = $this->get_date();
		$invoice_number = $number_store->increment( $this->order_id, $invoice_date->date_i18n( 'Y-m-d H:i:s' ) );

		$this->set_number( $invoice_number );

		return $invoice_number;
	}

	public function get_settings() {
		$common_settings = WPO_WCPDF()->settings->get_common_document_settings();
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_invoice' );
		return (array) $document_settings + (array) $common_settings;
	}

	public function get_filename( $context = 'download', $args = array() ) {
		$order_count = isset($args['order_ids']) ? count($args['order_ids']) : 1;

		$name = _n( 'invoice', 'invoices', $order_count, 'woocommerce-pdf-invoices-packing-slips' );

		if ( $order_count == 1 ) {
			if ( isset( $this->settings['display_number'] ) ) {
				$suffix = (string) $this->get_number();
			} else {
				if ( empty( $this->order ) ) {
					$order = WCX::get_order ( $order_ids[0] );
					$suffix = method_exists( $order, 'get_order_number' ) ? $order->get_order_number() : '';
				} else {
					$suffix = method_exists( $this->order, 'get_order_number' ) ? $this->order->get_order_number() : '';
				}
			}
		} else {
			$suffix = date('Y-m-d'); // 2020-11-11
		}

		$filename = $name . '-' . $suffix . '.pdf';

		// Filter filename
		$order_ids = isset($args['order_ids']) ? $args['order_ids'] : array( $this->order_id );
		$filename = apply_filters( 'wpo_wcpdf_filename', $filename, $this->get_type(), $order_ids, $context );

		// sanitize filename (after filters to prevent human errors)!
		return sanitize_file_name( $filename );
	}


	/**
	 * Initialise settings
	 */
	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_documents_settings_invoice';

		$settings_fields = array(
			array(
				'type'			=> 'section',
				'id'			=> 'invoice',
				'title'			=> '',
				'callback'		=> 'section',
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'enabled',
				'title'			=> __( 'Enable', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'enabled',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'attach_to_email_ids',
				'title'			=> __( 'Attach to:', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_checkboxes',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'attach_to_email_ids',
					'fields' 		=> $this->get_wc_emails(),
					'description'	=> !is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'woocommerce-pdf-invoices-packing-slips' ), WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ).'</span>':'',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_shipping_address',
				'title'			=> __( 'Display shipping address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_shipping_address',
					'description'		=> __( 'Display shipping address (in addition to the default billing address) if different from billing address', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_email',
				'title'			=> __( 'Display email address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_email',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_phone',
				'title'			=> __( 'Display phone number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_phone',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_date',
				'title'			=> __( 'Display invoice date', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_date',
					'value' 			=> 'invoice_date',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_number',
				'title'			=> __( 'Display invoice number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_number',
					'value' 			=> 'invoice_number',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'next_invoice_number',
				'title'			=> __( 'Next invoice number (without prefix/suffix etc.)', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'next_number_edit',
				'section'		=> 'invoice',
				'args'			=> array(
					'store'			=> 'invoice_number',
					'size'			=> '10',
					'description'	=> __( 'This is the number that will be used for the next document. By default, numbering starts from 1 and increases for every new document. Note that if you override this and set it lower than the current/highest number, this could create duplicate numbers!', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'number_format',
				'title'			=> __( 'Number format', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'multiple_text_input',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'			=> $option_name,
					'id'					=> 'number_format',
					'fields'				=> array(
						'prefix'			=> array(
							'placeholder'	=> __( 'Prefix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'description'	=> __( 'to use the invoice year and/or month, use [invoice_year] or [invoice_month] respectively' , 'woocommerce-pdf-invoices-packing-slips' ),
						),
						'suffix'			=> array(
							'placeholder'	=> __( 'Suffix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'description'	=> '',
						),
						'padding'			=> array(
							'placeholder'	=> __( 'Padding' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'			=> 20,
							'type'			=> 'number',
							'description'	=> __( 'enter the number of digits here - enter "6" to display 42 as 000042' , 'woocommerce-pdf-invoices-packing-slips' ),
						),
					),
					'description'			=> __( 'note: if you have already created a custom invoice number format with a filter, the above settings will be ignored' , 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'reset_number_yearly',
				'title'			=> __( 'Reset invoice number yearly', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'reset_number_yearly',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'my_account_buttons',
				'title'			=> __( 'Allow My Account invoice download', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'my_account_buttons',
					'options' 		=> array(
						'available'	=> __( 'Only when an invoice is already created/emailed' , 'woocommerce-pdf-invoices-packing-slips' ),
						'custom'	=> __( 'Only for specific order statuses (define below)' , 'woocommerce-pdf-invoices-packing-slips' ),
						'always'	=> __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
						'never'		=> __( 'Never' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
					'custom'		=> array(
						'type'		=> 'multiple_checkboxes',
						'args'		=> array(
							'option_name'	=> $option_name,
							'id'			=> 'my_account_restrict',
							'fields'		=> $this->get_wc_order_status_list(),
						),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'invoice_number_column',
				'title'			=> __( 'Enable invoice number column in the orders list', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'invoice_number_column',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'disable_free',
				'title'			=> __( 'Disable for free products', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'disable_free',
					'description'	=> __( "Disable automatic creation/attachment when only free products are ordered", 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
		);


		// remove/rename some fields when invoice number is controlled externally
		if( apply_filters('woocommerce_invoice_number_by_plugin', false) ) {
			$remove_settings = array( 'next_invoice_number', 'number_format', 'reset_number_yearly' );
			foreach ($settings_fields as $key => $settings_field) {
				if (in_array($settings_field['id'], $remove_settings)) {
					unset($settings_fields[$key]);
				} elseif ( $settings_field['id'] == 'display_number' ) {
					// alternate description for invoice number
					$invoice_number_desc = __( 'Invoice numbers are created by a third-party extension.', 'woocommerce-pdf-invoices-packing-slips' );
					if ( esc_attr( apply_filters( 'woocommerce_invoice_number_configuration_link', null ) ) ) {
						$invoice_number_desc .= ' '.sprintf(__( 'Configure it <a href="%s">here</a>.', 'woocommerce-pdf-invoices-packing-slips' ), $config_link);
					}
					$settings_fields[$key]['args']['description'] = '<i>'.$invoice_number_desc.'</i>';
				}
			}
		}

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_documents_invoice', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		return;

	}

}

endif; // class_exists

return new Invoice();