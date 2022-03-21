<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Display options', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Select some additional display options for your invoice.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<table>
	<?php
	$current_settings = get_option( 'wpo_wcpdf_documents_settings_invoice', array() );
	?>
		<tr>
			<th>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_shipping_address]" value="">
				<input id="display-shipping-address" type="checkbox" <?php echo ! empty( $current_settings['display_shipping_address'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_shipping_address]" value="1">
			</th>
			<td>
				<label for="display-shipping-address" class="checkbox"><?php esc_html_e( 'Display shipping address', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</td>
		</tr>
		<tr>
			<th>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_email]" value="">
				<input id="display-email" type="checkbox" <?php echo ! empty( $current_settings['display_email'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_email]" value="1">
			</th>
			<td>
				<label for="display-email" class="checkbox"><?php esc_html_e( 'Display email address', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</td>
		</tr>
		<tr>
			<th>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_phone]" value="">
				<input id="display-phone" type="checkbox" <?php echo ! empty( $current_settings['display_phone'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_phone]" value="1">
			</th>
			<td>
				<label for="display-phone" class="checkbox"><?php esc_html_e( 'Display phone number', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</td>
		</tr>
		<tr>
			<th>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_date]" value="">
				<input id="display-date" type="checkbox" <?php echo ! empty( $current_settings['display_date'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_date]" value="invoice_date">
			</th>
			<td>
				<label for="display-date" class="checkbox"><?php esc_html_e( 'Display invoice date', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</td>
		<tr>
		</tr>
			<th>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_number]" value="">
				<input id="display-number" type="checkbox" <?php echo ! empty( $current_settings['display_number'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_number]" value="invoice_number">
			</th>
			<td>
				<label for="display-number" class="checkbox"><?php esc_html_e( 'Display invoice number', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</td>
		</tr>
	</table>
</div>