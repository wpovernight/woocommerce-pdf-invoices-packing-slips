<?php

namespace WPO\WC\UBL\Collections;

defined( 'ABSPATH' ) or exit;

class OrderCollection extends Collection
{
	public function addOrder( Order $order )
	{
		$this->items[] = $order;
	}
}