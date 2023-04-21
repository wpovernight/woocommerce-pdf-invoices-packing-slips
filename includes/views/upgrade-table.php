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
			$extensions_disabled = [];
			$extension_columns   = [];
			
			// pro & templates columns
			foreach ( $extension_license_infos as $extension => $info ) {
				// enabled
				if ( $info['enabled'] ) {
					$title = __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' );
					if ( ! empty( $info['status'] ) && $info['status'] == 'valid' ) {
						$subtitle = sprintf(
							/* translators: learn more link */
							__( 'License not yet activated: %s', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/general/installing-wp-overnight-plugins/" target="_blank">'.__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' ).'</a>'
						);
					} else {
						$subtitle = __( 'License activated!', 'woocommerce-pdf-invoices-packing-slips' );
					}
					
					$extension_columns[$extension] = sprintf(
						'<td><h4>%s</h4><p>%s</p></td>',
						$title,
						$subtitle
					);
					
				// buy
				} else {
					$extensions_disabled[]         = $extension;
					$extension_columns[$extension] = sprintf(
						'<td><a class="buy_button" href="%s" target="_blank">%s</a></td>', 
						esc_url_raw( $info['url'] ), 
						__( 'Buy now', 'woocommerce-pdf-invoices-packing-slips' )
					);
				}
			}
			
			// bundle column + maybe disable 1 extension column
			if ( count( $extensions_disabled ) == 1 ) {
				$extension_disabled                     = reset( $extensions_disabled );
				$extension_columns[$extension_disabled] = '<td align="left">&nbsp;</td>';
				
				// set bundle to upgrade
				$extension_columns['bundle'] = sprintf(
					'<td><a class="buy_button" href="%s" target="_blank">%s</a></td>', 
					esc_url_raw( $extension_license_infos[$extension_disabled]['upgrade_link'] ), 
					__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' )
				);
			} elseif ( count( $extensions_disabled ) != 0 ) {
				$extension_columns['bundle'] = sprintf(
					'<td><a class="buy_button" href="%s" target="_blank">%s</a></td>', 
					esc_url_raw( 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/' ), 
					__( 'Buy now', 'woocommerce-pdf-invoices-packing-slips' )
				);
			}
			
			foreach ( $extension_columns as $column ) {
				echo $column;
			}
			
		?>
		<td align="left" class="last">&nbsp;</td>
	</tr>	
</table>