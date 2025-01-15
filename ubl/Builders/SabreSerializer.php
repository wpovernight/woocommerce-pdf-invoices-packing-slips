<?php

namespace WPO\IPS\UBL\Builders;

use WPO\IPS\UBL\Documents\Document;
use WPO\IPS\Vendor\Sabre\Xml\XmlSerializable;
use WPO\IPS\Vendor\Sabre\Xml\Writer;

class SabreSerializer implements XmlSerializable {

	/** @var Document */
	private $document;

	public function __construct( Document $document ) {
		$this->document = $document;
	}

	public function xmlSerialize( Writer $writer ): void {
		$additionalElements = $this->document->get_additional_root_elements();
		
		if ( ! empty( $additionalElements ) && is_array( $additionalElements ) ) {
			$writer->writeAttributes( $additionalElements );
		}
		
		$writer->write( $this->document->get_data() );
	}
}