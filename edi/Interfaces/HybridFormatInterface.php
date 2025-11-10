<?php

namespace WPO\IPS\EDI\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface HybridFormatInterface {
	
	/**
	 * Generate RDF metadata string for embedding in PDF/A-3.

	 * @return string
	 */
	public function get_rdf_metadata(): string;
	
	/**
	 * Get the filename for this format.
	 *
	 * @return string
	 */
	public function get_document_filename(): string;
	
	/**
	 * Get the slug for this format.
	 *
	 * @return string
	 */
	public function get_document_type(): string;
	
	/**
	 * Get the conformance level for this format.
	 *
	 * @return string
	 */
	public function get_conformance_level(): string;
	
	/**
	 * Get the version of this format.
	 *
	 * @return string
	 */
	public function get_version(): string;
	
	/**
	 * Get the prefix for this format.
	 *
	 * @return string
	 */
	public function get_prefix(): string;
	
	/**
	 * Get the namespace for this format.
	 *
	 * @return string
	 */
	public function get_namespace(): string;

}
