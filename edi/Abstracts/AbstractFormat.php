<?php

namespace WPO\IPS\EDI\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractFormat {

	public string $slug;
	public string $name;

	/**
	 * Dynamic method handler for document-specific getters.
	 * Example: get_invoice_structure(), get_credit_note_root_element()
	 *
	 * @param string $name
	 * @param array  $arguments
	 * @return mixed|null
	 */
	public function __call( string $name, array $arguments ) {
		if ( preg_match( '/^get_([a-z0-9_]+)_([a-z0-9_]+)$/', strtolower( $name ), $matches ) ) {
			return $this->get_method( $matches[1], $matches[2] );
		}
		
		if ( function_exists( 'doing_it_wrong' ) ) {
			doing_it_wrong(
				__METHOD__,
				sprintf( 'Call to undefined method %s::%s()', static::class, $name ),
				WPO_WCPDF()->version
			);
		}

		return null;
	}

	/**
	 * Call a document-specific method if it exists.
	 *
	 * @param string $slug
	 * @param string $suffix
	 * @return mixed|null
	 */
	public function get_method( string $slug, string $suffix ) {
		$method = "get_{$slug}_{$suffix}";

		if ( method_exists( $this, $method ) ) {
			return $this->$method();
		}

		return null;
	}

}
