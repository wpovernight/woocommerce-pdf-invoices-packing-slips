<?php
namespace WPO\IPS\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\BulkDocument' ) ) :

class BulkDocument {

	public string $slug;
	public string $type;
	public array $order_ids;
	public bool $is_bulk;
	public array $output_formats;
	public ?object $wrapper_document = null;

	/**
	 * Constructor.
	 *
	 * @param string $document_type
	 * @param array $order_ids
	 */
	public function __construct( string $document_type, array $order_ids = array() ) {
		$this->slug      = 'bulk';
		$this->type      = $document_type;
		$this->order_ids = $order_ids;
		$this->is_bulk   = true;

		// output formats (placed after parent construct to override the abstract default)
		$this->output_formats = apply_filters( 'wpo_wcpdf_document_output_formats', array( 'pdf' ), $this );
	}

	/**
	 * Check if at least one of the documents in the bulk document exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		$exists = false;

		foreach ( $this->order_ids as $order_id ) {
			$document = wcpdf_get_document( $this->type, $order_id );
			if ( $document && is_callable( array( $document, 'exists' ) ) && $document->exists() ) {
				$exists = true;
				break;
			}
		}

		return $exists;
	}

	/**
	 * Check if the document type is enabled for output.
	 *
	 * @param string $output_format
	 * @return bool
	 */
	public function is_enabled( string $output_format = 'pdf' ): bool {
		if ( in_array( $output_format, $this->output_formats ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the document type.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get the PDF content.
	 *
	 * @return string|null
	 */
	public function get_pdf(): ?string {
		do_action( 'wpo_wcpdf_before_pdf', $this->get_type(), $this );

		// temporarily apply filters that need to be removed again after the pdf is generated
		$pdf_filters = apply_filters( 'wpo_wcpdf_pdf_filters', array(), $this );
		\wpo_ips_add_filters( $pdf_filters );

		$html = $this->get_html();
		$pdf_settings = array(
			'paper_size'        => apply_filters( 'wpo_wcpdf_paper_format', $this->wrapper_document->get_setting( 'paper_size', 'A4' ), $this->get_type(), $this ),
			'paper_orientation' => apply_filters( 'wpo_wcpdf_paper_orientation', 'portrait', $this->get_type(), $this ),
			'font_subsetting'   => $this->wrapper_document->get_setting( 'font_subsetting', false ),
		);
		$pdf_maker = wcpdf_get_pdf_maker( $html, $pdf_settings, $this );
		$pdf = apply_filters( 'wpo_wcpdf_pdf_data', $pdf_maker->output(), $this );

		do_action( 'wpo_wcpdf_after_pdf', $this->get_type(), $this );

		// remove temporary filters
		\wpo_ips_remove_filters( $pdf_filters );

		return $pdf;
	}

	/**
	 * Get the HTML content.
	 *
	 * @param array $args
	 * @return string
	 */
	public function get_html( array $args = array() ): string {
		// temporarily apply filters that need to be removed again after the html is generated
		$html_filters = apply_filters( 'wpo_wcpdf_html_filters', array(), $this );
		\wpo_ips_add_filters( $html_filters );

		do_action( 'wpo_wcpdf_before_html', $this->get_type(), $this );

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

		// clean up special characters
		if ( apply_filters( 'wpo_wcpdf_convert_encoding', function_exists( 'htmlspecialchars_decode' ) ) ) {
			$html = htmlspecialchars_decode( wcpdf_convert_encoding( $html ), ENT_QUOTES );
		}

		do_action( 'wpo_wcpdf_after_html', $this->get_type(), $this );

		// remove temporary filters
		\wpo_ips_remove_filters( $html_filters );

		return $html;
	}

	/**
	 * Merge the HTML content of the individual documents into one HTML string, with page breaks in between.
	 *
	 * @param array $html_content
	 * @return string
	 */
	public function merge_documents( array $html_content ): string {
		// insert page breaks merge
		$page_break = "\n<div style=\"page-break-before: always;\"></div>\n";
		$html = implode( $page_break, $html_content );
		return apply_filters( 'wpo_wcpdf_merged_bulk_document_content', $html, $html_content, $this );
	}

	/**
	 * Output the PDF document.
	 *
	 * @param string $output_mode
	 */
	public function output_pdf( string $output_mode = 'download' ) {
		$pdf = $this->get_pdf();
		wcpdf_pdf_headers( $this->get_filename(), $output_mode, $pdf );
		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		die();
	}

	/**
	 * Output the HTML document.
	 */
	public function output_html() {
		echo $this->get_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		die();
	}

	/**
	 * Get the filename for the document.
	 *
	 * @param string $context
	 * @param array $args
	 * @return string
	 */
	public function get_filename( string $context = 'download', array $args = array() ): string {
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

	/**
	 * Add filters.
	 *
	 * @param array $filters
	 * @return array
	 */
	protected function add_filters( array $filters ): array {
		\wcpdf_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_add_filters' );
		return wpo_ips_add_filters( $filters );
	}

	/**
	 * Remove filters.
	 *
	 * @param array $filters
	 * @return array
	 */
	protected function remove_filters( array $filters ): array {
		\wcpdf_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_remove_filters' );
		return wpo_ips_remove_filters( $filters );
	}

	/**
	 * Normalize filter arguments.
	 *
	 * @param array $filter
	 * @return array
	 */
	protected function normalize_filter_args( array $filter ): array {
		\wcpdf_deprecated_function( __FUNCTION__, '5.0.0', 'wpo_ips_normalize_filter_args' );
		return wpo_ips_normalize_filter_args( $filter );
	}

}

endif; // class_exists
