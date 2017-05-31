<?php
namespace WPO\WC\PDF_Invoices\Legacy;

defined( 'ABSPATH' ) or exit;

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Legacy\\WPO_WCPDF_Legacy' ) ) :

class WPO_WCPDF_Legacy {
	public $settings;
	public $export;
	public $functions;

	protected static $_instance = null;

	/**
	 * Main Plugin Instance
	 *
	 * Ensures only one instance of plugin is loaded or can be loaded.
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
		$this->settings  = include_once( 'class-wcpdf-legacy-settings.php' );
		$this->export    = include_once( 'class-wcpdf-legacy-export.php' );
		$this->functions = include_once( 'class-wcpdf-legacy-functions.php' );
	}

	/**
	 * Redirect function calls directly to legacy functions class
	 */
	public function __call( $name, $arguments ) {
		if ( is_callable( array( $this->functions, $name ) ) ) {
			return call_user_func_array( array( $this->functions, $name ), $arguments );
		} else {
			throw new \Exception("Call to undefined method ".__CLASS__."::{$name}()", 1);
		}
	}
}

endif; // Class exists check

function WPO_WCPDF_Legacy() {
	return WPO_WCPDF_Legacy::instance();
}

// Global for backwards compatibility.
$GLOBALS['wpo_wcpdf'] = WPO_WCPDF_Legacy();