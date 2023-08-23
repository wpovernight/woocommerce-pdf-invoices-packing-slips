<?php

namespace WPO\WC\UBL\Repositories\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface OrderRepository {
	
	public function getById( $id );
	
}
