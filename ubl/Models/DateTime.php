<?php

namespace WPO\IPS\UBL\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DateTime extends Model {

	/** @var string */
	public $date;

	/** @var string */
	public $time;

	/** @var string */
	public $timezone;

}
