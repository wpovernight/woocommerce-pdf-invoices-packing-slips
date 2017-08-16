<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Document_Number' ) ) :

/**
 * Document Number class
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Document_Number
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

class Document_Number {
	/**
	 * The raw, unformatted number
	 * @var int
	 */
	public $number;

	/**
	 * Document number formatted for display
	 * @var String
	 */
	public $formatted_number;

	/**
	 * Number prefix
	 * @var string
	 */
	public $prefix;

	/**
	 * Number suffix
	 * @var string
	 */
	public $suffix;

	/**
	 * Document Type
	 * @var string
	 */
	public $document_type;

	/**
	 * Order ID
	 * @var int
	 */
	public $order_id;

	/**
	 * Zeros padding (total number of digits including leading zeros)
	 * @var int
	 */
	public $padding;

	public function __construct( $number, $settings = array(), $document = null, $order = null ) {
		$number = apply_filters( 'wpo_wcpdf_raw_document_number', $number, $settings, $document, $order );
		if ( !is_array( $number ) ) {
			// we're creating a new number with settings as passed
			$this->number = $number;

			foreach ($settings as $key => $value) {
				$this->{$key} = $value;
			}

			if ( !isset( $this->formatted_number ) ) {
				$this->apply_formatting( $document, ( !empty( $document->order ) ? $document->order : $order ) );
			}

		} elseif ( is_array( $number ) ) {
			// loaded with full number data
			foreach ($number as $key => $value) {
				$this->{$key} = $value;
			}
		}

		if (!empty($document)) {
			$this->document_type = $document->get_type();
		}
		if (!empty($order)) {
			$this->order_id = WCX_Order::get_id( $order );
		}
	}

	public function __toString() {
		return $this->get_formatted();
	}

	public function get_formatted() {
		$formatted_number = isset( $this->formatted_number ) ? $this->formatted_number : '';
		$formatted_number = apply_filters( 'wpo_wcpdf_formatted_document_number', $formatted_number, $this, $this->document_type, $this->order_id );
		return $formatted_number;
	}

	public function get_plain() {
		return $this->number;
	}

	public function apply_formatting( $document, $order ) {
		if ( empty( $document ) || empty( $order ) ) {
			$this->formatted_number = $this->number;
			return;
		}

		// load plain number
		$number = $this->number;

		// get dates
		$order_date = WCX_Order::get_prop( $order, 'date_created' );
		$document_date = $document->get_date();
		// fallback to order date if no document date available
		if (empty($document_date)) {
			$document_date = $order_date;
		}

		// get format settings
		$formats = array(
			'prefix'	=> $this->prefix,
			'suffix'	=> $this->suffix,
		);

		// load replacement values
		$order_year       = $order_date->date_i18n( 'Y' );
		$order_month      = $order_date->date_i18n( 'm' );
		$order_day        = $order_date->date_i18n( 'd' );
		$document_year    = $document_date->date_i18n( 'Y' );
		$document_month	  = $document_date->date_i18n( 'm' );
		$document_day     = $document_date->date_i18n( 'd' );
		$document_year_short = $document_date->date_i18n( 'y' );

		// make replacements
		foreach ($formats as $key => $value) {
			$value = str_replace('[order_year]', $order_year, $value);
			$value = str_replace('[order_month]', $order_month, $value);
			$value = str_replace('[order_day]', $order_day, $value);
			$value = str_replace("[{$document->slug}_year]", $document_year, $value);
			$value = str_replace("[{$document->slug}_year_short]", $document_year_short, $value);
			$value = str_replace("[{$document->slug}_month]", $document_month, $value);
			$value = str_replace("[{$document->slug}_day]", $document_day, $value);
			$formats[$key] = $value;
		}

		// Padding
		if ( ctype_digit( (string)$this->padding ) ) {
			$number = sprintf('%0'.$this->padding.'d', $number);
		}

		// Add prefix & suffix
		$this->formatted_number = $formats['prefix'] . $number . $formats['suffix'] ;
		// Apply filters and store
		$this->formatted_number = apply_filters( 'wpo_wcpdf_format_document_number', $this->formatted_number, $this, $document, $order );

		return $this->formatted_number;
	}

	public function to_array() {
		return (array) $this;
	}
}

endif; // class_exists
