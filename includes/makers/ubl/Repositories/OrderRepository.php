<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Repositories;

use WPO\WC\PDF_Invoices\Makers\UBL\Transformers\OrderTransformer;
use WPO\WC\PDF_Invoices\Makers\UBL\Repositories\Contracts\OrderRepository as OrderRepositoryContract;

defined( 'ABSPATH' ) or exit;

class OrderRepository extends Repository implements OrderRepositoryContract
{
	/** @var OrderTransformer */
	private $transformer;

	public function __construct( OrderTransformer $transformer )
	{
		$this->transformer = $transformer;
	}

	public function getById( $id )
	{
		/** @var \WC_Abstract_Order */
		$order = wc_get_order($id);
		return $this->transformer->transform($order);
	}
}