<?php

namespace WPO\WC\UBL\Repositories;

use WPO\WC\UBL\Transformers\OrderTransformer;
use WPO\WC\UBL\Repositories\Contracts\OrderRepository as OrderRepositoryContract;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class OrderRepository extends Repository implements OrderRepositoryContract {
	
	/** @var OrderTransformer */
	private $transformer;

	public function __construct( OrderTransformer $transformer ) {
		$this->transformer = $transformer;
	}

	public function get_by_id( $id ) {
		/** @var \WC_Abstract_Order */
		$order = wc_get_order( $id );
		return $this->transformer->transform( $order );
	}
	
}