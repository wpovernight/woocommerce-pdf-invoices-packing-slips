<?php
namespace WPO\IPS\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\OrderUtil' ) ) :

class OrderUtil {

	public $wc_order_util_class_object;
	protected static $_instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->wc_order_util_class_object = $this->get_wc_order_util_class();
	}

	/**
	 * Get the fully qualified class name of WooCommerce's OrderUtil if it exists, or null if it doesn't.
	 *
	 * @return string|null
	 */
	public function get_wc_order_util_class(): ?string {
		return class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			? \Automattic\WooCommerce\Utilities\OrderUtil::class
			: null;
	}

	/**
	 * Get the order type for a given order ID.
	 *
	 * @param int $order_id
	 * @return string
	 */
	public function get_order_type( int $order_id ): string {
		if ( $this->wc_order_util_class_object && is_callable( [ $this->wc_order_util_class_object, 'get_order_type' ] ) ) {
			return $this->wc_order_util_class_object::get_order_type( intval( $order_id ) );
		} else {
			return get_post_type( intval( $order_id ) );
		}
	}

	/**
	 * Check if the custom orders table usage is enabled in WooCommerce.
	 *
	 * @return bool
	 */
	public function custom_orders_table_usage_is_enabled(): bool {
		if ( $this->wc_order_util_class_object && is_callable( array( $this->wc_order_util_class_object, 'custom_orders_table_usage_is_enabled' ) ) ) {
			return $this->wc_order_util_class_object::custom_orders_table_usage_is_enabled();
		} else {
			return false;
		}
	}

	/**
	 * Check if the current page is a WooCommerce Admin page.
	 *
	 * @return bool
	 */
	public function is_wc_admin_page(): bool {
		return class_exists( 'Automattic\WooCommerce\Admin\PageController' ) &&
			is_callable( array( '\\Automattic\\WooCommerce\\Admin\\PageController', 'is_admin_or_embed_page' ) ) &&
			\Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page();
	}
	
}

endif; // Class exists check
