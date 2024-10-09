<?php

namespace WPO\IPS\UBL\Builders;

use WPO\IPS\UBL\Documents\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Builder {

	abstract public function build( Document $document );

}
