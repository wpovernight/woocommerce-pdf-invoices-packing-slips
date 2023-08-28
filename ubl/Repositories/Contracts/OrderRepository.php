<?php

namespace WPO\WC\UBL\Repositories\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface OrderRepository {
	
	public function get_by_id( $id );
	
}
