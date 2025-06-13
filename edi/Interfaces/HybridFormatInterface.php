<?php

namespace WPO\IPS\EDI\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface HybridFormatInterface {
	
	/**
	 * Generate RDF metadata string for embedding in PDF/A-3.
	 *
	 * @param string $filename
	 * @param string $document_type
	 * @param string $profile
	 * @param string $version
	 * @param string $namespace_prefix
	 * @param string $namespace_uri
	 * @return string
	 */
	public function get_rdf_metadata(
		string $filename,
		string $document_type = 'INVOICE',
		string $profile = 'EN16931',
		string $version = '1.0',
		string $namespace_prefix = 'fx',
		string $namespace_uri = 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#'
	): string;

}
