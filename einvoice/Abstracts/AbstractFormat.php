<?php

namespace WPO\IPS\EInvoice\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractFormat {
	
	public string $slug;
	public string $name;
	protected static ?AbstractFormat $_instance = null;
	
	/**
	 * Get the instance of the class
	 *
	 * @return static
	 */
	public static function instance() {
		if ( is_null( static::$_instance ) ) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
	
	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	abstract public function get_structure(): array;

}
