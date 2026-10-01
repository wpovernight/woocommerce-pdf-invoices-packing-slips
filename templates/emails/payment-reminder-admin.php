<?php
/**
 * @see \WPO\IPS\Compatibility\ThirdPartyPlugins::get_payment_reminder_email_content()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p>
	<?php
	printf(
		/* translators: %s: Order number */
		esc_html__( 'This is to inform you that the payment for Order #%s is still pending beyond the due date.', 'woocommerce-pdf-invoices-packing-slips' ),
		'{order_number}'
	);
	?>
</p>
