<?php

namespace WPO\IPS\EInvoice\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractFormat {
	
	public string $slug;
	public string $name;
		
	/**
	 * Get the format structure for the given document.
	 *
	 * @param string $document_slug
	 * @return array
	 */
	public function get_structure( string $document_slug ): array {
		$normalized_slug = strtolower( preg_replace( '/[^a-z0-9]+/', '_', $document_slug ) );
		$method          = 'get_' . $normalized_slug . '_structure';

		if ( method_exists( $this, $method ) ) {
			$structure = $this->$method();
		} else {
			$structure = array();
		}

		return apply_filters(
			'wpo_ips_einvoice_format_structure',
			$structure,
			$document_slug,
			$this
		);
	}

}
