<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Handlers;

use WPO\WC\PDF_Invoices\Makers\UBL\Documents\Document;
use WPO\WC\PDF_Invoices\Makers\UBL\Models\Order;

defined( 'ABSPATH' ) or exit;

abstract class Handler
{
	/** @var Document */
	public $document;

	public function __construct( Document $document )
	{
		$this->document = $document;
	}

	abstract public function handle( $data, $options = [] );
}