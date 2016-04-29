<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.extensions .more').hide();

	jQuery('.extensions > li').click(function() {
		jQuery(this).toggleClass('expanded');
		jQuery(this).find('.more').slideToggle();
	});
});
</script>

<div class="wcpdf-extensions-ad">

	<img src="<?php echo WooCommerce_PDF_Invoices::$plugin_url . 'images/wpo-helper.png'; ?>" class="wpo-helper">
	<h3><?php _e( 'Check out these premium extensions!', 'wpo_wcpdf' ); ?></h3>
	<i>(<?php _e( 'click items to read more', 'wpo_wcpdf' ); ?>)</i>
	<ul class="extensions">

		<?php
		if (!class_exists('WooCommerce_PDF_IPS_Pro')) {
			?>
			<li>
				<?php _e('Go Pro: Proforma invoices, credit notes (=refunds) & more!', 'wpo_wcpdf')?>
				<div class="more" style="display:none;">
				<?php _e( 'Supercharge WooCommerce PDF Invoices & Packing Slips with the following features:', 'wpo_wcpdf' ); ?>
				<ul>
					<li><?php _e( 'Email/print/download <b>PDF Credit Notes & Proforma invoices</b>', 'wpo_wcpdf' ); ?></li>
					<li><?php _e( 'Send out a separate <b>notification email</b> with (or without) PDF invoices/packing slips, for example to a drop-shipper or a supplier.', 'wpo_wcpdf' ); ?></li>
					<li><?php _e( 'Attach <b>up to 3 static files</b> (for example a terms & conditions document) to the WooCommerce emails of your choice.', 'wpo_wcpdf' ); ?></li>
					<li><?php _e( 'Use <b>separate numbering systems</b> and/or format for proforma invoices and credit notes or utilize the main invoice numbering system', 'wpo_wcpdf' ); ?></li>
					<li><?php _e( '<b>Customize</b> the <b>shipping & billing address</b> format to include additional custom fields, font sizes etc. without the need to create a custom template.', 'wpo_wcpdf' ); ?></li>
					<li><?php _e( 'Use the plugin in multilingual <b>WPML</b> setups', 'wpo_wcpdf' ); ?></li>
				</ul>
				<a href="https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/" target="_blank"><?php _e("Get WooCommerce PDF Invoices & Packing Slips Professional!", 'wpo_wcpdf'); ?></a>
			</li>
		<?php } ?>

		<?php
		if (!class_exists('WooCommerce_PDF_IPS_Dropbox')) {
			?>
			<li>
				<?php _e('Upload all invoices automatically to your dropbox', 'wpo_wcpdf')?>
				<div class="more" style="display:none;">
				<table>
					<tr>
						<td><img src="<?php echo WooCommerce_PDF_Invoices::$plugin_url . 'images/dropbox_logo.png'; ?>" class="dropbox-logo"></td>
						<td>
						<?php _e( 'This extension conveniently uploads all the invoices (and other pdf documents from the professional extension) that are emailed to your customers to Dropbox. The best way to keep your invoice administration up to date!', 'wpo_wcpdf' ); ?><br/>
						<a href="https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-dropbox/" target="_blank"><?php _e("Get WooCommerce PDF Invoices & Packing Slips to dropbox!", 'wpo_wcpdf'); ?></a>
						</td>
					</tr>
				</table>
				</div>
			</li>
		<?php } ?>

		<?php
		if (!class_exists('WooCommerce_Ext_PrintOrders')) {
			?>
			<li>
				<?php _e('Automatically send new orders or packing slips to your printer, as soon as the customer orders!', 'wpo_wcpdf')?>
				<div class="more" style="display:none;">
				<table>
					<tr>
						<td><img src="<?php echo WooCommerce_PDF_Invoices::$plugin_url . 'images/cloud-print.png'; ?>" class="cloud-logo"></td>
						<td>
						<?php _e( 'Check out the WooCommerce Automatic Order Printing extension from our partners at Simba Hosting', 'wpo_wcpdf' ); ?><br/>
						<a href="https://www.simbahosting.co.uk/s3/product/woocommerce-automatic-order-printing/?affiliates=2" target="_blank"><?php _e("WooCommerce Automatic Order Printing", 'wpo_wcpdf'); ?></a>
						</td>
					</tr>
				</table>
				</div>
			</li>
		<?php } ?>

		<?php
		if (!class_exists('WooCommerce_PDF_IPS_Templates')) {
			$template_link = '<a href="https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/" target="_blank">wpovernight.com</a>';
			$email_link = '<a href="mailto:support@wpovernight.com">support@wpovernight.com</a>'
			?>
			<li>
				<?php _e('Advanced, customizable templates', 'wpo_wcpdf')?>
				<div class="more" style="display:none;">
				<ul>
					<li><?php _e( 'Completely customize the invoice contents (prices, taxes, thumbnails) to your needs with a drag & drop customizer', 'wpo_wcpdf' ); ?></li>
					<li><?php _e( 'Two extra stylish premade templates (Modern & Business)', 'wpo_wcpdf' ); ?></li>
					<li><?php printf( __("Check out the Premium PDF Invoice & Packing Slips templates at %s.", 'wpo_wcpdf'), $template_link );?></li>
					<li><?php printf( __("For custom templates, contact us at %s.", 'wpo_wcpdf'), $email_link );?></li>
				</ul>
				</div>
			</li>
		<?php } ?>
	</ul>
	<?php
	// link to hide message when one of the premium extensions is installed
	if ( class_exists('WooCommerce_PDF_IPS_Pro') || class_exists('WooCommerce_PDF_IPS_Dropbox') || class_exists('WooCommerce_PDF_IPS_Templates') || class_exists('WooCommerce_Ext_PrintOrders') ) {
		printf('<a href="%s" style="display:inline-block; margin-top: 10px;">%s</a>', add_query_arg( 'wpo_wcpdf_hide_extensions_ad', 'true' ), __( 'Hide this message', 'wpo_wcpdf' ) );
	}
	?>
</div>