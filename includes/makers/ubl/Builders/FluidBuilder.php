<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Builders;

use FluidXml\FluidXml;
use WPO\WC\PDF_Invoices\Makers\UBL\Models\Order;
use WPO\WC\PDF_Invoices\Makers\UBL\Documents\Document;
use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\IdHandler;
use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\IssueDateHandler;

defined( 'ABSPATH' ) or exit;

class FluidBuilder extends Builder
{
	/** @var FluidXml */
	public $xml;

	/** @var array */
	private $handlers = [];

	public function __construct()
	{
		// Disable this class entirely
		throw new \Exception('This class should not be called and is stored simply for future reference');

		$this->xml = new FluidXml('Invoice');
		$this->xml->namespace('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
		$this->xml->namespace('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
		$this->xml->namespace('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');

		$this->xml->attr([
			'xmlns:cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'xmlns:cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			'xmlns' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		]);
	}

	public function setup()
	{
		$idHandler = new IdHandler($this);
		$this->handlers['id'] = $idHandler;

		$issueDateHandler = new IssueDateHandler($this);
		$this->handlers['issuedate'] = $issueDateHandler;
	}

	public function build( Document $document )
	{
		$order = $document->order;
		$format = $document->getFormat();

		$this->xml->addChild('cbc:UBLVersionID', '2.0');

		header('Content-Type: application/xml');
		
		foreach ( $format as $key ) {
			if ( isset( $this->handlers[$key] ) ) {
				$this->handlers[$key]->handle( $order );
			}
		}

		echo $this->xml;
		die();
	}
}