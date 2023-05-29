<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Models;

use WPO\WC\PDF_Invoices\Makers\UBL\Models\Address;
use WPO\WC\PDF_Invoices\Makers\UBL\Models\DateTime;

defined( 'ABSPATH' ) or exit;

class Order extends Model
{
	/** @var int */
	public $id;

	/** @var DateTime */
	public $date;

	/** @var Address */
	public $billing_address;
	
	/** @var Address */
	public $shipping_address;
}