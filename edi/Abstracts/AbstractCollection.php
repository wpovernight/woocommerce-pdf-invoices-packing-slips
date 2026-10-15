<?php

declare(strict_types=1);

namespace WPO\IPS\EDI\Abstracts;

use Iterator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractCollection implements Iterator {

	private array $items  = array();
	private int $position;

	/**
	 * Constructor to initialize the collection.
	 */
	public function __construct() {
		$this->position = 0;
	}

	/**
     * Rewind the iterator to the first element.
     */
    public function rewind(): void {
		$this->position = 0;
	}

	/**
     * Current element in the collection.
     */
    public function current(): mixed {
		return $this->items[ $this->position ];
	}

	/**
     * Get the current key
     */
    public function key(): int {
		return $this->position;
	}

	/**
     * Next element in the collection.
     */
    public function next(): void {
		++$this->position;
	}

	/**
     * Check if the current position is valid.
     */
    public function valid(): bool {
		return isset( $this->items[ $this->position ] );
	}

}
