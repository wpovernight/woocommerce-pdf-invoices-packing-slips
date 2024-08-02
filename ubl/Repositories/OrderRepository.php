<?php

namespace WPO\IPS\UBL\Repositories;

use WPO\IPS\UBL\Transformers\OrderTransformer;
use WPO\IPS\UBL\Repositories\Contracts\OrderRepository as OrderRepositoryContract;

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
