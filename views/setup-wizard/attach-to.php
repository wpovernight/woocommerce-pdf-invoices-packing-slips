<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Attach to...', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Select to which emails you would like to attach your invoice.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<table>
	<?php
	$current_settings  = get_option( 'wpo_wcpdf_documents_settings_invoice', array() );
	$invoice           = wcpdf_get_invoice( null ); // load invoice to reuse method to get wc emails
	$wc_emails         = $invoice->get_wc_emails();
	$default           = isset( $current_settings['attach_to_email_ids'] ) ? array_keys( array_filter( $current_settings['attach_to_email_ids'], function( $value ) { return $value === '1'; } ) ) : array();
	$attach_to_setting = array(
		'option_name'     => 'wcpdf_settings[wpo_wcpdf_documents_settings_invoice]',
		'id'              => 'attach_to_email_ids',
		'options'         => $wc_emails,
		'multiple'        => true,
		'enhanced_select' => true,
		'default'         => $default,
	);

	WPO_WCPDF()->settings->callbacks->select( $attach_to_setting );

	?>
	</table>
</div>
