<?php
namespace WPO\IPS\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\SummaryDocument' ) ) :

	abstract class SummaryDocument {

		public string $type              = 'summary';
		public string $slug              = 'summary';
		public string $title             = '';
		public array $order_ids          = array();
		public array $settings           = array();
		public array $common_settings    = array();
		public array $output_formats     = array();

		/**
		 * Constructor.
		 *
		 * @param array $order_ids
		 * @param array $settings
		 */
		public function __construct( array $order_ids = array(), array $settings = array() ) {
			$this->slug            = ! empty( $this->type ) ? str_replace( '-', '_', $this->type ) : 'summary';
			$this->order_ids       = $order_ids;
			$this->settings        = $settings;
			$this->common_settings = WPO_WCPDF()->get_instance( 'settings' )->get_common_document_settings();
			$this->output_formats  = apply_filters( 'wpo_wcpdf_document_output_formats', array( 'pdf' ), $this );
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
		 * Get the document title.
		 *
		 * @return string
		 */
		public function get_title(): string {
			return $this->title;
		}

		/**
		 * Get a summary setting, falling back to the common document settings.
		 *
		 * @param string $key
		 * @param mixed  $default
		 * @return mixed
		 */
		public function get_setting( string $key, mixed $default = '' ): mixed {
			if ( array_key_exists( $key, $this->settings ) ) {
				return $this->settings[ $key ];
			}

			return array_key_exists( $key, $this->common_settings )
				? $this->common_settings[ $key ]
				: $default;
		}

		/**
		 * Get the document date.
		 *
		 * @return \WC_DateTime
		 */
		public function get_date(): \WC_DateTime {
			return new \WC_DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		}

		/**
		 * Output the document date.
		 *
		 * @return void
		 */
		public function output_date(): void {
			echo esc_html( $this->get_date()->date_i18n( wcpdf_date_format( $this, 'document_date' ) ) );
		}

		/**
		 * Get the document date title.
		 *
		 * @return string
		 */
		public function get_date_title(): string {
			$title = __( 'Summary date', 'woocommerce-pdf-invoices-packing-slips' );

			return apply_filters( 'wpo_wcpdf_document_date_title', $title, $this );
		}

		/**
		 * Checks if the document exists.
		 *
		 * @return bool
		 */
		public function exists(): bool {
			return true;
		}

		/**
		 * Check if the document type is enabled for output.
		 *
		 * @param string $output_format
		 * @return bool
		 */
		public function is_enabled( string $output_format = 'pdf' ): bool {
			$is_enabled = in_array( $output_format, $this->output_formats, true );

			return (bool) apply_filters(
				'wpo_wcpdf_document_is_enabled',
				$is_enabled,
				$this->type,
				$output_format
			);
		}

		/**
		 * Get the PDF file contents.
		 *
		 * @return string|null
		 */
		public function get_pdf(): ?string {
			// Maybe we need to reinstall fonts first.
			WPO_WCPDF()->get_instance( 'main' )->maybe_reinstall_fonts();

			$pdf_file = apply_filters( 'wpo_wcpdf_load_pdf_file_path', null, $this );

			if ( $pdf_file ) {
				$pdf = WPO_WCPDF()->get_instance( 'file_system' )->get_contents( $pdf_file );
			} else {
				$pdf = null;
			}

			$pdf = apply_filters( 'wpo_wcpdf_pdf_data', $pdf, $this );

			if ( ! empty( $pdf ) ) {
				return $pdf;
			}

			do_action( 'wpo_wcpdf_before_pdf', $this->get_type(), $this );

			// Temporarily apply filters that need to be removed again after the PDF is generated.
			$pdf_filters = apply_filters( 'wpo_wcpdf_pdf_filters', array(), $this );
			\wpo_ips_add_filters( $pdf_filters );

			$pdf_settings = array(
				'paper_size'        => apply_filters(
					'wpo_wcpdf_paper_format',
					$this->get_setting( 'paper_size', 'A4' ),
					$this->get_type(),
					$this
				),
				'paper_orientation' => apply_filters(
					'wpo_wcpdf_paper_orientation',
					$this->get_setting( 'orientation', 'portrait' ),
					$this->get_type(),
					$this
				),
				'font_subsetting'   => $this->get_setting( 'font_subsetting', false ),
			);

			$pdf_maker = wcpdf_get_pdf_maker( $this->get_html(), $pdf_settings, $this );
			$pdf       = $pdf_maker->output();

			do_action( 'wpo_wcpdf_after_pdf', $this->get_type(), $this );

			// Remove temporary filters.
			\wpo_ips_remove_filters( $pdf_filters );

			do_action( 'wpo_wcpdf_pdf_created', $pdf, $this );

			$pdf = apply_filters(
				'wpo_wcpdf_get_pdf',
				$pdf,
				$this
			);

			return is_string( $pdf )
				? $pdf
				: null;
		}

		/**
		 * Get the HTML content for the document.
		 *
		 * @param array $args
		 * @return string
		 */
		public function get_html( array $args = array() ): string {
			WPO_WCPDF()->get_instance( 'main' )->load_template_functions();

			// Temporarily apply filters that need to be removed again after the HTML is generated.
			$html_filters = apply_filters( 'wpo_wcpdf_html_filters', array(), $this );
			\wpo_ips_add_filters( $html_filters );

			do_action( 'wpo_wcpdf_before_html', $this->get_type(), $this );

			$default_args = array(
				'wrap_html_content' => true,
			);
			$args = $args + $default_args;

			$html = $this->render_template(
				$this->locate_template_file( "{$this->get_type()}.php" )
			);

			if ( $args['wrap_html_content'] ) {
				$html = $this->wrap_html_content( $html );
			}

			// Clean up special characters.
			if ( apply_filters( 'wpo_wcpdf_convert_encoding', function_exists( 'htmlspecialchars_decode' ) ) ) {
				$html = htmlspecialchars_decode( wcpdf_convert_encoding( $html ), ENT_QUOTES );
			}

			do_action( 'wpo_wcpdf_after_html', $this->get_type(), $this );

			// Remove temporary filters.
			\wpo_ips_remove_filters( $html_filters );

			return (string) apply_filters(
				'wpo_wcpdf_get_html',
				$html,
				$this
			);
		}

		/**
		 * Output the PDF file to the browser.
		 *
		 * @param string $output_mode
		 * @return never
		 */
		public function output_pdf( string $output_mode = 'download' ): never {
			$pdf = $this->get_pdf();

			wcpdf_pdf_headers( $this->get_filename(), $output_mode, $pdf );

			echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit();
		}

		/**
		 * Output the HTML document.
		 *
		 * @return void
		 */
		public function output_html(): void {
			echo $this->get_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Wrap the HTML content in a full HTML document structure.
		 *
		 * @param string $content
		 * @param bool   $is_bulk
		 * @return string
		 */
		public function wrap_html_content( string $content, bool $is_bulk = false ): string {
			return $this->render_template(
				$this->locate_template_file( 'html-document-wrapper.php' ),
				array(
					'content' => apply_filters( 'wpo_wcpdf_html_content', $content ),
					'is_bulk' => $is_bulk,
				)
			);
		}

		/**
		 * Get the filename for the document.
		 *
		 * @param string $context
		 * @param array  $args
		 * @return string
		 */
		public function get_filename( string $context = 'download', array $args = array() ): string {
			$name          = $this->get_type();
			$suffix        = date_i18n( 'Y-m-d' );
			$output_format = ! empty( $args['output'] ) ? esc_attr( $args['output'] ) : 'pdf';
			$filename      = $name . '-' . $suffix . wcpdf_get_document_output_format_extension( $output_format );

			$order_ids = isset( $args['order_ids'] ) ? $args['order_ids'] : $this->order_ids;
			$filename  = apply_filters(
				'wpo_wcpdf_filename',
				$filename,
				$this->get_type(),
				$order_ids,
				$context,
				$args
			);

			return sanitize_file_name( $filename );
		}

		/**
		 * Get the base template path for this summary document.
		 *
		 * @return string
		 */
		abstract public function get_template_path(): string;

		/**
		 * Locate a template file.
		 *
		 * @param string $file
		 * @return string
		 */
		public function locate_template_file( string $file ): string {
			if ( empty( $file ) ) {
				$file = $this->get_type() . '.php';
			}

			$file_path = trailingslashit( $this->get_template_path() ) . $file;
			$file_path = apply_filters(
				'wpo_wcpdf_template_file',
				$file_path,
				$this->get_type(),
				$this->order_ids
			);

			return $file_path;
		}

		/**
		 * Render the template file with the given arguments and return the output.
		 *
		 * @param string $file
		 * @param array  $args
		 * @return string
		 */
		public function render_template( string $file, array $args = array() ): string {
			do_action( 'wpo_wcpdf_process_template', $this->get_type(), $this );

			if ( ! empty( $args ) ) {
				extract( $args );
			}

			ob_start();

			if ( WPO_WCPDF()->get_instance( 'file_system' )->exists( $file ) ) {
				include $file;
			}

			return ob_get_clean();
		}

		/**
		 * Output template styles.
		 *
		 * @return void
		 */
		public function template_styles(): void {
			$css_file_path = apply_filters(
				'wpo_wcpdf_template_styles_file',
				$this->locate_template_file( 'style.css' )
			);

			$css = '';

			if ( WPO_WCPDF()->get_instance( 'file_system' )->exists( $css_file_path ) ) {
				ob_start();
				include $css_file_path;
				$css = ob_get_clean();
			}

			$css = apply_filters( 'wpo_wcpdf_template_styles', $css, $this );

			echo esc_textarea( $css );
		}

	}

	endif; // class_exists
