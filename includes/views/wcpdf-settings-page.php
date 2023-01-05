<?php defined( 'ABSPATH' ) or exit; ?>
<?php
$review_url = 'https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/#new-post';
$review_link = sprintf( '<a href="%s">★★★★★</a>', $review_url );
$review_invitation = sprintf(
	/* translators: ★★★★★ (5-star) */
	__( 'If you like <strong>PDF Invoices & Packing Slips for WooCommerce</strong> please leave us a %s rating. A huge thank you in advance!', 'woocommerce-pdf-invoices-packing-slips' ),
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
	<h2><?php esc_html_e( 'PDF Invoices & Packing Slips for WooCommerce', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
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
	if ( isset( $_REQUEST['wpo_wcpdf_hide_extensions_ad'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
		// validate nonce
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hide_extensions_ad_nonce' ) ) {
			wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_extensions_ad' );
			$hide_ad = false;
		} else {
			update_option( 'wpo_wcpdf_hide_extensions_ad', true );
			$hide_ad = true;
		}
	} else {
		$hide_ad = get_option( 'wpo_wcpdf_hide_extensions_ad' );
	}
	
	if ( ! $hide_ad && ! ( class_exists( 'WooCommerce_PDF_IPS_Pro' ) && class_exists( 'WooCommerce_PDF_IPS_Templates' ) && class_exists( 'WooCommerce_Ext_PrintOrders' ) ) ) {
		include('wcpdf-extensions.php');
	}

	$preview_states = isset( $settings_tabs[$active_tab]['preview_states'] ) ? $settings_tabs[$active_tab]['preview_states'] : 1;
	$preview_states_lock = $preview_states == 3 ? false : true;
	?>
	<div id="wpo-wcpdf-preview-wrapper" data-preview-states="<?php echo esc_attr( $preview_states ); ?>" data-preview-state="closed" data-from-preview-state="" data-preview-states-lock="<?php echo esc_attr( $preview_states_lock ); ?>">

		<div class="sidebar">
			<form method="post" action="options.php" id="wpo-wcpdf-settings" class="<?php echo esc_attr( "{$active_tab} {$active_section}" ); ?>">
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
			<?php do_action( 'wpo_wcpdf_after_settings_page', $active_tab, $active_section ); ?>
		</div>

		<div class="gutter">
			<div class="slider slide-left"><span class="gutter-arrow arrow-left"></span></div>
			<div class="slider slide-right"><span class="gutter-arrow arrow-right"></span></div>
		</div>

		<div class="preview-document">
			<?php
				$documents     = WPO_WCPDF()->documents->get_documents( 'all' );
				$document_type = 'invoice';

				if ( ! empty( $_REQUEST['section'] ) ) {
					$document_type = sanitize_text_field( $_REQUEST['section'] );
				} elseif ( ! empty( $_REQUEST['preview'] ) ) {
					$document_type = sanitize_text_field( $_REQUEST['preview'] );
				}
			?>
			<div class="preview-data-wrapper">
				<div class="save-settings"><?php submit_button(); ?></div>
				<div class="preview-data preview-order-data">
					<div class="preview-order-search-wrapper">
						<input type="text" name="preview-order-search" id="preview-order-search" placeholder="<?php esc_html_e( 'ID, email or name', 'woocommerce-pdf-invoices-packing-slips' ); ?>" data-nonce="<?= wp_create_nonce( 'wpo_wcpdf_preview' ); ?>">
						<img class="preview-order-search-clear" src="<?php echo WPO_WCPDF()->plugin_url().'/assets/images/reset-input.svg'; ?>" alt="<?php esc_html_e( 'Clear search text', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
					</div>
					<p class="last-order"><?php esc_html_e( 'Currently showing last order', 'woocommerce-pdf-invoices-packing-slips' ); ?><span class="arrow-down">&#9660;</span></p>
					<p class="order-search"><span class="order-search-label"><?php esc_html_e( 'Search for an order', 'woocommerce-pdf-invoices-packing-slips' ); ?></span><span class="arrow-down">&#9660;</span></p>
					<ul>
						<li class="last-order"><?php esc_html_e( 'Show last order', 'woocommerce-pdf-invoices-packing-slips' ); ?></li>
						<li class="order-search"><?php esc_html_e( 'Search for an order', 'woocommerce-pdf-invoices-packing-slips' ); ?></li>
					</ul>
					<div id="preview-order-search-results"><!-- Results populated with JS --></div>
				</div>
				<?php if ( $active_tab != 'documents' ) : ?>
				<div class="preview-data preview-document-type">
					<?php
						if ( $document_type ) {
							$document = WPO_WCPDF()->documents->get_document( sanitize_text_field( $document_type ), null );
							echo '<p class="current"><span class="current-label">'.esc_html( $document->get_title() ).'</span><span class="arrow-down">&#9660;</span></p>';
						} else {
							echo '<p class="current"><span class="current-label">'.__( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' ).'</span><span class="arrow-down">&#9660;</span></p>';
						}
					?>
					<ul class="preview-data-option-list" data-input-name="document_type">
						<?php 
							foreach ( $documents as $document ) {
								/* translators: 1. document type, 2. document title */
								printf( '<li data-value="%1$s">%2$s</li>', $document->get_type(), $document->get_title() );
							}
						?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
			<input type="hidden" name="document_type" data-default="<?php esc_attr_e( $document_type ); ?>" value="<?php esc_attr_e( $document_type ); ?>">
			<input type="hidden" name="order_id" value="">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'wpo_wcpdf_preview' ); ?>">
			<script src="<?php echo WPO_WCPDF()->plugin_url() ?>/assets/js/pdf_js/pdf.js"></script>
			<div class="preview"></div>
		</div>

	</div>

</div>
