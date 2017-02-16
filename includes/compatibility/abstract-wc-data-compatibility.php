<?php
/**
 * Derived from SkyVerge WooCommerce Plugin Framework https://github.com/skyverge/wc-plugin-framework/
 */

namespace WPO\WC\PDF_Invoices\Compatibility;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Compatibility\\Data' ) ) :

/**
 * WooCommerce data compatibility class.
 *
 * @since 4.6.0-dev
 */
abstract class Data {

	/**
	 * Creates aliases for add_meta_data, update_meta_data and delete_meta_data without the _data suffix
	 *
	 * @param  string $name      static function name
	 * @param  array  $arguments function arguments
	 */
	public static function __callStatic( $name, $arguments ) {
		if ( substr( $name, -strlen('_meta') ) == '_meta' && method_exists( __CLASS__, $name.'_data' ) ) {
			call_user_func_array( array( __CLASS__, $name.'_data' ), $arguments );
		}
	}


	/**
	 * Gets an object property.
	 *
	 * @since 4.6.0-dev
	 * @param object $object the data object, likely \WC_Order or \WC_Product
	 * @param string $prop the property name
	 * @param string $context if 'view' then the value will be filtered
	 * @return string
	 */
	public static function get_prop( $object, $prop, $context = 'view', $compat_props = array() ) {

		$value = '';

		if ( WC_Core::is_wc_version_gte_2_7() ) {

			if ( is_callable( array( $object, "get_{$prop}" ) ) ) {
				$value = $object->{"get_{$prop}"}( $context );
			} else {
				$value = '';
			}

		} else {

			// backport the property name
			if ( isset( $compat_props[ $prop ] ) ) {
				$prop = $compat_props[ $prop ];
			}

			// if this is the 'view' context and there is an accessor method, use it
			if ( is_callable( array( $object, "get_{$prop}" ) ) && 'view' === $context ) {
				$value = $object->{"get_{$prop}"}();
			} else {
				$value = $object->$prop;
			}
		}

		return $value;
	}


	/**
	 * Sets an object's properties.
	 *
	 * Note that this does not save any data to the database.
	 *
	 * @since 4.6.0-dev
	 * @param object $object the data object, likely \WC_Order or \WC_Product
	 * @param array $props the new properties as $key => $value
	 * @return object
	 */
	public static function set_props( $object, $props, $compat_props = array() ) {

		if ( WC_Core::is_wc_version_gte_2_7() ) {

			$object->set_props( $props );

		} else {

			foreach ( $props as $prop => $value ) {

				if ( isset( $compat_props[ $prop ] ) ) {
					$prop = $compat_props[ $prop ];
				}

				$object->$prop = $value;
			}
		}

		return $object;
	}


	/**
	 * Gets an object's stored meta value.
	 *
	 * @since 4.6.0-dev
	 * @param object $object the data object, likely \WC_Order or \WC_Product
	 * @param string $key the meta key
	 * @param bool $single whether to get the meta as a single item. Defaults to `true`
	 * @param string $context if 'view' then the value will be filtered
	 * @return string
	 */
	public static function get_meta( $object, $key = '', $single = true, $context = 'view' ) {

		if ( WC_Core::is_wc_version_gte_2_7() ) {
			$value = $object->get_meta( $key, $single, $context );
		} else {
			$value = get_post_meta( $object->id, $key, $single );
		}

		return $value;
	}


	/**
	 * Stores an object meta value.
	 *
	 * @since 4.6.0-dev
	 * @param object $object the data object, likely \WC_Order or \WC_Product
	 * @param string $key the meta key
	 * @param string $value the meta value
	 * @param strint $meta_id Optional. The specific meta ID to update
	 */
	public static function add_meta_data( $object, $key, $value, $unique = false ) {

		if ( WC_Core::is_wc_version_gte_2_7() ) {

			$object->add_meta_data( $key, $value, $unique );

			$object->save_meta_data();

		} else {

			add_post_meta( $object->id, $key, $value, $unique );
		}
	}


	/**
	 * Updates an object's stored meta value.
	 *
	 * @since 4.6.0-dev
	 * @param object $object the data object, likely \WC_Order or \WC_Product
	 * @param string $key the meta key
	 * @param string $value the meta value
	 * @param strint $meta_id Optional. The specific meta ID to update
	 */
	public static function update_meta_data( $object, $key, $value, $meta_id = '' ) {

		if ( WC_Core::is_wc_version_gte_2_7() ) {

			$object->update_meta_data( $key, $value, $meta_id );

			$object->save_meta_data();

		} else {

			update_post_meta( $object->id, $key, $value );
		}
	}


	/**
	 * Deletes an object's stored meta value.
	 *
	 * @since 4.6.0-dev
	 * @param object $object the data object, likely \WC_Order or \WC_Product
	 * @param string $key the meta key
	 */
	public static function delete_meta_data( $object, $key ) {

		if ( WC_Core::is_wc_version_gte_2_7() ) {

			$object->delete_meta_data( $key );

			$object->save_meta_data();

		} else {

			delete_post_meta( $object->id, $key );
		}
	}


}


endif; // Class exists check
