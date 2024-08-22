<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$all_plugins = get_plugins();
?>
<style>
	#wpo-wcpdf-settings { display: none; }
</style>

<div class="upgrade-table-description">
	<h1><?php esc_html_e( 'Wait, there is more...', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
	<p>
		<span><?php esc_html_e( 'A quick overview of the features our PDF Invoices & Packing Slips extensions have to offer.', 'woocommerce-pdf-invoices-packing-slips' ); ?><span>
		<span><?php printf( '%s: %s', esc_html_e( 'If you have any questions feel free to send us an email at', 'woocommerce-pdf-invoices-packing-slips' ), '<a href="mailto:support@wpovernight.com">support@wpovernight.com</a>' ); ?><span>
	</p>
</div>

<table id="upgrade-table">
	<tr>
		<th class="first" align="left">&nbsp;</th>
		<th align="left"><?php esc_html_e( 'Professional', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left"><?php esc_html_e( 'Bundle', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left" class="last">&nbsp;</td>
	</tr>

	<?php
	foreach ( $features as $feature ) {
		echo '<tr><td class="first feature-label">' . $feature['label'];
		echo ! empty( $feature['description'] ) ? '<br><span class="description">' . $feature['description'] . '</span></td>' : '</td>';
		foreach ( ['pro', 'bundle'] as $extension ) {
			echo in_array( $extension, $feature['extensions'] ) ? '<td><span class="feature-available"></span></td>' : '<td>-</td>';
		}
		echo '<td align="left" class="last">&nbsp;</td></tr>';
	}
	?>
	<tr class="upgrade-links">
		<td class="first" align="left">&nbsp;</td>
		<?php
			$extensions_disabled = [];
			$extensions_enabled  = [];
			$extension_columns   = [];

			// pro, templates & bundle columns
			foreach ( $extension_license_infos as $extension => $info ) {
				// enabled
				if ( WPO_WCPDF()->settings->upgrade->extension_is_enabled( $extension ) ) {
					$extensions_enabled[] = $extension;

					$title = __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' );
					if ( empty( $info['status'] ) || $info['status'] != 'valid' ) {
						$subtitle = sprintf(
							/* translators: learn more link */
							__( 'License not yet activated: %s', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/general/installing-wp-overnight-plugins/#activating-your-license" target="_blank">'.__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' ).'</a>'
						);
					} else {
						$subtitle = '';
					}

					$extension_columns[$extension] = sprintf(
						'<td align="left"><h4>%s</h4><p>%s</p></td>',
						$title,
						$subtitle
					);

				// disabled (includes bundle)
				} else {
					$extensions_disabled[] = $extension;
					if ( $info['url'] == 'is_bundled' ) { // extension license is bundled, no need to buy
						$extension_columns[$extension] = '<td align="left">&nbsp;</td>';
					} else {
						$extension_columns[$extension] = sprintf(
							'<td align="left"><a class="upgrade_button" href="%s" target="_blank">%s</a></td>',
							esc_url_raw( $info['url'] ),
							__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' )
						);
					}
				}
			}

			// maybe disable 1 extension or bundle column
			foreach ( $extensions_disabled as $extension_disabled ) {
				if ( ( count( $extensions_disabled ) < 3 && $extension_disabled != 'bundle' ) || ( count( $extensions_disabled ) == 1 && $extension_disabled == 'bundle' ) ) {
					$extension_columns[$extension_disabled] = '<td align="left">&nbsp;</td>';
				}
			}

			foreach ( $extension_columns as $column ) {
				echo $column;
			}

		?>
		<td align="left" class="last">&nbsp;</td>
	</tr>
</table>

<div class="upgrade-table-description">
	<h1><?php esc_html_e( 'You might also like...', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
</div>

<div class="card-container">
	<div class="card <?php echo isset( $all_plugins['wc-reminder-emails/wc-reminder-emails.php'] ) ? 'installed last-item' : ''; ?>">
		<div class="card-content">
			<img src="<?php echo WPO_WCPDF()->plugin_url().'/assets/images/Smart-Reminder-Emails-Logo-400x400.png'; ?>" alt="WooCommerce Smart Reminder Emails Logo">
			<h5><?php esc_html_e( 'WooCommerce Smart Reminder Emails', 'woocommerce-pdf-invoices-packing-slips' ); ?></h5>
			<p><?php esc_html_e( 'Automatically schedule and send Reminder Emails for WooCommerce orders.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<a class="button" target="_blank" href="https://wpovernight.com/downloads/woocommerce-reminder-emails-payment-reminders/">Buy now</a>
		</div>
		<?php echo isset( $all_plugins['wc-reminder-emails/wc-reminder-emails.php'] ) ? '<span class="installed">' . __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' ) . '</span>' : ''; ?>
	</div>

	<div class="card <?php echo isset( $all_plugins['woocommerce-address-labels/woocommerce-address-labels.php'] ) ? 'installed last-item' : ''; ?>">
		<div class="card-content">
			<img src="<?php echo WPO_WCPDF()->plugin_url().'/assets/images/Print-Address-Labels-Logo-400x400.png'; ?>" alt="WooCommerce Smart Reminder Emails Logo">
			<h5><?php esc_html_e( 'WooCommerce Print Address Labels', 'woocommerce-pdf-invoices-packing-slips' ); ?></h5>
			<p><?php esc_html_e( 'Print out address labels for selected orders straight from WooCommerce.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<a class="button" target="_blank" href="https://wpovernight.com/downloads/woocommerce-print-address-labels/">Buy now</a>
		</div>	
		<?php echo isset( $all_plugins['woocommerce-address-labels/woocommerce-address-labels.php'] ) ? '<span class="installed">' . __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' ) . '</span>' : ''; ?>
	</div>

	<div class="card <?php echo isset( $all_plugins['woocommerce-printnode/print-orders.php'] ) ? 'installed last-item' : ''; ?>">
		<div class="card-content">
			<img src="<?php echo WPO_WCPDF()->plugin_url().'/assets/images/WC-printnode-logo-400x400.png'; ?>" alt="WooCommerce Smart Reminder Emails Logo">
			<h5><?php esc_html_e( 'WooCommerce Automatic Printing - PrintNode', 'woocommerce-pdf-invoices-packing-slips' ); ?></h5>
			<p><?php esc_html_e( 'A plugin to automatically print completed orders via PrintNode.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<a class="button" target="_blank" href="https://www.simbahosting.co.uk/s3/product/woocommerce-printnode-automatic-order-printing/">Buy now</a>
		</div>	
		<?php echo isset( $all_plugins['woocommerce-printnode/print-orders.php'] ) ? '<span class="installed">' . __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' ) . '</span>' : ''; ?>
	</div>
</div>