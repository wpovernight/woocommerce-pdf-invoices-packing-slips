<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;
use WPO\WC\PDF_Invoices\Compatibility\WC_DateTime;

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
			$this->order = WCX::get_order( $order_id );
		} elseif ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) {
			$this->order_id = WCX_Order::get_id( $order );
			$this->order = $order;
		}

		// set properties
		$this->slug = str_replace('-', '_', $this->type);

		// load settings
		$this->settings = $this->get_settings();
		$this->enabled = $this->get_setting( 'enabled', false );

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
		
	}

	public function init_settings() {
		return ;
	}

	public function get_settings() {
		$common_settings = WPO_WCPDF()->settings->get_common_document_settings();
		$document_settings = get_option( 'wpo_wcpdf_documents_settings_'.$this->get_type() );
		return (array) $document_settings + (array) $common_settings;
	}

	public function get_setting( $key, $default = '' ) {
		$setting = isset( $this->settings[$key] ) ? $this->settings[$key] : $default;
		return $setting;
	}

	public function get_attach_to_email_ids() {
		$email_ids = isset( $this->settings['attach_to_email_ids'] ) ? array_keys( $this->settings['attach_to_email_ids'] ) : array();
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
		$number = WCX_Order::get_meta( $order, "_wcpdf_{$this->slug}_number_data", true );
		// fallback to legacy data for number
		if ( empty( $number ) ) {
			$number = WCX_Order::get_meta( $order, "_wcpdf_{$this->slug}_number", true );
			$formatted_number = WCX_Order::get_meta( $order, "_wcpdf_formatted_{$this->slug}_number", true );
			if (!empty($formatted_number)) {
				$number = compact( 'number', 'formatted_number' );
			}
		}

		// pass data to setter functions
		$this->set_data( array(
			// always load date before number, because date is used in number formatting
			'date'			=> WCX_Order::get_meta( $order, "_wcpdf_{$this->slug}_date", true ),
			'number'		=> $number,
		), $order );

		return;
	}

	public function init() {
		$this->set_date( current_time( 'timestamp', true ) );
		do_action( 'wpo_wcpdf_init_document', $this );
	}

	public function save( $order = null ) {
		$order = empty( $order ) ? $this->order : $order;
		if ( empty( $order ) ) {
			return; // nowhere to save to...
		}

		foreach ($this->data as $key => $value) {
			if ( empty( $value ) ) {
				WCX_Order::delete_meta_data( $order, "_wcpdf_{$this->slug}_{$key}" );
				if ( $key == 'date' ) {
					WCX_Order::delete_meta_data( $order, "_wcpdf_{$this->slug}_{$key}_formatted" );
				} elseif ( $key == 'number' ) {
					WCX_Order::delete_meta_data( $order, "_wcpdf_{$this->slug}_{$key}_data" );
				}
			} else {
				if ( $key == 'date' ) {
					// store dates as timestamp and formatted as mysql time
					WCX_Order::update_meta_data( $order, "_wcpdf_{$this->slug}_{$key}", $value->getTimestamp() );
					WCX_Order::update_meta_data( $order, "_wcpdf_{$this->slug}_{$key}_formatted", $value->date( 'Y-m-d H:i:s' ) );
				} elseif ( $key == 'number' ) {
					// store both formatted number and number data
					WCX_Order::update_meta_data( $order, "_wcpdf_{$this->slug}_{$key}", $value->formatted_number );
					WCX_Order::update_meta_data( $order, "_wcpdf_{$this->slug}_{$key}_data", $value->to_array() );
				}
			}
		}
	}

	public function exists() {
		return !empty( $this->data['number'] );
	}

	/*
	|--------------------------------------------------------------------------
	| Data getters
	|--------------------------------------------------------------------------
	*/

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

	public function get_title() {
		return apply_filters( "wpo_wcpdf_{$this->slug}_title", $this->title, $this );
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
				$datetime = new WC_DateTime( "@{$value}", new \DateTimeZone( 'UTC' ) );
			} else {
				// Strings are defined in local WP timezone. Convert to UTC.
				if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $value, $date_bits ) ) {
					$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
					$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
				} else {
					$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $value ) ) ) );
				}
				$datetime  = new WC_DateTime( "@{$timestamp}", new \DateTimeZone( 'UTC' ) );
			}

			// Set local timezone or offset.
			if ( get_option( 'timezone_string' ) ) {
				$datetime->setTimezone( new \DateTimeZone( wc_timezone_string() ) );
			} else {
				$datetime->set_utc_offset( wc_timezone_offset() );
			}

			$this->data[ 'date' ] = $datetime;
		} catch ( Exception $e ) {}


	}

	public function set_number( $value, $order = null ) {
		$order = empty( $order ) ? $this->order : $order;

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

	/*
	|--------------------------------------------------------------------------
	| Settings getters / outputters
	|--------------------------------------------------------------------------
	*/

	public function get_number_settings() {
		$number_settings = isset($this->settings['number_format'])?$this->settings['number_format']:array();
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
	 * Show logo html
	 */
	public function header_logo() {
		if ($this->get_header_logo_id()) {
			$attachment_id = $this->get_header_logo_id();
			$company = $this->get_shop_name();
			if( $attachment_id ) {
				$attachment = wp_get_attachment_image_src( $attachment_id, 'full', false );
				
				$attachment_src = $attachment[0];
				$attachment_width = $attachment[1];
				$attachment_height = $attachment[2];

				$attachment_path = get_attached_file( $attachment_id );

				if ( apply_filters('wpo_wcpdf_use_path', true) && file_exists($attachment_path) ) {
					$src = $attachment_path;
				} else {
					$src = $attachment_src;
				}
				
				printf('<img src="%1$s" width="%2$d" height="%3$d" alt="%4$s" />', $src, $attachment_width, $attachment_height, esc_attr( $company ) );
			}
		}
	}

	public function get_settings_text( $settings_key, $default = false, $autop = true ) {
		if ( !empty( $this->settings[$settings_key]['default'] ) ) {
			$text = wptexturize( trim( $this->settings[$settings_key]['default'] ) );
			if ($autop === true) {
				$text = wpautop( $text );
			}
		} else {
			$text = $default;
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
		return $this->get_settings_text( 'footer' );
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
		do_action( 'wpo_wcpdf_before_pdf', $this->get_type(), $this );
		
		$pdf_settings = array(
			'paper_size'		=> apply_filters( 'wpo_wcpdf_paper_format', $this->get_setting( 'paper_size', 'A4' ), $this->get_type() ),
			'paper_orientation'	=> apply_filters( 'wpo_wcpdf_paper_orientation', 'portrait', $this->get_type() ),
			'font_subsetting'	=> $this->get_setting( 'font_subsetting', false ),
		);
		$pdf_maker = wcpdf_get_pdf_maker( $this->get_html(), $pdf_settings );
		$pdf = $pdf_maker->output();
		
		do_action( 'wpo_wcpdf_after_pdf', $this->get_type(), $this );
		do_action( 'wpo_wcpdf_pdf_created', $pdf, $this );

		return $pdf;
	}

	public function get_html( $args = array() ) {
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
		if ($args['wrap_html_content']) {
			$html = $this->wrap_html_content( $html );
		}

		// clean up special characters
		if ( function_exists('utf8_decode') && function_exists('mb_convert_encoding') ) {
			$html = utf8_decode(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
		}

		do_action( 'wpo_wcpdf_after_html', $this->get_type(), $this );

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
				'content' => $content,
			)
		);
		return $html;
	}

	public function get_filename( $context = 'download', $args = array() ) {
		$order_count = isset($args['order_ids']) ? count($args['order_ids']) : 1;

		$name = $this->get_type();
		if ( get_post_type( $this->order_id ) == 'shop_order_refund' ) {
			$number = $this->order_id;
		} else {
			$number = method_exists( $this->order, 'get_order_number' ) ? $this->order->get_order_number() : '';
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
		$path = WPO_WCPDF()->settings->get_template_path( $file );
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
		// get emails from WooCommerce
		global $woocommerce;
		$mailer = $woocommerce->mailer();
		$wc_emails = $mailer->get_emails();

		$non_order_emails = array(
			'customer_note',
			'customer_reset_password',
			'customer_new_account'
		);

		$emails = array();
		foreach ($wc_emails as $class => $email) {
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
		if ( version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
			$statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
			foreach ( $statuses as $status ) {
				$order_statuses[esc_attr( $status->slug )] = esc_html__( $status->name, 'woocommerce' );
			}
		} else {
			$statuses = wc_get_order_statuses();
			foreach ( $statuses as $status_slug => $status ) {
				$status_slug   = 'wc-' === substr( $status_slug, 0, 3 ) ? substr( $status_slug, 3 ) : $status_slug;
				$order_statuses[$status_slug] = $status;
			}
		}
		return $order_statuses;
	}


}

endif; // class_exists
