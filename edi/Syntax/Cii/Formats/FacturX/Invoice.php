<?php

namespace WPO\IPS\EDI\Syntax\Cii\Formats\FacturX;

use WPO\IPS\EDI\Syntax\Cii\Formats\CiiD16B\Invoice as CiiD16BInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends CiiD16BInvoice {
	
	public string $slug = 'factur-x';
	public string $name = 'Factur-X';

}
