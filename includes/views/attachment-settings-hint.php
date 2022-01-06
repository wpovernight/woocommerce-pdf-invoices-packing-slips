<?php defined( 'ABSPATH' ) or exit; ?>
<?php
$invoice_settings_url = add_query_arg( array(
	'tab' => 'documents',
	'section' => 'invoice',
) );
?>
<style>
.wcpdf-attachment-settings-hint {
	display: inline-block;
	background: #fff;
	border-left: 4px solid #51266b !important;
	-webkit-box-shadow: 0 0 35px -8px rgba(0, 0, 0, 0.12);
	box-shadow: 0 0 35px -8px rgba(0, 0, 0, 0.12);
	padding: 15px;
	margin-top: 15px;
}
</style>
<div class="wcpdf-attachment-settings-hint">
	<?php /* translators: <a> tags */ ?>
	<?php printf( wp_kses_post( __( 'It looks like you haven\'t setup any email attachments yet, check the settings under <b>%1$sDocuments > Invoice%2$s</b>', 'woocommerce-pdf-invoices-packing-slips' ) ), '<a href="'.$invoice_settings_url.'">', '</a>' ); ?>
	<?php printf( '<a href="%s" style="display:block; margin-top:10px;">%s</a>', add_query_arg( 'wpo_wcpdf_hide_attachments_hint', 'true' ), esc_html__( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ) ); ?>
</div>
