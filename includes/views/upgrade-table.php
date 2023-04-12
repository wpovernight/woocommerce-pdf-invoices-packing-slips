<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

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
		<th align="left"><?php esc_html_e( 'Premium Templates', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left"><?php esc_html_e( 'Bundle', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left" class="last">&nbsp;</td>
	</tr>

	<?php
	foreach ( $features as $feature ) {
		echo '<tr><td class="first feature-label">' . $feature['label'];
		echo ! empty( $feature['description'] ) ? '<br><span class="description">' . $feature['description'] . '</span></td>' : '</td>';
		foreach ( ['pro', 'templates', 'bundle'] as $extension ) {
			echo in_array( $extension, $feature['extensions'] ) ? '<td><span class="feature-available"></span></td>' : '<td>-</td>';
		}
		echo '<td align="left" class="last">&nbsp;</td></tr>';
	}
	?>
	<tr class="upgrade-links">
		<td class="first" align="left">&nbsp;</td>
		<?php
		printf(
			'<td><a href="%s" target="_blank">%s</a></td>', 
			'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/', 
			__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' ) 
		);
		printf(
			'<td><a href="%s" target="_blank">%s</a></td>', 
			'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/', 
			__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' ) 
		);
		printf(
			'<td><a href="%s" target="_blank">%s</a></td>', 
			'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/', 
			__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' ) 
		);
		?>
		<td align="left" class="last">&nbsp;</td>
	</tr>	
</table>