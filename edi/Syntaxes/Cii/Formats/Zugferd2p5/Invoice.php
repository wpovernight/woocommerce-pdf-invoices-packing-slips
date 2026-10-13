<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\Zugferd2p5;

use WPO\IPS\EDI\Syntaxes\Cii\Formats\FacturX1p0\Invoice as FacturXInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends FacturXInvoice {

	public string $slug = 'zugferd-invoice-2p5';
	public string $name = 'ZUGFeRD Invoice 2.5';

}
