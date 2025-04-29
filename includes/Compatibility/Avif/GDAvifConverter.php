<?php
/**
 * GD AVIF converter class.
 *
 * @since 4.2
 */

namespace WPO\IPS\Compatibility\Avif;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\Avif\\GDAvifConverter' ) ) :

class GDAvifConverter extends AbstractAvifConverter {
	
	/**
	 * Convert AVIF image to JPEG format using GD library
	 *
	 * @param string $image_html The HTML image element
	 * @return string The HTML image element with converted image
	 */
	public function convert( string $avif_src, string $jpg_src ): void {
        $image = imagecreatefromavif( $avif_src );
        if ( $image ) {
            imagejpeg( $image, $jpg_src, 90 );
            imagedestroy( $image );
        }
	}
	
}

endif; // class_exists
