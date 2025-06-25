<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Enter your shop name', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Lets quickly setup your invoice. Please enter the name and address of your shop in the fields on the right.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<?php
	// Set default values for current setting to be used in case the user has not set them yet.
	$current_settings = wp_parse_args(
		get_option( 'wpo_wcpdf_settings_general', array() ),
		array(
			'shop_name'             => array( 'default' => get_bloginfo( 'name' ) ?? '' ),
			'shop_address_line_1'   => array( 'default' => get_option( 'woocommerce_store_address' ), '' ),
			'shop_address_line_2'   => array( 'default' => get_option( 'woocommerce_store_address_2' ), '' ),
			'shop_address_country'  => array( 'default' => get_option( 'woocommerce_store_country' ), '' ),
			'shop_address_state'    => array( 'default' => get_option( 'woocommerce_store_state' ), '' ),
			'shop_address_city'     => array( 'default' => get_option( 'woocommerce_store_city' ), '' ),
			'shop_address_postcode' => array( 'default' => get_option( 'woocommerce_store_postcode' ), '' ),
		)
	);
	$countries       = array_merge( array( '' => __( 'Select a country', 'woocommerce-pdf-invoices-packing-slips' ) ), \WC()->countries->get_countries() );
	$states          = wpo_wcpdf_get_country_states( $current_settings['shop_address_country']['default'] );
	$states_disabled = empty( $states ) ? 'disabled' : '';
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
	<select class="shop-address-country" name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_country][default]" style="width: 100%;">
		<?php
			foreach ( $countries as $country_code => $country_name ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $country_code ),
					selected( $current_settings['shop_address_country']['default'], $country_code, false ),
					esc_html( $country_name )
				);
			}
		?>
	</select>
	<select class="shop-address-state" name="wcpdf_settings[wpo_wcpdf_settings_general][shop_address_state][default]" <?php echo esc_attr( $states_disabled ); ?>>
		<?php
			if ( ! empty( $states ) ) {
				foreach ( $states as $state_code => $state_name ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $state_code ),
						selected( $current_settings['shop_address_state']['default'], $state_code, false ),
						esc_html( $state_name )
					);
				}
			} else {
				printf(
					'<option value="" %s>%s</option>',
					selected( $current_settings['shop_address_state']['default'], '', false ),
					esc_html__( 'No states available', 'woocommerce-pdf-invoices-packing-slips' )
				);
			}
		?>
	</select>
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
