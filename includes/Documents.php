<?php
namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents' ) ) :

class Documents {

	public array $documents           = array();
	protected static ?self $_instance = null;

	/**
	 * Singleton instance accessor.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		if ( WPO_WCPDF()->is_order_page() || WPO_WCPDF()->is_settings_page() || WPO_WCPDF()->is_account_page() ) {
			add_action( 'init', array( $this, 'init' ), 15 ); // after regular 10 actions but before most 'follow-up' actions (usually 20+)
		}
	}

	/**
	 * Init document classes.
	 * 
	 * @return void
	 */
	public function init(): void {
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
	 * @param string $filter
	 * @param string $output_format  Can be 'pdf', 'xml' or anything for all
	 * @return array
	 */
	public function get_documents( string $filter = 'enabled', string $output_format = 'pdf' ): array {
		if ( empty( $this->documents ) ) {
			$this->init();
		}

		// enabled
		if ( 'enabled' === $filter && ! empty( $output_format ) ) {
			$documents = array();

			foreach ( $this->documents as $class_name => $document ) {
				$document_output_formats = isset( $document->output_formats ) && is_array( $document->output_formats )
					? $document->output_formats
					: array( 'pdf' );
				
				switch ( $output_format ) {
					case 'pdf':
					case 'xml':
						if ( in_array( $output_format, $document_output_formats ) && is_callable( array( $document, 'is_enabled' ) ) && $document->is_enabled( $output_format ) ) {
							$documents[ $class_name ] = $document;
						}
						break;
					default:
						foreach ( $document_output_formats as $document_output_format ) {
							if ( is_callable( array( $document, 'is_enabled' ) ) && $document->is_enabled( $document_output_format ) ) {
								$documents[ $class_name ] = $document;
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

	/**
	 * Return an instance of the document class for a given document type and order.
	 *
	 * @param string $document_type
	 * @param int|object|\WC_Order $order
	 * @return OrderDocument|false
	 */
	public function get_document( string $document_type, $order ) {
		foreach ( $this->get_documents( 'all' ) as $class_name => $document ) {
			if ( $document->get_type() == $document_type && class_exists( $class_name ) ) {
				return new $class_name( $order );
			}
		}

		return false;
	}

	/**
	 * Return an array of document titles, indexed by document type.
	 *
	 * @return array
	 */
	public function get_document_titles(): array {
		$documents       = $this->get_documents();
		$document_titles = array();

		foreach ( $documents as $document ) {
			$document_titles[ $document->get_type() ] = $document->get_title();
		}

		return $document_titles;
	}

}

endif; // class_exists
