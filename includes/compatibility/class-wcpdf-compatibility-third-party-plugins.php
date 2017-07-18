<?php
namespace WPO\WC\PDF_Invoices\Compatibility;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;
use WPO\WC\PDF_Invoices\Compatibility\WC_DateTime;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Compatibility\\Third_Party_Plugins' ) ) :

/**
 * Third party plugin compatibility class.
 *
 * @since 2.0
 */
class Third_Party_Plugins {
	function __construct()	{
		// WooCommerce Subscriptions compatibility
		if ( class_exists('WC_Subscriptions') ) {
			if ( version_compare( \WC_Subscriptions::$version, '2.0', '<' ) ) {
				add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'woocommerce_subscriptions_renewal_order_created' ), 10, 4 );
			} else {
				add_action( 'wcs_renewal_order_created', array( $this, 'wcs_renewal_order_created' ), 10, 2 );
			}
		}

		// WooCommerce Product Bundles compatibility (add row classes)
		if ( class_exists('WC_Bundles') ) {
			add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_product_bundles_classes' ), 10, 4 );
		}

		// WooCommerce Chained Products compatibility (add row classes)
		if ( class_exists('SA_WC_Chained_Products') ) {
			add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_chained_product_class' ), 10, 4 );
		}
	}

	/**
	 * Reset invoice data for WooCommerce subscription renewal orders
	 * https://wordpress.org/support/topic/subscription-renewal-duplicate-invoice-number?replies=6#post-6138110
	 */
	public function woocommerce_subscriptions_renewal_order_created ( $renewal_order, $original_order, $product_id, $new_order_role ) {
		$this->reset_invoice_data( $renewal_order );
		return $renewal_order;
	}

	public function wcs_renewal_order_created ( $renewal_order, $subscription ) {
		$this->reset_invoice_data( $renewal_order );
		return $renewal_order;
	}

	public function reset_invoice_data ( $order ) {
		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}
		// delete invoice number, invoice date & invoice exists meta
		WCX_Order::delete_meta_data( $order, '_wcpdf_invoice_number' );
		WCX_Order::delete_meta_data( $order, '_wcpdf_invoice_number_data' );
		WCX_Order::delete_meta_data( $order, '_wcpdf_formatted_invoice_number' );
		WCX_Order::delete_meta_data( $order, '_wcpdf_invoice_date' );
		WCX_Order::delete_meta_data( $order, '_wcpdf_invoice_exists' );
	}

	/**
	 * WooCommerce Product Bundles
	 * @param string $classes       CSS classes for item row (tr) 
	 * @param string $document_type PDF Document type
	 * @param object $order         WC_Order order
	 * @param int    $item_id       WooCommerce Item ID
	 */
	public function add_product_bundles_classes ( $classes, $document_type, $order, $item_id = '' ) {
		if ( empty($item_id) ) {
			// get item id from classes (backwards compatibility fix)
			$class_array = explode(' ', $classes);
			foreach ($class_array as $class) {
				if (is_numeric($class)) {
					$item_id = $class;
					break;
				}
			}

			// if still empty, we lost the item id somewhere :(
			if (empty($item_id)) {
				return $classes;
			}
		}

		if ( $bundled_by = WCX_Order::get_item_meta( $order, $item_id, '_bundled_by', true ) ) {
			$classes = $classes . ' bundled-item';

			// check bundled item visibility
			if ( $hidden = WCX_Order::get_item_meta( $order, $item_id, '_bundled_item_hidden', true ) ) {
				$classes = $classes . ' hidden';
			}

			return $classes;
		} elseif ( $bundled_items = WCX_Order::get_item_meta( $order, $item_id, '_bundled_items', true ) ) {
			return  $classes . ' product-bundle';
		}

		return $classes;
	}

	/**
	 * WooCommerce Chanined Products
	 * @param string $classes       CSS classes for item row (tr) 
	 * @param string $document_type PDF Document type
	 * @param object $order         WC_Order order
	 * @param int    $item_id       WooCommerce Item ID
	 */
	public function add_chained_product_class ( $classes, $document_type, $order, $item_id = '' ) {
		if ( empty($item_id) ) {
			// get item id from classes (backwards compatibility fix)
			$class_array = explode(' ', $classes);
			foreach ($class_array as $class) {
				if (is_numeric($class)) {
					$item_id = $class;
					break;
				}
			}

			// if still empty, we lost the item id somewhere :(
			if (empty($item_id)) {
				return $classes;
			}
		}

		if ( $chained_product_of = WCX_Order::get_item_meta( $order, $item_id, '_chained_product_of', true ) ) {
			return  $classes . ' chained-product';
		}

		return $classes;
	}


}


endif; // Class exists check

return new Third_Party_Plugins();
