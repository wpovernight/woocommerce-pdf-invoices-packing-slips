<?php
namespace WPO\WC\PDF_Invoices;

use Dompdf\Dompdf;
use Dompdf\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' ) ) :

class PDF_Maker {
	public $html;
	public $settings;

	public function __construct( $html, $settings = array() ) {
		$this->html = $html;

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
		
		require WPO_WCPDF()->plugin_path() . '/vendor/autoload.php';

		// set options
		$options = new Options();
		$options->setdefaultFont( 'dejavu sans');
		$options->setTempDir( WPO_WCPDF()->main->get_tmp_path('dompdf') );
		$options->setLogOutputFile( WPO_WCPDF()->main->get_tmp_path('dompdf') . "/log.htm");
		$options->setFontDir( WPO_WCPDF()->main->get_tmp_path('fonts') );
		$options->setFontCache( WPO_WCPDF()->main->get_tmp_path('fonts') );
		$options->setIsRemoteEnabled( true );
		$options->setIsFontSubsettingEnabled( $this->settings['font_subsetting'] );

		// instantiate and use the dompdf class
		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $this->html );
		$dompdf->setPaper( $this->settings['paper_size'], $this->settings['paper_orientation'] );
		$dompdf = apply_filters( 'wpo_wcpdf_before_dompdf_render', $dompdf, $this->html );
		$dompdf->render();
		$dompdf = apply_filters( 'wpo_wcpdf_after_dompdf_render', $dompdf, $this->html );

		return $dompdf->output();
	}
}

endif; // class_exists
