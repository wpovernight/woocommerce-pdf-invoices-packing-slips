<?php

namespace WPO\WC\UBL\Collections;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class OrderCollection extends Collection {
	
	public function add_order( Order $order ) {
		$this->items[] = $order;
	}
	
}