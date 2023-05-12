<?php
namespace WPO\WC\PDF_Invoices\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Order_Document' ) ) :

/**
 * Abstract Document
 *
 * Handles generic pdf document & order data and database interaction
 * which is extended by both Invoices & Packing Slips
 *
 * @class       \WPO\WC\PDF_Invoices\Documents\Order_Document
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

abstract class Order_Document {
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
	 * @var object
	 */
	public $order_id;

	/**
	 * Document settings.
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
	 * TRUE if document is enabled.
	 * @var bool
	 */
	public $enabled;

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
			$this->order_id = $order;
			$this->order    = wc_get_order( $this->order_id );
		} elseif ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) {
			$this->order_id = $order->get_id();
			$this->order    = $order;
		}

		// set properties
		$this->slug = str_replace('-', '_', $this->type);

		// load data
		if ( $this->order ) {
			$this->read_data( $this->order );
			if ( WPO_WCPDF()->legacy_mode_enabled() ) {
				global $wpo_wcpdf;
				$wpo_wcpdf->export->order = $this->order;
				$wpo_wcpdf->export->document = $this;
				$wpo_wcpdf->export->order_id = $this->order_id;
				$wpo_wcpdf->export->template_type = $this->type;
			}
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
		$this->order_settings  = $this->get_order_settings();
		$this->settings        = $this->get_settings();
		$this->latest_settings = $this->get_settings( true );
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

	public function get_settings( $latest = false ) {
		// get most current settings
		$common_settings   = WPO_WCPDF()->settings->get_common_document_settings();
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_'.$this->get_type() );
		$settings          = (array) $document_settings + (array) $common_settings;

		if ( $latest != true ) {
			// get historical settings if enabled
			if ( ! empty( $this->order ) && $this->use_historical_settings() == true ) {
				if ( ! empty( $this->order_settings ) && is_array( $this->order_settings ) ) {
					// ideally we should combine the order settings with the latest settings, so that new settings will
					// automatically be applied to existing orders too. However, doing this by combining arrays is not
					// possible because the way settings are currently stored means unchecked options are not included.
					// This means there is no way to tell whether an option didn't exist yet (in which case the new
					// option should be added) or whether the option was simly unchecked (in which case it should not
					// be overwritten). This can only be address by storing unchecked checkboxes too.
					$settings = (array) $this->order_settings + array_intersect_key( (array) $settings, array_flip( $this->get_non_historical_settings() ) );
				}
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

		$settings = ( $latest === true ) ? $this->latest_settings : $this->settings;

		if ( $this->storing_settings_enabled() && ( empty( $this->order_settings ) || $latest ) && ! empty( $settings ) && ! empty( $this->order ) ) {
			// this is either the first time the document is generated, or historical settings are disabled
			// in both cases, we store the document settings
			// exclude non historical settings from being saved in order meta
			$this->order->update_meta_data( "_wcpdf_{$this->slug}_settings", array_diff_key( $settings, array_flip( $this->get_non_historical_settings() ) ) );

			if ( 'invoice' == $this->slug ) {
				if ( isset( $settings['display_date'] ) && $settings['display_date'] == 'order_date' ) {
					$this->order->update_meta_data( "_wcpdf_{$this->slug}_display_date", 'order_date' );
				} else {
					$this->order->update_meta_data( "_wcpdf_{$this->slug}_display_date", 'invoice_date' );
				}
			}
			
			$this->order->save_meta_data();
		}
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
			'disable_for_statuses',
			'number_format', // this is stored in the number data already!
			'my_account_buttons',
			'my_account_restrict',
			'invoice_number_column',
			'invoice_date_column',
			'paper_size',
			'font_subsetting',
		), $this );
	}

	public function get_setting( $key, $default = '' ) {
		$non_historical_settings = $this->get_non_historical_settings();
		if ( in_array( $key, $non_historical_settings ) && isset( $this->latest_settings ) ) {
			$setting = isset( $this->latest_settings[$key] ) ? $this->latest_settings[$key] : $default;
		} else {
			$setting = isset( $this->settings[$key] ) ? $this->settings[$key] : $default;
		}
		return $setting;
	}

	public function get_attach_to_email_ids() {
		$email_ids = isset( $this->settings['attach_to_email_ids'] ) ? array_keys( array_filter( $this->settings['attach_to_email_ids'] ) ) : array();
		return $email_ids;  
	}

	public function get_type() {
		return $this->type;
	}

	public function is_enabled() {
		return apply_filters( 'wpo_wcpdf_document_is_enabled', $this->enabled, $this->type );
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
		$order = empty( $order ) ? $this->order : $order;
		if ( empty( $order ) ) {
			return; //Nothing to update
		}

		// pass data to setter functions
		if( ! empty( $data ) ) {
			$this->set_data( $data, $order );
			$this->save();
		}

		// save settings
		$this->save_settings( true );

		//Add order note
		$parent_order = $refund_id = false;
		// If credit note
		if ( $this->get_type() == 'credit-note' ) {
			$refund_id = $order->get_id();
			$parent_order = wc_get_order( $order->get_parent_id() );
		} /*translators: 1. credit note title, 2. refund id */
		$note = $refund_id ? sprintf( __( '%1$s (refund #%2$s) was regenerated.', 'woocommerce-pdf-invoices-packing-slips' ), ucfirst( $this->get_title() ), $refund_id ) : sprintf( __( '%s was regenerated', 'woocommerce-pdf-invoices-packing-slips' ), ucfirst( $this->get_title() ) );
		$note = wp_kses( $note, 'strip' );
		$parent_order ? $parent_order->add_order_note( $note ) : $order->add_order_note( $note );

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

	public function get_number( $document_type = '', $order = null, $context = 'view'  ) {
		return $this->get_data( 'number', $document_type, $order, $context );
	}

	public function get_date( $document_type = '', $order = null, $context = 'view'  ) {
		return $this->get_data( 'date', $document_type, $order, $context );
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
	
	public function get_title() {
		return apply_filters( "wpo_wcpdf_{$this->slug}_title", $this->title, $this );
	}

	public function title() {
		echo $this->get_title(); 
	}

	public function get_number_title() {
		/* translators: %s: document name */
		$number_title = sprintf( __( '%s Number:', 'woocommerce-pdf-invoices-packing-slips' ), $this->title );
		return apply_filters( "wpo_wcpdf_{$this->slug}_number_title", $number_title, $this );
	}

	public function get_date_title() {
		/* translators: %s: document name */
		$date_title = sprintf( __( '%s Date:', 'woocommerce-pdf-invoices-packing-slips' ), $this->title );
		return apply_filters( "wpo_wcpdf_{$this->slug}_date_title", $date_title, $this );
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
		} elseif ( $value instanceof Document_Number ) {
			// WCPDF 2.0 number data
			$document_number = $value;
		} elseif ( is_array( $value ) ) {
			// WCPDF 2.0 number data as array
			$document_number = new Document_Number( $value, $this->get_number_settings(), $this, $order  );
		} else {
			// plain number
			$document_number = new Document_Number( $value, $this->get_number_settings(), $this, $order );
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
		if (empty($this->settings)) {
			$settings = $this->get_settings( true ); // we always want the latest settings
			$number_settings = isset($settings['number_format'])?$settings['number_format']:array();
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
		return !empty( $this->settings['header_logo'] );
	}

	/**
	 * Return logo id
	 */
	public function get_header_logo_id() {
		if ( !empty( $this->settings['header_logo'] ) ) {
			return apply_filters( 'wpo_wcpdf_header_logo_id', $this->settings['header_logo'], $this );
		}
	}

	/**
	 * Return logo height
	 */
	public function get_header_logo_height() {
		if ( !empty( $this->settings['header_logo_height'] ) ) {
			return apply_filters( 'wpo_wcpdf_header_logo_height', str_replace( ' ', '', $this->settings['header_logo_height'] ), $this );
		}
	}

	/**
	 * Show logo html
	 */
	public function header_logo() {
		if ( $this->get_header_logo_id() ) {
			$attachment_id = $this->get_header_logo_id();
			$company       = $this->get_shop_name();

			if ( $attachment_id ) {
				$attachment      = wp_get_attachment_image_src( $attachment_id, 'full', false );
				$attachment_path = get_attached_file( $attachment_id );

				if ( empty( $attachment ) || empty( $attachment_path ) ) {
					return;
				}
				
				$attachment_src    = $attachment[0];
				$attachment_width  = $attachment[1];
				$attachment_height = $attachment[2];

				if ( apply_filters( 'wpo_wcpdf_use_path', true ) && file_exists( $attachment_path ) ) {
					$src = $attachment_path;
				} else {
					$head = wp_remote_head( $attachment_src, [ 'sslverify' => false ] );
					if ( is_wp_error( $head ) ) {
						$errors = $head->get_error_messages();
						foreach ( $errors as $error ) {
							wcpdf_log_error( $error, 'critical' );
						}
						return;
					} elseif ( isset( $head['response']['code'] ) && $head['response']['code'] === 200 ) {
						$src = $attachment_src;
					} else {
						return;
					}
				}
				
				$img_element = sprintf( '<img src="%1$s" alt="%2$s" />', esc_attr( $src ), esc_attr( $company ) );
				
				echo apply_filters( 'wpo_wcpdf_header_logo_img_element', $img_element, $attachment, $this );
			}
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
	 * Return/Show shop/company address if provided
	 */
	public function get_shop_address() {
		return $this->get_settings_text( 'shop_address' );
	}
	public function shop_address() {
		echo $this->get_shop_address();
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
		$pdf_maker    = wcpdf_get_pdf_maker( $this->get_html(), $pdf_settings, $this );
		$pdf          = $pdf_maker->output();
		
		return $pdf;
	}

	public function get_html( $args = array() ) {
		do_action( 'wpo_wcpdf_before_html', $this->get_type(), $this );

		// temporarily apply filters that need to be removed again after the html is generated
		$html_filters = apply_filters( 'wpo_wcpdf_html_filters', array(), $this );
		$this->add_filters( $html_filters );

		$default_args = array (
			'wrap_html_content'	=> true,
		);
		$args = $args + $default_args;

		$html = $this->render_template( $this->locate_template_file( "{$this->type}.php" ), array(
				'order' => $this->order,
				'order_id' => $this->order_id,
			)
		);
		if ($args['wrap_html_content']) {
			$html = $this->wrap_html_content( $html );
		}

		// clean up special characters
		if ( apply_filters( 'wpo_wcpdf_convert_encoding', function_exists('utf8_decode') && function_exists('mb_convert_encoding') ) ) {
			$html = utf8_decode(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
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
		die();
	}

	public function output_html() {
		echo $this->get_html();
		die();
	}

	public function wrap_html_content( $content ) {
		if ( WPO_WCPDF()->legacy_mode_enabled() ) {
			$GLOBALS['wpo_wcpdf']->export->output_body = $content;
		}

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

		$filename = $name . '-' . $suffix . '.pdf';

		// Filter filename
		$order_ids = isset($args['order_ids']) ? $args['order_ids'] : array( $this->order_id );
		$filename = apply_filters( 'wpo_wcpdf_filename', $filename, $this->get_type(), $order_ids, $context );

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
	 * @return Sequential_Number_Store
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
			$number_store = new Sequential_Number_Store( $store_name, $method );	
	
			if ( $number_store->is_new ) {
				$number_store->set_next( apply_filters( 'wpo_wcpdf_reset_number_yearly_start', 1, $this ) );
			}
		// reset: off
		} else {
			$store_name   = $this->get_sequential_number_store_name( $now, $method, $reset_number_yearly );
			$number_store = new Sequential_Number_Store( $store_name, $method );
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
	 * @param  string $metod
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
			// default to currenty year if no results
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
