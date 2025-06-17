<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\FacturX;

use WPO\IPS\EDI\Interfaces\HybridFormatInterface;
use WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Invoice as CiiD16BInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends CiiD16BInvoice implements HybridFormatInterface {

	public string $slug = 'factur-x';
	public string $name = 'Factur-X';
	
	/**
	 * Get RDF metadata for embedding XML in PDF/A-3.
	 *
	 * @param string $filename The name of the file to be embedded.
	 * @return string RDF metadata string.
	 */
	public function get_rdf_metadata(
		string $filename,
		string $document_type = 'INVOICE',
		string $profile = 'EN16931',
		string $version = '1.0',
		string $namespace_prefix = 'fx',
		string $namespace_uri = 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#'
	): string {
		return implode(
			"\n",
			array(
				'',
				sprintf( '<rdf:Description rdf:about="" xmlns:%s="%s">', $namespace_prefix, $namespace_uri ),
				sprintf( '  <%s:DocumentType>%s</%s:DocumentType>', $namespace_prefix, strtoupper( $document_type ), $namespace_prefix ),
				sprintf( '  <%s:DocumentFileName>%s</%s:DocumentFileName>', $namespace_prefix, $filename, $namespace_prefix ),
				sprintf( '  <%s:Version>%s</%s:Version>', $namespace_prefix, $version, $namespace_prefix ),
				sprintf( '  <%s:ConformanceLevel>%s</%s:ConformanceLevel>', $namespace_prefix, strtoupper( $profile ), $namespace_prefix ),
				'</rdf:Description>'
			)
		);
	}

}
