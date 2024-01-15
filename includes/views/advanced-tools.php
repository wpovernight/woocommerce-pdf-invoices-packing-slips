<?php defined( 'ABSPATH' ) or exit; ?>

<div id="debug-tools">
	<div class="wrapper">
		<?php do_action( 'wpo_wcpdf_before_debug_tools', $this ); ?>
		<!-- generate_random_string -->
		<div class="tool">
			<h4><?php _e( 'Generate random temporary directory', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'For security reasons, it is preferable to use a random name for the temporary directory.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpo_wcpdf_debug_tools_action', 'security' ); ?>
				<input type="hidden" name="wpo_wcpdf_debug_tools_action" value="generate_random_string">
				<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e( 'Generate temporary directory', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
			</form>
		</div>
		<!-- /generate_random_string -->
		<!-- install_fonts -->
		<div class="tool">
			<h4><?php _e( 'Reinstall plugin fonts', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'If you are experiencing issues with rendering fonts there might have been an issue during installation or upgrade.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpo_wcpdf_debug_tools_action', 'security' ); ?>
				<input type="hidden" name="wpo_wcpdf_debug_tools_action" value="install_fonts">
				<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e( 'Reinstall fonts', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
			</form>
		</div>
		<!-- /install_fonts -->
		<!-- reschedule_yearly_reset -->
		<?php if ( ! WPO_WCPDF()->settings->yearly_reset_action_is_scheduled() ) : ?>
		<div class="tool">
			<h4><?php _e( 'Reschedule the yearly reset of the numbering system', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( "You seem to have the yearly reset enabled for one of your documents but the action that performs this isn't scheduled yet.", 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpo_wcpdf_debug_tools_action', 'security' ); ?>
				<input type="hidden" name="wpo_wcpdf_debug_tools_action" value="reschedule_yearly_reset">
				<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e( 'Reschedule yearly reset', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
			</form>
		</div>
		<?php endif; ?>
		<!-- /reschedule_yearly_reset -->
		<!-- clear_tmp -->
		<div class="tool">
			<h4><?php _e( 'Remove temporary files', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'Clean up the PDF files stored in the temporary folder (used for email attachments).', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpo_wcpdf_debug_tools_action', 'security' ); ?>
				<input type="hidden" name="wpo_wcpdf_debug_tools_action" value="clear_tmp">
				<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e( 'Remove temporary files', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
			</form>
		</div>
		<!-- /clear_tmp -->
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
			<form class="wpo_wcpdf_debug_tools_form" method="post">
				<input type="hidden" name="debug_tool" value="export-settings">
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
					<a href="" class="button button-secondary submit"><?php _e( 'Export', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
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
			<form class="wpo_wcpdf_debug_tools_form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="debug_tool" value="import-settings">
				<fieldset>
					<input type="file" name="file" accept="application/json" required>
					<a href="" class="button button-secondary submit"><?php _e( 'Import', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
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
			<form class="wpo_wcpdf_debug_tools_form" method="post">
				<input type="hidden" name="debug_tool" value="reset-settings">
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
					<a href="" class="button button-secondary submit"><?php _e( 'Reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
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
					<?php wp_nonce_field( 'wpo_wcpdf_debug_tools_action', 'security' ); ?>
					<input type="hidden" name="wpo_wcpdf_debug_clear_extensions_license_cache" value="clear_extensions_license_cache">
					<input type="submit" name="submit" id="submit" class="button" value="<?php esc_attr_e( 'Clear licenses cache', 'woocommerce-pdf-invoices-packing-slips' ); ?>">
					<?php
						if ( ! empty( $_POST ) && isset( $_POST['wpo_wcpdf_debug_clear_extensions_license_cache'] ) && 'clear_extensions_license_cache' === $_POST['wpo_wcpdf_debug_clear_extensions_license_cache'] ) {
							// check permissions
							if ( ! check_admin_referer( 'wpo_wcpdf_debug_tools_action', 'security' ) ) {
								return;
							}

							WPO_WCPDF()->settings->upgrade->clear_extensions_license_cache();
							
							printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html__( 'Extensions\' license cache cleared successfully!', 'woocommerce-pdf-invoices-packing-slips' ) ); 
						}
					?>
				</form>
				<?php
					if ( WPO_WCPDF()->settings->upgrade->get_extensions_license_data() ) {
						$type    = 'warning';
						$message = __( 'Licenses data is cached', 'woocommerce-pdf-invoices-packing-slips' );
					} else {
						$type    = 'success';
						$message = __( 'Licenses cache is empty', 'woocommerce-pdf-invoices-packing-slips' );
					}
					
					printf( '<div class="notice inline notice-%s"><p>%s</p></div>', $type, $message );
				?>
			</div>
			<!-- /clear_extensions_license_cache -->
		<?php endif; ?>
		<?php do_action( 'wpo_wcpdf_after_debug_tools', $this ); ?>
	</div>
	<!-- danger_zone (admin access only) -->
	<?php if ( current_user_can( 'administrator' ) && isset( WPO_WCPDF()->settings->debug_settings['enable_danger_zone_tools'] ) ) : ?>
	<?php $documents = WPO_WCPDF()->documents->get_documents( 'all' ); ?>
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
		</div>
		<!-- /renumber_documents -->
		<!-- delete_documents -->
		<div class="tool">
			<h4><?php _e( 'Delete existing documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></h4>
			<p><?php _e( 'This tool will delete existing documents within the selected order date range.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
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
		</div>
		<!-- /delete_documents -->
	</div>
	<?php endif; ?>
	<!-- /danger_zone (admin access only) -->
</div>