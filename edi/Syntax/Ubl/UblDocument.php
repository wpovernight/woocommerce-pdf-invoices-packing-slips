<?php

namespace WPO\IPS\EDI\Syntax\Ubl;

use WPO\IPS\EDI\Abstracts\AbstractDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblDocument extends AbstractDocument {
	
	public string $syntax = 'ubl';

	/**
	 * Get the root element
	 *
	 * @return string
	 */
	public function get_root_element(): string {
		return apply_filters( 'wpo_ips_edi_root_element', 'Invoice', $this );
	}
	
	/**
	 * Get additional root elements
	 *
	 * @return array
	 */
	public function get_additional_root_elements(): array {
		return apply_filters( 'wpo_ips_edi_additional_root_elements', array(), $this );
	}
	
	/**
	 * Get the namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array {
		return apply_filters( 'wpo_ips_edi_namespaces', array(
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			''    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		), $this );
	}

}
