<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

/*
|--------------------------------------------------------------------------
| Document getter functions
|--------------------------------------------------------------------------
|
| Global functions to get the document object for an order
|
*/

function wcpdf_filter_order_ids( $order_ids, $document_type ) {
	$order_ids = apply_filters( 'wpo_wcpdf_process_order_ids', $order_ids, $document_type );
	// filter out trashed orders
	foreach ( $order_ids as $key => $order_id ) {
		$order_status = get_post_status( $order_id );
		if ( $order_status == 'trash' ) {
			unset( $order_ids[ $key ] );
		}
	}
	return $order_ids;
}

function wcpdf_get_document( $document_type, $order, $init = false ) {
	// $order can be one of the following:
	// - WC Order object
	// - array of order ids
	// - null if order not loaded or loaded later
	if ( !empty( $order ) ) {
		if ( is_object( $order ) ) {
			// we filter order_ids for objects too:
			// an order object may need to be converted to several refunds for example
			$order_ids = array( WCX_Order::get_id( $order ) );
			$filtered_order_ids = wcpdf_filter_order_ids( $order_ids, $document_type );
			// check if something has changed
			if ( empty( array_diff( $filtered_order_ids, $order_ids ) ) && count( $order_ids ) == count( $filtered_order_ids ) ) {
				// nothing changed, load document with Order object
				do_action( 'wpo_wcpdf_process_template_order', $document_type, WCX_Order::get_id( $order ) );
				$document = WPO_WCPDF()->documents->get_document( $document_type, $order );

				if ( $init && !$document->exists() ) {
					$document->init();
					$document->save();
				}
				// $document->read_data( $order ); // isn't data already read from construct?
				return $document;
			} else {
				// order ids array changed, continue processing that array
				$order_ids = $filtered_order_ids;
			}
		} elseif ( is_array( $order ) ) {
			$order_ids = wcpdf_filter_order_ids( $order, $document_type );
		} else {
			return false;
		}

		// throw error when no order ids
		if ( empty( $order_ids ) ) {
			throw new Exception('No orders to export!');
		}

		// if we only have one order, it's simple
		if ( count( $order_ids ) == 1 ) {
			$order_id = array_pop ( $order_ids );
			do_action( 'wpo_wcpdf_process_template_order', $document_type, $order_id );
			$order = WCX::get_order( $order_id );

			$document = WPO_WCPDF()->documents->get_document( $document_type, $order );
			if ( $init && !$document->exists() ) {
				$document->init();
				$document->save();
			}
		// otherwise we use bulk class to wrap multiple documents in one
		} else {
			$document = wcpdf_get_bulk_document( $document_type, $order_ids );
		}
	} else {
		// orderless document (used as wrapper for bulk, for example)
		$document = WPO_WCPDF()->documents->get_document( $document_type, $order );
	}

	return $document;
}

function wcpdf_get_bulk_document( $document_type, $order_ids ) {
	return new \WPO\WC\PDF_Invoices\Documents\Bulk_Document( $document_type, $order_ids );
}

function wcpdf_get_invoice( $order ) {
	return wcpdf_get_document( 'invoice', $order );
}

function wcpdf_get_packing_slip( $order ) {
	return wcpdf_get_document( 'packing-slip', $order );
}

/**
 * Load HTML into (pluggable) PDF library, DomPDF 0.6 by default
 * Use wpo_wcpdf_pdf_maker filter to change the PDF class (which can wrap another PDF library).
 * @return WC_Logger
 */
function wcpdf_get_pdf_maker( $html, $settings = array() ) {
	if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/class-wcpdf-pdf-maker.php' );
	}
	$class = apply_filters( 'wpo_wcpdf_pdf_maker', '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' );
	return new $class( $html, $settings );
}

function wcpdf_pdf_headers( $filename, $mode = 'inline', $pdf = null ) {
	switch ($mode) {
		case 'download':
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$filename.'"'); 
			header('Content-Transfer-Encoding: binary');
			header('Connection: Keep-Alive');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			break;
		case 'inline':
		default:
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="'.$filename.'"');
			break;
	}
}