<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}

	$now              = new \DateTime();
	$end              = new \DateTime( '2026-12-02 23:59:59' ); // Black Friday 2026!
	$hide_ad          = false;

	if ( isset( $_REQUEST['wpo_wcpdf_hide_promo_ad_bfcm_2026'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
		// validate nonce
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'hide_promo_ad_nonce' ) ) {
			wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_promo_ad_bfcm_2026' );
		} else {
			update_option( 'wpo_wcpdf_hide_promo_ad_bfcm_2026', true );
			$hide_ad = true;
		}
	} else {
		$hide_ad = get_option( 'wpo_wcpdf_hide_promo_ad_bfcm_2026' );
	}
	if ( $now->getTimestamp() < $end->getTimestamp() && ! $hide_ad ) :
?>

<div class="wpo-promo" aria-label="<?php esc_attr_e( 'Explore store productivity upgrades', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
	<div class="wpo-promo__content">
		<p class="wpo-promo__eyebrow"><span class="wpo-promo__tag"><?php esc_html_e( 'Black Friday', 'woocommerce-pdf-invoices-packing-slips' ); ?></span> <?php esc_html_e( 'Last chance to save!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<h2 class="wpo-promo__heading"><?php esc_html_e( 'Upgrade your workflow.', 'woocommerce-pdf-invoices-packing-slips' ); ?><br><span><?php esc_html_e( '40% discount.', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></h2>
		<p class="wpo-promo__description"><?php esc_html_e( 'Explore extensions for PDF Invoices & Packing Slips, or browse our other tools to find new ways to streamline your store’s workflow.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<p class="wpo-promo__offer">
			<?php
			printf(
				/* translators: %s: promotional code wrapped in a code element. */
				esc_html__( 'Use code %s at checkout.', 'woocommerce-pdf-invoices-packing-slips' ),
				'<code class="wpo-promo__code">BFCM2026</code>'
			);
			?>
		</p>
		<div class="wpo-promo__actions">
			<?php if ( ( isset( $_GET['tab'] ) && $_GET['tab'] !== 'upgrade' ) || ! isset( $_GET['tab'] ) ) : ?>
				<a class="wpo-promo__cta" href="<?php echo esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=upgrade' ) ); ?>"><?php esc_html_e( 'Explore upgrades', 'woocommerce-pdf-invoices-packing-slips' ); ?> <span aria-hidden="true">↗</span></a>
			<?php endif; ?>
			<span class="wpo-promo__deadline"><?php esc_html_e( 'Offer ends December 2.', 'woocommerce-pdf-invoices-packing-slips' ); ?></span>
		</div>
	</div>
	<div class="wpo-promo__art" aria-hidden="true"><img src="<?php echo esc_url( WPO_WCPDF()->plugin_url() . '/assets/images/e-wout-discount-banner.png' ); ?>" class="wpo-helper"></div>
	<a class="wpo-promo__close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_promo_ad_bfcm_2026', 'true' ), 'hide_promo_ad_nonce' ) ); ?>" aria-label="<?php esc_attr_e( 'Hide upgrade message', 'woocommerce-pdf-invoices-packing-slips' ); ?>"><svg aria-hidden="true" width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="m3 3 8 8M11 3l-8 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></a>
</div>
<?php endif; ?>
