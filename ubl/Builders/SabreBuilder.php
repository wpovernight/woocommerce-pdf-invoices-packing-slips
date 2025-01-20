<?php

namespace WPO\IPS\UBL\Builders;

use WPO\IPS\Vendor\Sabre\Xml\Service;
use WPO\IPS\Vendor\Sabre\Xml\Writer;
use WPO\IPS\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SabreBuilder extends Builder {

	private Service $service;
	private Document $document;

	public function __construct() {
		$this->service = new Service();
	}

	public function build( Document $document ): string {
		$this->document = $document;

		// Map namespaces (Sabre requires URI => prefix)
		$this->service->namespaceMap = array_flip( $document->get_namespaces() );

		return $this->service->write(
			$document->get_root_element(),
			function ( Writer $writer ) {
				$this->xmlSerialize( $writer );
			}
		);
	}

	public function xmlSerialize( Writer $writer ): void {
		$additionalElements = $this->document->get_additional_root_elements();

		if ( ! empty( $additionalElements ) && is_array( $additionalElements ) ) {
			$writer->writeAttributes( $additionalElements );
		}

		$writer->write( $this->document->get_data() );
	}

}
