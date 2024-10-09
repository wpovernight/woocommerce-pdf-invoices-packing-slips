<?php
namespace WPO\IPS\Makers;

use WPO\IPS\Vendor\Dompdf\Dompdf;
use WPO\IPS\Vendor\Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Makers\\PDFMaker' ) ) :

class PDFMaker {

	public $html;
	public $settings;
	public $document;

	public function __construct( $html, $settings = array(), $document = null ) {
		$this->html     = $html;
		$this->document = $document;

		$default_settings = array(
			'paper_size'		=> 'A4',
			'paper_orientation'	=> 'portrait',
			'font_subsetting'	=> false,
		);
		$this->settings = $settings + $default_settings;
	}

	public function output() {
		if ( empty( $this->html ) ) {
			return;
		}

		// set options
		$options = new Options( apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'tempDir'                 => WPO_WCPDF()->main->get_tmp_path( 'dompdf' ),
			'fontDir'                 => WPO_WCPDF()->main->get_tmp_path( 'fonts' ),
			'fontCache'               => WPO_WCPDF()->main->get_tmp_path( 'fonts' ),
			'chroot'                  => $this->get_chroot_paths(),
			'logOutputFile'           => WPO_WCPDF()->main->get_tmp_path( 'dompdf' ) . "/log.htm",
			'defaultFont'             => 'dejavu sans',
			'isRemoteEnabled'         => true,
			'isHtml5ParserEnabled'    => true,
			'isFontSubsettingEnabled' => $this->settings['font_subsetting'],
		) ) );

		// instantiate and use the dompdf class
		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html, $options, $this->document );
		$dompdf->render();
		$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html, $options, $this->document );

		return $dompdf->output();
	}

	private function get_chroot_paths() {
		$chroot = array( WP_CONTENT_DIR ); // default

		if( $wp_upload_base = WPO_WCPDF()->main->get_wp_upload_base() ) {
			$chroot[] = $wp_upload_base;
		}
		if( $tmp_base = WPO_WCPDF()->main->get_tmp_base() ) {
			$chroot[] = $tmp_base;
		}

		return apply_filters( 'wpo_wcpdf_dompdf_chroot', $chroot );
	}
}

endif; // class_exists
