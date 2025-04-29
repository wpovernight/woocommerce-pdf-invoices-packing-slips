<?php
namespace WPO\IPS\Compatibility\Avif;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\Avif\\AvifCompatibility' ) ) :

class AvifCompatibility {

	protected static ?self $_instance = null;
	protected AbstractAvifConverter $avif_converter;

	/**
	 * Singleton instance.
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize the appropriate AVIF converter
		if ( version_compare( PHP_VERSION, '8.1.0', '>=' ) && extension_loaded( 'gd' ) ) {
			$this->avif_converter = new GDAvifConverter();
		}

		$this->add_hooks();
	}

	public function add_hooks() {
		if ( ! isset( $this->avif_converter ) ) {
			return;
		}
		
		add_filter('wpo_wcpdf_header_logo_img_element', array( $this, 'custom_wcpdf_header_logo_img_element' ), 10, 3);
	}

    /**
     * Function to replace AVIF images with JPG images
     *
     * @param string $img_element The HTML image element
     * @param int $attachment_id The attachment ID
     * @param OrderDocument $document The document object
     * 
     * @return string The HTML image element
     */

    public function custom_wcpdf_header_logo_img_element( string $img_element, int $attachment_id, \WPO\IPS\Documents\OrderDocument $document ) {
        return $this->avif_converter->maybe_convert( $img_element );
    }
}

endif; // class_exists
