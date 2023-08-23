<?php

namespace WPO\WC\UBL\Builders;

use WPO\WC\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Builder {
	
	abstract public function build( Document $document );
	
}