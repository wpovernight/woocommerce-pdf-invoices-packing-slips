<?php

namespace WPO\IPS\EDI\Syntax\Cii;

use WPO\IPS\EDI\Abstracts\AbstractDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CiiDocument extends AbstractDocument {
	
	public string $syntax = 'cii';

	/**
	 * Get the root element
	 *
	 * @return string
	 */
	public function get_root_element(): string {
		return apply_filters( 'wpo_ips_edi_cii_document_root_element', 'rsm:CrossIndustryInvoice', $this );
	}
	
	/**
	 * Get additional root elements
	 *
	 * @return array
	 */
	public function get_additional_root_elements(): array {
		return apply_filters( 'wpo_ips_edi_cii_document_additional_root_elements', array(), $this );
	}
	
	/**
	 * Get the namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array {
		return apply_filters( 'wpo_ips_edi_cii_document_namespaces', array(
			'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
			'qdt' => 'urn:un:unece:uncefact:data:standard:QualifiedDataType:100',
			'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
			'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
		), $this );
	}

}
