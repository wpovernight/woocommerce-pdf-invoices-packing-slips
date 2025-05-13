<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Enter your shop name', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Lets quickly setup your invoice. Please enter the name and address of your shop in the fields on the right.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<?php
	// Set default values for current setting to be used in case the user has not set them yet.
	$current_settings = wp_parse_args( get_option( 'wpo_wcpdf_settings_general', array() ), array(
		'shop_name'             => array( 'default' => get_bloginfo( 'name' ) ?? '' ),
		'shop_address_line_1'   => array( 'default' => get_option( 'woocommerce_store_address' ), '' ),
		'shop_address_line_2'   => array( 'default' => get_option( 'woocommerce_store_address_2' ), '' ),
		'shop_address_country'  => array( 'default' => get_option( 'woocommerce_store_country' ), '' ),
		'shop_address_state'    => array( 'default' => get_option( 'woocommerce_store_state' ), '' ),
		'shop_address_city'     => array( 'default' => get_option( 'woocommerce_store_city' ), '' ),
		'shop_address_postcode' => array( 'default' => get_option( 'woocommerce_store_postcode' ), '' ),
	) );
	?>
	<input
		type="text"
		class="shop-name"
		placeholder="<?php esc_attr_e( 'Shop name', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_name][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_name'] ) ); ?>"
	>
	<input
		type="text"
		class="shop-address-line-1"
		placeholder="<?php esc_attr_e( 'Shop address line 1', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_line_1][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_address_line_1'] ) ); ?>"
	>
	<input
		type="text"
		class="shop-address-line-2"
		placeholder="<?php esc_attr_e( 'Shop address line 2', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_line_2][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_address_line_2'] ) ); ?>"
	>
	<input
		type="text"
		class="shop-address-country"
		placeholder="<?php esc_attr_e( 'Shop address country', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_country][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_address_country'] ) ); ?>"
	>
	<input
		type="text"
		class="shop-address-state"
		placeholder="<?php esc_attr_e( 'Shop address country', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_state][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_address_state'] ) ); ?>"
	>
	<input
		type="text"
		class="shop-address-city"
		placeholder="<?php esc_attr_e( 'Shop address city', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_city][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_address_city'] ) ); ?>"
	>
	<input
		type="text"
		class="shop-address-postcode"
		placeholder="<?php esc_attr_e( 'Shop address postcode', 'woocommerce-pdf-invoices-packing-slips' ); ?>"
		name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_postcode][default]"
		value="<?php echo esc_attr( array_pop( $current_settings['shop_address_postcode'] ) ); ?>"
	>
</div>
