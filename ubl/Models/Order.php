<?php

namespace WPO\WC\UBL\Models;

use WPO\WC\UBL\Models\Address;
use WPO\WC\UBL\Models\DateTime;

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