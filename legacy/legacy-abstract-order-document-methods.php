<?php
namespace WPO\WC\PDF_Invoices\Documents;

use WPO\IPS\Documents\OrderDocumentMethods;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Order_Document_Methods' ) ) :

abstract class Order_Document_Methods extends OrderDocumentMethods {

	// This is a legacy class, do not use it directly!
	// Required by the Professional extension for backwards compatibility, version 2.15.10 and older

}

endif; // class_exists
