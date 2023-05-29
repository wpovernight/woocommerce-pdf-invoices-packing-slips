<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Builders;

use WPO\WC\PDF_Invoices\Makers\UBL\Documents\Document;

defined( 'ABSPATH' ) or exit;

abstract class Builder
{
	abstract public function build( Document $document );
}