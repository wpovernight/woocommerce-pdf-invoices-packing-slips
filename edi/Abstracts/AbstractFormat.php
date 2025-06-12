<?php

namespace WPO\IPS\EDI\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractFormat {

	public string $type;
	public string $slug;
	public string $name;
	public string $syntax;

	/**
	 * Get the format root element
	 *
	 * @return string
	 */
	abstract public function get_root_element(): string;
	
	/**
	 * Get the format additional attributes
	 *
	 * @return array
	 */
	abstract public function get_additional_attributes(): array;

	/**
	 * Get the format namespaces
	 *
	 * @return array
	 */
	abstract public function get_namespaces(): array;

	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	abstract public function get_structure(): array;

}
