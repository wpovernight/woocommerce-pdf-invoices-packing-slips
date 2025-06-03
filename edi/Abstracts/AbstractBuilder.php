<?php

namespace WPO\IPS\EDI\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractBuilder {

	abstract public function build( AbstractDocument $document );

}
