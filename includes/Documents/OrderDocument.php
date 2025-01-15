<?php
namespace WPO\IPS\Documents;

use WPO\IPS\UBL\Builders\SabreBuilder;
use WPO\IPS\UBL\Documents\UblDocument;
use WPO\IPS\Semaphore;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\OrderDocument' ) ) :

/**
 * Abstract Document
 *
 * Handles generic pdf document & order data and database interaction
 * which is extended by both Invoices & Packing Slips
 */

abstract class OrderDocument {

	/**
	 * Document type.
	 * @var String
	 */
	public $type;

	/**
	 * Document slug.
	 * @var String
	 */
	public $slug;

	/**
	 * Document title.
	 * @var string
	 */
	public $title;

	/**
	 * Document icon.
	 * @var string
	 */
	public $icon;

	/**
	 * WC Order object
	 * @var object
	 */
	public $order;

	/**
	 * WC Order ID
	 * @var int
	 */
	public $order_id;

	/**
	 * PDF document settings.
	 * @var array
	 */
	public $settings;

	/**
	 * Document latest settings.
	 * @var array
	 */
	public $latest_settings;

	/**
	 * Order settings.
	 * @var array
	 */
	public $order_settings;

	/**
	 * TRUE if PDF document is enabled.
	 * @var bool
	 */
	public $enabled;

	/**
	 * Linked output formats.
	 * @var array
	 */
	public $output_formats = array();

	/**
	 * Linked documents, used for data retrieval
	 * @var array
	 */
	protected $linked_documents = array();

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 * @var array
	 */
	protected $data = array();

	/**
	 * Init/load the order object.
	 *
	 * @param  int|object|WC_Order $order Order to init.
	 */
	public function __construct( $order = 0 ) {
		if ( is_numeric( $order ) && $order > 0 ) {
			$this->order_id = absint( $order );
			$this->order    = wc_get_order( $this->order_id );
		} elseif ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) {
			$this->order_id = $order->get_id();
			$this->order    = $order;
		}

		// set properties
		$this->slug = ! empty( $this->type ) ? str_replace(  '-', '_', $this->type ) : '';

		// output formats
		$this->output_formats = apply_filters( 'wpo_wcpdf_document_output_formats', array( 'pdf' ), $this );
		$this->output_formats = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_output_formats", array( $this->output_formats, $this ), '3.8.7', 'wpo_wcpdf_document_output_formats' );

		// load data
		if ( $this->order ) {
			$this->read_data( $this->order );
		}

		// load settings
		$this->init_settings_data();

		// check enable
		$this->enabled = $this->get_setting( 'enabled', false );
	}

	public function init_settings() {
		return;
	}

	public function init_settings_data() {
		// don't override/save settings on Preview requests
		if ( isset( $_REQUEST['action'] ) && 'wpo_wcpdf_preview' === $_REQUEST['action'] ) {
			return;
		}

		// order
		$this->order_settings  = $this->get_order_settings();
		// pdf
		$this->settings        = $this->get_settings();
		$this->latest_settings = $this->get_settings( true );

		// save settings
		$this->save_settings( $this->maybe_use_latest_settings() );
	}

	public function get_order_settings() {
		$order_settings = array();

		if ( ! empty( $this->order ) ) {
			$order_settings = $this->order->get_meta( "_wcpdf_{$this->slug}_settings" );
			if ( ! empty( $order_settings ) && ! is_array( $order_settings ) ) {
				$order_settings = maybe_unserialize( $order_settings );
			}
		}

		return $order_settings;
	}

	public function get_settings( $latest = false, $output_format = 'pdf' ) {
		// get most current settings
		$common_settings   = WPO_WCPDF()->settings->get_common_document_settings();
		$document_settings = WPO_WCPDF()->settings->get_document_settings( $this->get_type(), $output_format );
		$settings          = (array) $document_settings + (array) $common_settings;

		if ( ! $latest ) {
			// get historical settings if enabled
			if ( ! empty( $this->order ) && $this->use_historical_settings() && ! empty( $this->order_settings ) ) {
				// ideally we should combine the order settings with the latest settings, so that new settings will
				// automatically be applied to existing orders too. However, doing this by combining arrays is not
				// possible because the way settings are currently stored means unchecked options are not included.
				// This means there is no way to tell whether an option didn't exist yet (in which case the new
				// option should be added) or whether the option was simply unchecked (in which case it should not
				// be overwritten). This can only be address by storing unchecked checkboxes too.
				$settings = (array) $this->order_settings + array_intersect_key( (array) $settings, array_flip( $this->get_non_historical_settings() ) );
			}
		}

		// display date & display number were checkbox settings but now a select setting that could be set but empty - should behave as 'unchecked'
		if ( array_key_exists( 'display_date', $settings ) && empty( $settings['display_date'] ) ) {
			unset( $settings['display_date'] );
		}

		if ( array_key_exists( 'display_number', $settings ) && empty( $settings['display_number'] ) ) {
			unset( $settings['display_number'] );
		}

		return $settings;
	}

	public function save_settings( $latest = false ) {
		if ( empty( $this->settings ) || empty( $this->latest_settings ) ) {
			$this->init_settings_data();
		}

		$settings = $latest ? $this->latest_settings : $this->settings;
		$update   = true;

		if ( $this->storing_settings_enabled() && ( empty( $this->order_settings ) || $latest ) && ! empty( $settings ) && ! empty( $this->order ) ) {
			if ( ! empty( $this->order_settings ) ) {
				$update = 0 !== strcmp( serialize( (array) $this->order_settings ), serialize( (array) $settings ) );
			}

			if ( $update ) {
				// this is either the first time the document is generated, or historical settings are disabled
				// in both cases, we store the document settings
				// exclude non historical settings from being saved in order meta
				$this->order->update_meta_data( "_wcpdf_{$this->slug}_settings", array_diff_key( (array) $settings, array_flip( $this->get_non_historical_settings() ) ) );

				if ( 'invoice' === $this->slug ) {
					if ( isset( $settings['display_date'] ) && 'order_date' === $settings['display_date'] ) {
						$this->order->update_meta_data( "_wcpdf_{$this->slug}_display_date", 'order_date' );
					} else {
						$this->order->update_meta_data( "_wcpdf_{$this->slug}_display_date", 'invoice_date' );
					}
				}

				$this->order->save_meta_data();
			}
		}
	}

	public function initiate_number( $force_new_number = false ) {
		$lock            = new Semaphore( "initiate_{$this->slug}_number" );
		$document_number = $this->exists() ? $this->get_data( 'number' ) : null;
		$document_number = ! empty( $document_number ) && $force_new_number ? null : $document_number;

		if ( $lock->lock() && empty( $document_number ) ) {
			$lock->log( "Lock acquired for the {$this->slug} number init.", 'info' );

			try {
				// If a third-party plugin claims to generate document numbers, trigger this instead
				if ( apply_filters( "woocommerce_{$this->slug}_number_by_plugin", false ) || apply_filters( "wpo_wcpdf_external_{$this->slug}_number_enabled", false, $this ) ) {
					$document_number = apply_filters( "woocommerce_generate_{$this->slug}_number", $document_number, $this->order );  // legacy (backwards compatibility)
					$document_number = apply_filters( "woocommerce_{$this->slug}_number", $document_number, $this->order->get_id() ); // legacy (backwards compatibility)
					$document_number = apply_filters( "wpo_wcpdf_external_{$this->slug}_number", $document_number, $this );
				} elseif ( isset( $this->settings['display_number'] ) && 'order_number' === $this->settings['display_number'] && ! empty( $this->order ) ) {
					$document_number = $this->order->get_order_number();
				}

				if ( ! empty( $document_number ) ) { // overridden by plugin or set to order number
					if ( ! is_numeric( $document_number ) && ! ( $document_number instanceof DocumentNumber ) ) {
						// document number is not numeric, treat as formatted
						// try to extract meaningful number data
						$formatted_number = $document_number;
						$number           = (int) preg_replace( '/\D/', '', $document_number );
						$document_number  = compact( 'number', 'formatted_number' );
					}
				} else {
					$number_store    = $this->get_sequential_number_store();
					$document_number = $number_store->increment( intval( $this->order_id ), $this->get_date()->date_i18n( 'Y-m-d H:i:s' ) );
				}

				if ( ! is_null( $document_number ) ) {
					$this->set_number( $document_number );
				}

			} catch ( \Exception $e ) {
				$lock->log( $e, 'critical' );
			} catch ( \Error $e ) {
				$lock->log( $e, 'critical' );
			}

			if ( $lock->release() ) {
				$lock->log( "Lock released for the {$this->slug} number init.", 'info' );
			}

		} else {
			$lock->log( "Couldn't get the lock for the {$this->slug} number init.", 'critical' );
		}

		return $document_number;
	}

	public function maybe_use_latest_settings() {
		return ! $this->use_historical_settings();
	}

	public function use_historical_settings() {
		return apply_filters( 'wpo_wcpdf_document_use_historical_settings', false, $this );
	}

	public function storing_settings_enabled() {
		return apply_filters( 'wpo_wcpdf_document_store_settings', false, $this );
	}

	public function get_non_historical_settings() {
		return apply_filters( 'wpo_wcpdf_non_historical_settings', array(
			'enabled',
			'attach_to_email_ids',
			'ubl_format',
			'disable_for_statuses',
			'number_format', // this is stored in the number data already!
			'my_account_buttons',
			'my_account_restrict',
			'invoice_number_column',
			'invoice_date_column',
			'paper_size',
			'font_subsetting',
			'include_encrypted_pdf',
		), $this );
	}

	public function get_setting( $key, $default = '', $output_format = 'pdf' ) {
		if ( in_array( $output_format, $this->output_formats ) ) {
			$settings        = $this->get_settings( false, $output_format );
			$latest_settings = $this->get_settings( true, $output_format );
		} else {
			$settings        = $this->settings;
			$latest_settings = $this->latest_settings;
		}

		$non_historical_settings = $this->get_non_historical_settings();

		if ( in_array( $key, $non_historical_settings ) && isset( $latest_settings ) ) {
			$setting = isset( $latest_settings[$key] ) ? $latest_settings[$key] : $default;
		} else {
			$setting = isset( $settings[$key] ) ? $settings[$key] : $default;
		}

		return $setting;
	}

	public function get_attach_to_email_ids( $output_format = 'pdf' ) {
		$settings = $this->get_settings( false, $output_format );

		return isset( $settings['attach_to_email_ids'] ) ? array_keys( array_filter( $settings['attach_to_email_ids'] ) ) : array();
	}

	public function get_type() {
		return $this->type;
	}

	public function is_enabled( $output_format = 'pdf' ) {
		$is_enabled = $this->get_setting( 'enabled', false, $output_format );

		return apply_filters( 'wpo_wcpdf_document_is_enabled', $is_enabled, $this->type, $output_format );
	}

	/**
	 * Get the UBL format
	 *
	 * @return string|false
	 */
	public function get_ubl_format() {
		$ubl_format = $this->get_setting( 'ubl_format', false, 'ubl' );

		return apply_filters( 'wpo_wcpdf_document_ubl_format', $ubl_format, $this );
	}

	public function get_hook_prefix() {
		return 'wpo_wcpdf_' . $this->slug . '_get_';
	}

	public function read_data( $order ) {
		$number = $order->get_meta( "_wcpdf_{$this->slug}_number_data" );
		// fallback to legacy data for number
		if ( empty( $number ) ) {
			$number = $order->get_meta( "_wcpdf_{$this->slug}_number" );
			$formatted_number = $order->get_meta( "_wcpdf_formatted_{$this->slug}_number" );
			if (!empty($formatted_number)) {
				$number = compact( 'number', 'formatted_number' );
			}
		}

		// pass data to setter functions
		$this->set_data( array(
			// always load date before number, because date is used in number formatting
			'date'             => $order->get_meta( "_wcpdf_{$this->slug}_date" ),
			'number'           => $number,
			'notes'            => $order->get_meta( "_wcpdf_{$this->slug}_notes" ),
			'display_date'	   => $order->get_meta( "_wcpdf_{$this->slug}_display_date" ),
			'creation_trigger' => $order->get_meta( "_wcpdf_{$this->slug}_creation_trigger" ),
		), $order );

		return;
	}

	public function init() {
		// save settings
		$this->save_settings();

		$this->set_date( current_time( 'timestamp', true ) );
		do_action( 'wpo_wcpdf_init_document', $this );
	}

	public function save( $order = null ) {
		$order = empty( $order ) ? $this->order : $order;
		if ( empty( $order ) ) {
			return; // nowhere to save to...
		}

		foreach ( $this->data as $key => $value ) {
			if ( empty( $value ) ) {
				$order->delete_meta_data( "_wcpdf_{$this->slug}_{$key}" );
				if ( $key == 'date' ) {
					$order->delete_meta_data( "_wcpdf_{$this->slug}_{$key}_formatted" );
				} elseif ( $key == 'number' ) {
					$order->delete_meta_data( "_wcpdf_{$this->slug}_{$key}_data" );
					// deleting the number = deleting the document, so also delete document settings
					$order->delete_meta_data( "_wcpdf_{$this->slug}_settings" );
				} elseif ( $key == 'notes' || $key == 'display_date') {
					$order->delete_meta_data( "_wcpdf_{$this->slug}_{$key}" );
				}

			} else {
				if ( $key == 'date' ) {
					// store dates as timestamp and formatted as mysql time
					$order->update_meta_data( "_wcpdf_{$this->slug}_{$key}", $value->getTimestamp() );
					$order->update_meta_data( "_wcpdf_{$this->slug}_{$key}_formatted", $value->date( 'Y-m-d H:i:s' ) );
				} elseif ( $key == 'number' ) {
					// store both formatted number and number data
					$order->update_meta_data( "_wcpdf_{$this->slug}_{$key}", $value->formatted_number );
					$order->update_meta_data( "_wcpdf_{$this->slug}_{$key}_data", $value->to_array() );
				} elseif ( $key == 'notes' || $key == 'display_date' ) {
					// store notes
					$order->update_meta_data( "_wcpdf_{$this->slug}_{$key}", $value );
				}

			}
		}

		$order->save_meta_data();

		do_action( 'wpo_wcpdf_save_document', $this, $order );
	}

	public function delete( $order = null ) {
		$order = empty( $order ) ? $this->order : $order;
		if ( empty( $order ) ) {
			return; // nothing to delete
		}

		$data_to_remove = apply_filters( 'wpo_wcpdf_delete_document_data_keys', array(
			'settings',
			'date',
			'date_formatted',
			'number',
			'number_data',
			'notes',
			'printed',
			'display_date',
			'creation_trigger',
		), $this );
		foreach ( $data_to_remove as $data_key ) {
			$order->delete_meta_data( "_wcpdf_{$this->slug}_{$data_key}" );
		}

		$order->save_meta_data();

		do_action( 'wpo_wcpdf_delete_document', $this, $order );
	}

	public function regenerate( $order = null, $data = null ) {
		$order     = empty( $order ) ? $this->order : $order;
		$refund_id = false;
		
		if ( empty( $order ) ) {
			return;
		}

		// pass data to setter functions
		if ( ! empty( $data ) ) {
			$this->set_data( $data, $order );
			$this->save();
		}

		// save settings
		$this->save_settings( true );
		
		// if credit note
		if ( 'credit-note' === $this->get_type() ) {
			$refund_id = $order->get_id();
			$order     = wc_get_order( $order->get_parent_id() );
		}
		
		// ubl
		if ( $this->is_enabled( 'ubl' ) && wcpdf_is_ubl_available() ) {
			wpo_ips_ubl_save_order_taxes( $order );
		}
		
		$note = $refund_id ? sprintf(
			/* translators: 1. credit note title, 2. refund id */
			__( '%1$s (refund #%2$s) was regenerated.', 'woocommerce-pdf-invoices-packing-slips' ),
			ucfirst( $this->get_title() ),
			$refund_id
		) : sprintf(
			/* translators: 1. document title */
			__( '%s was regenerated', 'woocommerce-pdf-invoices-packing-slips' ),
			ucfirst( $this->get_title() )
		);
		
		$note = wp_kses( $note, 'strip' );
		
		// add note to order
		$order->add_order_note( $note );

		do_action( 'wpo_wcpdf_regenerate_document', $this );
	}

	public function is_allowed() {
		$allowed = true;
		// Check if document is enabled
		if ( ! $this->is_enabled() ) {
			$allowed = false;
		// Check disabled for statuses
		} elseif ( ! $this->exists() && ! empty( $this->settings['disable_for_statuses'] ) && ! empty( $this->order ) && is_callable( array( $this->order, 'get_status' ) ) ) {
			$status = $this->order->get_status();

			$disabled_statuses = array_map( function ( $status ) {
				$status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
				return $status;
			}, $this->settings['disable_for_statuses'] );

			if ( in_array( $status, $disabled_statuses ) ) {
				$allowed = false;
			}
		}
		return apply_filters( 'wpo_wcpdf_document_is_allowed', $allowed, $this );
	}

	public function exists() {
		return !empty( $this->data['date'] );
	}

	public function printed() {
		return WPO_WCPDF()->main->is_document_printed( $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Data getters
	|--------------------------------------------------------------------------
	*/

	public function get_printed_data() {
		return WPO_WCPDF()->main->get_document_printed_data( $this );
	}

	public function get_data( $key, $document_type = '', $order = null, $context = 'view' ) {
		$document_type = empty( $document_type ) ? $this->type : $document_type;
		$order = empty( $order ) ? $this->order : $order;

		// redirect get_data call for linked documents
		if ( $document_type != $this->type ) {
			if ( !isset( $this->linked_documents[ $document_type ] ) ) {
				// always assume parent for documents linked to credit notes
				if ($this->type == 'credit-note') {
					$order = $this->get_refund_parent( $order );
				}
				// order is not loaded to avoid overhead - we pass this by reference directly to the read_data method instead
				$this->linked_documents[ $document_type ] = wcpdf_get_document( $document_type, null );
				$this->linked_documents[ $document_type ]->read_data( $order );
			}
			return $this->linked_documents[ $document_type ]->get_data( $key, $document_type );
		}

		$value = null;

		if ( array_key_exists( $key, $this->data ) ) {
			$value = $this->data[ $key ];

			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $key, $value, $this );
			}
		}

		return $value;
	}

	public function get_number( $document_type = '', $order = null, $context = 'view', $formatted = false ) {
		$number = $this->get_data( 'number', $document_type, $order, $context );

		if ( $number && $formatted ) {
			$number = $number->get_formatted();
		}

		return apply_filters( "wpo_wcpdf_{$this->slug}_number", $number, $document_type, $order, $context, $formatted, $this );
	}

	public function number( $document_type ) {
		echo $this->get_number( $document_type, null, 'view', true );
	}

	public function get_date( $document_type = '', $order = null, $context = 'view', $formatted = false ) {
		$date = $this->get_data( 'date', $document_type, $order, $context );

		if ( $date && $formatted ) {
			$date = $date->date_i18n( wcpdf_date_format( $this, 'document_date' ) );
		}

		return apply_filters( "wpo_wcpdf_{$this->slug}_date", $date, $document_type, $order, $context, $formatted, $this );
	}

	public function date( $document_type ) {
		echo $this->get_date( $document_type, null, 'view', true );
	}

	public function get_notes( $document_type = '', $order = null, $context = 'view'  ) {
		return $this->get_data( 'notes', $document_type, $order, $context );
	}

	public function get_display_date( $document_type = '', $order = null, $context = 'view'  ) {
		return $this->get_data( 'display_date', $document_type, $order, $context );
	}

	public function get_creation_trigger( $document_type = '', $order = null, $context = 'view'  ) {
		return $this->get_data( 'creation_trigger', $document_type, $order, $context );
	}

	/**
	 * Get the document title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->get_title_for( 'document' );
	}

	/**
	 * Print the document number title
	 *
	 * @return void
	 */
	public function title() {
		echo $this->get_title();
	}

	/**
	 * Get the document number title
	 *
	 * @return string
	 */
	public function get_number_title() {
		return $this->get_title_for( 'document_number' );
	}

	/**
	 * Print the document number title
	 *
	 * @return void
	 */
	public function number_title() {
		echo $this->get_number_title();
	}

	/**
	 * Get the document date title
	 *
	 * @return string
	 */
	public function get_date_title() {
		return $this->get_title_for( 'document_date' );
	}

	/**
	 * Print the document date title
	 *
	 * @return void
	 */
	public function date_title() {
		echo $this->get_date_title();
	}

	/**
	 * Get the document due date title
	 *
	 * @return string
	 */
	public function get_due_date_title() {
		return $this->get_title_for( 'document_due_date' );
	}

	/**
	 * Print the document due date title
	 *
	 * @return void
	 */
	public function due_date_title() {
		echo $this->get_due_date_title();
	}

	/**
	 * Get the billing address title
	 *
	 * @return string
	 */
	public function get_billing_address_title(): string {
		return $this->get_title_for( 'billing_address' );
	}

	/**
	 * Print the billing address title
	 *
	 * @return void
	 */
	public function billing_address_title(): void {
		echo $this->get_billing_address_title();
	}

	/**
	 * Get the shipping address title
	 *
	 * @return string
	 */
	public function get_shipping_address_title(): string {
		return $this->get_title_for( 'shipping_address' );
	}

	/**
	 * Print the shipping address title
	 *
	 * @return void
	 */
	public function shipping_address_title(): void {
		echo $this->get_shipping_address_title();
	}

	/**
	 * Get the order number title
	 *
	 * @return string
	 */
	public function get_order_number_title(): string {
		return $this->get_title_for( 'order_number' );
	}

	/**
	 * Print the order number title
	 *
	 * @return void
	 */
	public function order_number_title(): void {
		echo $this->get_order_number_title();
	}

	/**
	 * Get the order date title
	 *
	 * @return string
	 */
	public function get_order_date_title(): string {
		return $this->get_title_for( 'order_date' );
	}

	/**
	 * Print the order date title
	 *
	 * @return void
	 */
	public function order_date_title(): void {
		echo $this->get_order_date_title();
	}

	/**
	 * Get the payment method title
	 *
	 * @return string
	 */
	public function get_payment_method_title(): string {
		return $this->get_title_for( 'payment_method' );
	}

	/**
	 * Print the payment method title
	 *
	 * @return void
	 */
	public function payment_method_title(): void {
		echo $this->get_payment_method_title();
	}

	/**
	 * Get the payment date title
	 *
	 * @return string
	 */
	public function get_payment_date_title(): string {
		return $this->get_title_for( 'payment_date' );
	}

	/**
	 * Print the payment date title
	 *
	 * @return void
	 */
	public function payment_date_title(): void {
		echo $this->get_payment_date_title();
	}

	/**
	 * Get the shipping method title
	 *
	 * @return string
	 */
	public function get_shipping_method_title(): string {
		return $this->get_title_for( 'shipping_method' );
	}

	/**
	 * Print the shipping method title
	 *
	 * @return void
	 */
	public function shipping_method_title(): void {
		echo $this->get_shipping_method_title();
	}

	/**
	 * Get the SKU title
	 *
	 * @return string
	 */
	public function get_sku_title(): string {
		return $this->get_title_for( 'sku' );
	}

	/**
	 * Print the SKU title
	 *
	 * @return void
	 */
	public function sku_title(): void {
		echo $this->get_sku_title();
	}

	/**
	 * Get the weight title
	 *
	 * @return string
	 */
	public function get_weight_title(): string {
		return $this->get_title_for( 'weight' );
	}

	/**
	 * Print the weight title
	 *
	 * @return void
	 */
	public function weight_title(): void {
		echo $this->get_weight_title();
	}

	/**
	 * Get the notes title
	 *
	 * @return string
	 */
	public function get_notes_title(): string {
		return $this->get_title_for( 'notes' );
	}

	/**
	 * Print the notes title
	 *
	 * @return void
	 */
	public function notes_title(): void {
		echo $this->get_notes_title();
	}

	/**
	 * Get the customer notes title
	 *
	 * @return string
	 */
	public function get_customer_notes_title(): string {
		return $this->get_title_for( 'customer_notes' );
	}

	/**
	 * Print the customer notes title
	 *
	 * @return void
	 */
	public function customer_notes_title(): void {
		echo $this->get_customer_notes_title();
	}

	/**
	 * Get the title for a specific slug
	 *
	 * @param string $slug
	 * @return string
	 */
	public function get_title_for( string $slug ): string {
		switch ( $slug ) {
			case 'document':
				$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_title", array( $this->title, $this ), '3.8.7', 'wpo_wcpdf_document_title' );
				break;
			case 'document_number':
				$title = sprintf(
					/* translators: %s: document name */
					__( '%s Number:', 'woocommerce-pdf-invoices-packing-slips' ),
					$this->title
				);
				$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_number_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_number_title' );
				break;
			case 'document_date':
				$title = sprintf(
					/* translators: %s: document name */
					__( '%s Date:', 'woocommerce-pdf-invoices-packing-slips' ),
					$this->title
				);
				$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_date_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_date_title' );
				break;
			case 'document_due_date':
				$title = __( 'Due Date:', 'woocommerce-pdf-invoices-packing-slips' );
				$title = apply_filters_deprecated( "wpo_wcpdf_{$this->slug}_due_date_title", array( $title, $this ), '3.8.7', 'wpo_wcpdf_document_due_date_title' );
				break;
			case 'billing_address':
				$title = __( 'Billing Address:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'shipping_address':
				$title = __( 'Shipping Address:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'order_number':
				$title = __( 'Order Number:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'order_date':
				$title = __( 'Order Date:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'payment_method':
				$title = __( 'Payment Method:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'payment_date':
				$title = __( 'Payment Date:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'shipping_method':
				$title = __( 'Shipping Method:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'sku':
				$title = __( 'SKU:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'weight':
				$title = __( 'Weight:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'notes':
				$title = __( 'Notes:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			case 'customer_notes':
				$title = __( 'Customer Notes:', 'woocommerce-pdf-invoices-packing-slips' );
				break;
			default:
				$title = '';
				break;
		}

		$title = apply_filters( 'wpo_wcpdf_title_for', $title, $slug, $this ); // used by Pro to translate strings

		return apply_filters( "wpo_wcpdf_{$slug}_title", $title, $this );
	}

	/**
	 * Prints the due date.
	 *
	 * @return void
	 */
	public function due_date(): void {
		$due_date_timestamp = $this->get_due_date();
		echo apply_filters( "wpo_wcpdf_{$this->slug}_formatted_due_date", date_i18n( wcpdf_date_format( $this, 'due_date' ), $due_date_timestamp ), $due_date_timestamp, $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Data setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting order data. These should not update anything in the
	| order itself and should only change what is stored in the class
	| object.
	*/

	public function set_data( $data, $order ) {
		$order = empty( $order ) ? $this->order : $order;
		foreach ($data as $key => $value) {
			$setter = "set_$key";
			if ( is_callable( array( $this, $setter ) ) ) {
				$this->$setter( $value, $order );
			} else {
				$this->data[ $key ] = $value;
			}
		}
	}

	public function set_date( $value, $order = null ) {
		$order = empty( $order ) ? $this->order : $order;
		try {
			if ( empty( $value ) ) {
				$this->data[ 'date' ] = null;
				return;
			}

			if ( is_a( $value, 'WC_DateTime' ) ) {
				$datetime = $value;
			} elseif ( is_numeric( $value ) ) {
				// Timestamps are handled as UTC timestamps in all cases.
				$datetime = new \WC_DateTime( "@{$value}", new \DateTimeZone( 'UTC' ) );
			} else {
				// Strings are defined in local WP timezone. Convert to UTC.
				if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
					$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
					$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
				} else {
					$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $value ) ) ) );
				}
				$datetime  = new \WC_DateTime( "@{$timestamp}", new \DateTimeZone( 'UTC' ) );
			}

			// Set local timezone or offset.
			if ( get_option( 'timezone_string' ) ) {
				$datetime->setTimezone( new \DateTimeZone( wc_timezone_string() ) );
			} else {
				$datetime->set_utc_offset( wc_timezone_offset() );
			}

			$this->data[ 'date' ] = $datetime;
		} catch ( \Exception $e ) {
			wcpdf_log_error( $e->getMessage() );
		} catch ( \Error $e ) {
			wcpdf_log_error( $e->getMessage() );
		}

	}

	public function set_number( $value, $order = null ) {
		$order = empty( $order ) ? $this->order : $order;

		$value = maybe_unserialize( $value ); // fix incorrectly stored meta

		if ( is_array( $value ) ) {
			$filtered_value = array_filter( $value );
		}

		if ( empty( $value ) || ( is_array( $value ) && empty( $filtered_value ) ) ) {
			$document_number = null;
		} elseif ( $value instanceof DocumentNumber ) {
			// WCPDF 2.0 number data
			$document_number = $value;
		} elseif ( is_array( $value ) ) {
			// WCPDF 2.0 number data as array
			$document_number = new DocumentNumber( $value, $this->get_number_settings(), $this, $order  );
		} else {
			// plain number
			$document_number = new DocumentNumber( $value, $this->get_number_settings(), $this, $order );
		}

		$this->data[ 'number' ] = $document_number;
	}

	public function set_notes( $value, $order = null ) {
		$order = empty( $order ) ? $this->order : $order;

		try {
			if ( empty( $value ) ) {
				$this->data[ 'notes' ] = null;
				return;
			}

			$this->data[ 'notes' ] = $value;
		} catch ( \Exception $e ) {
			wcpdf_log_error( $e->getMessage() );
		} catch ( \Error $e ) {
			wcpdf_log_error( $e->getMessage() );
		}
	}

	public function set_display_date( $value, $order = null ) {
		$order = empty( $order ) ? $this->order : $order;

		try {
			if ( empty( $value ) ) {
				$this->data['display_date'] = null;
				return;
			}

			$this->data['display_date'] = $value;
		} catch ( \Exception $e ) {
			wcpdf_log_error( $e->getMessage() );
		} catch ( \Error $e ) {
			wcpdf_log_error( $e->getMessage() );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Settings getters / outputters
	|--------------------------------------------------------------------------
	*/

	public function get_number_settings() {
		if ( empty( $this->settings ) ) {
			$settings        = $this->get_settings( true ); // we always want the latest settings
			$number_settings = isset( $settings['number_format'] ) ? $settings['number_format'] : array();
		} else {
			$number_settings = $this->get_setting( 'number_format', array() );
		}
		return apply_filters( 'wpo_wcpdf_document_number_settings', $number_settings, $this );
	}

	/**
	 * Output template styles
	 */
	public function template_styles() {
		$css = apply_filters( 'wpo_wcpdf_template_styles_file', $this->locate_template_file( "style.css" ) );

		ob_start();
		if (file_exists($css)) {
			include($css);
		}
		$css = ob_get_clean();
		$css = apply_filters( 'wpo_wcpdf_template_styles', $css, $this );

		echo $css;
	}

	public function has_header_logo() {
		return ! empty( $this->settings['header_logo'] );
	}

	/**
	 * Return logo id
	 *
	 * @return int
	 */
	public function get_header_logo_id(): int {
		$header_logo_id = ! empty( $this->settings['header_logo'] ) ? $this->get_settings_text( 'header_logo', 0, false ) : 0;
		$header_logo_id = apply_filters( 'wpo_wcpdf_header_logo_id', $header_logo_id, $this );

		return $header_logo_id && is_numeric( $header_logo_id ) ? absint( $header_logo_id ) : 0;
	}

	/**
	 * Return logo height
	 */
	public function get_header_logo_height() {
		if ( ! empty( $this->settings['header_logo_height'] ) ) {
			return apply_filters( 'wpo_wcpdf_header_logo_height', str_replace( ' ', '', $this->settings['header_logo_height'] ), $this );
		}
	}

	/**
	 * Show logo HTML
	 *
	 * @return void
	 */
	public function header_logo(): void {
		$attachment_id = $this->get_header_logo_id();

		if ( $attachment_id > 0 ) {
			$company         = $this->get_shop_name();
			$attachment_src  = wp_get_attachment_image_url( $attachment_id, 'full' );
			$attachment_path = wp_normalize_path( realpath( get_attached_file( $attachment_id ) ) );
			$src             = apply_filters( 'wpo_wcpdf_use_path', true ) ? $attachment_path : $attachment_src;

			if ( empty( $src ) ) {
				wcpdf_log_error( 'Header logo file not found.', 'critical' );
				return;
			}

			// fix URLs using path
			if ( ! apply_filters( 'wpo_wcpdf_use_path', true ) && false !== strpos( $src, 'http' ) && false !== strpos( $src, WP_CONTENT_DIR ) ) {
				$path = preg_replace( '/^https?:\/\//', '', $src ); // removes http(s)://
				$src  = str_replace( trailingslashit( WP_CONTENT_DIR ), trailingslashit( WP_CONTENT_URL ), $path ); // replaces path with URL
			}

			if ( ! wpo_wcpdf_is_file_readable( $src ) ) {
				wcpdf_log_error( 'Header logo file not readable: ' . $src, 'critical' );
				return;
			}

			$image_src   = isset( WPO_WCPDF()->settings->debug_settings['embed_images'] ) ? wpo_wcpdf_get_image_src_in_base64( $src ) : $src;
			$img_element = sprintf( '<img src="%1$s" alt="%2$s"/>', esc_attr( $image_src ), esc_attr( $company ) );

			echo apply_filters( 'wpo_wcpdf_header_logo_img_element', $img_element, $attachment_id, $this );

		}
	}

	public function get_settings_text( $settings_key, $default = false, $autop = true ) {
		$setting = $this->get_setting( $settings_key, $default );
		// check for 'default' key existence
		if ( ! empty( $setting ) && is_array( $setting ) && array_key_exists( 'default', $setting ) ) {
			$text = $setting['default'];
		// fallback to first array element if default is not present
		} elseif( ! empty( $setting ) && is_array( $setting ) ) {
			$text = reset( $setting );
		} else {
			$text = $setting;
		}

		// fallback to default
		if ( empty( $text ) ) {
			$text = $default;
		}

		// clean up
		$text = wptexturize( trim( $text ) );

		// replacements
		if ( $autop === true ) {
			$text = wpautop( $text );
		}

		// legacy filters
		if ( in_array( $settings_key, array( 'shop_name', 'shop_address', 'footer', 'extra_1', 'extra_2', 'extra_3' ) ) ) {
			$text = apply_filters( "wpo_wcpdf_{$settings_key}", $text, $this );
		}

		return apply_filters( "wpo_wcpdf_{$settings_key}_settings_text", $text, $this );
	}

	/**
	 * Return/Show custom company name or default to blog name
	 */
	public function get_shop_name() {
		$default = get_bloginfo( 'name' );
		return $this->get_settings_text( 'shop_name', $default, false );
	}
	public function shop_name() {
		echo $this->get_shop_name();
	}

	/**
	 * Return/Show company VAT number
	 */
	public function get_shop_vat_number() {
		return $this->get_settings_text( 'vat_number', '', false );
	}
	public function shop_vat_number() {
		echo $this->get_shop_vat_number();
	}

	/**
	 * Return/Show company COC number
	 */
	public function get_shop_coc_number() {
		return $this->get_settings_text( 'coc_number', '', false );
	}
	public function shop_coc_number() {
		echo $this->get_shop_coc_number();
	}

	/**
	 * Return/Show shop/company address if provided
	 */
	public function get_shop_address() {
		return $this->get_settings_text( 'shop_address' );
	}
	public function shop_address() {
		echo $this->get_shop_address();
	}

	/**
	 * Return/Show shop/company phone number if provided.
	 */
	public function get_shop_phone_number() {
		return $this->get_settings_text( 'shop_phone_number', '', false );
	}
	public function shop_phone_number() {
		echo $this->get_shop_phone_number();
	}

	/**
	 * Return/Show shop/company footer imprint, copyright etc.
	 */
	public function get_footer() {
		ob_start();
		do_action( 'wpo_wcpdf_before_footer', $this->get_type(), $this->order );
		echo $this->get_settings_text( 'footer' );
		do_action( 'wpo_wcpdf_after_footer', $this->get_type(), $this->order );
		return ob_get_clean();
	}
	public function footer() {
		echo $this->get_footer();
	}

	/**
	 * Return/Show Extra field 1
	 */
	public function get_extra_1() {
		return $this->get_settings_text( 'extra_1' );

	}
	public function extra_1() {
		echo $this->get_extra_1();
	}

	/**
	 * Return/Show Extra field 2
	 */
	public function get_extra_2() {
		return $this->get_settings_text( 'extra_2' );
	}
	public function extra_2() {
		echo $this->get_extra_2();
	}

	/**
	 * Return/Show Extra field 3
	 */
	public function get_extra_3() {
		return $this->get_settings_text( 'extra_3' );
	}
	public function extra_3() {
		echo $this->get_extra_3();
	}

	/*
	|--------------------------------------------------------------------------
	| Output functions
	|--------------------------------------------------------------------------
	*/

	public function get_pdf() {
		// maybe we need to reinstall fonts first?
		WPO_WCPDF()->main->maybe_reinstall_fonts();

		$pdf = null;
		if ( $pdf_file = apply_filters( 'wpo_wcpdf_load_pdf_file_path', null, $this ) ) {
			$pdf = file_get_contents( $pdf_file );
		}
		$pdf = apply_filters( 'wpo_wcpdf_pdf_data', $pdf, $this );
		if ( !empty( $pdf ) ) {
			return $pdf;
		}

		do_action( 'wpo_wcpdf_before_pdf', $this->get_type(), $this );

		// temporarily apply filters that need to be removed again after the pdf is generated
		$pdf_filters = apply_filters( 'wpo_wcpdf_pdf_filters', array(), $this );
		$this->add_filters( $pdf_filters );

		$pdf_settings = array(
			'paper_size'		=> apply_filters( 'wpo_wcpdf_paper_format', $this->get_setting( 'paper_size', 'A4' ), $this->get_type(), $this ),
			'paper_orientation'	=> apply_filters( 'wpo_wcpdf_paper_orientation', 'portrait', $this->get_type(), $this ),
			'font_subsetting'	=> $this->get_setting( 'font_subsetting', false ),
		);
		$pdf_maker    = wcpdf_get_pdf_maker( $this->get_html(), $pdf_settings, $this );
		$pdf          = $pdf_maker->output();

		do_action( 'wpo_wcpdf_after_pdf', $this->get_type(), $this );

		// remove temporary filters
		$this->remove_filters( $pdf_filters );

		do_action( 'wpo_wcpdf_pdf_created', $pdf, $this );

		return apply_filters( 'wpo_wcpdf_get_pdf', $pdf, $this );
	}

	public function preview_pdf() {
		// maybe we need to reinstall fonts first?
		WPO_WCPDF()->main->maybe_reinstall_fonts();

		// get last settings
		$this->settings = ! empty( $this->latest_settings ) ? $this->latest_settings : $this->get_settings( true );

		$pdf_settings = array(
			'paper_size'		=> apply_filters( 'wpo_wcpdf_paper_format', $this->get_setting( 'paper_size', 'A4' ), $this->get_type(), $this ),
			'paper_orientation'	=> apply_filters( 'wpo_wcpdf_paper_orientation', 'portrait', $this->get_type(), $this ),
			'font_subsetting'	=> $this->get_setting( 'font_subsetting', false ),
		);
		$pdf_maker = wcpdf_get_pdf_maker( $this->get_html(), $pdf_settings, $this );
		$pdf       = $pdf_maker->output();

		return $pdf;
	}

	public function get_html( $args = array() ) {
		// temporarily apply filters that need to be removed again after the html is generated
		$html_filters = apply_filters( 'wpo_wcpdf_html_filters', array(), $this );
		$this->add_filters( $html_filters );

		do_action( 'wpo_wcpdf_before_html', $this->get_type(), $this );

		$default_args = array (
			'wrap_html_content'	=> true,
		);
		$args = $args + $default_args;

		$html = $this->render_template( $this->locate_template_file( "{$this->type}.php" ), array(
				'order' => $this->order,
				'order_id' => $this->order_id,
			)
		);

		if ( $args['wrap_html_content'] ) {
			$html = $this->wrap_html_content( $html );
		}

		// clean up special characters
		if ( apply_filters( 'wpo_wcpdf_convert_encoding', function_exists( 'htmlspecialchars_decode' ) ) ) {
			$html = htmlspecialchars_decode( wcpdf_convert_encoding( $html ), ENT_QUOTES );
		}

		do_action( 'wpo_wcpdf_after_html', $this->get_type(), $this );

		// remove temporary filters
		$this->remove_filters( $html_filters );

		return apply_filters( 'wpo_wcpdf_get_html', $html, $this );
	}

	public function output_pdf( $output_mode = 'download' ) {
		$pdf = $this->get_pdf();
		wcpdf_pdf_headers( $this->get_filename(), $output_mode, $pdf );
		echo $pdf;
		exit();
	}

	public function output_html() {
		echo $this->get_html();
	}

	public function preview_ubl() {
		// get last settings
		$this->settings = $this->get_settings( true, 'ubl' );

		return $this->output_ubl( true );
	}

	public function output_ubl( $contents_only = false ) {
		$ubl_maker    = wcpdf_get_ubl_maker();
		$ubl_document = new UblDocument();

		$document = $contents_only ? $this : wcpdf_get_document( $this->get_type(), $this->order, true );

		if ( $document ) {
			$ubl_document->set_order_document( $document );
		} else {
			wcpdf_log_error( 'Error generating order document for UBL!', 'error' );
			exit();
		}

		$builder  = new SabreBuilder();
		$contents = $builder->build( $ubl_document );

		if ( $contents_only ) {
			return $contents;
		}

		$filename      = $document->get_filename( 'download', array( 'output' => 'ubl' ) );
		$full_filename = $ubl_maker->write( $filename, $contents );
		$quoted        = sprintf( '"%s"', addcslashes( basename( $full_filename ), '"\\' ) );
		$size          = filesize( $full_filename );

		wcpdf_ubl_headers( $quoted, $size );

		ob_clean();
		flush();
		@readfile( $full_filename );
		@unlink( $full_filename );

		exit();
	}

	public function wrap_html_content( $content ) {
		$html = $this->render_template( $this->locate_template_file( "html-document-wrapper.php" ), array(
				'content' => apply_filters( 'wpo_wcpdf_html_content', $content ),
			)
		);
		return $html;
	}

	public function get_filename( $context = 'download', $args = array() ) {
		$order_count = isset($args['order_ids']) ? count($args['order_ids']) : 1;

		$name = $this->get_type();

		if ( is_callable( array( $this->order, 'get_type' ) ) && $this->order->get_type() == 'shop_order_refund' ) {
			$number = $this->order_id;
		} else {
			$number = is_callable( array( $this->order, 'get_order_number' ) ) ? $this->order->get_order_number() : '';
		}

		if ( $order_count == 1 ) {
			$suffix = $number;
		} else {
			$suffix = date('Y-m-d'); // 2020-11-11
		}

		// get filename
		$output_format = ! empty( $args['output'] ) ? esc_attr( $args['output'] ) : 'pdf';
		$filename      = $name . '-' . $suffix . wcpdf_get_document_output_format_extension( $output_format );

		// Filter filename
		$order_ids = isset( $args['order_ids'] ) ? $args['order_ids'] : array( $this->order_id );
		$filename  = apply_filters( 'wpo_wcpdf_filename', $filename, $this->get_type(), $order_ids, $context, $args );

		// sanitize filename (after filters to prevent human errors)!
		return sanitize_file_name( $filename );
	}

	public function get_template_path() {
		return WPO_WCPDF()->settings->get_template_path();
	}

	public function locate_template_file( $file ) {
		if (empty($file)) {
			$file = $this->type.'.php';
		}
		$path = $this->get_template_path();
		$file_path = "{$path}/{$file}";

		$fallback_file_path = WPO_WCPDF()->plugin_path() . '/templates/Simple/' . $file;
		if ( !file_exists( $file_path ) && file_exists( $fallback_file_path ) ) {
			$file_path = $fallback_file_path;
		}

		$file_path = apply_filters( 'wpo_wcpdf_template_file', $file_path, $this->type, $this->order );

		return $file_path;
	}

	public function render_template( $file, $args = array() ) {
		do_action( 'wpo_wcpdf_process_template', $this->get_type(), $this );

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}
		ob_start();
		if (file_exists($file)) {
			include($file);
		}
		return ob_get_clean();
	}

	/*
	|--------------------------------------------------------------------------
	| Settings helper functions
	|--------------------------------------------------------------------------
	*/

	/**
	 * get all emails registered in WooCommerce
	 * @param  boolean $remove_defaults switch to remove default woocommerce emails
	 * @return array   $emails       list of all email ids/slugs and names
	 */
	public function get_wc_emails() {
		// only run this in the context of the settings page or setup wizard
		// prevents WPML language mixups
		if ( empty( $_GET['page'] ) || !in_array( $_GET['page'], array('wpo-wcpdf-setup','wpo_wcpdf_options_page') ) ) {
			return array();
		}

		// get emails from WooCommerce
		if (function_exists('WC')) {
			$mailer = WC()->mailer();
		} else {
			global $woocommerce;

			if ( empty( $woocommerce ) ) { // bail if WooCommerce not active
				return apply_filters( 'wpo_wcpdf_wc_emails', array() );
			}

			$mailer = $woocommerce->mailer();
		}
		$wc_emails = $mailer->get_emails();

		$non_order_emails = array(
			'customer_reset_password',
			'customer_new_account'
		);

		$emails = array();
		foreach ($wc_emails as $class => $email) {
			if ( !is_object( $email ) ) {
				continue;
			}
			if ( !in_array( $email->id, $non_order_emails ) ) {
				switch ($email->id) {
					case 'new_order':
						$emails[$email->id] = sprintf('%s (%s)', $email->title, __( 'Admin email', 'woocommerce-pdf-invoices-packing-slips' ) );
						break;
					case 'customer_invoice':
						$emails[$email->id] = sprintf('%s (%s)', $email->title, __( 'Manual email', 'woocommerce-pdf-invoices-packing-slips' ) );
						break;
					default:
						$emails[$email->id] = $email->title;
						break;
				}
			}
		}

		return apply_filters( 'wpo_wcpdf_wc_emails', $emails );
	}

	// get list of WooCommerce statuses
	public function get_wc_order_status_list() {
		$order_statuses = array();
		$statuses       = function_exists('wc_get_order_statuses') ? wc_get_order_statuses() : array();

		foreach ( $statuses as $status_slug => $status ) {
			$status_slug   = 'wc-' === substr( $status_slug, 0, 3 ) ? substr( $status_slug, 3 ) : $status_slug;
			$order_statuses[$status_slug] = $status;
		}

		return $order_statuses;
	}

	/**
	 * Get the Sequential Number Store class that handles invoice number generation/consumption
	 *
	 * @return SequentialNumberStore
	 */
	public function get_sequential_number_store() {
		$reset_number_yearly = isset( $this->settings['reset_number_yearly'] ) ? true : false;
		$method              = WPO_WCPDF()->settings->get_sequential_number_store_method();
		$now                 = new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) ); // for settings callback

		// reset: on
		if ( $reset_number_yearly ) {
			if ( ! ( $date = $this->get_date() ) ) {
				$date = $now;
			}

			// for yearly reset debugging only
			if ( apply_filters( 'wpo_wcpdf_enable_yearly_reset_debug', false ) ) {
				$date = new \WC_DateTime( '1st January Next Year' );
			}

			$store_name   = $this->get_sequential_number_store_name( $date, $method, $reset_number_yearly );
			$number_store = new SequentialNumberStore( $store_name, $method );

			if ( $number_store->is_new ) {
				$number_store->set_next( apply_filters( 'wpo_wcpdf_reset_number_yearly_start', 1, $this ) );
			}
		// reset: off
		} else {
			$store_name   = $this->get_sequential_number_store_name( $now, $method, $reset_number_yearly );
			$number_store = new SequentialNumberStore( $store_name, $method );
		}

		return $number_store;
	}

	/**
	 * Get the name of the Sequential Number Store, based on the date ('now' or 'document date')
	 * and whether the number should be reset yearly. When the number is reset yearly, numbered
	 * stores are used for non-current years, adding the year as the suffix
	 *
	 * @return string $number_store_name
	 */
	public function get_sequential_number_store_name( $date, $method, $reset_number_yearly ) {
		$store_base_name    = $this->order ? apply_filters( 'wpo_wcpdf_document_sequential_number_store', "{$this->slug}_number", $this ) : "{$this->slug}_number";
		$default_table_name = $this->get_number_store_table_default_name( $store_base_name, $method );
		$current_store_year = $this->get_number_store_year( $default_table_name );
		$requested_year     = intval( $date->date_i18n( 'Y' ) );

		// if we don't reset the number yearly, the store name is always the same
		if ( ! $reset_number_yearly ) {
			$number_store_name = $store_base_name;
		} else {
			// if the current store year doesn't match the year requested, check if we need to retire the store
			// (meaning that we have entered a new year)
			if( $requested_year !== $current_store_year ) {
				$current_store_year = $this->maybe_retire_number_store( $date, $store_base_name, $method );
			}

			// If it's a non-current year (future or past), append the year to the store name, otherwise use default
			if( $requested_year !== $current_store_year ) {
				$number_store_name = "{$store_base_name}_{$requested_year}";
			} else {
				$number_store_name = $store_base_name;
			}
		}

		return apply_filters( "wpo_wcpdf_{$this->slug}_number_store_name", $number_store_name, $store_base_name, $date, $method, $this );
	}

	/**
	 * Get the default table name of the Sequential Number Store
	 * @param  string $store_base_name
	 * @param  string $method
	 *
	 * @return string $table_name
	 */
	public function get_number_store_table_default_name( $store_base_name, $method ) {
		global $wpdb;
		return apply_filters( "wpo_wcpdf_number_store_table_name", "{$wpdb->prefix}wcpdf_{$store_base_name}", $store_base_name, $method );
	}

	/**
	 * Takes care of the rotation of database tables for the number store, used when 'reset yearly' is enabled:
	 *
	 * The table name for the current year is _always_ "{$wpdb->prefix}wcpdf_{$store_base_name}", e.g. wp_wcpdf_invoice_number
	 *
	 * when a year lapses, the existing table ('last year') is 'retired' by renaming it with the year appended,
	 * e.g. wp_wcpdf_invoice_number_2021 (when the current/new year is 2022). If there was a table for the new year,
	 * this will be renamed to the default store name (e.g. wp_wcdpdf_invoice_number)
	 *
	 * returns requested year if any error occurs, so that the current store table will be used
	 *
	 * @return int $year year of the current number store
	 */
	public function maybe_retire_number_store( $date, $store_base_name, $method ) {
		global $wpdb;
		$was_showing_errors = $wpdb->hide_errors(); // if we encounter errors, we'll log them instead

		$default_table_name = $this->get_number_store_table_default_name( $store_base_name, $method );
		$now                = new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) );

		// for yearly reset debugging only
		if ( apply_filters( 'wpo_wcpdf_enable_yearly_reset_debug', false ) ) {
			$now = new \WC_DateTime( '1st January Next Year' );
		}

		$current_year       = intval( $now->date_i18n( 'Y' ) );
		$current_store_year = intval( $this->get_number_store_year( $default_table_name ) );
		$requested_year     = intval( $date->date_i18n( 'Y' ) );

		// nothing to retire if requested year matches current store year or if current store year is not in the past
		if ( empty( $current_store_year ) || $requested_year == $current_store_year || ! ( $current_store_year < $current_year ) ) {
			return $current_store_year;
		}

		// current store year is in the past: rename table so that we can replace it with the current year

		$retired_table_name      = "{$default_table_name}_{$current_store_year}";
		$current_year_table_name = "{$default_table_name}_{$current_year}";

		// first, remove last year if it already exists
		$retired_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$retired_table_name}'" ) == $retired_table_name;
		if( $retired_exists ) {
			$table_removed = $wpdb->query( "DROP TABLE IF EXISTS {$retired_table_name}" );

			if( ! $table_removed ) {
				wcpdf_log_error( sprintf( 'An error occurred while trying to remove the duplicate number store %s: %s', $retired_table_name, $wpdb->last_error ) );
				return $requested_year;
			}
		}

		// rename current to last year
		$default_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$default_table_name}'" ) == $default_table_name;
		if( $default_exists ) {
			$table_renamed = $wpdb->query( "ALTER TABLE {$default_table_name} RENAME {$retired_table_name}" );

			if( ! $table_renamed ) {
				wcpdf_log_error( sprintf( 'An error occurred while trying to rename the number store from %s to %s: %s', $default_table_name, $retired_table_name, $wpdb->last_error ) );
				return $requested_year;
			}
		}

		// if the current year table name already exists (created earlier as a 'future' year), rename that to default
		$current_year_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$current_year_table_name}'" ) == $current_year_table_name;
		if( $current_year_exists ) {
			$table_renamed = $wpdb->query( "ALTER TABLE {$current_year_table_name} RENAME {$default_table_name}" );

			if( ! $table_renamed ) {
				wcpdf_log_error( sprintf( 'An error occurred while trying to rename the number store from %s to %s: %s', $current_year_table_name, $default_table_name, $wpdb->last_error ) );
				return $requested_year;
			}
		}

		if( $was_showing_errors ) {
			$wpdb->show_errors();
		}

		// current store year has been updated to current year, returning this means no year suffix has to be used
		return $current_year;
	}

	/**
	 * Gets the year from the last row of a number store table
	 * @param  string $table_name
	 *
	 * @return string
	 */
	public function get_number_store_year( $table_name ) {
		global $wpdb;
		$was_showing_errors = $wpdb->hide_errors(); // if we encounter errors, we'll log them instead

		$current_year = date_i18n( 'Y' );

		// for yearly reset debugging only
		if ( apply_filters( 'wpo_wcpdf_enable_yearly_reset_debug', false ) ) {
			$next_year    = new \WC_DateTime( '1st January Next Year' );
			$current_year = intval( $next_year->date_i18n( 'Y' ) );
		}

		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") == $table_name;
		if( $table_exists ) {
			// get year for the last row
			$year = $wpdb->get_var( "SELECT YEAR(date) FROM {$table_name} ORDER BY id DESC LIMIT 1" );
			// default to current year if no results
			if( ! $year ) {
				$year = $current_year;
				// if we don't get a result, this could either mean there's an error,
				// OR that the first number simply has not been created yet (=no rows)
				// we only log when there's an actual error
				if( ! empty( $wpdb->last_error ) ) {
					wcpdf_log_error( sprintf( 'An error occurred while trying to get the current year from the %s table: %s', $table_name, $wpdb->last_error ) );
				}
			}
		} else {
			$year = $current_year;
		}

		if( $was_showing_errors ) {
			$wpdb->show_errors();
		}

		return intval( $year );
	}

	/**
	 * Returns the due date timestamp.
	 *
	 * @return int
	 */
	public function get_due_date(): int {
		$due_date      = $this->get_setting( 'due_date' );
		$due_date_days = absint( $this->get_setting( 'due_date_days' ) );

		if ( empty( $this->order ) || empty( $due_date ) || $due_date_days < 0 ) {
			return 0;
		}

		return $this->calculate_due_date( $due_date_days );
	}

	/**
	 * Calculate the due date.
	 *
	 * @param int $due_date_days
	 *
	 * @return int Due date timestamp.
	 */
	public function calculate_due_date( int $due_date_days ): int {
		$due_date_days = apply_filters_deprecated(
			'wpo_wcpdf_due_date_days',
			array( $due_date_days, $this->get_type(), $this ),
			'3.8.7',
			'wpo_wcpdf_document_due_date_days'
		);
		$due_date_days = apply_filters( 'wpo_wcpdf_document_due_date_days', $due_date_days, $this );

		if ( ! is_numeric( $due_date_days ) || intval( $due_date_days ) < 0 ) {
			return 0;
		}

		$document_creation_date = $this->get_date( $this->get_type(), $this->order ) ?? new \WC_DateTime( 'now', new \DateTimeZone( wc_timezone_string() ) );
		$base_date              = apply_filters_deprecated(
			'wpo_wcpdf_due_date_base_date',
			array( $document_creation_date, $this->get_type(), $this ),
			'3.8.7',
			'wpo_wcpdf_document_due_date_base_date'
		);
		$base_date              = apply_filters( 'wpo_wcpdf_document_due_date_base_date', $base_date, $this );
		$due_date_datetime      = clone $base_date;
		$due_date_datetime      = $due_date_datetime->modify( "+$due_date_days days" );

		$due_date = apply_filters_deprecated(
			'wpo_wcpdf_due_date',
			array( $due_date_datetime->getTimestamp() ?? 0, $this->get_type(), $this ),
			'3.8.7',
			'wpo_wcpdf_document_due_date'
		);

		return apply_filters( 'wpo_wcpdf_document_due_date', $due_date ?? 0, $this );
	}

	/**
	 * Check if due date should be shown
	 *
	 * @return bool
	 */
	public function show_due_date(): bool {
		return $this->get_due_date() > 0;
	}

	protected function add_filters( $filters ) {
		foreach ( $filters as $filter ) {
			$filter = $this->normalize_filter_args( $filter );
			add_filter( $filter['hook_name'], $filter['callback'], $filter['priority'], $filter['accepted_args'] );
		}
	}

	protected function remove_filters( $filters ) {
		foreach ( $filters as $filter ) {
			$filter = $this->normalize_filter_args( $filter );
			remove_filter( $filter['hook_name'], $filter['callback'], $filter['priority'] );
		}
	}

	protected function normalize_filter_args( $filter ) {
		$filter = array_values( $filter );
		$hook_name = $filter[0];
		$callback = $filter[1];
		$priority = isset( $filter[2] ) ? $filter[2] : 10;
		$accepted_args = isset( $filter[3] ) ? $filter[3] : 1;
		return compact( 'hook_name', 'callback', 'priority', 'accepted_args' );
	}

}

endif; // class_exists
