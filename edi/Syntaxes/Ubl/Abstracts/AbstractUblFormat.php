<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Abstracts;

use WPO\IPS\EDI\Interfaces\FormatInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractUblFormat implements FormatInterface {
	
	public string $syntax = 'ubl';
	
	/**
	 * Get the type code
	 *
	 * @return string
	 */
	abstract public function get_type_code(): string;
	
	/**
	 * Get the quantity role
	 *
	 * @return string
	 */
	abstract public function get_quantity_role(): string;

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
