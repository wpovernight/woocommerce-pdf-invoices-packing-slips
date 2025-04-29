<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Action buttons', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Would you like to display the action buttons in your WooCommerce order list? The action buttons allow you to manually create a PDF.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
	<p><small><?php esc_html_e( '(You can always change this setting later via the Screen Options menu)', 'woocommerce-pdf-invoices-packing-slips' ); ?></small></p>
</div>
<div class="wpo-setup-input">
	<?php
	$actions    = true;
	$user_id    = get_current_user_id();
	$column_key = 'wc_actions';
	
	$orders_column_hidden_key = WPO_WCPDF()->order_util->custom_orders_table_usage_is_enabled()
		? 'managewoocommerce_page_wc-orderscolumnshidden'
		: 'manageedit-shop_ordercolumnshidden';
	
	$hidden = get_user_meta( $user_id, $orders_column_hidden_key, true );
	
	if ( empty( $hidden ) ) {
		$hidden = array( 'shipping_address', 'billing_address', $column_key );
		update_user_option( $user_id, $orders_column_hidden_key, $hidden, true );
	}
	
	if ( is_array( $hidden ) && in_array( $column_key, $hidden, true ) ) {
		$actions = false;
	}
	?>
	<input id="show-action-buttons" type="checkbox" <?php echo $actions !== false ? 'checked' : ''; ?> name="wc_show_action_buttons" value="1">
	<label for="show-action-buttons" class="slider"></label>
	<label for="show-action-buttons" class="checkbox"><?php esc_html_e( 'Show action buttons', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
</div>
