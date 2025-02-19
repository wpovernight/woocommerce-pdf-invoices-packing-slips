<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<caption><?php esc_html_e( 'Installed Plugin Versions', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
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
			<td class="valid-status"><?php esc_html_e( 'Active', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
		</tr>
		<?php
		if ( ! empty( $premium_plugins ) ) {
			foreach ( $premium_plugins as $premium_plugin ) {
				$class = $premium_plugin['is_active'] ? 'valid-status' : 'invalid-status';
				$status = $premium_plugin['is_active'] ? esc_html__( 'Active', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'Inactive', 'woocommerce-pdf-invoices-packing-slips' );
				?>
				<tr>
					<td class="title"><?php echo esc_html( $premium_plugin['name'] ); ?></td>
					<td><?php echo esc_html( $premium_plugin['version'] ); ?></td>
					<td class="<?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( $status ); ?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<caption>
		<?php esc_html_e( 'System Configuration', 'woocommerce-pdf-invoices-packing-slips' ); ?>
		<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/server-requirements/" target="_blank" rel="noopener">
			<span class="dashicons dashicons-external"></span>
		</a>
	</caption>
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
				if ( $server_config['result'] ) {
					$class = 'valid-status';
				} elseif ( isset( $server_config['fallback'] ) ) {
					$class = 'warning-status';
				} else {
					$class = 'invalid-status';
				}
				?>
				<tr>
					<td class="title"><?php echo esc_html( $label ); ?></td>
					<td><?php echo wp_kses_post( $server_config['required'] === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : $server_config['required'] ); ?></td>
					<td class="<?php echo esc_attr( $class ); ?>">
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
	<caption><?php esc_html_e( 'Documents\' Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
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
				$is_enabled       = (bool) $document->is_enabled();
				$is_enabled_class = $is_enabled ? 'valid-status' : 'invalid-status';
				$is_enabled_text  = $is_enabled ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' );

				$is_reset_enabled       = isset( $document->settings['reset_number_yearly'] );
				$is_reset_enabled_class = $is_reset_enabled ? 'valid-status' : 'invalid-status';
				$is_reset_enabled_text  = $is_reset_enabled ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' );
				?>
		<tr>
			<td class="title"><?php echo esc_html( $document->get_title() ); ?></td>
			<td class="<?php echo esc_attr( $is_enabled_class ); ?>"><?php echo wp_kses_post( $is_enabled_text ); ?></td>
			<td class="<?php echo esc_attr( $is_reset_enabled_class ); ?>"><?php echo wp_kses_post( $is_reset_enabled_text ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<?php
		if ( ! empty( $yearly_reset_schedule ) ) :
			$class = $yearly_reset_schedule['result'] ? 'valid-status' : 'invalid-status';
		?>
		<tfoot>
			<tr>
				<td class="title"><strong><?php esc_html_e( __( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></strong></td>
				<td colspan="2" class="<?php echo esc_attr( $class ); ?>">
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
	<caption><?php esc_html_e( 'Directory Permissions', 'woocommerce-pdf-invoices-packing-slips' ); ?></caption>
	<thead>
		<tr>
			<th align="left"><?php esc_html_e( 'Directory', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Path', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach ( $directory_permissions as $directory_permission ) {
				$class = $directory_permission['status'] === 'ok' ? 'valid-status' : 'invalid-status';
				?>
		<tr>
			<td><?php echo wp_kses_post( $directory_permission['description'] ); ?></td>
			<td><?php echo ! empty( $directory_permission['value'] ) ? wp_kses_post( str_replace( array( '/', '\\' ), array( '/<wbr>', '\\<wbr>' ), $directory_permission['value'] ) ) : ''; ?></td>
			<td class="<?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( $directory_permission['status_message'] ); ?></td>
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
