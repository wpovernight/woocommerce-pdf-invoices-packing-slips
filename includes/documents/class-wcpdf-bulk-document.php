<?php
namespace WPO\WC\PDF_Invoices\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Bulk_Document' ) ) :

/**
 * Bulk Document
 *
 * Wraps single documents in a bulk document
 */

class Bulk_Document {
	
	/**
	 * Document type.
	 * @var String
	 */
	public $type;

	/**
	 * Wrapper document - used for filename etc.
	 * @var String
	 */
	public $wrapper_document;

	/**
	 * Order IDs.
	 * @var array
	 */
	public $order_ids;

	public function __construct( $document_type, $order_ids = array() ) {
		$this->type      = $document_type;
		$this->order_ids = $order_ids;
		$this->is_bulk   = true;
	}

	public function get_type() {
		return $this->type;
	}

	public function get_pdf() {
		do_action( 'wpo_wcpdf_before_pdf', $this->get_type(), $this );

		// temporarily apply filters that need to be removed again after the pdf is generated
		$pdf_filters = apply_filters( 'wpo_wcpdf_pdf_filters', array(), $this );
		$this->add_filters( $pdf_filters );

		$html = $this->get_html();
		$pdf_settings = array(
			'paper_size'		=> apply_filters( 'wpo_wcpdf_paper_format', $this->wrapper_document->get_setting( 'paper_size', 'A4' ), $this->get_type(), $this ),
			'paper_orientation'	=> apply_filters( 'wpo_wcpdf_paper_orientation', 'portrait', $this->get_type(), $this ),
			'font_subsetting'	=> $this->wrapper_document->get_setting( 'font_subsetting', false ),
		);
		$pdf_maker = wcpdf_get_pdf_maker( $html, $pdf_settings, $this );
		$pdf = apply_filters( 'wpo_wcpdf_pdf_data', $pdf_maker->output(), $this );
		
		do_action( 'wpo_wcpdf_after_pdf', $this->get_type(), $this );

		// remove temporary filters
		$this->remove_filters( $pdf_filters );

		return $pdf;
	}

	public function get_html() {
		do_action( 'wpo_wcpdf_before_html', $this->get_type(), $this );

		// temporarily apply filters that need to be removed again after the html is generated
		$html_filters = apply_filters( 'wpo_wcpdf_html_filters', array(), $this );
		$this->add_filters( $html_filters );

		$html_content = array();
		foreach ( $this->order_ids as $key => $order_id ) {
			do_action( 'wpo_wcpdf_process_template_order', $this->get_type(), $order_id );

			$order = wc_get_order( $order_id );

			if ( $document = wcpdf_get_document( $this->get_type(), $order, true ) ) {
				$html_content[ $key ] = $document->get_html( array( 'wrap_html_content' => false ) );
			}
		}

		// get wrapper document & insert body content
		$this->wrapper_document = wcpdf_get_document( $this->get_type(), null );
		$html = $this->wrapper_document->wrap_html_content( $this->merge_documents( $html_content ) );
		do_action( 'wpo_wcpdf_after_html', $this->get_type(), $this );

		// remove temporary filters
		$this->remove_filters( $html_filters );
		
		return $html;
	}


	public function merge_documents( $html_content ) {
		// insert page breaks merge
		$page_break = "\n<div style=\"page-break-before: always;\"></div>\n";
		$html = implode( $page_break, $html_content );
		return apply_filters( 'wpo_wcpdf_merged_bulk_document_content', $html, $html_content, $this );
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

	public function get_filename( $context = 'download', $args = array() ) {
		if ( empty( $this->wrapper_document ) ) {
			$this->wrapper_document = wcpdf_get_document( $this->get_type(), null );
		}
		$default_args = array(
			'order_ids' => $this->order_ids,
		);
		$args = $args + $default_args;
		$filename = $this->wrapper_document->get_filename( $context, $args );
		return $filename;
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
