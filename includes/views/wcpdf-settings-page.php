<?php defined( 'ABSPATH' ) or exit; ?>
<?php
$review_url = 'https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/#new-post';
$review_link = sprintf( '<a href="%s">★★★★★</a>', $review_url );
$review_invitation = sprintf(
	/* translators: ★★★★★ (5-star) */
	__( 'If you like <strong>WooCommerce PDF Invoices & Packing Slips</strong> please leave us a %s rating. A huge thank you in advance!', 'woocommerce-pdf-invoices-packing-slips' ),
	$review_link
);
?>
<script type="text/javascript">
	jQuery( function( $ ) {
		$("#footer-thankyou").html('<?php echo wp_kses_post( $review_invitation ); ?>');
	});
</script>
<div class="wrap">
	<div class="icon32" id="icon-options-general"><br /></div>
	<h2><?php esc_html_e( 'WooCommerce PDF Invoices', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<h2 class="nav-tab-wrapper">
	<?php
	foreach ( $settings_tabs as $tab_slug => $tab_data ) {
		$tab_title = is_array( $tab_data ) ? $tab_data['title'] : $tab_data;
		$tab_link = esc_url("?page=wpo_wcpdf_options_page&tab={$tab_slug}");
		printf('<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', $tab_link, esc_attr( $tab_slug ), ( ( $active_tab == $tab_slug ) ? 'nav-tab-active' : '' ), esc_html( $tab_title ) );
	}
	?>
	</h2>

	<?php
	do_action( 'wpo_wcpdf_before_settings_page', $active_tab, $active_section );

	// save or check option to hide extensions ad
	if ( isset( $_GET['wpo_wcpdf_hide_extensions_ad'] ) ) {
		update_option( 'wpo_wcpdf_hide_extensions_ad', true );
		$hide_ad = true;
	} else {
		$hide_ad = get_option( 'wpo_wcpdf_hide_extensions_ad' );
	}
	
	if ( ! $hide_ad && ! ( class_exists( 'WooCommerce_PDF_IPS_Pro' ) && class_exists( 'WooCommerce_PDF_IPS_Templates' ) && class_exists( 'WooCommerce_Ext_PrintOrders' ) ) ) {
		include('wcpdf-extensions.php');
	}

	$preview_states = isset( $settings_tabs[$active_tab]['preview_states'] ) ? $settings_tabs[$active_tab]['preview_states'] : 1;
	$preview_state = $preview_states == 3 ? 'sidebar' : 'closed';
	?>
	<div id="wpo-wcpdf-preview-wrapper" data-preview-states="<?php echo $preview_states; ?>" data-preview-state="<?php echo $preview_state; ?>">

		<div class="sidebar">
			<form method="post" action="options.php" id="wpo-wcpdf-settings" class="<?php echo "{$active_tab} {$active_section}"; ?>">
				<?php
					do_action( 'wpo_wcpdf_before_settings', $active_tab, $active_section );
					if ( has_action( 'wpo_wcpdf_settings_output_'.$active_tab ) ) {
						do_action( 'wpo_wcpdf_settings_output_'.$active_tab, $active_section );
					} else {
						// legacy settings
						settings_fields( "wpo_wcpdf_{$active_tab}_settings" );
						do_settings_sections( "wpo_wcpdf_{$active_tab}_settings" );

						submit_button();
					}
					do_action( 'wpo_wcpdf_after_settings', $active_tab, $active_section );
				?>
			</form>
			<div class="slider slide-left">&#9664;</div>
			<?php do_action( 'wpo_wcpdf_after_settings_page', $active_tab, $active_section ); ?>
		</div>

		<div class="preview-document">
			<?php
				$last_order_id = wc_get_orders( array(
					'limit'  => 1,
					'return' => 'ids',
					'type'   => 'shop_order',
				) );
				$order_id      = ! empty( $last_order_id ) ? reset( $last_order_id ) : false;
			?>
			<div class="slider slide-right">&#9654;</div>
			<div class="preview-data">
				<input type="number" name="preview-order-number" id="preview-order-number">
				<div class="preview-order-search-wrapper">
					<input type="text" name="preview-order-search" id="preview-order-search" placeholder="<?php esc_attr_e( 'Type...', 'woocommerce-pdf-invoices-packing-slips' ); ?>" data-nonce="<?= wp_create_nonce( 'wpo_wcpdf_preview' ); ?>">
					<img class="preview-order-search-clear" src="<?php echo WPO_WCPDF()->plugin_url().'/assets/images/reset-input.svg'; ?>" alt="<?php esc_html_e( 'Clear search text', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				</div>
				<p class="last-order"><?php esc_html_e( 'Currently showing last order', 'woocommerce-pdf-invoices-packing-slips' ); ?><span class="arrow-down">&#9660;</span></p>
				<p class="order-number"><?php esc_html_e( 'Currently showing order number', 'woocommerce-pdf-invoices-packing-slips' ); ?><span class="arrow-down">&#9660;</span></p>
				<p class="order-search"><?php esc_html_e( 'Currently showing order search results', 'woocommerce-pdf-invoices-packing-slips' ); ?><span class="arrow-down">&#9660;</span></p>
				<ul>
					<li class="last-order"><?php esc_html_e( 'Show last order', 'woocommerce-pdf-invoices-packing-slips' ); ?></li>
					<li class="order-number"><?php esc_html_e( 'Show specific order number', 'woocommerce-pdf-invoices-packing-slips' ); ?></li>
					<li class="order-search"><?php esc_html_e( 'Search for an order', 'woocommerce-pdf-invoices-packing-slips' ); ?></li>
				</ul>
				<div id="preview-order-search-results"><!-- Results populated with JS --></div>
			</div>
			<script src="<?= WPO_WCPDF()->plugin_url() ?>/assets/js/pdf_js/pdf.js"></script>
			<div class="preview" data-order_id="<?= $order_id; ?>" data-nonce="<?= wp_create_nonce( 'wpo_wcpdf_preview' ); ?>" data-no_order="<?= __( 'No WooCommerce orders found! Please consider adding your first order to see this preview.', 'woocommerce-pdf-invoices-packing-slips' ); ?>" data-save_settings="<?= __( 'Please save your settings to preview the changes!', 'woocommerce-pdf-invoices-packing-slips' ); ?>"></div>
		</div>

	</div>

</div>
