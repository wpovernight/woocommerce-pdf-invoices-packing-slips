<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Enter your shop name', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Lets quickly setup your invoice. Please enter the name and address of your shop in the fields on the right.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<?php
	$current_settings = wp_parse_args( get_option( 'wpo_wcpdf_settings_general', array() ), array(
		'shop_name'    => array( 'default' => get_bloginfo( 'name' ) ),
		'shop_address' => array( 'default' => '' ),
	) );
	?>
	<input type="text" class="shop-name" placeholder="<?php esc_attr_e( 'Shop name', 'woocommerce-pdf-invoices-packing-slips' ); ?>" name="wcpdf_settings[wpo_wcpdf_settings_general][shop_name][default]" value="<?php echo esc_attr( array_pop( $current_settings['shop_name'] ) ); ?>">
	<textarea class="shop-address" placeholder="<?php esc_attr_e( 'Shop address', 'woocommerce-pdf-invoices-packing-slips' ); ?>" name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address][default]"><?php echo esc_html( array_pop( $current_settings['shop_address'] ) ); ?></textarea>
</div>