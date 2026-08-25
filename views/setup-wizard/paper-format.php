<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Paper format', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Select the paper format for your invoice.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<?php
	$current_settings = wp_parse_args( get_option( 'wpo_wcpdf_settings_general', array() ), array(
		'paper_size' => 'a4',
	) );
	?>
	<select name="wcpdf_settings[wpo_wcpdf_settings_general][paper_size]">
		<option <?php echo $current_settings['paper_size'] == 'a4' ? 'selected' : ''; ?> value="a4"><?php esc_html_e( 'A4', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
		<option <?php echo $current_settings['paper_size'] == 'letter' ? 'selected' : ''; ?> value="letter"><?php esc_html_e( 'Letter', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
	</select>
</div>
