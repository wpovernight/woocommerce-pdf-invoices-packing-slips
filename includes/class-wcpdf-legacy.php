<?php
namespace WPO\WC\PDF_Invoices;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Legacy' ) ) :

class Legacy {
	
	public static $version;
	public $functions;
	public $deprecated_hooks;

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
		self::$version          = WPO_WCPDF()->version;
		$this->functions        = \WPO\WC\PDF_Invoices\Legacy\Legacy_Functions::instance();
		$this->deprecated_hooks = \WPO\WC\PDF_Invoices\Legacy\Deprecated_Hooks::instance();
	}

	/**
	 * Redirect function calls directly to legacy functions class
	 */
	public function __call( $name, $arguments ) {
		$human_readable_call = '$wpo_wcpdf->'.$name.'()';
		$this->auto_enable_check( $human_readable_call );

		if ( is_callable( array( WPO_WCPDF(), $name ) ) ) {
			wcpdf_deprecated_function( $human_readable_call, '2.0', 'WPO_WCPDF()->'.$name.'()' );
			return call_user_func_array( array( WPO_WCPDF(), $name ), $arguments );
			
		} elseif ( is_callable( array( $this->functions, $name ) ) ) {
			wcpdf_deprecated_function( $human_readable_call, '2.0', '$this->'.$name.'()' );
			return call_user_func_array( array( $this->functions, $name ), $arguments );
			
		} else {
			throw new \Exception( "Call to undefined method ".__CLASS__."::{$name}()", 1 );
		}
	}

	/**
	 * Fired when a call is made to the legacy class (also used by sub classes).
	 * Reloading the page should then work in legacy mode
	 */
	public function auto_enable_check( $call = '', $die = true ) {
		add_action( 'wp_die_ajax_handler', function() {
			return '_default_wp_die_handler';
		} );
		
		$title   = __( 'Error', 'woocommerce-pdf-invoices-packing-slips' );
		$message = __( 'An outdated template or action hook was used to generate the PDF. Legacy mode has been activated, please try again by reloading this page.', 'woocommerce-pdf-invoices-packing-slips' );
		
		if ( ! empty( $call ) ) {
			$message = sprintf( '%s</p><p>%s: <b>%s</b>', $message, __( 'The following function was called', 'woocommerce-pdf-invoices-packing-slips' ), $call );
		}
		
		wp_die( "<h1>{$title}</h1><p>{$message}</p>", $title );
	}
}

endif; // Class exists check
