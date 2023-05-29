<?php

namespace WPO\WC\UBL\Repositories\Contracts;

defined( 'ABSPATH' ) or exit;

interface OrderRepository
{
	public function getById( $id );
}
