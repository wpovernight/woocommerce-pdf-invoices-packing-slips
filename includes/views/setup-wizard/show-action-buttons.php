<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Action buttons', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Would you like to display the action buttons in your WooCommerce order list? The action buttons allow you to manually create a PDF.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
	<p><small><?php esc_html_e( '(You can always change this setting later via the Screen Options menu)', 'woocommerce-pdf-invoices-packing-slips' ); ?></small></p>
</div>
<div class="wpo-setup-input">
	<?php
	$actions = true;
	$user_id = get_current_user_id();
	$hidden = get_user_meta( $user_id, 'manageedit-shop_ordercolumnshidden', true );
	if ( empty( $hidden ) )
		$hidden = array( 'shipping_address', 'billing_address', 'wc_actions' );
		update_user_option( $user_id, 'manageedit-shop_ordercolumnshidden', $hidden, true );
	if ( in_array( 'wc_actions', $hidden ) )
		$actions = false
	?>
	<input id="show-action-buttons" type="checkbox" <?php echo $actions !== false ? 'checked' : ''; ?> name="wc_show_action_buttons" value="1"><label for="show-action-buttons" class="checkbox"><?php esc_html_e( 'Show action buttons', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
</div>