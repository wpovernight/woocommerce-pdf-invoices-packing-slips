<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<thead>
		<tr>
			<td colspan="3"><strong><?php esc_html_e( 'System Configuration', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th align="left">&nbsp;</th>
			<th align="left"><?php esc_html_e( 'Required', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Present', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>

		<?php
			$server_configs = apply_filters( 'wpo_wcpdf_advanced_status_server_configs', $server_configs );
			foreach ( $server_configs as $label => $server_config ) :
				if ( $server_config['result'] ) {
					$background = '#68de7c'; // green
					$color      = 'black';
				} elseif ( isset( $server_config['fallback'] ) ) {
					$background = '#f2d675'; // yellow
					$color      = 'black';
				} else {
					$background = '#ffabaf'; // red
					$color      = 'black';
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
	<thead>
		<tr>
			<td colspan="3"><strong><?php esc_html_e( 'Documents status', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th align="left">&nbsp;</th>
			<th align="left"><?php esc_html_e( 'Enabled', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
		<?php
			foreach ( WPO_WCPDF()->documents->get_documents( 'all' ) as $document ) :
				$is_reset_enabled = isset( $document->settings['reset_number_yearly'] ) ? true : false;
				$is_enabled       = $document->is_enabled() ? true : false;
		?>
		<tr>
			<td class="title"><?php echo esc_html( $document->get_title() ); ?></td>
			<td style="<?= $is_enabled ? 'background-color:#68de7c; color:black;' : 'background-color:#ffabaf; color:black;' ?>"><?php echo wp_kses_post( $is_enabled === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></td>
			<td style="<?= $is_reset_enabled ? 'background-color:#68de7c; color:black;' : 'background-color:#ffabaf; color:black;' ?>"><?php echo wp_kses_post( $is_reset_enabled === true ? esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' ) : esc_html__( 'No', 'woocommerce-pdf-invoices-packing-slips' ) ); ?></td>
		</tr>
	</tbody>
	<?php endforeach; ?>
	<?php
		if ( WPO_WCPDF()->settings->maybe_schedule_yearly_reset_numbers() ) :
			if ( function_exists( 'as_get_scheduled_actions' ) ) {
				$scheduled_actions = as_get_scheduled_actions( array(
					'hook'   => 'wpo_wcpdf_schedule_yearly_reset_numbers',
					'status' => \ActionScheduler_Store::STATUS_PENDING,
				) );

				$yearly_reset = array(
					'required' => __( 'Required to reset documents numeration', 'woocommerce-pdf-invoices-packing-slips' ),
					'fallback' => __( 'Yearly reset action not found', 'woocommerce-pdf-invoices-packing-slips' ),
				);

				if ( ! empty( $scheduled_actions ) ) {
					$total_actions = count( $scheduled_actions );
					if ( $total_actions === 1 ) {
						$action      = reset( $scheduled_actions );
						$action_date = is_callable( array( $action->get_schedule(), 'get_date' ) ) ? $action->get_schedule()->get_date() : $action->get_schedule()->get_next( as_get_datetime_object() );
						/* translators: action date */
						$yearly_reset['value']  = sprintf(
							__( 'Scheduled to: %s' ), date( wcpdf_date_format( null, 'yearly_reset_schedule' ),
							$action_date->getTimeStamp() )
						);
						$yearly_reset['result'] = true;
					} else {
						/* translators: total actions */
						$yearly_reset['value']  = sprintf(
							/* translators: total scheduled actions */
							__( 'Only 1 scheduled action should exist, but %s were found', 'woocommerce-pdf-invoices-packing-slips' ),
							$total_actions
						);
						$yearly_reset['result'] = false;
					}
				} else {
					$yearly_reset['value']  = sprintf(
						/* translators: 1. open anchor tag, 2. close anchor tag */
						__( 'Scheduled action not found. Please reschedule it %1$shere%2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="' . esc_url( add_query_arg( 'section', 'tools' ) ) . '" style="color:black; text-decoration:underline;">',
						'</a>'
					);
					$yearly_reset['result'] = false;
				}
			}

			$label = __( 'Yearly reset', 'woocommerce-pdf-invoices-packing-slips' );

			if ( $yearly_reset['result'] ) {
				$background = '#68de7c'; // green
				$color      = 'black';
			} else {
				$background = '#ffabaf'; // red
				$color      = 'black';
			}
	?>
		<tfoot>
			<tr>
				<td class="title"><strong><?php echo esc_html( $label ); ?></strong></td>
				<td colspan="2" style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>">
					<?php
						echo wp_kses_post( $yearly_reset['value'] );
						if ( $yearly_reset['result'] && ! $yearly_reset['value'] ) {
							echo esc_html__( 'Yes', 'woocommerce-pdf-invoices-packing-slips' );
						}
					?>
				</td>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>

<?php
	$status = array(
		'ok'     => __( 'Writable', 'woocommerce-pdf-invoices-packing-slips' ),
		'failed' => __( 'Not writable', 'woocommerce-pdf-invoices-packing-slips' ),
	);

	$permissions = apply_filters( 'wpo_wcpdf_plugin_directories', array(
		'WCPDF_TEMP_DIR' => array (
			'description'    => __( 'Central temporary plugin folder', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => WPO_WCPDF()->main->get_tmp_path(),
			'status'         => is_writable( WPO_WCPDF()->main->get_tmp_path() ) ? 'ok' : 'failed',
			'status_message' => is_writable( WPO_WCPDF()->main->get_tmp_path() ) ? $status['ok'] : $status['failed'],
		),
		'WCPDF_ATTACHMENT_DIR' => array (
			'description'    => __( 'Temporary attachments folder', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => trailingslashit( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ),
			'status'         => is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? 'ok' : 'failed',
			'status_message' => is_writable( WPO_WCPDF()->main->get_tmp_path( 'attachments' ) ) ? $status['ok'] : $status['failed'],
		),
		'DOMPDF_TEMP_DIR' => array (
			'description'    => __( 'Temporary DOMPDF folder', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => trailingslashit(WPO_WCPDF()->main->get_tmp_path( 'dompdf' )),
			'status'         => is_writable(WPO_WCPDF()->main->get_tmp_path( 'dompdf' )) ? 'ok' : 'failed',
			'status_message' => is_writable(WPO_WCPDF()->main->get_tmp_path( 'dompdf' )) ? $status['ok'] : $status['failed'],
		),
		'DOMPDF_FONT_DIR' => array (
			'description'    => __( 'DOMPDF fonts folder (needs to be writable for custom/remote fonts)', 'woocommerce-pdf-invoices-packing-slips' ),
			'value'          => trailingslashit(WPO_WCPDF()->main->get_tmp_path( 'fonts' )),
			'status'         => is_writable(WPO_WCPDF()->main->get_tmp_path( 'fonts' )) ? 'ok' : 'failed',
			'status_message' => is_writable(WPO_WCPDF()->main->get_tmp_path( 'fonts' )) ? $status['ok'] : $status['failed'],
		),
	), $status );

	$upload_dir  = wp_upload_dir();
	$upload_base = trailingslashit( $upload_dir['basedir'] );
?>
<table class="widefat system-status-table" cellspacing="1px" cellpadding="4px" style="width:100%;">
	<thead>
		<tr>
			<td colspan="3"><strong><?php esc_html_e( 'Write Permissions', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th align="left">&nbsp;</th>
			<th align="left"><?php esc_html_e( 'Path', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th align="left"><?php esc_html_e( 'Status', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
		<?php
			foreach ( $permissions as $permission ) {
				if ( $permission['status'] == 'ok' ) {
					$background = '#68de7c'; // green
					$color      = 'black';
				} else {
					$background = '#ffabaf'; // red
					$color      = 'black';
				}
		?>
		<tr>
			<td><?php echo wp_kses_post( $permission['description'] ); ?></td>
			<td><?php echo ! empty( $permission['value'] ) ? str_replace( array('/','\\' ), array('/<wbr>','\\<wbr>' ), wp_kses_post( $permission['value'] ) ) : ''; ?></td>
			<td style="background-color:<?php echo esc_attr( $background ); ?>; color:<?php echo esc_attr( $color ); ?>"><?php echo wp_kses_post( $permission['status_message'] ); ?></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3">
				<?php
					/* translators: 1,2. directory paths, 3. UPLOADS, 4. wpo_wcpdf_tmp_path, 5. attachments, 6. dompdf, 7. fonts */
					printf( esc_attr__( 'The central temp folder is %1$s. By default, this folder is created in the WordPress uploads folder (%2$s), which can be defined by setting %3$s in wp-config.php. Alternatively, you can control the specific folder for PDF invoices by using the %4$s filter. Make sure this folder is writable and that the subfolders %5$s, %6$s and %7$s are present (these will be created by the plugin if the central temp folder is writable).', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>'.WPO_WCPDF()->main->get_tmp_path().'</code>',
						'<code>'.$upload_base.'</code>',
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
					/* translators: directory path */
					printf( esc_attr__('If the temporary folders were not automatically created by the plugin, verify that all the font files (from %s) are copied to the fonts folder. Normally, this is fully automated, but if your server has strict security settings, this automated copying may have been prohibited. In that case, you also need to make sure these folders get synchronized on plugin updates!', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>'.WPO_WCPDF()->plugin_path() . "/vendor/dompdf/dompdf/lib/fonts/".'</code>'
					);
				?>
			</td>
		</tr>
	</tfoot>
</table>
