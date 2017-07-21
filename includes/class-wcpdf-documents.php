<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents' ) ) :

class Documents {

	/** @var array Array of document classes */
	public $documents = array();

	/** @var Documents The single instance of the class */
	protected static $_instance = null;

	/**
	 * Main Documents Instance.
	 *
	 * Ensures only one instance of Documents is loaded or can be loaded.
	 *
	 * @since 2.0
	 * @static
	 * @return Documents Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor for the document class hooks in all documents that can be created.
	 *
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 999 );
	}

	/**
	 * Init document classes.
	 */
	public function init() {
		// Include document abstracts
		include_once( dirname( __FILE__ ) . '/documents/abstract-wcpdf-order-document.php' );
		include_once( dirname( __FILE__ ) . '/documents/abstract-wcpdf-order-document-methods.php' );
		// Include bulk document
		include_once( dirname( __FILE__ ) . '/documents/class-wcpdf-bulk-document.php' );
		// Document number formatting class
		include_once( dirname( __FILE__ ) . '/documents/class-wcpdf-document-number.php' );
		// Sequential number handler
		include_once( dirname( __FILE__ ) . '/documents/class-wcpdf-sequential-number-store.php' );

		// Load Invoice & Packing Slip
		$this->documents['\WPO\WC\PDF_Invoices\Documents\Invoice']		= include( 'documents/class-wcpdf-invoice.php' );
		$this->documents['\WPO\WC\PDF_Invoices\Documents\Packing_Slip']	= include( 'documents/class-wcpdf-packing-slip.php' );

		// Allow plugins to add their own documents
		$this->documents = apply_filters( 'wpo_wcpdf_document_classes', $this->documents );
	}

	/**
	 * Return the document classes - used in admin to load settings.
	 *
	 * @return array
	 */
	public function get_documents( $filter = 'enabled' ) {
		if ( $filter == 'enabled' ) {
			$documents = array();
			foreach ($this->documents as $class_name => $document) {
				if ($document->is_enabled()) {
					$documents[$class_name] = $document;
				}
			}
			return $documents;
		} else {
			// return all documents
			return $this->documents;
		}
	}

	public function get_document( $document_type, $order ) {
		foreach ($this->documents as $class_name => $document) {
			if ($document->get_type() == $document_type) {
				return new $class_name( $order );
			}
		}
		// document not known, inject into legacy document
		$document = include( WPO_WCPDF()->plugin_path() . '/includes/legacy/class-wcpdf-legacy-document.php' );
		// set document properties, which will trigger parent construct and load data correctly
		$document->set_props( array(
			'type'	=> $document_type,
			'title'	=> '',
			'order'	=> $order,
		) );

		return $document;
	}

}

endif; // class_exists

return new Documents();