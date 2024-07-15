<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}

	$now              = new \DateTime();
	$end              = new \DateTime( '2023-11-29 23:59:59' ); // Black Friday 2023!
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
	<h3><?php esc_html_e( 'Last chance to use your Black Friday discount!', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
	<p>Elevate your customer experience and streamline your operations with one of our powerful extensions.</p>
	<p>Use promocode <strong class="code">blackfriday30</strong> to get a <strong>30% discount</strong> on your upgrade!</p>
	<?php if ( ( isset( $_GET['tab'] ) && $_GET['tab'] !== 'upgrade' ) || ! isset( $_GET['tab'] ) ) : ?>
		<p class="upgrade-tab">Check out the <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=upgrade' ) ); ?>">Upgrade tab</a> for a feature overview of our PDF Invoices & Packing Slips extensions.</p>
	<?php  endif; ?>
	<p class="expiration">Offer ends on November 29.</p>
	<?php printf( '<a href="%s" class="dismiss" style="display:inline-block; margin-top: 10px;">%s</a>', esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_promo_ad', 'true' ), 'hide_promo_ad_nonce' ) ), esc_html__( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ) ); ?>
</div>
<?php endif; ?>
