<?php

namespace WPO\IPS\EInvoice\Syntax\Ubl;

use WPO\IPS\EInvoice\Abstracts\AbstractDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblDocument extends AbstractDocument {
	
	public string $sintax = 'ubl';

	/**
	 * Get the root element
	 *
	 * @return string
	 */
	public function get_root_element(): string {
		return apply_filters( 'wpo_ips_einvoice_root_element', 'Invoice', $this );
	}
	
	/**
	 * Get additional root elements
	 *
	 * @return array
	 */
	public function get_additional_root_elements(): array {
		return apply_filters( 'wpo_ips_einvoice_additional_root_elements', array(), $this );
	}
	
	/**
	 * Get the namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array {
		return apply_filters( 'wpo_ips_einvoice_namespaces', array(
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			''    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		), $this );
	}
	
	/**
	 * Get the available formats
	 *
	 * @return array
	 */
	public function get_available_formats(): array {
		return apply_filters( 'wpo_ips_einvoice_formats', array(
			'ubltwodotone' => \WPO\IPS\EInvoice\Syntax\Ubl\Formats\UblTwoDotOne::class,
		), $this );
	}

}
