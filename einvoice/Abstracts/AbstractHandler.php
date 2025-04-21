<?php

namespace WPO\IPS\EInvoice\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractHandler {

	/** @var AbstractDocument */
	public $document;

	public function __construct( AbstractDocument $document ) {
		$this->document = $document;
	}

	abstract public function handle( $data, $options = array() );

}
