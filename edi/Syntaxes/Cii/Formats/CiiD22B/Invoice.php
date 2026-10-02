<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD22B;

use WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Invoice as CiiD16BInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends CiiD16BInvoice {

	public string $slug = 'cii-invoice-d22b';
	public string $name = 'CII Invoice D22B';

}
