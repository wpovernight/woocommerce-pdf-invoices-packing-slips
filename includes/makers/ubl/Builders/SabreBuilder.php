<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Builders;

use Sabre\Xml\Service;
use WPO\WC\PDF_Invoices\Makers\UBL\Documents\Document;

defined( 'ABSPATH' ) or exit;

class SabreBuilder extends Builder
{
	/** Service */
	private $service;

	public function __construct()
	{
		$this->service = new Service();
	}

	public function build( Document $document )
	{
		// Sabre wants namespaces in value/key format, so we need to flip it
		$namespaces = array_flip($document->getNamespaces());
		$this->service->namespaceMap = $namespaces;

		return $this->service->write('Invoice', $document->getData());
	}
}