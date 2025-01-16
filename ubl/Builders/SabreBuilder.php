<?php

namespace WPO\IPS\UBL\Builders;

use WPO\IPS\Vendor\Sabre\Xml\Service;
use WPO\IPS\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SabreBuilder extends Builder {

	/** @var Service */
	private $service;

	public function __construct() {
		$this->service = new Service();
	}

	public function build( Document $document ) {
		// Flip namespaces so Sabre sees prefix => URI
		$this->service->namespaceMap = array_flip( $document->get_namespaces() );

		$rootElement        = $document->get_root_element();
		$additionalElements = $document->get_additional_root_elements();

		// If there are no elements
		if ( empty( $additionalElements ) ) {
			return $this->service->write( $rootElement, $document->get_data() );

		// If there are elements
		} else {
			// We map that root element to our custom class so Sabre knows how to serialize it
			$this->service->elementMap = array(
				$rootElement => SabreSerializer::class,
			);

			return $this->service->write(
				$rootElement,
				new SabreSerializer( $document )
			);
		}
	}

}
