<?php
namespace WPO\WC\PDF_Invoices\Compatibility;

use Dompdf\FontMetrics;
use Dompdf\Options;
use Dompdf\Canvas;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Compatibility\\Dompdf_FontMetrics' ) ) :

	class Dompdf_FontMetrics extends FontMetrics {

		public function __construct( Canvas $canvas, Options $options ) {
			parent::__construct( $canvas, $options );
			
			$this->fontLookup += $this->wcpdf_fonts( $options );

			if( ! file_exists( $this->getCacheFile() ) ) {
				$this->saveFontFamilies();
			}
		}

		public function wcpdf_fonts( Options $options ) {
			$fontDir = $options->getFontDir();
			return array (
				'sans-serif' => array(
					'normal' => $fontDir . '/Helvetica',
					'bold' => $fontDir . '/Helvetica-Bold',
					'italic' => $fontDir . '/Helvetica-Oblique',
					'bold_italic' => $fontDir . '/Helvetica-BoldOblique',
				),
				'times' => array(
					'normal' => $fontDir . '/Times-Roman',
					'bold' => $fontDir . '/Times-Bold',
					'italic' => $fontDir . '/Times-Italic',
					'bold_italic' => $fontDir . '/Times-BoldItalic',
				),
				'times-roman' => array(
					'normal' => $fontDir . '/Times-Roman',
					'bold' => $fontDir . '/Times-Bold',
					'italic' => $fontDir . '/Times-Italic',
					'bold_italic' => $fontDir . '/Times-BoldItalic',
				),
				'courier' => array(
					'normal' => $fontDir . '/Courier',
					'bold' => $fontDir . '/Courier-Bold',
					'italic' => $fontDir . '/Courier-Oblique',
					'bold_italic' => $fontDir . '/Courier-BoldOblique',
				),
				'helvetica' => array(
					'normal' => $fontDir . '/Helvetica',
					'bold' => $fontDir . '/Helvetica-Bold',
					'italic' => $fontDir . '/Helvetica-Oblique',
					'bold_italic' => $fontDir . '/Helvetica-BoldOblique',
				),
				'zapfdingbats' => array(
					'normal' => $fontDir . '/ZapfDingbats',
					'bold' => $fontDir . '/ZapfDingbats',
					'italic' => $fontDir . '/ZapfDingbats',
					'bold_italic' => $fontDir . '/ZapfDingbats',
				),
				'symbol' => array(
					'normal' => $fontDir . '/Symbol',
					'bold' => $fontDir . '/Symbol',
					'italic' => $fontDir . '/Symbol',
					'bold_italic' => $fontDir . '/Symbol',
				),
				'serif' => array(
					'normal' => $fontDir . '/Times-Roman',
					'bold' => $fontDir . '/Times-Bold',
					'italic' => $fontDir . '/Times-Italic',
					'bold_italic' => $fontDir . '/Times-BoldItalic',
				),
				'monospace' => array(
					'normal' => $fontDir . '/Courier',
					'bold' => $fontDir . '/Courier-Bold',
					'italic' => $fontDir . '/Courier-Oblique',
					'bold_italic' => $fontDir . '/Courier-BoldOblique',
				),
				'fixed' => array(
					'normal' => $fontDir . '/Courier',
					'bold' => $fontDir . '/Courier-Bold',
					'italic' => $fontDir . '/Courier-Oblique',
					'bold_italic' => $fontDir . '/Courier-BoldOblique',
				),
				'dejavu sans' => array(
					'bold' => $fontDir . '/DejaVuSans-Bold',
					'bold_italic' => $fontDir . '/DejaVuSans-BoldOblique',
					'italic' => $fontDir . '/DejaVuSans-Oblique',
					'normal' => $fontDir . '/DejaVuSans',
				),
				'dejavu sans mono' => array(
					'bold' => $fontDir . '/DejaVuSansMono-Bold',
					'bold_italic' => $fontDir . '/DejaVuSansMono-BoldOblique',
					'italic' => $fontDir . '/DejaVuSansMono-Oblique',
					'normal' => $fontDir . '/DejaVuSansMono',
				),
				'dejavu serif' => array(
					'bold' => $fontDir . '/DejaVuSerif-Bold',
					'bold_italic' => $fontDir . '/DejaVuSerif-BoldItalic',
					'italic' => $fontDir . '/DejaVuSerif-Italic',
					'normal' => $fontDir . '/DejaVuSerif',
				),
				'open sans' => array(
					'normal' => $fontDir . '/OpenSans-Normal',
					'bold' => $fontDir . '/OpenSans-Bold',
					'italic' => $fontDir . '/OpenSans-Italic',
					'bold_italic' => $fontDir . '/OpenSans-BoldItalic',
				),
				'segoe' => array(
					'normal' => $fontDir . '/Segoe-Normal',
					'bold' => $fontDir . '/Segoe-Bold',
					'italic' => $fontDir . '/Segoe-Italic',
					'bold_italic' => $fontDir . '/Segoe-BoldItalic',
				),
				'roboto slab' => array(
					'normal' => $fontDir . '/RobotoSlab-Normal',
					'bold' => $fontDir . '/RobotoSlab-Bold',
					'italic' => $fontDir . '/RobotoSlab-Italic',
					'bold_italic' => $fontDir . '/RobotoSlab-BoldItalic',
				),
				'currencies' => array(
					'normal' => $fontDir . '/currencies',
					'bold' => $fontDir . '/currencies',
					'italic' => $fontDir . '/currencies',
					'bold_italic' => $fontDir . '/currencies',
				),
			);
		}
		
	}

endif; // Class exists check

