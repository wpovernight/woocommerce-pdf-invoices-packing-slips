<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Repositories\Contracts;

defined( 'ABSPATH' ) or exit;

interface OrderRepository
{
	public function getById( $id );
}
