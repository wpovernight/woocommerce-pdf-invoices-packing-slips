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
		<th align="left" class="pro"><?php esc_html_e( 'Professional', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left" class="templates"><?php esc_html_e( 'Premium Templates', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left" class="bundle"><?php esc_html_e( 'Plus Bundle', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		<th align="left" class="last">&nbsp;</td>
	</tr>

	<?php
	foreach ( $features as $feature ) {
		echo '<tr><td class="first feature-label">' . $feature['label'];
		echo ! empty( $feature['description'] ) ? '<br><span class="description">' . $feature['description'] . '</span></td>' : '</td>';
		foreach ( ['pro', 'templates', 'bundle'] as $extension ) {
			echo in_array( $extension, $feature['extensions'] ) ? '<td class="' . $extension . '"><span class="feature-available"></span></td>' : '<td class="' . $extension . '">-</td>';
		}
		echo '<td align="left" class="last">&nbsp;</td></tr>';
	}
	?>
	<tr class="upgrade-links">
		<td class="first" align="left">&nbsp;</td>
		<?php
			$default_extensions  = array( 'pro', 'templates' );
			$extensions_enabled  = array();
			$extensions_disabled = array();
			$extension_columns   = array();
			
			// check if pro and templates are enabled
			foreach ( $default_extensions as $extension ) {
				$extension_is_enabled = WPO_WCPDF()->settings->upgrade->extension_is_enabled( $extension );
				
				if ( $extension_is_enabled ) {
					$extensions_enabled[]  = $extension;
				} else {
					$extensions_disabled[] = $extension;
				}
			}

			// pro, templates & bundle columns
			foreach ( $extension_license_infos as $extension => $info ) {
				$extension_is_enabled = in_array( $extension, $extensions_enabled );
				$bundle_is_enabled    = array() === array_diff( array( 'pro', 'templates' ), $extensions_enabled );
				
				// enabled
				if ( $extension_is_enabled || $bundle_is_enabled ) {
					$title = __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' );

					// if the bundle is enabled, display only "Bundle" as installed
					if ( $bundle_is_enabled && 'bundle' !== $extension ) {
						$title = '';
					}
					
					if ( ( empty( $info['status'] ) || 'valid' !== $info['status'] ) && 'bundle' !== $extension ) {
						$subtitle = sprintf(
							/* translators: learn more link */
							__( 'License not yet activated: %s', 'woocommerce-pdf-invoices-packing-slips' ),
							'<a href="https://docs.wpovernight.com/general/installing-wp-overnight-plugins/#activating-your-license" target="_blank">'.__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' ).'</a>'
						);
					} else {
						$subtitle = '';
					}

					$extension_columns[ $extension ] = sprintf(
						'<td class="%s" align="left"><h4>%s</h4><p>%s</p></td>',
						$extension,
						$title,
						$subtitle
					);

				// disabled
				} else {
					// add bundle to disabled extensions
					if ( 'bundle' === $extension && ! in_array( $extension, $extensions_disabled ) ) {
						$extensions_disabled[] = $extension;
					}
					
					$extension_columns[ $extension ] = sprintf(
						'<td class="' . $extension . '" align="left"><a class="upgrade_button" href="%s" target="_blank">%s</a></td>',
						esc_url_raw( $info['url'] ),
						__( 'Upgrade now', 'woocommerce-pdf-invoices-packing-slips' )
					);
				}
			}

			$styles = '';

			switch ( implode( ',', $extensions_enabled ) . '-' . implode( ',', $extensions_disabled ) ) {
				case 'pro-templates,bundle':
					$styles .= '#upgrade-table .templates { display: none; }';
					break;
				case 'templates-pro,bundle':
					$styles .= '#upgrade-table .pro { display: none; }';
					break;
				case 'pro,templates-':
					$styles .= '#upgrade-table .templates { display: none; }';
					break;
				case 'pro,templates-bundle':
					$styles .= '#upgrade-table .templates { display: none; }';
					break;
				case 'pro,templates,bundle-':
					$styles .= '#upgrade-table .templates { display: none; }';
					break;
				case '-pro,templates,bundle':
					$styles .= '#upgrade-table .templates { display: none; }';
					break;
			}

			echo '<style>' . $styles . '</style>';

			foreach ( $extension_columns as $column ) {
				echo $column;
			}

		?>
		<td align="left" class="last">&nbsp;</td>
	</tr>
</table>

<div id="plugin-recommendations" class="upgrade-table-description">
	<h1><?php esc_html_e( 'You might also like these plugins...', 'woocommerce-pdf-invoices-packing-slips' ); ?></h1>
	<?php
	if ( count( array_column( $sorted_plugin_recommendations, 'installed' ) ) === count( $sorted_plugin_recommendations ) ) {
		printf( '<p>%s</p>', __( 'Wow! It looks like you own all of our recommendations. Check out our shop for even more plugins.', 'woocommerce-pdf-invoices-packing-slips' ) );
		printf( '<a class="upgrade_button" target="_blank" href="%s">%s</a>', 'https://wpovernight.com/shop/', __( 'Visit shop', 'woocommerce-pdf-invoices-packing-slips' ) );
	}
	?>
	<div class="card-container">
	<?php
		foreach ( $sorted_plugin_recommendations as $plugin ) {
			?>
			<div class="<?php echo isset( $plugin['installed'] ) ? 'recommendation-card currently-installed' : 'recommendation-card'; ?>">
				<img src="<?php echo $plugin['thumbnail']; ?>" alt="<?php echo $plugin['title']; ?>">
				<div class="card-content">
					<h5><?php echo $plugin['title']; ?></h5>
					<p><?php echo $plugin['description']; ?></p>
					<?php 
					if ( isset( $plugin['installed'] ) ) {
						printf( '<span class="currently-installed">%s</span>', __( 'Currently installed', 'woocommerce-pdf-invoices-packing-slips' ) );
					} else {
						printf( '<a class="upgrade_button" target="_blank" href="%s">%s</a>', $plugin['url'], __( 'Buy now', 'woocommerce-pdf-invoices-packing-slips' ) );
					}
					?>
				</div>
			</div>
			<?php
		}
	?>
	</div>
</div>