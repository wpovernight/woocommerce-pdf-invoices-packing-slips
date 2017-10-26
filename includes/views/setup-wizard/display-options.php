<div class="wpo-step-description">
	<h2><?php _e( 'Display options', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php _e( 'Select some additional display options for your invoice.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<?php
	$current_settings = get_option( 'wpo_wcpdf_documents_settings_invoice', array() );
	?>
	<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_shipping_address]" value="">
	<input type="checkbox" <?php echo !empty($current_settings['display_shipping_address']) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_shipping_address]" value="1"><span class="checkbox"><?php _e( 'Display shipping address', 'woocommerce-pdf-invoices-packing-slips' ); ?></span><br>
	<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_email]" value="">
	<input type="checkbox" <?php echo !empty($current_settings['display_email']) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_email]" value="1"><span class="checkbox"><?php _e( 'Display email address', 'woocommerce-pdf-invoices-packing-slips' ); ?></span><br>
	<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_phone]" value="">
	<input type="checkbox" <?php echo !empty($current_settings['display_phone']) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_phone]" value="1"><span class="checkbox"><?php _e( 'Display phone number', 'woocommerce-pdf-invoices-packing-slips' ); ?></span><br>
	<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_date]" value="">
	<input type="checkbox" <?php echo !empty($current_settings['display_date']) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_date]" value="invoice_date"><span class="checkbox"><?php _e( 'Display invoice date', 'woocommerce-pdf-invoices-packing-slips' ); ?></span><br>
	<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_number]" value="">
	<input type="checkbox" <?php echo !empty($current_settings['display_number']) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_number]" value="invoice_number"><span class="checkbox"><?php _e( 'Display invoice number', 'woocommerce-pdf-invoices-packing-slips' ); ?></span><br>
</div>