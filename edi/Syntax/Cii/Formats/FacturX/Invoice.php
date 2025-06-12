<?php

namespace WPO\IPS\EDI\Syntax\Cii\Formats\FacturX;

use WPO\IPS\EDI\Syntax\Cii\Formats\CiiD16B\Invoice as CiiD16BInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends CiiD16BInvoice {
	
	public string $slug = 'factur-x';
	public string $name = 'Factur-X';
	
	/**
	 * Get RDF metadata for embedding XML in PDF/A-3.
	 *
	 * @param string $filename The name of the file to be embedded.
	 * @param string $profile  The conformance profile, default is 'EN16931'.
	 * 
	 * @return string RDF metadata string.
	 */
	public function get_rdf_metadata( string $filename, string $profile = 'EN16931' ): string {
		$document_type    = str_replace( '-', '_', $this->type );
		$version          = '1.0';
		$namespace_prefix = 'fx';
		$namespace_uri    = 'urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#';
		
		return implode(
			"\n",
			array(
				'',
				sprintf( '<rdf:Description rdf:about="" xmlns:%s="%s">', $namespace_prefix, $namespace_uri ),
				sprintf( '  <%s:DocumentType>%s</%s:DocumentType>', $namespace_prefix, strtoupper( $document_type ), $namespace_prefix ),
				sprintf( '  <%s:DocumentFileName>%s</%s:DocumentFileName>', $namespace_prefix, $filename, $namespace_prefix ),
				sprintf( '  <%s:Version>%s</%s:Version>', $namespace_prefix, $version, $namespace_prefix ),
				sprintf( '  <%s:ConformanceLevel>%s</%s:ConformanceLevel>', $namespace_prefix, strtoupper( $profile ), $namespace_prefix ),
				sprintf( '</rdf:Description>' )
			)
		);
	}

}
