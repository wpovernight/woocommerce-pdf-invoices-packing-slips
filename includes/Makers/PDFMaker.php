<?php
namespace WPO\IPS\Makers;

use WPO\IPS\Vendor\Dompdf\Dompdf;
use WPO\IPS\Vendor\Dompdf\Options;

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
			'paper_size'		=> 'A4',
			'paper_orientation'	=> 'portrait',
			'font_subsetting'	=> false,
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

		// set options
		$options = new Options( apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'tempDir'                 => $main_instance->get_tmp_path( 'dompdf' ),
			'fontDir'                 => $main_instance->get_tmp_path( 'fonts' ),
			'fontCache'               => $main_instance->get_tmp_path( 'fonts' ),
			'chroot'                  => $this->get_chroot_paths(),
			'logOutputFile'           => $main_instance->get_tmp_path( 'dompdf' ) . "/log.htm",
			'defaultFont'             => 'dejavu sans',
			'isRemoteEnabled'         => true,
			'isHtml5ParserEnabled'    => true,
			'isFontSubsettingEnabled' => (bool) $this->settings['font_subsetting'],
		) ) );
		
		if ( isset( WPO_WCPDF()->get_instance( 'settings' )->debug_settings['enable_debug'] ) ) {
			$this->set_additional_debug_options( $options );
		}
		
		// instantiate and use the dompdf class
		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html, $options, $this->document );
		$dompdf->render();
		$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html, $options, $this->document );
		
		return $dompdf->output();
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

		return apply_filters( 'wpo_wcpdf_dompdf_chroot', $chroot );
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

}

endif; // class_exists
