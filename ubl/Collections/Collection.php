<?php

namespace WPO\IPS\UBL\Collections;

use Iterator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Collection implements Iterator {

	/** @var array */
	private $items = array();

	/** @var int */
	private $position = 0;

	public function __construct() {
		$this->position = 0;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->items[ $this->position ];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset( $this->items[ $this->position ] );
	}

}
