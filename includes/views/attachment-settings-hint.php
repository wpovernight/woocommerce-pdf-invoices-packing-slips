<?php defined( 'ABSPATH' ) or exit; ?>
<?php
$invoice_settings_url = esc_url( add_query_arg( array(
	'tab' => 'documents',
	'section' => 'invoice',
) ) );
?>
<div class="wcpdf-attachment-settings-hint notice inline">
	<p>
		<?php /* translators: <a> tags */ ?>
		<?php printf( wp_kses_post( __( 'It looks like you haven\'t setup any email attachments yet, check the settings under <b>%1$sDocuments > Invoice%2$s</b>', 'woocommerce-pdf-invoices-packing-slips' ) ), '<a href="'.$invoice_settings_url.'">', '</a>' ); ?>
		<?php printf( '<a href="%s" style="display:block; margin-top:10px;">%s</a>', esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_attachments_hint', 'true' ), 'hide_attachments_hint_nonce' ) ), esc_html__( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ) ); ?>
	</p>
</div>
