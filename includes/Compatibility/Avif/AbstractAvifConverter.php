<?php
namespace WPO\IPS\Compatibility\Avif;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\Avif\\AbstractAvifConverter' ) ) :

abstract class AbstractAvifConverter {
	
	/**
	 * Convert AVIF image to another format
	 *
	 * @param string $avif_src The AVIF image source
	 * @param string $jpg_src The JPG image source
	 * @return void
	 */
	abstract public function convert( string $avif_src, string $jpg_src ): void;
	
	/**
	 * Convert AVIF image if conversion is enabled in settings
	 *
	 * @param string $image_html The HTML image element
	 * @return string The HTML image element
	 */
	public function maybe_convert( string $image_html ): string {
		if ( $this->is_avif_conversion_enabled() ) {
            $src     = '';
            $jpg_src = '';
            
            if ( preg_match('/src=["\'](.*?)["\']/', $image_html, $matches ) ) {
                $src = $matches[1];
                
                if ( '.avif' === substr( $src, -5 ) && 'http' !== substr( $src, 0, 4 ) ) {
                    $jpg_src = substr( $src, 0, -5 ) . '.jpg';
                    if ( WPO_WCPDF()->file_system->exists( $src ) && ! WPO_WCPDF()->file_system->exists( $jpg_src ) ) {
                        $this->convert( $src, $jpg_src );
                    }
                }
            }
            
            return str_replace('.avif', '.jpg', $image_html);
		}

		return $image_html;
	}
	
	/**
	 * Check if AVIF conversion is enabled in settings
	 *
	 * @return bool True if AVIF conversion is enabled, false otherwise
	 */
	protected function is_avif_conversion_enabled(): bool {
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );
		return isset( $debug_settings['enable_avif_support'] ) && wc_string_to_bool( $debug_settings['enable_avif_support'] );
	}
	
}

endif; // class_exists
