<?php

namespace WPO\WC\UBL\Builders;

use WPO\WC\UBL\Documents\Document;

defined( 'ABSPATH' ) or exit;

abstract class Builder
{
	abstract public function build( Document $document );
}