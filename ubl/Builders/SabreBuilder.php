<?php

namespace WPO\IPS\UBL\Builders;

use WPO\IPS\Vendor\Sabre\Xml\Service;
use WPO\IPS\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SabreBuilder extends Builder {

	private $service;

	public function __construct() {
		$this->service = new Service();
	}

	public function build( Document $document ) {
		// Sabre wants namespaces in value => key format, so we need to flip it
		$this->service->namespaceMap = array_flip( $document->get_namespaces() );

		return $this->service->write(
			$document->get_root_element(),
			empty( $document->get_additional_root_elements() ) ? $document->get_data() : new SabreSerializer( $document )
		);
	}

}
