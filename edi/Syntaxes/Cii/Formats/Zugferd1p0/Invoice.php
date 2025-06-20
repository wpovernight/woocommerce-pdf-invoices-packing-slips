<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\Zugferd1p0;

use WPO\IPS\EDI\Interfaces\HybridFormatInterface;
use WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Invoice as CiiD16BInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends CiiD16BInvoice implements HybridFormatInterface {

	public string $slug = 'zugferd-1p0';
	public string $name = 'ZUGFeRD 1.0';

	/**
	 * Get RDF metadata for embedding XML in PDF/A-3.
	 *
	 * @return string RDF metadata string.
	 */
	public function get_rdf_metadata(): string {
		$rdf  = sprintf( '<rdf:Description rdf:about="" xmlns:%s="%s">', $this->get_prefix(), $this->get_namespace() ) . "\n";
		$rdf .= sprintf( '  <%s:DocumentType>%s</%s:DocumentType>', $this->get_prefix(), strtoupper( $this->get_document_type() ), $this->get_prefix() ) . "\n";
		$rdf .= sprintf( '  <%s:DocumentFileName>%s</%s:DocumentFileName>', $this->get_prefix(), $this->get_document_filename(), $this->get_prefix() ) . "\n";
		$rdf .= sprintf( '  <%s:Version>%s</%s:Version>', $this->get_prefix(), $this->get_version(), $this->get_prefix() ) . "\n";
		$rdf .= sprintf( '  <%s:ConformanceLevel>%s</%s:ConformanceLevel>', $this->get_prefix(), strtoupper( $this->get_conformance_level() ), $this->get_prefix() ) . "\n";
		$rdf .= '</rdf:Description>' . "\n";

		return $rdf;
	}
	
	/**
	 * Get the filename for this format.
	 *
	 * @return string The filename.
	 */
	public function get_document_filename(): string {
		return 'ZUGFeRD_Invoice.xml';
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
	 * - Can be: BASIC, BASIC WL, COMFORT, EXTENDED
	 *
	 * @return string The conformance level.
	 */
	public function get_conformance_level(): string {
		return 'BASIC'; // EN 16931 not available
	}
	
	/**
	 * Get the version of this format.
	 *
	 * @return string The version of the format.
	 */
	public function get_version(): string {
		return '1p0'; // not 1.0
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
		return 'urn:ferd:pdfa:CrossIndustryDocument:invoice:1p0#';
	}
	
}
