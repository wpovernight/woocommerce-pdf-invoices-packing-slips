<?php

declare(strict_types=1);

namespace WPO\IPS\EDI\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface FormatInterface {
	
	/**
     * Get the format type code
     */
    public function get_type_code(): string;
	
	/**
     * Get the format root element
     */
    public function get_root_element(): string;
	
	/**
     * Get the format additional attributes
     */
    public function get_additional_attributes(): array;

	/**
     * Get the format namespaces
     */
    public function get_namespaces(): array;

	/**
     * Get the format structure
     */
    public function get_structure(): array;

}
