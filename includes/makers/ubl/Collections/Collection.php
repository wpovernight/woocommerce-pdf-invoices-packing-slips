<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Collections;

defined( 'ABSPATH' ) or exit;

abstract class Collection implements Iterator
{
	/** @var array */
	private $items = [];

	/** @var int */
	private $position = 0;  

	public function __construct()
	{
		$this->position = 0;
	}

	public function rewind()
	{
		$this->position = 0;
	}

	public function current()
	{
		return $this->items[$this->position];
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
	}

	public function valid()
	{
		return isset($this->items[$this->position]);
	}
}