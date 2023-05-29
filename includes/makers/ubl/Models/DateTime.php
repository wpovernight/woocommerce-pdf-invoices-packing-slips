<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Models;

defined( 'ABSPATH' ) or exit;

class DateTime extends Model
{
	/** @var string */
	public $date;

	/** @var string */
	public $time;

	/** @var string */
	public $timezone;
}