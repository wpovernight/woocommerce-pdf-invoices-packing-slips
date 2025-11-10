<?php

namespace WPO\IPS\EDI;

use WPO\IPS\EDI\Interfaces\BuilderInterface;
use WPO\IPS\Vendor\Sabre\Xml\Service;
use WPO\IPS\Vendor\Sabre\Xml\Writer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SabreBuilder implements BuilderInterface {

	private Service $service;
	private Document $document;

	/**
	 * SabreBuilder constructor.
	 */
	public function __construct() {
		$this->service = new Service();
	}

	/**
	 * Build the EDI document using Sabre XML serializer.
	 *
	 * @param Document $document The EDI document to build.
	 * @return string The serialized XML string.
	 */
	public function build( Document $document ): string {
		$this->document = $document;

		// Map namespaces (Sabre requires URI => prefix)
		$this->service->namespaceMap = array_flip( $document->get_namespaces() );
		
		if ( ! function_exists( 'WPO\\IPS\\Vendor\\Sabre\\Xml\\Serializer\\standardSerializer' ) ) {
			require_once WPO_WCPDF()->plugin_path() . '/vendor/strauss/sabre/xml/lib/Serializer/functions.php';
		}		

		return $this->service->write(
			$document->get_root_element(),
			function ( Writer $writer ) {
				$this->xmlSerialize( $writer );
			}
		);
	}

	/**
	 * Serialize the document to XML using Sabre's Writer.
	 *
	 * @param Writer $writer The Sabre XML writer.
	 * @return void
	 */
	public function xmlSerialize( Writer $writer ): void {
		$additional_attributes = $this->document->get_additional_attributes();

		if ( ! empty( $additional_attributes ) ) {
			$writer->writeAttributes( $additional_attributes );
		}

		$writer->write( $this->document->get_data() );
	}

}
