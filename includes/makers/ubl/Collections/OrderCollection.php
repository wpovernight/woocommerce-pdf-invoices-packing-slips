<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Collections;

defined( 'ABSPATH' ) or exit;

class OrderCollection extends Collection
{
	public function addOrder( Order $order )
	{
		$this->items[] = $order;
	}
}