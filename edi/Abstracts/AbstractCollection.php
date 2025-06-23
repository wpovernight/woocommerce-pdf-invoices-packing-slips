<?php

namespace WPO\IPS\EDI\Abstracts;

use Iterator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractCollection implements Iterator {

	private array $items  = array();
	private int $position = 0;

	/**
	 * Constructor to initialize the collection.
	 */
	public function __construct() {
		$this->position = 0;
	}

	/**
	 * Rewind the iterator to the first element.
	 *
	 * @return void
	 */
	public function rewind(): void {
		$this->position = 0;
	}

	/**
	 * Current element in the collection.
	 * 
	 * @return mixed
	 */
	public function current() {
		return $this->items[ $this->position ];
	}

	/**
	 * Get the current key
	 *
	 * @return int
	 */
	public function key(): int {
		return $this->position;
	}

	/**
	 * Next element in the collection.
	 * 
	 * @return void
	 */
	public function next(): void {
		++$this->position;
	}

	/**
	 * Check if the current position is valid.
	 *
	 * @return bool
	 */
	public function valid(): bool {
		return isset( $this->items[ $this->position ] );
	}

}
