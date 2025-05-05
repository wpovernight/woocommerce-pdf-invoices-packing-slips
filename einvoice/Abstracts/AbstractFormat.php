<?php

namespace WPO\IPS\EInvoice\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractFormat {
	
	protected static string $slug;
	protected static string $name;
	
	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	abstract protected static function get_structure(): array;

}
