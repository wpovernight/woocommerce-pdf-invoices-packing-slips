<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\Zugferd2p0;

use WPO\IPS\EDI\Interfaces\HybridFormatInterface;
use WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Invoice as CiiD16BInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends CiiD16BInvoice implements HybridFormatInterface {

	public string $slug = 'zugferd-invoice-2p0';
	public string $name = 'ZUGFeRD Invoice 2.0';

	/**
	 * Get RDF metadata for embedding XML in PDF/A-3.
	 *
	 * Schema: https://www.pdflib.com/fileadmin/pdf-knowledge-base/zugferd/ZUGFeRD2_extension_schema.xmp
	 *
	 * @return string RDF metadata.
	 */
	public function get_rdf_metadata(): string {
		// ZUGFeRD actual description
		$rdf  = sprintf( '<rdf:Description rdf:about="" xmlns:%s="%s">', $this->get_prefix(), $this->get_namespace() ) . "\n";
		$rdf .= sprintf( '  <%s:DocumentType>%s</%s:DocumentType>', $this->get_prefix(), strtoupper( $this->get_document_type() ), $this->get_prefix() ) . "\n";
		$rdf .= sprintf( '  <%s:DocumentFileName>%s</%s:DocumentFileName>', $this->get_prefix(), $this->get_document_filename(), $this->get_prefix() ) . "\n";
		$rdf .= sprintf( '  <%s:Version>%s</%s:Version>', $this->get_prefix(), $this->get_version(), $this->get_prefix() ) . "\n";
		$rdf .= sprintf( '  <%s:ConformanceLevel>%s</%s:ConformanceLevel>', $this->get_prefix(), strtoupper( $this->get_conformance_level() ), $this->get_prefix() ) . "\n";
		$rdf .= '</rdf:Description>' . "\n\n";

		// PDF/A Extension schema
		$rdf .= '<rdf:Description rdf:about=""' . "\n";
		$rdf .= '      xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/"' . "\n";
		$rdf .= '      xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#"' . "\n";
		$rdf .= '      xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">' . "\n";
		$rdf .= '  <pdfaExtension:schemas>' . "\n";
		$rdf .= '    <rdf:Bag>' . "\n";
		$rdf .= '      <rdf:li rdf:parseType="Resource">' . "\n";
		$rdf .= '        <pdfaSchema:schema>ZUGFeRD PDFA Extension Schema</pdfaSchema:schema>' . "\n";
		$rdf .= '        <pdfaSchema:namespaceURI>' . $this->get_namespace() . '</pdfaSchema:namespaceURI>' . "\n";
		$rdf .= '        <pdfaSchema:prefix>' . $this->get_prefix() . '</pdfaSchema:prefix>' . "\n";
		$rdf .= '        <pdfaSchema:property>' . "\n";
		$rdf .= '          <rdf:Seq>' . "\n";
		$rdf .= '            <rdf:li rdf:parseType="Resource">' . "\n";
		$rdf .= '              <pdfaProperty:name>DocumentFileName</pdfaProperty:name>' . "\n";
		$rdf .= '              <pdfaProperty:valueType>Text</pdfaProperty:valueType>' . "\n";
		$rdf .= '              <pdfaProperty:category>external</pdfaProperty:category>' . "\n";
		$rdf .= '              <pdfaProperty:description>name of the embedded XML invoice file</pdfaProperty:description>' . "\n";
		$rdf .= '            </rdf:li>' . "\n";
		$rdf .= '            <rdf:li rdf:parseType="Resource">' . "\n";
		$rdf .= '              <pdfaProperty:name>DocumentType</pdfaProperty:name>' . "\n";
		$rdf .= '              <pdfaProperty:valueType>Text</pdfaProperty:valueType>' . "\n";
		$rdf .= '              <pdfaProperty:category>external</pdfaProperty:category>' . "\n";
		$rdf .= '              <pdfaProperty:description>INVOICE</pdfaProperty:description>' . "\n";
		$rdf .= '            </rdf:li>' . "\n";
		$rdf .= '            <rdf:li rdf:parseType="Resource">' . "\n";
		$rdf .= '              <pdfaProperty:name>Version</pdfaProperty:name>' . "\n";
		$rdf .= '              <pdfaProperty:valueType>Text</pdfaProperty:valueType>' . "\n";
		$rdf .= '              <pdfaProperty:category>external</pdfaProperty:category>' . "\n";
		$rdf .= '              <pdfaProperty:description>The actual version of the ZUGFeRD XML schema</pdfaProperty:description>' . "\n";
		$rdf .= '            </rdf:li>' . "\n";
		$rdf .= '            <rdf:li rdf:parseType="Resource">' . "\n";
		$rdf .= '              <pdfaProperty:name>ConformanceLevel</pdfaProperty:name>' . "\n";
		$rdf .= '              <pdfaProperty:valueType>Text</pdfaProperty:valueType>' . "\n";
		$rdf .= '              <pdfaProperty:category>external</pdfaProperty:category>' . "\n";
		$rdf .= '              <pdfaProperty:description>The conformance level of the embedded ZUGFeRD data</pdfaProperty:description>' . "\n";
		$rdf .= '            </rdf:li>' . "\n";
		$rdf .= '          </rdf:Seq>' . "\n";
		$rdf .= '        </pdfaSchema:property>' . "\n";
		$rdf .= '      </rdf:li>' . "\n";
		$rdf .= '    </rdf:Bag>' . "\n";
		$rdf .= '  </pdfaExtension:schemas>' . "\n";
		$rdf .= '</rdf:Description>' . "\n";

		return $rdf;
	}
	
	/**
	 * Get the filename for this format.
	 *
	 * @return string The filename.
	 */
	public function get_document_filename(): string {
		return 'zugferd-invoice.xml';
	}
	
	/**
	 * Get the slug for this format.
	 *
	 * @return string The slug.
	 */
	public function get_document_type(): string {
		return strtoupper( $this->type );
	}
	
	/**
	 * Get the conformance level for this format.
	 * 
	 * - Can be: MINIMUM, BASIC WL, EN 16931
	 *
	 * @return string The conformance level.
	 */
	public function get_conformance_level(): string {
		return 'EN 16931';
	}
	
	/**
	 * Get the version of this format.
	 *
	 * @return string The version of the format.
	 */
	public function get_version(): string {
		return '2p0'; // not 2.0
	}
	
	/**
	 * Get the prefix for this format.
	 *
	 * @return string The prefix.
	 */
	public function get_prefix(): string {
		return 'zf';
	}
	
	/**
	 * Get the namespace for this format.
	 *
	 * @return string The namespace.
	 */
	public function get_namespace(): string {
		return 'urn:zugferd:pdfa:CrossIndustryDocument:invoice:2p0#';
	}
	
}
