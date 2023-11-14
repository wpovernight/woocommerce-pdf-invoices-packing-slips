<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	
	$now              = new \DateTime();
	$end              = new \DateTime( '2023-11-29' ); // Black Friday 2023!
	$bundle_installed = class_exists( 'WooCommerce_PDF_IPS_Pro' ) && class_exists( 'WPO_WCPDF_Templates' );
	$hide_ad          = false;
	
	if ( isset( $_REQUEST['wpo_wcpdf_hide_promo_ad'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
		// validate nonce
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hide_promo_ad_nonce' ) ) {
			wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_promo_ad' );
		} else {
			update_option( 'wpo_wcpdf_hide_promo_ad', true );
			$hide_ad = true;
		}
	} else {
		$hide_ad = get_option( 'wpo_wcpdf_hide_promo_ad' );
	}
	
	if ( $now->getTimestamp() < $end->getTimestamp() && ! $bundle_installed && ! $hide_ad ) :
?>


<div class="wcpdf-promo-ad">
	<img src="<?php echo esc_url( WPO_WCPDF()->plugin_url() . '/assets/images/wpo-helper.png' ); ?>" class="wpo-helper">
	<h3><?php esc_html_e( 'Black Friday!', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
	<ul class="wcpdf-extensions">
		<li>
			<?php //TODO ?>
		</li>
	</ul>
	<?php printf( '<a href="%s" style="display:inline-block; margin-top: 10px;">%s</a>', esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_promo_ad', 'true' ), 'hide_promo_ad_nonce' ) ), esc_html__( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ) ); ?>
</div>
<?php endif; ?>