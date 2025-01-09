<?php defined( 'ABSPATH' ) or exit; ?>

<div id="debug-tools">
	<div class="wrapper">
		<?php do_action( 'wpo_wcpdf_before_debug_tools', $this ); ?>
		<!-- generate_random_string -->
		<div class="tool">
			<h4><?php _e( 'Generate random temporary directory', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'For security reasons, it is preferable to use a random name for the temporary directory.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<input type="hidden" name="debug_tool" value="generate_random_string">
				<input type="submit" class="button button-secondary submit" value="<?php _e( 'Generate temporary directory', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<!-- /generate_random_string -->
		<!-- install_fonts -->
		<div class="tool">
			<h4><?php _e( 'Reinstall plugin fonts', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'If you are experiencing issues with rendering fonts there might have been an issue during installation or upgrade.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<input type="hidden" name="debug_tool" value="install_fonts">
				<input type="submit" class="button button-secondary submit" value="<?php _e( 'Reinstall fonts', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<!-- /install_fonts -->
		<!-- reschedule_yearly_reset -->
		<?php if ( ! WPO_WCPDF()->settings->yearly_reset_action_is_scheduled() ) : ?>
		<div class="tool">
			<h4><?php _e( 'Reschedule the yearly reset of the numbering system', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( "You seem to have the yearly reset enabled for one of your documents but the action that performs this isn't scheduled yet.", 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post" id="reschedule_yearly_reset">
				<input type="hidden" name="debug_tool" value="reschedule_yearly_reset">
				<input type="submit" class="button button-secondary submit" value="<?php _e( 'Reschedule yearly reset', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<?php endif; ?>
		<!-- /reschedule_yearly_reset -->
		<!-- clear_tmp -->
		<div class="tool">
			<h4><?php _e( 'Remove temporary files', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Clean up the PDF files stored in the temporary folder (used for email attachments).', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post" id="clear_tmp">
				<input type="hidden" name="debug_tool" value="clear_tmp">
				<input type="submit" class="button button-secondary submit" value="<?php _e( 'Remove temporary files', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<!-- /clear_tmp -->
		<!-- clear_released_semaphore_locks -->
		<div class="tool">
			<h4><?php _e( 'Remove released semaphore locks', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Clean up the released semaphore locks from the database. These locks prevent simultaneous document generation requests, ensuring correct document numbering. Once released, they are safe to remove.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post" id="clear_released_semaphore_locks">
				<input type="hidden" name="debug_tool" value="clear_released_semaphore_locks">
				<input type="submit" class="button button-secondary submit" value="<?php _e( 'Remove released locks', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
			<?php $released_semaphore_locks = \WPO\IPS\Semaphore::count_released_locks(); ?>
			<?php if ( $released_semaphore_locks > 0 ) : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php
							printf(
								/* translators: 1: number of released semaphore locks */
								_n(
									'There is %s released semaphore lock in the database.',
									'There are %s released semaphore locks in the database.',
									$released_semaphore_locks,
									'woocommerce-pdf-invoices-packing-slips'
								),
								'<strong>' . $released_semaphore_locks . '</strong>'
							);
						?>
					</p>
				</div>
			<?php else : ?>
				<div class="notice notice-success inline">
					<p><?php _e( 'There are no released semaphore locks in the database.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				</div>
			<?php endif; ?>
			<?php $cleanup_action = \WPO\IPS\Semaphore::get_cleanup_action();  ?>
			<?php if ( $cleanup_action ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php
							$schedule      = $cleanup_action->get_schedule();
							$current_time  = new \DateTime();
							$next_run_date = $schedule->get_next( $current_time );

							printf(
								/* translators: 1: next run date */
								__( 'The next cleanup action is scheduled to run on %s.', 'woocommerce-pdf-invoices-packing-slips' ),
								'<strong>' . $next_run_date->format( 'Y-m-d H:i:s' ) . '</strong>'
							);
						?>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<!-- /clear_released_semaphore_locks -->
		<!-- clear_released_legacy_semaphore_locks -->
		<?php
			$released_legacy_semaphore_locks = \WPO\IPS\Semaphore::count_released_locks( true );
			if ( $released_legacy_semaphore_locks > 0 ) :
		?>
		<div class="tool">
			<h4><?php _e( 'Remove released legacy semaphore locks', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Clean up the released legacy semaphore locks from the database.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post" id="clear_released_legacy_semaphore_locks">
				<input type="hidden" name="debug_tool" value="clear_released_legacy_semaphore_locks">
				<input type="submit" class="button button-secondary submit" value="<?php _e( 'Remove released legacy locks', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
			<div class="notice notice-warning inline">
				<p>
					<?php
						printf(
							/* translators: 1: number of released legacy semaphore locks */
							_n(
								'There is %s released legacy semaphore lock in the database.',
								'There are %s released legacy semaphore locks in the database.',
								$released_legacy_semaphore_locks,
								'woocommerce-pdf-invoices-packing-slips'
							),
							'<strong>' . $released_legacy_semaphore_locks . '</strong>'
						);
					?>
				</p>
			</div>
		</div>
		<?php endif; ?>
		<!-- /clear_released_legacy_semaphore_locks -->
		<!-- run_wizard -->
		<div class="tool">
			<h4><?php _e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Set up your basic invoice workflow via our Wizard.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo-wcpdf-setup' ) ); ?>" class="button"><?php esc_html_e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
		</div>
		<!-- /run_wizard -->
		<!-- export_settings -->
		<div class="tool">
			<h4><?php _e( 'Export Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Download plugin settings in JSON format to easily export your current setup.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<input type="hidden" name="debug_tool" value="export_settings">
				<fieldset>
					<select name="type" required>
						<?php
							foreach ( $this->get_setting_types() as $type => $name ) {
								?>
								<option value="<?php echo $type; ?>"><?php echo $name; ?></option>
								<?php
							}
						?>
					</select>
					<input type="submit" class="button button-secondary submit" value="<?php _e( 'Export', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				</fieldset>
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<!-- /export_settings -->
		<!-- import_settings -->
		<div class="tool">
			<h4><?php _e( 'Import Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Import plugin settings in JSON format.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="debug_tool" value="import_settings">
				<fieldset>
					<input type="file" name="file" accept="application/json" required>
					<input type="submit" class="button button-secondary submit" value="<?php _e( 'Import', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				</fieldset>
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<!-- /import_settings -->
		<!-- reset_settings -->
		<div class="tool">
			<h4><?php _e( 'Reset Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'This will clear all your selected settings data. Please do a backup first using the export tool above.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<input type="hidden" name="debug_tool" value="reset_settings">
				<fieldset>
					<select name="type" required>
						<?php
							foreach ( $this->get_setting_types() as $type => $name ) {
								?>
								<option value="<?php echo $type; ?>"><?php echo $name; ?></option>
								<?php
							}
						?>
					</select>
					<input type="submit" class="button button-secondary submit" value="<?php _e( 'Reset', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
				</fieldset>
				<fieldset>
					<div class="notice inline" style="display:none;"><p></p></div>
				</fieldset>
			</form>
		</div>
		<!-- /reset_settings -->
		<?php if ( WPO_WCPDF()->settings->upgrade->are_any_extensions_installed() ) : ?>
			<!-- clear_extensions_license_cache -->
			<div class="tool">
				<h4><?php _e( 'Clear extensions license caching', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
				<p><?php _e( 'This will clear all extensions\' license caching. This could be required to update the license status in the Upgrade tab or for new Cloud Storage activations (Professional extension).', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<form method="post">
					<input type="hidden" name="debug_tool" value="clear_extensions_license_cache">
					<input type="submit" class="button button-secondary submit" value="<?php _e( 'Clear licenses cache', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
					<fieldset>
						<div class="notice inline" style="display:none;"><p></p></div>
					</fieldset>
				</form>
			</div>
			<!-- /clear_extensions_license_cache -->
		<?php endif; ?>
		<?php do_action( 'wpo_wcpdf_after_debug_tools', $this ); ?>
	</div>
	<!-- danger_zone (admin access only) -->
	<?php if ( current_user_can( 'administrator' ) && isset( WPO_WCPDF()->settings->debug_settings['enable_danger_zone_tools'] ) ) : ?>
		<?php
			$documents  = WPO_WCPDF()->documents->get_documents( 'all' );
			$date_types = array(
				'date_created'   => __( 'Order date created', 'woocommerce-pdf-invoices-packing-slips' ),
				'date_modified'  => __( 'Order date modified', 'woocommerce-pdf-invoices-packing-slips' ),
				'date_completed' => __( 'Order date completed', 'woocommerce-pdf-invoices-packing-slips' ),
				'date_paid'      => __( 'Order date paid', 'woocommerce-pdf-invoices-packing-slips' ),
				'document_date'  => __( 'Document date', 'woocommerce-pdf-invoices-packing-slips' ),
			);
		?>
		<div id="danger_zone" class="wrapper">
			<div class="tool">
				<div class="notice notice-warning inline">
					<p><?php _e( '<strong>DANGER ZONE:</strong> Create a backup before using these tools, the actions they perform are irreversible!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				</div>
			</div>
			<!-- renumber_documents -->
			<div class="tool">
				<h4><?php _e( 'Renumber existing documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
				<p><?php _e( 'This tool will renumber existing documents within the selected order date range, while keeping the assigned document date.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<p>
					<?php
						printf(
							/* translators: step-by-step instructions */
							__( 'Set the <strong>next document number</strong> setting %s to the number you want to use for the first document. ', 'woocommerce-pdf-invoices-packing-slips' ),
							'<code>WooCommerce > PDF Invoices > Documents > Select document</code>'
						);
					?>
				</p>
				<form method="post">
					<table>
						<tr>
							<td><?php _e( 'Document type:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td>
								<select id="renumber-document-type" name="renumber-document-type">
									<option value=""><?php _e( 'Select', 'woocommerce-pdf-invoices-packing-slips' ); ?>...</option>
									<?php foreach ( $documents as $document ) : ?>
										<option value="<?php echo $document->get_type(); ?>"><?php echo $document->get_title(); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php _e( 'Date type:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td>
								<select id="renumber-date-type" name="renumber-date-type">
									<?php
										foreach ( $date_types as $key => $label ) {
											printf( '<option value="%s">%s</option>', $key, $label );
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php _e( 'From:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td><input type="text" id="renumber-date-from" name="renumber-date-from" value="<?php echo date( 'Y-m-d' ); ?>" size="10"><span class="add-info"><?php _e( '(as: yyyy-mm-dd)', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></td>
						</tr>
						<tr>
							<td><?php _e( 'To:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td><input type="text" id="renumber-date-to" name="renumber-date-to" value="<?php echo date( 'Y-m-d' ); ?>" size="10"><span class="add-info"><?php _e( '(as: yyyy-mm-dd)', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<button class="button button-large number-tools-btn" id="renumber-documents-btn"><?php _e( 'Renumber documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></button>
								<div class="spinner renumber-spinner"></div>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<!-- /renumber_documents -->
			<!-- delete_documents -->
			<div class="tool">
				<h4><?php _e( 'Delete existing documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
				<p><?php _e( 'This tool will delete existing documents within the selected order date range.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<form method="post">
					<table>
						<tr>
							<td><?php _e( 'Document type:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td>
								<select id="delete-document-type" name="delete-document-type">
									<option value=""><?php _e( 'Select', 'woocommerce-pdf-invoices-packing-slips' ); ?>...</option>
									<?php foreach ( $documents as $document ) : ?>
										<option value="<?php echo $document->get_type(); ?>"><?php echo $document->get_title(); ?></option>
									<?php endforeach; ?>
									<option value="all"><?php _e( 'All', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php _e( 'Date type:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td>
								<select id="delete-date-type" name="delete-date-type">
									<?php
										foreach ( $date_types as $key => $label ) {
											printf( '<option value="%s">%s</option>', $key, $label );
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php _e( 'From:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td><input type="text" id="delete-date-from" name="delete-date-from" value="<?php echo date( 'Y-m-d' ); ?>" size="10"><span class="add-info"><?php _e( '(as: yyyy-mm-dd)', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></td>
						</tr>
						<tr>
							<td><?php _e( 'To:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td><input type="text" id="delete-date-to" name="delete-date-to" value="<?php echo date( 'Y-m-d' ); ?>" size="10"><span class="add-info"><?php _e( '(as: yyyy-mm-dd)', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<button class="button button-large number-tools-btn" id="delete-documents-btn"><?php _e( 'Delete documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></button>
								<div class="spinner delete-spinner"></div>
							</td>
						</tr>
					</table>
				</form>
			</div>
			<!-- /delete_documents -->
		</div>
	<?php endif; ?>
	<!-- /danger_zone (admin access only) -->
</div>
