<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Your logo' , 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Set the header image that will display on your invoice.' , 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<script><?php echo "var wpo_wcpdf_admin = " . wp_json_encode( array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ) . ';'; ?></script>
	<?php
	WPO_WCPDF()->settings->callbacks->media_upload( array(
		'option_name'          => 'wpo_wcpdf_settings_general',
		'setting_name'         => 'wcpdf_settings[wpo_wcpdf_settings_general][header_logo]',
		'id'                   => 'header_logo',
		'uploader_title'       => __( 'Select or upload your invoice header/logo', 'woocommerce-pdf-invoices-packing-slips' ),
		'uploader_button_text' => __( 'Set image', 'woocommerce-pdf-invoices-packing-slips' ),
		'remove_button_text'   => __( 'Remove image', 'woocommerce-pdf-invoices-packing-slips' ),
	) );
	?>
</div>