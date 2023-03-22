<?php
/**
 * WooCommerce OrderUtil compatibility class.
 *
 * @since 3.5
 */

namespace WPO\WC\PDF_Invoices\Compatibility;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Compatibility\\Order_Util' ) ) :

class Order_Util {
	
	public $wc_order_util_class_object;
	
	function __construct() {
		$this->wc_order_util_class_object = $this->get_wc_order_util_class();
	}
	
	function get_wc_order_util_class() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return \Automattic\WooCommerce\Utilities\OrderUtil::class;
		} else {
			return false;
		}
	}
	
	function get_order_type( $order_id ) {
		if ( $this->wc_order_util_class_object && is_callable( [ $this->wc_order_util_class_object, 'get_order_type' ] ) ) {
			return $this->wc_order_util_class_object::get_order_type( intval( $order_id ) );
		} else {
			return get_post_type( intval( $order_id ) );
		}
	}
	
	function custom_orders_table_usage_is_enabled() {
		if ( $this->wc_order_util_class_object && is_callable( [ $this->wc_order_util_class_object, 'custom_orders_table_usage_is_enabled' ] ) ) {
			return $this->wc_order_util_class_object::custom_orders_table_usage_is_enabled();
		} else {
			return false;
		}
	}
	
}

endif; // Class exists check

return new Order_Util();
