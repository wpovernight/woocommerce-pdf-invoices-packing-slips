<?php

namespace WPO\WC\UBL\Handlers;

use WPO\WC\UBL\Documents\Document;
use WPO\WC\UBL\Models\Order;

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