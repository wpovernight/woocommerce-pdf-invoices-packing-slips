<?php
/**
 * WooCommerce OrderUtil compatibility class.
 *
 * @since 3.5
 */

namespace WPO\IPS\Compatibility;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\OrderUtil' ) ) :

class OrderUtil {

	public $wc_order_util_class_object;
	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->wc_order_util_class_object = $this->get_wc_order_util_class();
	}

	public function get_wc_order_util_class() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return \Automattic\WooCommerce\Utilities\OrderUtil::class;
		} else {
			return false;
		}
	}

	public function get_order_type( $order_id ) {
		if ( $this->wc_order_util_class_object && is_callable( [ $this->wc_order_util_class_object, 'get_order_type' ] ) ) {
			return $this->wc_order_util_class_object::get_order_type( intval( $order_id ) );
		} else {
			return get_post_type( intval( $order_id ) );
		}
	}

	public function custom_orders_table_usage_is_enabled() {
		if ( $this->wc_order_util_class_object && is_callable( [ $this->wc_order_util_class_object, 'custom_orders_table_usage_is_enabled' ] ) ) {
			return $this->wc_order_util_class_object::custom_orders_table_usage_is_enabled();
		} else {
			return false;
		}
	}

	public function is_wc_admin_page() {
		return class_exists( 'Automattic\WooCommerce\Admin\PageController' ) &&
			is_callable( array( '\\Automattic\\WooCommerce\\Admin\\PageController', 'is_admin_or_embed_page' ) ) &&
			\Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page();
	}
}

endif; // Class exists check
