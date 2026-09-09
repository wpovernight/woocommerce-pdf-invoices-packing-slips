<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCius;

use WPO\IPS\EDI\Syntaxes\Ubl\Formats\Ubl2p1\Invoice as UblInvoice;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends UblInvoice {

	public string $slug = 'france-cius-ubl-invoice';
	public string $name = 'France CIUS UBL Invoice';

}
