<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\WC\PDF_Invoices\Updraft_Semaphore_3_0 as Semaphore;

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
	
	public $type;
	public $title;
	public $icon;
	public $lock_name;
	public $lock_context;
	public $lock_time;
	public $lock_retries;
	
	/**
	 * Init/load the order object.
	 *
	 * @param  int|object|WC_Order $order Order to init.
	 */
	public function __construct( $order = 0 ) {
		// set properties
		$this->type         = 'invoice';
		$this->title        = __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' );
		$this->icon         = WPO_WCPDF()->plugin_url() . "/assets/images/invoice.svg";
		
		// semaphore
		$this->lock_name    = "wpo_wcpdf_{$this->slug}_number_lock";
		$this->lock_context = array( 'source' => "wpo-wcpdf-{$this->type}-semaphore" );
		$this->lock_time    = apply_filters( "wpo_wcpdf_{$this->type}_number_lock_time", 2 );
		$this->lock_retries = apply_filters( "wpo_wcpdf_{$this->type}_number_lock_retries", 0 );
		
		// Call parent constructor
		parent::__construct( $order );
	}

	public function use_historical_settings() {
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_'.$this->get_type() );
		// this setting is inverted on the frontend so that it needs to be actively/purposely enabled to be used
		if (!empty($document_settings) && isset($document_settings['use_latest_settings'])) {
			$use_historical_settings = false;
		} else {
			$use_historical_settings = true;
		}
		return apply_filters( 'wpo_wcpdf_document_use_historical_settings', $use_historical_settings, $this );
	}

	public function storing_settings_enabled() {
		return apply_filters( 'wpo_wcpdf_document_store_settings', true, $this );
	}

	public function get_title() {
		// override/not using $this->title to allow for language switching!
		return apply_filters( "wpo_wcpdf_{$this->slug}_title", __( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' ), $this );
	}

	public function init() {
		// save settings
		$this->save_settings();

		if ( isset( $this->settings['display_date'] ) && $this->settings['display_date'] == 'order_date' && !empty( $this->order ) ) {
			$this->set_date( $this->order->get_date_created() );
			$this->set_display_date( 'order_date' );	
		} elseif( empty( $this->get_date() ) ) {
			$this->set_date( current_time( 'timestamp', true ) );
			$this->set_display_date( 'invoice_date' );	
		}

		
		$this->init_number();

		do_action( 'wpo_wcpdf_init_document', $this );
	}

	public function exists() {
		return !empty( $this->data['number'] );
	}

	public function init_number() {
		$lock           = new Semaphore( $this->lock_name, $this->lock_time, array( wc_get_logger() ), $this->lock_context );
		$invoice_number = null;
		
		if ( $lock->lock( $this->lock_retries ) ) {
			
			try {
				// If a third-party plugin claims to generate invoice numbers, trigger this instead
				if ( apply_filters( 'woocommerce_invoice_number_by_plugin', false ) || apply_filters( 'wpo_wcpdf_external_invoice_number_enabled', false, $this ) ) {
					$invoice_number = apply_filters( 'woocommerce_generate_invoice_number', null, $this->order );
					$invoice_number = apply_filters( 'wpo_wcpdf_external_invoice_number', $invoice_number, $this );
				} elseif ( isset( $this->settings['display_number'] ) && $this->settings['display_number'] == 'order_number' && ! empty( $this->order ) ) {
					$invoice_number = $this->order->get_order_number();
				}

				if ( ! empty( $invoice_number ) ) { // overriden by plugin or set to order number
					if ( ! is_numeric( $invoice_number ) && ! ( $invoice_number instanceof Document_Number ) ) {
						// invoice number is not numeric, treat as formatted
						// try to extract meaningful number data
						$formatted_number = $invoice_number;
						$number           = (int) preg_replace( '/\D/', '', $invoice_number );
						$invoice_number   = compact( 'number', 'formatted_number' );
					}
				} else {
					$number_store   = $this->get_sequential_number_store();
					$invoice_number = $number_store->increment( intval( $this->order_id ), $this->get_date()->date_i18n( 'Y-m-d H:i:s' ) );
				}
				
				if ( ! is_null( $invoice_number ) ) {
					$this->set_number( $invoice_number );
				}
				
			} catch ( \Exception $e ) {
				$lock->log( $e, 'critical' );
			} catch ( \Error $e ) {
				$lock->log( $e, 'critical' );
			}

			$lock->release();
			
		} else {
			$lock->log( "Couldn't get the Invoice Number lock!", 'critical' );
		}
		
		return $invoice_number;
	}

	public function get_filename( $context = 'download', $args = array() ) {
		$order_count = isset($args['order_ids']) ? count($args['order_ids']) : 1;

		$name = _n( 'invoice', 'invoices', $order_count, 'woocommerce-pdf-invoices-packing-slips' );

		if ( $order_count == 1 ) {
			if ( isset( $this->settings['display_number'] ) && $this->settings['display_number'] == 'invoice_number' ) {
				$suffix = (string) $this->get_number();
			} else {
				if ( empty( $this->order ) && isset( $args['order_ids'][0] ) ) {
					$order = wc_get_order( $args['order_ids'][0] );
					$suffix = is_callable( array( $order, 'get_order_number' ) ) ? $order->get_order_number() : '';
				} else {
					$suffix = is_callable( array( $this->order, 'get_order_number' ) ) ? $this->order->get_order_number() : '';
				}
			}
			// ensure unique filename in case suffix was empty
			if ( empty( $suffix ) ) {
				if ( ! empty( $this->order_id ) ) {
					$suffix = $this->order_id;
				} elseif ( ! empty( $args['order_ids'] ) && is_array( $args['order_ids'] ) ) {
					$suffix = reset( $args['order_ids'] );
				} else {
					$suffix = uniqid();
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
					'option_name'	  => $option_name,
					'id'			  => 'attach_to_email_ids',
					'fields_callback' => array( $this, 'get_wc_emails' ),
					/* translators: directory path */
					'description'	  => !is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? '<span class="wpo-warning">' . sprintf( __( 'It looks like the temp folder (<code>%s</code>) is not writable, check the permissions for this folder! Without having write access to this folder, the plugin will not be able to email invoices.', 'woocommerce-pdf-invoices-packing-slips' ), WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ).'</span>':'',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'disable_for_statuses',
				'title'			=> __( 'Disable for:', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'      => $option_name,
					'id'               => 'disable_for_statuses',
					'options_callback' => 'wc_get_order_statuses',
					'multiple'         => true,
					'enhanced_select'  => true,
					'placeholder'      => __( 'Select one or more statuses', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_shipping_address',
				'title'			=> __( 'Display shipping address', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_shipping_address',
					'options' 		=> array(
						''				=> __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
						'when_different'=> __( 'Only when different from billing address' , 'woocommerce-pdf-invoices-packing-slips' ),
						'always'		=> __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
					// 'description'		=> __( 'Display shipping address (in addition to the default billing address) if different from billing address', 'woocommerce-pdf-invoices-packing-slips' ),
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
				'id'			=> 'display_customer_notes',
				'title'			=> __( 'Display customer notes', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'		=> $option_name,
					'id'				=> 'display_customer_notes',
					'store_unchecked'	=> true,
					'default'			=> 1,
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_date',
				'title'			=> __( 'Display invoice date', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_date',
					'options' 		=> array(
						''				=> __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
						'invoice_date'	=> __( 'Invoice Date' , 'woocommerce-pdf-invoices-packing-slips' ),
						'order_date'	=> __( 'Order Date' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'display_number',
				'title'			=> __( 'Display invoice number', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'select',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'display_number',
					'options' 		=> array(
						''					=> __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
						'invoice_number'	=> __( 'Invoice Number' , 'woocommerce-pdf-invoices-packing-slips' ),
						'order_number'		=> __( 'Order Number' , 'woocommerce-pdf-invoices-packing-slips' ),
					),
					'description'	=> sprintf(
						'<strong>%s</strong> %s <a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/invoice-numbers-explained/#why-is-the-pdf-invoice-number-different-from-the-woocommerce-order-number">%s</a>',
						__( 'Warning!', 'woocommerce-pdf-invoices-packing-slips' ),
						__( 'Using the Order Number as invoice number is not recommended as this may lead to gaps in the invoice number sequence (even when order numbers are sequential).', 'woocommerce-pdf-invoices-packing-slips' ),
						__( 'More information', 'woocommerce-pdf-invoices-packing-slips' )
					),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'next_invoice_number',
				'title'			=> __( 'Next invoice number (without prefix/suffix etc.)', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'next_number_edit',
				'section'		=> 'invoice',
				'args'			=> array(
					'store_callback' => array( $this, 'get_sequential_number_store' ),
					'size'           => '10',
					'description'    => __( 'This is the number that will be used for the next document. By default, numbering starts from 1 and increases for every new document. Note that if you override this and set it lower than the current/highest number, this could create duplicate numbers!', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'number_format',
				'title'    => __( 'Number format', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'multiple_text_input',
				'section'  => 'invoice',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'number_format',
					'fields'      => array(
						'prefix'  => array(
							'label'       => __( 'Prefix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'        => 20,
							'description' => __( 'If set, this value will be used as number prefix.' , 'woocommerce-pdf-invoices-packing-slips' ) . ' ' . sprintf(
								/* translators: 1. document type, 2-3 placeholders */
								__( 'You can use the %1$s year and/or month with the %2$s or %3$s placeholders respectively.', 'woocommerce-pdf-invoices-packing-slips' ),
								__( 'invoice', 'woocommerce-pdf-invoices-packing-slips' ), '<strong>[invoice_year]</strong>', '<strong>[invoice_month]</strong>'
							) . ' ' . __( 'Check the Docs article below to see all the available placeholders for prefix/suffix.', 'woocommerce-pdf-invoices-packing-slips' ),
						),
						'suffix'  => array(
							'label'       => __( 'Suffix' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'        => 20,
							'description' => __( 'If set, this value will be used as number suffix.' , 'woocommerce-pdf-invoices-packing-slips' ) . ' ' . sprintf(
								/* translators: 1. document type, 2-3 placeholders */
								__( 'You can use the %1$s year and/or month with the %2$s or %3$s placeholders respectively.', 'woocommerce-pdf-invoices-packing-slips' ),
								__( 'invoice', 'woocommerce-pdf-invoices-packing-slips' ), '<strong>[invoice_year]</strong>', '<strong>[invoice_month]</strong>'
							) . ' ' . __( 'Check the Docs article below to see all the available placeholders for prefix/suffix.', 'woocommerce-pdf-invoices-packing-slips' ),
						),
						'padding' => array(
							'label'       => __( 'Padding' , 'woocommerce-pdf-invoices-packing-slips' ),
							'size'        => 20,
							'type'        => 'number',
							/* translators: document type */
							'description' => sprintf( __( 'Enter the number of digits you want to use as padding. For instance, enter <code>6</code> to display the %s number <code>123</code> as <code>000123</code>, filling it with zeros until the number set as padding is reached.' , 'woocommerce-pdf-invoices-packing-slips' ), __( 'invoice', 'woocommerce-pdf-invoices-packing-slips' ) ),
						),
					),
					/* translators: document type */
					'description' => __( 'For more information about setting up the number format and see the available placeholders for the prefix and suffix, check this article:', 'woocommerce-pdf-invoices-packing-slips' ) . sprintf( ' <a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/number-format-explained/" target="_blank">%s</a>', __( 'Number format explained', 'woocommerce-pdf-invoices-packing-slips') ) . '.<br><br>'. sprintf( __( '<strong>Note</strong>: if you have already created a custom %s number format with a filter, the above settings will be ignored.', 'woocommerce-pdf-invoices-packing-slips' ), __( 'invoice', 'woocommerce-pdf-invoices-packing-slips' ) ),
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
							'option_name'     => $option_name,
							'id'              => 'my_account_restrict',
							'fields_callback' => array( $this, 'get_wc_order_status_list' ),
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
				'id'			=> 'invoice_date_column',
				'title'			=> __( 'Enable invoice date column in the orders list', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'invoice_date_column',
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'invoice_number_search',
				'title'			=> __( 'Enable invoice number search in the orders list', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'invoice_number_search',
					'description'   => __( 'Can potentially slow down the search process.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'disable_free',
				'title'			=> __( 'Disable for free orders', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'disable_free',
					/* translators: zero number */
					'description'	=> sprintf(__( "Disable document when the order total is %s", 'woocommerce-pdf-invoices-packing-slips' ), function_exists('wc_price') ? wc_price( 0 ) : 0 ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'mark_printed',
				'title'    => __( 'Mark as printed', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'select',
				'section'  => 'invoice',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'mark_printed',
					'options'     => array_merge(
						[
							'manually' => __( 'Manually', 'woocommerce-pdf-invoices-packing-slips' ),
						],
						apply_filters( 'wpo_wcpdf_document_triggers', [
							'single'           => __( 'On single order action', 'woocommerce-pdf-invoices-packing-slips' ),
							'bulk'             => __( 'On bulk order action', 'woocommerce-pdf-invoices-packing-slips' ),
							'my_account'       => __( 'On my account', 'woocommerce-pdf-invoices-packing-slips' ),
							'email_attachment' => __( 'On email attachment', 'woocommerce-pdf-invoices-packing-slips' ),
							'document_data'    => __( 'On order document data (number and/or date set manually)', 'woocommerce-pdf-invoices-packing-slips' ),
						] )
					),
					'multiple'         => true,
					'enhanced_select'  => true,
					'description'      => __( 'Allows you to mark the document as printed, manually (in the order page) or automatically (based on the document creation context you have selected).', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'unmark_printed',
				'title'    => __( 'Unmark as printed', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'invoice',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'unmark_printed',
					'description' => __( 'Adds a link in the order page to allow to remove the printed mark.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'			=> 'setting',
				'id'			=> 'use_latest_settings',
				'title'			=> __( 'Always use most current settings', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback'		=> 'checkbox',
				'section'		=> 'invoice',
				'args'			=> array(
					'option_name'	=> $option_name,
					'id'			=> 'use_latest_settings',
					'description'	=> __( "When enabled, the document will always reflect the most current settings (such as footer text, document name, etc.) rather than using historical settings.", 'woocommerce-pdf-invoices-packing-slips' )
					                   . "<br>"
					                   . __( "<strong>Caution:</strong> enabling this will also mean that if you change your company name or address in the future, previously generated documents will also be affected.", 'woocommerce-pdf-invoices-packing-slips' ),
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
					if ( $config_link = apply_filters( 'woocommerce_invoice_number_configuration_link', null ) ) {
						/* translators: link */
						$invoice_number_desc .= ' '.sprintf(__( 'Configure it <a href="%s">here</a>.', 'woocommerce-pdf-invoices-packing-slips' ), esc_attr( $config_link ) );
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

	/**
	 * Document number title
	 */
	public function get_number_title() {
		$number_title = __( 'Invoice Number:', 'woocommerce-pdf-invoices-packing-slips' );
		return apply_filters( "wpo_wcpdf_{$this->slug}_number_title", $number_title, $this );
	}

	/**
	 * Document date title
	 */
	public function get_date_title() {
		$date_title = __( 'Invoice Date:', 'woocommerce-pdf-invoices-packing-slips' );
		return apply_filters( "wpo_wcpdf_{$this->slug}_date_title", $date_title, $this );
	}

}

endif; // class_exists

return new Invoice();
