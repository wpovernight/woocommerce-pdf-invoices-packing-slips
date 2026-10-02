<?php
namespace WPO\IPS\Makers;

use WPO\IPS\Vendor\Dompdf\Dompdf;
use WPO\IPS\Vendor\Dompdf\Options;
use WPO\IPS\EDI\Document as EDI_Document;
use WPO\IPS\EDI\SabreBuilder as EDI_SabreBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Makers\\PDFMaker' ) ) :

class PDFMaker {

	public string $html;
	public array $settings;
	public ?object $document;

	/**
	 * Constructor.
	 *
	 * @param string $html The HTML content to convert to PDF.
	 * @param array $settings Optional settings for PDF generation.
	 * @param object|null $document Optional document object related to the PDF.
	 */
	public function __construct( string $html, array $settings = array(), ?object $document = null ) {
		$this->html     = $html;
		$this->document = $document;

		$default_settings = array(
			'paper_size'        => 'A4',
			'paper_orientation' => 'portrait',
			'font_subsetting'   => false,
		);
		$this->settings = $settings + $default_settings;
	}
	
	/**
	 * Output the PDF.
	 *
	 * @return string|null
	 */
	public function output(): ?string {
		if ( empty( $this->html ) ) {
			return null;
		}
		
		$main_instance = WPO_WCPDF()->get_instance( 'main' );

		$tmp_path   = $main_instance->ensure_tmp_path( 'dompdf' );
		$fonts_path = $main_instance->ensure_tmp_path( 'fonts' );

		if ( false === $tmp_path || false === $fonts_path ) {
			wcpdf_log_error( 'PDF temporary folders are not available.', 'critical' );
			return null;
		}

		$hybrid = $this->is_hybrid_format();

		// set options
		$options = new Options( apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'tempDir'                 => $tmp_path,
			'fontDir'                 => $fonts_path,
			'fontCache'               => $fonts_path,
			'chroot'                  => $this->get_chroot_paths(),
			'logOutputFile'           => $tmp_path . '/log.htm',
			'defaultFont'             => 'dejavu sans',
			'isRemoteEnabled'         => true,
			'isHtml5ParserEnabled'    => true,
			'isFontSubsettingEnabled' => (bool) $this->settings['font_subsetting'],
		) ) );

		if ( $hybrid ) {
			$options->setIsPdfAEnabled( true );
		}

		$this->restrict_remote_resources( $options );
		
		if ( isset( WPO_WCPDF()->get_instance( 'settings' )->get_settings( 'debug' )['enable_debug'] ) ) {
			$this->set_additional_debug_options( $options );
		}
		
		// instantiate and use the dompdf class
		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html, $options, $this->document );

		$hybrid_file = null;

		try {
			if ( $hybrid ) {
				$hybrid_file = $this->set_pdfa_file( $dompdf, $tmp_path );
			}

			$dompdf->render();
			$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html, $options, $this->document );

			return $dompdf->output();
		} finally {
			if ( ! empty( $hybrid_file ) ) {
				WPO_WCPDF()->get_instance( 'file_system' )->delete( $hybrid_file );
			}
		}
	}
	
	/**
	 * Get the chroot paths for Dompdf.
	 *
	 * @return array
	 */
	private function get_chroot_paths(): array {
		$chroot         = array( WP_CONTENT_DIR ); // default
		$main_instance  = WPO_WCPDF()->get_instance( 'main' );
		$wp_upload_base = $main_instance->get_wp_upload_base();
		$tmp_base       = $main_instance->get_tmp_base();

		if ( ! empty( $wp_upload_base ) ) {
			$chroot[] = $wp_upload_base;
		}
		
		if ( ! empty( $tmp_base ) ) {
			$chroot[] = $tmp_base;
		}

		return (array) apply_filters(
			'wpo_wcpdf_dompdf_chroot',
			$chroot
		);
	}
	
	/**
	 * Set additional debug options for Dompdf.
	 *
	 * @param Options $options
	 * @return void
	 */
	private function set_additional_debug_options( Options $options ): void {
		$dompdf_debug_options = apply_filters( 'wpo_wcpdf_dompdf_additional_debug_options', array(
			'debugPng',
			'debugCss',
			'debugLayout',
		) );
		
		foreach ( $dompdf_debug_options as $option ) {
			$options->set( $option, true );
		}
	}

	/**
	 * Restrict Dompdf remote resources to allowed hosts and ports (SSRF).
	 *
	 * @param Options $options
	 * @return void
	 */
	private function restrict_remote_resources( Options $options ): void {
		$resource_urls = wpo_ips_get_trusted_resource_urls( $this->document );

		if ( empty( $options->getAllowedRemoteHosts() ) ) {
			$hosts = $this->get_allowed_remote_hosts( $resource_urls );

			// Dompdf treats an empty host list as unrestricted.
			if ( empty( $hosts ) ) {
				$options->setIsRemoteEnabled( false );
			} else {
				$options->setAllowedRemoteHosts( $hosts );
			}
		}

		$allowed_ports = wpo_ips_get_allowed_remote_ports( $this->document, $resource_urls );
		$port_rule     = static function ( string $uri ) use ( $allowed_ports ): array {
			$port = wp_parse_url( $uri, PHP_URL_PORT ) ?? ( 'https' === strtolower( (string) wp_parse_url( $uri, PHP_URL_SCHEME ) ) ? 443 : 80 );

			return in_array( $port, $allowed_ports, true )
				? array( true, null )
				: array( false, 'Remote port not allowed: ' . $uri );
		};

		$protocols = $options->getAllowedProtocols();

		foreach ( array( 'http://', 'https://' ) as $protocol ) {
			if ( isset( $protocols[ $protocol ] ) ) {
				$options->addAllowedProtocol( $protocol, ...array_merge( $protocols[ $protocol ]['rules'], array( $port_rule ) ) );
			}
		}
	}

	/**
	 * Hosts Dompdf may load remote resources from.
	 *
	 * @param array $resource_urls Previously discovered resource URLs.
	 * @return array
	 */
	private function get_allowed_remote_hosts( array $resource_urls ): array {
		$hosts          = array_filter( array_map( static function ( $url ) {
			return wp_parse_url( $url, PHP_URL_HOST );
		}, $resource_urls ) );
		$debug_settings = \WPO_WCPDF()->get_instance( 'settings' )->get_settings( 'debug' );
		$hosts          = array_merge( $hosts, wpo_ips_normalize_remote_hosts( (string) ( $debug_settings['allowed_remote_hosts'] ?? '' ) ) );
		$hosts          = (array) apply_filters( 'wpo_ips_allowed_remote_hosts', $hosts, $this->document );

		return array_values( array_unique( array_filter( $hosts, 'is_string' ) ) );
	}

	/**
	 * Check whether the current document requires a hybrid PDF.
	 *
	 * @return bool
	 */
	private function is_hybrid_format(): bool {
		if ( ! function_exists( 'wpo_ips_edi_is_available' ) || ! wpo_ips_edi_is_available() ) {
			return false;
		}

		$format = wpo_ips_edi_get_current_format( true );

		if ( ! is_array( $format ) || empty( $format['hybrid'] ) ) {
			return false;
		}

		$document_types = isset( $format['documents'] ) && is_array( $format['documents'] )
			? array_keys( $format['documents'] )
			: array();

		$document_type = $this->document && is_callable( array( $this->document, 'get_type' ) )
			? $this->document->get_type()
			: '';

		return in_array( $document_type, $document_types, true );
	}

	/**
	 * Add the hybrid EDI document and RDF metadata to the PDF.
	 *
	 * @param Dompdf $dompdf
	 * @param string $tmp_path
	 * @return string|null Temporary EDI file path.
	 */
	private function set_pdfa_file( Dompdf $dompdf, string $tmp_path ): ?string {
		if (
			! function_exists( 'wpo_ips_edi_get_current_format' ) ||
			! function_exists( 'wpo_ips_edi_get_current_syntax' ) ||
			! class_exists( 'WPO\IPS\EDI\Document' ) ||
			! class_exists( 'WPO\IPS\EDI\SabreBuilder' )
		) {
			wcpdf_log_error( 'Required functions or classes are not available for setting the hybrid EDI file.' );
			return null;
		}

		$format = wpo_ips_edi_get_current_format();

		if ( empty( $format ) || ! is_string( $format ) ) {
			wcpdf_log_error( 'EDI format is not available.' );
			return null;
		}

		$syntax = wpo_ips_edi_get_current_syntax();

		if ( empty( $syntax ) ) {
			wcpdf_log_error( 'EDI syntax is not available.' );
			return null;
		}

		$temp_file = null;

		try {
			$edi_document = new EDI_Document( $syntax, $format, $this->document );
			$builder      = new EDI_SabreBuilder();
			$content      = $builder->build( $edi_document );

			if ( empty( $content ) ) {
				wcpdf_log_error( 'EDI document content is empty.' );
				return null;
			}

			$edi_format_document = $edi_document->get_format_document();

			if (
				! $edi_format_document ||
				! is_callable( array( $edi_format_document, 'get_document_filename' ) ) ||
				! is_callable( array( $edi_format_document, 'get_rdf_metadata' ) )
			) {
				wcpdf_log_error( 'EDI format document does not support hybrid PDF metadata.' );
				return null;
			}

			$canvas = $dompdf->getCanvas();

			if ( ! is_callable( array( $canvas, 'get_cpdf' ) ) ) {
				wcpdf_log_error( 'Dompdf CPDF backend is not available.' );
				return null;
			}

			$cpdf = $canvas->get_cpdf();

			if (
				! is_callable( array( $cpdf, 'addEmbeddedFile' ) ) ||
				! is_callable( array( $cpdf, 'setAdditionalXmpRdf' ) )
			) {
				wcpdf_log_error( 'Required Dompdf methods are not available for setting the hybrid EDI file.' );
				return null;
			}

			$temp_file = tempnam( $tmp_path, 'wpo-edi-' );

			if ( false === $temp_file ) {
				wcpdf_log_error( 'Could not create temporary EDI file.' );
				return null;
			}

			$file_system = WPO_WCPDF()->get_instance( 'file_system' );

			if ( false === $file_system->put_contents( $temp_file, $content, FS_CHMOD_FILE ) ) {
				$file_system->delete( $temp_file );
				wcpdf_log_error( 'Could not write temporary EDI file.' );
				return null;
			}

			$cpdf->addEmbeddedFile(
				$temp_file,
				$edi_format_document->get_document_filename(),
				$format,
				'text/xml',
				array(
					$cpdf->catalogId => 'Alternative',
				)
			);

			$cpdf->setAdditionalXmpRdf(
				$edi_format_document->get_rdf_metadata()
			);

			return $temp_file;
		} catch ( \Throwable $e ) {
			if ( ! empty( $temp_file ) ) {
				WPO_WCPDF()->get_instance( 'file_system' )->delete( $temp_file );
			}

			wcpdf_log_error( 'Could not add the hybrid EDI file to the PDF.', 'error', $e );

			return null;
		}
	}

}

endif; // class_exists
