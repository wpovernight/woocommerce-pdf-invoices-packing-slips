<?php

namespace WPO\IPS\UBL\Handlers;

use WPO\IPS\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Handler {

	/** @var Document */
	public $document;

	public function __construct( Document $document ) {
		$this->document = $document;
	}

	abstract public function handle( $data, $options = array() );

}
