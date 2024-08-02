<?php
namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents' ) ) :

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
		$this->documents['\WPO\IPS\Documents\Invoice']     = new \WPO\IPS\Documents\Invoice();
		$this->documents['\WPO\IPS\Documents\PackingSlip'] = new \WPO\IPS\Documents\PackingSlip();

		// Allow plugins to add their own documents
		$this->documents = apply_filters( 'wpo_wcpdf_document_classes', $this->documents );

		do_action( 'wpo_wcpdf_init_documents' );
	}

	/**
	 * Return the document classes - used in admin to load settings.
	 *
	 * @param $filter
	 * @param $output_format  Can be 'pdf', 'ubl' or anything for all
	 *
	 * @return array
	 */
	public function get_documents( $filter = 'enabled', $output_format = 'pdf' ) {
		if ( empty( $this->documents ) ) {
			$this->init();
		}

		// enabled
		if ( 'enabled' === $filter && ! empty( $output_format ) ) {
			$documents = array();

			foreach ( $this->documents as $class_name => $document ) {
				switch ( $output_format ) {
					case 'pdf':
					case 'ubl':
						if ( in_array( $output_format, $document->output_formats ) && is_callable( array( $document, 'is_enabled' ) ) && $document->is_enabled( $output_format ) ) {
							$documents[$class_name] = $document;
						}
						break;
					default:
						foreach ( $document->output_formats as $document_output_format ) {
							if ( is_callable( array( $document, 'is_enabled' ) ) && $document->is_enabled( $document_output_format ) ) {
								$documents[$class_name] = $document;
								break; // prevents adding the same document twice or more
							}
						}
						break;
				}
			}

		// enabled and disabled
		} else {
			$documents = $this->documents;
		}

		return apply_filters( 'wpo_wcpdf_get_documents', $documents, $filter, $output_format, $this );
	}

	public function get_document( $document_type, $order ) {
		foreach ( $this->get_documents( 'all' ) as $class_name => $document ) {
			if ( $document->get_type() == $document_type && class_exists( $class_name ) ) {
				return new $class_name( $order );
			}
		}

		return false;
	}

	public function get_document_titles() {
		$documents       = $this->get_documents();
		$document_titles = array();

		foreach ( $documents as $document ) {
			$document_titles[ $document->get_type() ] = $document->get_title();
		}

		return $document_titles;
	}

}

endif; // class_exists
