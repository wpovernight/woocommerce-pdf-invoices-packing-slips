<?php

namespace WPO\IPS\UBL\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Address extends Model {

	public $first_name;
	public $last_name;
	public $address_1;
	public $address_2;
	public $city;
	public $state;
	public $postcode;
	public $country;
	public $email;
	public $phone;

}
