<?php

namespace WPO\IPS\EInvoice\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractFormat {
	
	public string $slug;
	public string $name;
		
	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	abstract public function get_structure(): array;

}
