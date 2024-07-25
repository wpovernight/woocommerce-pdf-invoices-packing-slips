<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\IPS\Documents\OrderDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Order_Document' ) ) :

abstract class Order_Document extends OrderDocument {

	// This is a legacy class, do not use it directly!

}

endif; // class_exists
