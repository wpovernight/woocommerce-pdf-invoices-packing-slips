<?php
namespace WPO\IPS\Compatibility;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\ThirdPartyPlugins' ) ) :

/**
 * Third party plugin compatibility class.
 *
 * @since 2.0
 */
class ThirdPartyPlugins {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()	{
		// WooCommerce Subscriptions compatibility
		if ( class_exists( 'WC_Subscriptions' ) ) {
			if ( version_compare( \WC_Subscriptions::$version, '2.0', '<' ) ) {
				add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'woocommerce_subscriptions_renewal_order_created' ), 10, 4 );
			} elseif ( version_compare( \WC_Subscriptions::$version, '4.7.0', '<' ) ) {
				add_filter( 'wcs_renewal_order_meta', array( $this, 'wcs_renewal_order_meta' ), 10, 3 );
				add_filter( 'wcs_resubscribe_order_meta', array( $this, 'wcs_renewal_order_meta' ), 10, 3 );
			} else {
				add_filter( 'wc_subscriptions_renewal_order_data', array( $this, 'wcs_renewal_order_meta' ), 10, 3 );
				add_filter( 'wc_subscriptions_resubscribe_order_data', array( $this, 'wcs_renewal_order_meta' ), 10, 3 );
			}
		}

		// WooCommerce Product Bundles compatibility (add row classes)
		add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_product_bundles_classes' ), 10, 4 );

		// WPC Bundles compatibility (add row classes)
		add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_wpc_product_bundles_classes' ), 10, 4 );

		// YITH WooCommerce Product Bundles compatibility (add row classes)
		add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_yith_product_bundles_classes' ), 10, 4 );

		// WooCommerce Chained Products compatibility (add row classes)
		add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_chained_product_class' ), 10, 4 );

		// WooCommerce Composite Products compatibility (add row classes)
		add_filter( 'wpo_wcpdf_item_row_class', array( $this, 'add_composite_product_class' ), 10, 4 );

		// WooCommerce Order Status & Actions Manager emails compatibility
		if (class_exists('WC_Custom_Status')) {
			add_filter( 'wpo_wcpdf_wc_emails', array( $this, 'wc_order_status_actions_emails' ), 10, 1 );
		}

		// Aelia Currency Switcher compatibility
		$currency_switcher_active = !empty($GLOBALS['woocommerce-aelia-currencyswitcher']);
		if ( $currency_switcher_active ) {
			add_action( 'wpo_wcpdf_before_html', array( $this, 'aelia_currency_formatting' ), 10, 2 );
		}

		// Avoid double images from WooCommerce German Market
		if ( class_exists('WGM_Product') ) {
			add_action( 'wpo_wcpdf_before_html', array( $this, 'remove_wgm_thumbnails' ), 10, 2 );
			add_action( 'wpo_wcpdf_after_html', array( $this, 'restore_wgm_thumbnails' ), 10, 2 );
		}

		add_filter( 'woocommerce_hpos_admin_search_filters', array( $this, 'hpos_admin_search_filters' ) );
		add_filter( 'woocommerce_shop_order_list_table_prepare_items_query_args', array( $this, 'invoice_number_query_args' ) );
	}

	/**
	 * Reset invoice data for WooCommerce subscription renewal orders
	 * https://wordpress.org/support/topic/subscription-renewal-duplicate-invoice-number?replies=6#post-6138110
	 */
	public function woocommerce_subscriptions_renewal_order_created ( $renewal_order, $original_order, $product_id, $new_order_role ) {
		$this->reset_invoice_data( $renewal_order );
		return $renewal_order;
	}

	public function reset_invoice_data ( $order ) {
		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}
		// delete invoice number, invoice date & invoice exists meta
		$order->delete_meta_data( '_wcpdf_invoice_number' );
		$order->delete_meta_data( '_wcpdf_invoice_number_data' );
		$order->delete_meta_data( '_wcpdf_formatted_invoice_number' );
		$order->delete_meta_data( '_wcpdf_invoice_date' );
		$order->delete_meta_data( '_wcpdf_invoice_exists' );

		$order->save_meta_data();
	}

	/**
	 * Removes documents meta from WooCommerce Subscriptions renewal order
	 */
	public function wcs_renewal_order_meta( $meta, $to_order, $from_order ) {
		if ( empty( $meta ) ) {
			return $meta;
		}

		$documents      = WPO_WCPDF()->documents->get_documents();
		$documents_meta = array();

		foreach ( $documents as $document ) {
			$document_data_keys = apply_filters( 'wpo_wcpdf_delete_document_data_keys', array(
				'settings',
				'date',
				'date_formatted',
				'number',
				'number_data',
				'notes',
				'exists',
			), $document );

			$document_meta   = array_map( function ( $data_key ) use ( $document ) {
				return "_wcpdf_{$document->slug}_{$data_key}";
			}, $document_data_keys );
			$document_meta[] = "_wcpdf_formatted_{$document->slug}_number"; // legacy meta key
			$documents_meta  = array_merge( $documents_meta, $document_meta );
		}

		foreach ( $meta as $key => $value ) {
			// The old deprecated hook (`wcs_renewal_order_meta`) sends $meta with this data structure: array(... , array( "meta_key":"_wcpdf_invoice_number","meta_value":"158" ), ...)
			// The new hook (`wc_subscriptions_renewal_order_data`) sends $meta with this data structure: array(... ,"_wcpdf_invoice_number":"158", ...)
			$meta_key = is_array( $value ) ? ( $value['meta_key'] ?? null ) : $key;
			if ( in_array( $meta_key, $documents_meta, true ) ) {
				unset( $meta[ $key ] );
			}
		}

		return $meta;
	}

	/**
	 * WooCommerce Product Bundles
	 * @param string $classes       CSS classes for item row (tr)
	 * @param string $document_type PDF Document type
	 * @param object $order         WC_Order order
	 * @param int    $item_id       WooCommerce Item ID
	 */
	public function add_product_bundles_classes ( $classes, $document_type, $order, $item_id = 0 ) {
		if ( !class_exists('WC_Bundles') ) {
			return $classes;
		}

		$item_id = ! empty( $item_id ) ? $item_id : $this->get_item_id_from_classes( $classes );
		if ( empty( $item_id ) ) {
			return $classes;
		}

		if ( $bundled_by = wc_get_order_item_meta( $item_id, '_bundled_by', true ) ) {
			$classes = $classes . ' bundled-item';

			// check bundled item visibility
			if ( $hidden = wc_get_order_item_meta( $item_id, '_bundled_item_hidden', true ) ) {
				$classes = $classes . ' hidden';
			}

			return $classes;
		} elseif ( $bundled_items = wc_get_order_item_meta( $item_id, '_bundled_items', true ) ) {
			return  $classes . ' product-bundle';
		}

		return $classes;
	}

	/**
	 * WPC Product Bundles
	 * @param string $classes       CSS classes for item row (tr)
	 * @param string $document_type PDF Document type
	 * @param object $order         WC_Order order
	 * @param int    $item_id       WooCommerce Item ID
	 */
	public function add_wpc_product_bundles_classes ( $classes, $document_type, $order, $item_id = 0 ) {
		if ( ! class_exists( 'WPCleverWoosb' ) ) {
			return $classes;
		}

		$item_id = ! empty( $item_id ) ? $item_id : $this->get_item_id_from_classes( $classes );
		if ( empty( $item_id ) ) {
			return $classes;
		}

		// Add row classes
		$refunded_item_id = wc_get_order_item_meta( $item_id, '_refunded_item_id', true );
		$class_item_id = ! empty( $refunded_item_id ) ? $refunded_item_id : $item_id;

		if ( $bundled_by = wc_get_order_item_meta( $class_item_id, '_woosb_parent_id', true ) ) {
			$classes = $classes . ' bundled-item';
		} elseif ( $bundled_items = wc_get_order_item_meta( $class_item_id, '_woosb_ids', true ) ) {
			$classes = $classes . ' product-bundle';
		}

		return $classes;
	}

	/**
	 * YITH WooCommerce Product Bundles compatibility
	 *
	 * @param string $classes CSS classes for item row (tr)
	 * @param ?string $document_type PDF Document type
	 * @param ?object $order order
	 * @param int|string $item_id WooCommerce Item ID
	 *
	 * @return string
	 */
	public function add_yith_product_bundles_classes( string $classes, ?string $document_type, ?object $order, $item_id = 0 ): string {
		if ( empty( $order ) ) {
			return $classes;
		}

		if ( ! $order instanceof \WC_Abstract_Order ) {
			return $classes;
		}

		if ( empty( $item_id ) && ! empty( $classes ) ) {
			$item_id = $this->get_item_id_from_classes( $classes );
		}

		if ( ! empty( $item_id ) && is_numeric( $item_id ) ) {
			$item_id = absint( $item_id );
		} else {
			return $classes;
		}

		$product    = null;
		$bundled_by = null;

		foreach ( $order->get_items() as $order_item_id => $order_item ) {
			if ( absint( $order_item_id ) === $item_id ) {
				$product    = $order_item->get_product();
				$bundled_by = $order_item->get_meta( '_bundled_by', true );
				break;
			}
		}

		if ( empty( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			return $classes;
		}

		if ( 'yith_bundle' === $product->get_type() ) {
			$classes .= ' product-bundle';
		} elseif ( ! empty( $bundled_by ) ) {
			$classes .= ' bundled-item';
		}

		return $classes;
	}

	/**
	 * WooCommerce Chained Products
	 * @param string $classes       CSS classes for item row (tr)
	 * @param string $document_type PDF Document type
	 * @param object $order         WC_Order order
	 * @param int    $item_id       WooCommerce Item ID
	 */
	public function add_chained_product_class ( $classes, $document_type, $order, $item_id = 0 ) {
		if ( !class_exists('SA_WC_Chained_Products') && !class_exists('WC_Chained_Products') ) {
			return $classes;
		}

		$item_id = !empty($item_id) ? $item_id : $this->get_item_id_from_classes( $classes );
		if ( empty($item_id) ) {
			return $classes;
		}

		if ( $chained_product_of = wc_get_order_item_meta( $item_id, '_chained_product_of', true ) ) {
			return  $classes . ' chained-product';
		}

		return $classes;
	}

	/**
	 * WooCommerce Composite Products
	 * @param string $classes       CSS classes for item row (tr)
	 * @param string $document_type PDF Document type
	 * @param object $order         WC_Order order
	 * @param int    $item_id       WooCommerce Item ID
	 */
	public function add_composite_product_class ( $classes, $document_type, $order, $item_id = 0 ) {
		if ( !function_exists('wc_cp_is_composited_order_item') || !function_exists('wc_cp_is_composite_container_order_item') ) {
			return $classes;
		}

		$item_id = !empty($item_id) ? $item_id : $this->get_item_id_from_classes( $classes );
		if ( empty($item_id) ) {
			return $classes;
		}

		// get order item object
		$order_items = $order->get_items();
		foreach ($order_items as $order_item_id => $order_item) {
			if ($order_item_id == $item_id) {
				if ( wc_cp_is_composited_order_item( $order_item, $order ) ) {
					$classes .= ' component_table_item';
				} elseif ( wc_cp_is_composite_container_order_item( $order_item ) ) {
					$classes .= ' component_container_table_item';
				}
				break;
			}
		}

		return $classes;
	}

	/**
	 * Backwards compatibility helper function: try to get item ID from row class
	 * @param string $classes       CSS classes for item row (tr)
	 */
	public function get_item_id_from_classes ( $classes ) {
		$class_array = explode(' ', $classes);
		foreach ($class_array as $class) {
			if (is_numeric($class)) {
				$item_id = $class;
				break;
			}
		}

		// if still empty, we lost the item id somewhere :(
		if (empty($item_id)) {
			return false;
		} else {
			return $item_id;
		}
	}

	/**
	 * WooCommerce Order Status & Actions Manager emails compatibility
	 */
	public function wc_order_status_actions_emails ( $emails ) {
		// get list of custom statuses from WooCommerce Custom Order Status & Actions
		// status slug => status name
		$custom_statuses = \WC_Custom_Status::get_status_list_names();
		// append _email to slug (=email_id) and add to emails list
		foreach ($custom_statuses as $status_slug => $status_name) {
			$emails[$status_slug.'_email'] = $status_name;
		}
		return $emails;
	}


	/**
	 * Aelia Currency Switcher compatibility
	 * Applies decimal & Thousand separator settings
	 */
	function aelia_currency_formatting( $document_type, $document ) {
		add_filter( 'wc_price_args', array( $this, 'aelia_currency_price_args' ), 10, 1 );
	}

	function aelia_currency_price_args( $args ) {
		if ( !empty( $args['currency'] ) && class_exists("\\Aelia\\WC\\CurrencySwitcher\\WC_Aelia_CurrencySwitcher") ) {
			$cs_settings = \Aelia\WC\CurrencySwitcher\WC_Aelia_CurrencySwitcher::settings();
			$args['decimal_separator'] = $cs_settings->get_currency_decimal_separator( $args['currency'] );
			$args['thousand_separator'] = $cs_settings->get_currency_thousand_separator( $args['currency'] );
		}
		return $args;
	}

	/**
	 * Avoid double images from German Market: remove filter
	 */
	function remove_wgm_thumbnails( $document_type, $document ) {
		remove_filter( 'woocommerce_order_item_name', array( 'WGM_Product', 'add_thumbnail_to_order' ), 100, 3 );
	}

	/**
	 * Restore above
	 */
	function restore_wgm_thumbnails( $document_type, $document ) {
		if ( is_callable( array( 'WGM_Product', 'add_thumbnail_to_order' ) ) && get_option( 'german_market_product_images_in_order', 'off' ) == 'on' ) {
			add_filter( 'woocommerce_order_item_name', array( 'WGM_Product', 'add_thumbnail_to_order' ), 100, 3 );
		}
	}

	/**
	 * Adds "Invoice numbers" filter to the search filters available in the admin order search.
	 *
	 * @param array $options List of available filters.
	 *
	 * @return array
	 */
	function hpos_admin_search_filters( array $options ): array {
		if ( WPO_WCPDF()->admin->invoice_number_search_enabled() ) {
			$all = $options['all'];
			unset( $options['all'] );
			$options['invoice_numbers'] = __( 'Invoice numbers', 'woocommerce-pdf-invoices-packing-slips' );
			$options['all'] = $all;
		}

		return $options;
	}

	/**
	 * Modifies the arguments passed to `wc_get_orders()` to support 'invoice_numbers' order search filter.
	 *
	 * @param array $order_query_args Arguments to be passed to `wc_get_orders()`.
	 *
	 * @return array
	 */
	function invoice_number_query_args( array $order_query_args ): array {
		if ( isset( $order_query_args['search_filter'] ) && 'invoice_numbers' === $order_query_args['search_filter'] && ! empty( $order_query_args['s'] ) ) {
			$invoice_numbers = explode( ',', $order_query_args['s'] );
			$invoice_numbers = array_map( function ( $number ) {
				return sanitize_text_field( trim( $number ) );
			}, $invoice_numbers );

			$order_query_args['meta_query']    = $order_query_args['meta_query'] ?? array();
			$order_query_args['meta_query'][]  = [
				'key'     => '_wcpdf_invoice_number',
				'value'   => $invoice_numbers,
				'compare' => 'IN',
			];
			$order_query_args['search_filter'] = 'all';
			unset( $order_query_args['s'] );
		}

		return $order_query_args;
	}
}


endif; // Class exists check
