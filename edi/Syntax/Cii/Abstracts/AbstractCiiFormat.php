<?php

namespace WPO\IPS\EDI\Syntax\Cii\Abstracts;

use WPO\IPS\EDI\Interfaces\FormatInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractCiiFormat implements FormatInterface {

	public string $syntax = 'cii';
	
	/**
	 * Get the format context
	 *
	 * @var string
	 */
	abstract public function get_context(): string;
	
	/**
	 * Get the format type code
	 *
	 * @return string
	 */
	abstract public function get_type_code(): string;

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
	
	/**
	 * Get the format RDF metadata
	 *
	 * @param string $filename The name of the file to be embedded.
	 * @param string $profile  The conformance profile, default is 'EN16931'.
	 * 
	 * @return string RDF metadata string.
	 */
	abstract public function get_rdf_metadata( string $filename, string $profile = 'EN16931' ): string;

}
