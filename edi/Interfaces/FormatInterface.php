<?php

namespace WPO\IPS\EDI\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface FormatInterface {
	
	/**
	 * Get the format type code
	 *
	 * @return string
	 */
	public function get_type_code(): string;
	
	/**
	 * Get the format root element
	 *
	 * @return string
	 */
	public function get_root_element(): string;
	
	/**
	 * Get the format additional attributes
	 *
	 * @return array
	 */
	public function get_additional_attributes(): array;

	/**
	 * Get the format namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array;

	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	public function get_structure(): array;

}
