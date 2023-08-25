<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents' ) ) :

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
		add_action( 'init', array( $this, 'init' ), 15 ); // after regular 10 actions but before most 'follow-up' actions (usually 20+)
	}

	/**
	 * Init document classes.
	 */
	public function init() {
		// Load Invoice & Packing Slip
		$this->documents['\WPO\WC\PDF_Invoices\Documents\Invoice']      = new \WPO\WC\PDF_Invoices\Documents\Invoice();
		$this->documents['\WPO\WC\PDF_Invoices\Documents\Packing_Slip'] = new \WPO\WC\PDF_Invoices\Documents\Packing_Slip();

		// Allow plugins to add their own documents
		$this->documents = apply_filters( 'wpo_wcpdf_document_classes', $this->documents );

		do_action( 'wpo_wcpdf_init_documents' );
	}

	/**
	 * Return the document classes - used in admin to load settings.
	 *
	 * @return array
	 */
	public function get_documents( $filter = 'enabled' ) {
		if ( empty($this->documents) ) {
			$this->init();
		}

		if ( $filter == 'enabled' ) {
			$documents = array();
			foreach ($this->documents as $class_name => $document) {
				if ( is_callable( array( $document, 'is_enabled' ) ) && $document->is_enabled() ) {
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
		foreach ( $this->get_documents( 'all' ) as $class_name => $document) {
			if ( $document->get_type() == $document_type && class_exists( $class_name ) ) {
				return new $class_name( $order );
			}
		}
		
		return false;
	}

}

endif; // class_exists
