<?php

namespace WPO\IPS\EDI\Abstracts;

use WPO\IPS\EDI\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractBuilder {

	abstract public function build( Document $document );

}
