<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$green_color_code  = '#68de7c';
$red_color_code    = '#ffabaf';
$yellow_color_code = '#f2d675';

?>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<caption><?php esc_html_e( 'Plugins Version', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
	<thead>
		<tr>
			<th align="left"><?php esc_html_e( 'Plugin Name', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Version', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="title">PDF Invoices & Packing Slips for WooCommerce</td>
			<td><?php esc_html_e( WPO_WCPDF()->version ); ?></td>
			<td style="background-color:<?php echo esc_attr( $green_color_code ); ?>; color:black;" ><?php esc_html_e( 'Active', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
		</tr>
		<?php
		if ( ! empty( $premium_plugins ) ) {
			foreach ( $premium_plugins as $premium_plugin ) {
				$style = $premium_plugin['is_active'] ? "background-color:{$green_color_code}; color:black;" : "background-color:{$red_color_code}; color:black;";
				$status = $premium_plugin['is_active'] ? esc_html__( 'Active', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'Inactive', 'woocommerce-pdf-invoices-packing-slips' );
				?>
				<tr>
					<td class="title"><?php echo esc_html( $premium_plugin['name'] ); ?></td>
					<td><?php echo esc_html( $premium_plugin['version'] ); ?></td>
					<td style="<?php echo esc_attr( $style ); ?>"><?php echo wp_kses_post( $status ); ?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<caption><?php esc_html_e( 'System Configuration', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
	<thead>
		<tr>
			<th align="left"><?php esc_html_e( 'Configuration', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Required', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Present', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$server_configs = apply_filters( 'wpo_wcpdf_advanced_status_server_configs', $server_configs );
			foreach ( $server_configs as $label => $server_config ) :
				$color = 'black';

				if ( $server_config['result'] ) {
					$background = $green_color_code;
				} elseif ( isset( $server_config['fallback'] ) ) {
					$background = $yellow_color_code;
				} else {
					$background = $red_color_code;
				}
				?>
				<tr>
					<td class="title"><?php echo esc_html( $label ); ?></td>
					<td><?php echo wp_kses_post( $server_config['required'] === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : $server_config['required'] ); ?></td>
					<td style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>">
						<?php
						if ( ! empty( $server_config['value'] ) ) {
							echo wp_kses_post( $server_config['value'] );
						}
						if ( $server_config['result'] && ! $server_config['value'] ) {
							echo esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' );
						}
						if ( ! $server_config['result'] ) {
							if ( isset( $server_config['fallback'] ) ) {
								printf( '<div>%s. %s</div>', esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ), esc_html( $server_config['fallback'] ) );
							} elseif ( isset( $server_config['failure'] ) ) {
								printf( '<div>%s</div>', wp_kses_post( $server_config['failure'] ) );
							} else {
								printf( '<div>%s</div>', esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) );
							}
						}
						?>
					</td>
				</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php do_action( 'wpo_wcpdf_after_system_status_table' ); ?>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<caption><?php esc_html_e( 'Documents Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
	<thead>
		<tr>
			<th align="left"><?php esc_html_e( 'Document', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Enabled', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ( WPO_WCPDF()->documents->get_documents( 'all' ) as $document ) :
				$is_reset_enabled = isset( $document->settings['reset_number_yearly'] ) ? true : false;
				$is_enabled       = $document->is_enabled() ? true : false;
		?>
		<tr>
			<td class="title"><?php echo esc_html( $document->get_title() ); ?></td>
			<td style="<?php echo $is_enabled ? "background-color:{$green_color_code}; color:black;" : "background-color:{$red_color_code}; color:black;" ?>"><?php echo wp_kses_post( $is_enabled === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></td>
			<td style="<?php echo $is_reset_enabled ? "background-color:{$green_color_code}; color:black;" : "background-color:{$red_color_code}; color:black;" ?>"><?php echo wp_kses_post( $is_reset_enabled === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<?php
		if ( ! empty( $yearly_reset_schedule ) ) :
			$color = 'black';

			if ( $yearly_reset_schedule['result'] ) {
				$background = $green_color_code;
			} else {
				$background = $red_color_code;
			}
		?>
		<tfoot>
			<tr>
				<td class="title"><strong><?php echo esc_html( __( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></strong></td>
				<td colspan="2" style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>">
					<?php
						echo wp_kses_post( $yearly_reset_schedule['value'] );
						if ( $yearly_reset_schedule['result'] && ! $yearly_reset_schedule['value'] ) {
							echo esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' );
						}
					?>
				</td>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>
<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<caption><?php esc_html_e( 'Write Permissions', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
	<thead>
		<tr>
			<th align="left"><?php esc_html_e( 'Directory', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Path', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ( $write_permissions as $write_permission ) {
				$color = 'black';

				if ( $write_permission['status'] === 'ok' ) {
					$background = $green_color_code;
				} else {
					$background = $red_color_code;
				}
				?>
		<tr>
			<td><?php echo wp_kses_post( $write_permission['description'] ); ?></td>
			<td><?php echo ! empty( $write_permission['value'] ) ? wp_kses_post( str_replace( array( '/', '\\' ), array( '/<wbr>', '\\<wbr>' ), $write_permission['value'] ) ) : ''; ?></td>
			<td style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>"><?php echo wp_kses_post( $write_permission['status_message'] ); ?></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				<?php
					printf(
						/* translators: 1,2. directory paths, 3. UPLOADS, 4. wpo_wcpdf_tmp_path, 5. attachments, 6. dompdf, 7. fonts */
						esc_html__( 'The central temp folder is %1$s. By default, this folder is created in the WordPress uploads folder (%2$s), which can be defined by setting %3$s in wp-config.php. Alternatively, you can control the specific folder for PDF invoices by using the %4$s filter. Make sure this folder is writable and that the subfolders %5$s, %6$s and %7$s are present (these will be created by the plugin if the central temp folder is writable).', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>' . wpo_wcpdf_escape_url_path_or_base64( WPO_WCPDF()->main->get_tmp_path() ) . '</code>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'<code>' . wpo_wcpdf_escape_url_path_or_base64( trailingslashit( wp_upload_dir()['basedir'] ) ) . '</code>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'<code>UPLOADS</code>',
						'<code>wpo_wcpdf_tmp_path</code>',
						'<code>attachments</code>',
						'<code>dompdf</code>',
						'<code>fonts</code>'
					);
				?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<?php
					printf(
						/* translators: directory path */
						esc_html__( 'If the temporary folders were not automatically created by the plugin, verify that all the font files (from %s) are copied to the fonts folder. Normally, this is fully automated, but if your server has strict security settings, this automated copying may have been prohibited. In that case, you also need to make sure these folders get synchronized on plugin updates!', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>' . wpo_wcpdf_escape_url_path_or_base64( WPO_WCPDF()->plugin_path() . '/vendor/dompdf/dompdf/lib/fonts/' ) . '</code>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
				?>
			</td>
		</tr>
	</tfoot>
</table>
