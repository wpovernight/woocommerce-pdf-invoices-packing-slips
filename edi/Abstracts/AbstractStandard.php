<?php

namespace WPO\IPS\EDI\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractStandard {

	public static string $slug;
	public static string $name;
	public static string $version;

}
