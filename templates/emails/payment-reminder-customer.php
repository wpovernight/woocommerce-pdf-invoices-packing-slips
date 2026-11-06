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
		/* translators: 1: First name, 2: Last name */
		esc_html__( 'Dear %1$s %2$s,', 'woocommerce-pdf-invoices-packing-slips' ),
		'{billing_first_name}',
		'{billing_last_name}'
	);
	?>
</p>
<p>
	<?php
	printf(
		/* translators: %s: Order number */
		esc_html__( 'This is a gentle reminder that payment for your order #%s is still pending.', 'woocommerce-pdf-invoices-packing-slips' ),
		'{order_number}'
	);
	?>
</p>
<p>
	<?php
	printf(
		/* translators: %s: Payment link */
		esc_html__( 'To complete the payment, please use the following link: %s', 'woocommerce-pdf-invoices-packing-slips' ),
		'<a href="{payment_url}">' . esc_html__( 'Pay the order', 'woocommerce-pdf-invoices-packing-slips' ) . '</a>'
	);
	?>
</p>
<p><?php esc_html_e( 'We kindly ask that you process the payment at your earliest convenience.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
<p><?php esc_html_e( 'Best regards', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
