<?php

namespace WPO\IPS\UBL\Builders;

use WPO\IPS\Vendor\Sabre\Xml\Service;
use WPO\IPS\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SabreBuilder extends Builder {

	/** Service */
	private $service;

	public function __construct() {
		$this->service = new Service();
	}

	public function build( Document $document ) {
		// Sabre wants namespaces in value/key format, so we need to flip it
		$namespaces                  = array_flip( $document->get_namespaces() );
		$this->service->namespaceMap = $namespaces;

		return $this->service->write( $document->get_root_element(), $document->get_data() );
	}

}
