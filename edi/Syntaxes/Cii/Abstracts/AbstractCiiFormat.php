<?php

declare(strict_types=1);

namespace WPO\IPS\EDI\Syntaxes\Cii\Abstracts;

use WPO\IPS\EDI\Interfaces\FormatInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractCiiFormat implements FormatInterface {

	public string $syntax = 'cii';
	
	/**
     * Get the format context
     */
    abstract public function get_context(): string;
	
	/**
     * Get the format type code
     */
    abstract public function get_type_code(): string;

	/**
     * Get the format root element
     */
    abstract public function get_root_element(): string;
	
	/**
     * Get the format additional attributes
     */
    abstract public function get_additional_attributes(): array;

	/**
     * Get the format namespaces
     */
    abstract public function get_namespaces(): array;
	
	/**
     * Get the format structure
     */
    abstract public function get_structure(): array;

}
