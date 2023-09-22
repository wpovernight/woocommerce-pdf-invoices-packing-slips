<?php
namespace WPO\WC\PDF_Invoices\Settings;

use WPO\WC\PDF_Invoices\Tables\Number_Store_List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_Debug' ) ) :

class Settings_Debug {
	
	protected static $_instance = null;
	public $sections;
		
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()	{
		$this->sections = array(
			'settings' => __( 'Settings', 'woocommerce-pdf-invoices-packing-slips' ),
			'status'   => __( 'Status', 'woocommerce-pdf-invoices-packing-slips' ),
			'tools'    => __( 'Tools', 'woocommerce-pdf-invoices-packing-slips' ),
			'numbers'  => __( 'Numbers', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_debug', array( $this, 'output' ), 10, 1 );
		
		add_action( 'wp_ajax_wpo_wcpdf_debug_tools', array( $this, 'ajax_process_settings_debug_tools' ) );
	}

	public function output( $active_section ) {
		$active_section = ! empty( $active_section ) ? $active_section : 'settings';
		
		?>
		<div class="wcpdf_debug_settings_sections">
			<h2 class="nav-tab-wrapper">
				<?php
					foreach ( $this->sections as $section => $title ) {
						$active = ( $section == $active_section ) ? 'nav-tab-active' : '';
						printf( '<a href="%1$s" class="nav-tab nav-tab-%2$s %3$s">%4$s</a>', esc_url( add_query_arg( 'section', $section ) ), esc_attr( $section ), $active, esc_html( $title ) );
					}
				?>
			</h2>
		</div>
		<?php
		
		switch ( $active_section ) {
			case 'settings':
				$this->display_settings();
				break;
			case 'status':
				$this->display_status();
				break;
			case 'tools':
				$this->process_debug_tools();
				$this->display_tools();
			case 'numbers':
				$this->display_numbers();
				break;
		}
	}
	
	public function display_settings() {
		settings_fields( 'wpo_wcpdf_settings_debug' );
		do_settings_sections( 'wpo_wcpdf_settings_debug' );

		submit_button();
	}
	
	public function display_status() {
		include( WPO_WCPDF()->plugin_path() . '/includes/views/advanced-status.php' );
	}

	public function display_tools() {
		?>
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
					<h4><span><?php _e( 'Export Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></h4>
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
					<h4><span><?php _e( 'Import Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></h4>
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
					<h4><span><?php _e( 'Reset Settings', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></h4>
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
				<?php do_action( 'wpo_wcpdf_after_debug_tools', $this ); ?>
			</div>
			<!-- nuke (admin access only) -->
			<?php if ( current_user_can( 'administrator' ) ) : ?>
			<div id="nuke" class="wrapper">
				<div class="tool">
					<div class="notice notice-warning inline">
						<p><?php _e( '<strong>DANGER ZONE:</strong> Create a backup before using this tools, the actions they performs are irreversible!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					</div>
				</div>
				<div class="tool">
					<h4><span><?php _e( 'Renumber existing documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></h4>
					<p><?php _e( 'This tool will renumber existing documents within the selected order date range, while keeping the assigned document date.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p><?php printf(
						/* translators: step-by-step instructions */
						__( 'Set the <strong>next document number</strong> setting %s to the number you want to use for the first document. ', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>WooCommerce > PDF Invoices > Documents > Select document</code>'
					); ?></p>
				</div>
				<div class="tool">
					<h4><span><?php _e( 'Delete existing documents', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></h4>
					<p><?php _e( 'This tool will delete existing documents within the selected order date range.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				</div>
			</div>
			<?php endif; ?>
			<!-- /nuke (admin access only) -->
		</div>
		<br>
		<?php
	}
	
	public function display_numbers() {
		global $wpdb;

		$number_store_tables = $this->get_number_store_tables();
		$store_name          = 'invoice_number';
		
		if ( isset( $_GET['table_name'] ) ) {
			$selected_table_name = esc_attr( $_GET['table_name'] );
		} else {
			$_GET['table_name'] = $selected_table_name = apply_filters( 'wpo_wcpdf_number_store_table_name', "{$wpdb->prefix}wcpdf_{$store_name}", $store_name, null ); // i.e. wp_wcpdf_invoice_number or wp_wcpdf_invoice_number_2021
			
			if ( ! isset( $number_store_tables[ $_GET['table_name'] ] ) ) {
				$_GET['table_name'] = $selected_table_name = null;
			}
		}

		$list_table = new Number_Store_List_Table();
		$list_table->prepare_items();
		?>
		<div class="wcpdf_document_settings_sections wcpdf_advanced_numbers_choose_table">
			<?php
				$choose_table_title = __( 'Choose a number store', 'woocommerce-pdf-invoices-packing-slips' );
				if ( isset( $number_store_tables[ $selected_table_name ] ) ) {
					$choose_table_title = esc_attr( $number_store_tables[ $selected_table_name ] );
				}
				echo '<h2>' . esc_html( $choose_table_title ) . '<span class="arrow-down">&#9660;</span></h2>';
			?>
			<ul>
				<?php
				foreach ( $number_store_tables as $table_name => $title ) {
					if ( isset( $_GET['table_name'] ) && $table_name !== $_GET['table_name'] ) {
						if ( empty( trim( $title ) ) ) {
							$title = '['.__( 'untitled', 'woocommerce-pdf-invoices-packing-slips' ).']';
						}
						printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( add_query_arg( 'table_name', $table_name ) ), esc_html( $title ) );
					}
				}
				?>
			</ul>
			<?php if ( ! empty( $selected_table_name ) && ! empty( $number_store_tables[ $selected_table_name ] ) ) : ?>
				<p>
					<?php printf(
						/* translators: document title */
						__( 'Below is a list of all the document numbers generated since the last reset (which happens when you set the <strong>next %s number</strong> value in the settings).', 'woocommerce-pdf-invoices-packing-slips' ),
						esc_attr( $number_store_tables[ $selected_table_name ] )
						);
					?>
				</p>
				<p><?php _e( 'Numbers may have been assigned to orders before this.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<div class="number-search" style="text-align:right;">
					<input type="search" id="number_search_input" name="number_search_input" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>">
					<a href="#" class="button button-primary number-search-button"><?php _e( 'Search number', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					<?php $disabled = ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) ? '' : 'disabled'; ?>
					<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>" class="button button-secondary" <?php echo $disabled; ?>><?php _e( 'Reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
				</div>
				<?php $list_table->display(); ?>	
			<?php else : ?>
				<div class="notice notice-info inline">
					<p><?php _e( 'Please select a number store!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
	
	private function get_number_store_tables() {
		global $wpdb;
		
		$tables          = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}wcpdf_%'" );
		$document_titles = WPO_WCPDF()->documents->get_document_titles();
		$table_names     = array();
		
		foreach ( $tables as $table ) {
			foreach ( $table as $table_name ) {
				if ( ! empty ( $table_name ) ) {
					// strip the default prefix
					$store_name = $full_store_name = substr( $table_name, strpos( $table_name, 'wcpdf_' ) + strlen( 'wcpdf_' ) );
					
					// strip year suffix, if present
					if ( is_numeric( substr( $full_store_name, -4 ) ) ) {
						$store_name = trim( substr( $full_store_name, 0, -4 ), '_' );
					}
					
					// strip '_number' and other remaining suffixes
					$suffix       = substr( $full_store_name, strpos( $full_store_name, '_number' ) + strlen( '_number' ) );
					$clean_suffix = trim( str_replace( '_number', '', $suffix ), '_' );
					$name         = substr( $store_name, 0, strpos( $store_name, '_number' ) );
					
					if ( ! empty ( $document_titles[ $name ] ) ) {
						$title = $document_titles[ $name ];
					} else {
						$title = ucwords( str_replace( array( "__", "_", "-" ), ' ', $name ) );
					}
					
					if ( ! empty ( $suffix ) ) {
						$title = "{$title} ({$clean_suffix})";
					}
					
					$table_names[ $table_name ] = $title;
				}
			}
		}

		ksort( $table_names );

		return $table_names;
	}
	
	public function process_debug_tools() {
		if ( isset( $_REQUEST['wpo_wcpdf_debug_tools_action'] ) && is_callable( array( $this, $_REQUEST['wpo_wcpdf_debug_tools_action'] ) ) ) {
			if ( check_admin_referer( 'wpo_wcpdf_debug_tools_action', 'security' ) ) {
				call_user_func( array( $this, $_REQUEST['wpo_wcpdf_debug_tools_action'] ) );
			}
		}
	}
	
	private function generate_random_string() {
		if ( ! empty( WPO_WCPDF()->main->get_random_string() ) ) {
			$old_path = WPO_WCPDF()->main->get_tmp_base();
		} else {
			$old_path = WPO_WCPDF()->main->get_tmp_base( false );
		}
		
		WPO_WCPDF()->main->generate_random_string();
		$new_path = WPO_WCPDF()->main->get_tmp_base();
		WPO_WCPDF()->main->copy_directory( $old_path, $new_path );
		WPO_WCPDF()->main->maybe_reinstall_fonts( true );
		
		/* translators: directory path */
		printf( '<div class="notice notice-success"><p>%s</p></div>', sprintf( esc_html__( 'Temporary folder moved to %s', 'woocommerce-pdf-invoices-packing-slips' ), '<code>'.$new_path.'</code>' ) );
	}
	
	private function install_fonts() {
		WPO_WCPDF()->main->maybe_reinstall_fonts( true );
		printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html__( 'Fonts reinstalled!', 'woocommerce-pdf-invoices-packing-slips' ) );
	}
	
	private function reschedule_yearly_reset() {
		WPO_WCPDF()->settings->schedule_yearly_reset_numbers();
		printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html__( 'Yearly reset numbering system rescheduled!', 'woocommerce-pdf-invoices-packing-slips' ) );
	}
	
	private function clear_tmp() {
		$output = WPO_WCPDF()->main->temporary_files_cleanup( time() );
		printf( '<div class="notice notice-%1$s"><p>%2$s</p></div>', key( $output ), reset( $output ) );
	}
	
	public function ajax_process_settings_debug_tools() {
		check_ajax_referer( 'wpo_wcpdf_debug_nonce', 'nonce' );
		
		$data = stripslashes_deep( $_REQUEST );
		
		if ( empty( $data['action'] ) || 'wpo_wcpdf_debug_tools' !== $data['action'] ) {
			return;
		}
		
		$debug_tool = str_replace( '-', '_', esc_attr( $data['debug_tool'] ) );
		
		if ( is_callable( array( $this, $debug_tool,  ) ) ) {
			call_user_func_array( array( $this, $debug_tool ), array( $data ) );
		}
		
		wp_die();
	}
	
	private function export_settings( $data ) {
		extract( $data );
		
		if ( empty( $type ) ) {
			$message = __( 'Export settings type is empty!', 'woocommerce-pdf-invoices-packing-slips' );
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
		
		$settings = [];
		
		switch ( $type ) {
			case 'general':
				$settings = WPO_WCPDF()->settings->general_settings;
				break;
			case 'debug':
				$settings = WPO_WCPDF()->settings->debug_settings;
				break;
			case 'ubl_taxes':
				$settings = WPO_WCPDF()->settings->ubl_tax_settings;
				break;
			default:
				$settings = apply_filters( 'wpo_wcpdf_export_settings', $settings, $type );
				break;
		}
		
		// maybe it's a document type settings request
		if ( empty( $settings ) ) {
			$documents = WPO_WCPDF()->documents->get_documents( 'all' );
			foreach ( $documents as $document ) {
				$document_type = $document->get_type();
				if (
					$document_type === substr( $type, 0, strlen( $document_type ) ) ||
					false !== strpos( $type, '_ubl' )
				) {
					$settings = get_option( "wpo_wcpdf_documents_settings_{$type}", [] );
					break;
				}
			}
			
			if ( empty( $settings ) ) {
				$message = __( 'Exported settings data is empty!', 'woocommerce-pdf-invoices-packing-slips' );
				wcpdf_log_error( $message );
				wp_send_json_error( compact( 'message' ) );
			}
		}
		
		$filename = apply_filters( 'wpo_wcpdf_export_settings_filename', sprintf( "{$type}-settings-export_%s.json", date( 'Y-m-d_H-i-s' ) ), $type );
		
		wp_send_json_success( compact( 'filename', 'settings' ) );
	}
	
	private function import_settings( $data ) {
		extract( $data );
		
		$file_data = [];
		
		if ( ! empty( $_FILES['file']['tmp_name'] ) && ! empty( $_FILES['file']['name'] ) ) {
			$json_data = file_get_contents( $_FILES['file']['tmp_name'], $_FILES['file']['name'] );
			if ( false === $json_data ) {
				$message = __( 'Failed to get contents from JSON file!', 'woocommerce-pdf-invoices-packing-slips' );
				wcpdf_log_error( $message );
				wp_send_json_error( compact( 'message' ) );
			} else {
				$file_data = json_decode( $json_data, true );
			}
		} else {
			$message = __( 'JSON file not found!', 'woocommerce-pdf-invoices-packing-slips' );
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
		
		if ( empty( $file_data ) || empty( $file_data['type'] ) || empty( $file_data['settings'] ) || ! is_array( $file_data['settings'] ) ) {
			$message = __( 'The JSON file data is corrupted!', 'woocommerce-pdf-invoices-packing-slips' );
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
		
		$setting_types   = $this->get_setting_types();
		$type            = esc_attr( $file_data['type'] );
		$new_settings    = stripslashes_deep( $file_data['settings'] );
		$settings_option = '';
		
		if ( ! in_array( $type, array_keys( $setting_types ) ) ) {
			$message = __( 'The JSON file settings type is not supported on this store!', 'woocommerce-pdf-invoices-packing-slips' );
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
		
		if ( in_array( $type, array( 'general', 'debug', 'ubl_taxes' ) ) ) {
			$settings_option = "wpo_wcpdf_settings_{$type}";
		} else {
			$documents = WPO_WCPDF()->documents->get_documents( 'all' );
			foreach ( $documents as $document ) {
				$document_type = $document->get_type();
				if (
					$document_type === substr( $type, 0, strlen( $document_type ) ) ||
					false !== strpos( $type, '_ubl' )
				) {
					$settings_option = "wpo_wcpdf_documents_settings_{$type}";
					break;
				}
			}
		}
		
		// used for extension settings
		$settings_option = apply_filters( 'wpo_wcpdf_import_settings_option', $settings_option, $type, $new_settings );
		
		if ( empty( $settings_option ) ) {
			$message = __( "Couldn't determine the settings option for the import!", 'woocommerce-pdf-invoices-packing-slips' );
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
		
		$updated = update_option( $settings_option, $new_settings );
		if ( $updated ) {
			$message = sprintf(
				/* translators: settings type */
				__( '%s settings imported successfully!', 'woocommerce-pdf-invoices-packing-slips' ),
				$setting_types[$type]
			);
			wcpdf_log_error( $message, 'info' );
			wp_send_json_success( compact( 'type', 'message' ) );
		} else {
			$message = sprintf(
				/* translators: settings type */
				__( 'The %s settings file you are trying to import is identical to your current settings, therefore, the settings were not imported.', 'woocommerce-pdf-invoices-packing-slips' ),
				$setting_types[$type]
			);
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
	}
	
	private function reset_settings( $data ) {
		extract( $data );
		
		if ( empty( $type ) ) {
			$message = __( 'Reset settings type is empty!', 'woocommerce-pdf-invoices-packing-slips' );
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
		
		$settings_option = '';
		
		switch ( $type ) {
			case 'general':
				$settings_option = 'wpo_wcpdf_settings_general';
				break;
			case 'debug':
				$settings_option = 'wpo_wcpdf_settings_debug';
				break;
			case 'ubl_taxes':
				$settings_option = 'wpo_wcpdf_settings_ubl_taxes';
				break;
			default:
				$settings_option = apply_filters( 'wpo_wcpdf_reset_settings_option', $settings_option, $type );
				break;
		}
		
		// maybe it's a document type settings request
		if ( empty( $settings_option ) ) {
			$documents = WPO_WCPDF()->documents->get_documents( 'all' );
			foreach ( $documents as $document ) {
				$document_type = $document->get_type();
				if (
					$document_type === substr( $type, 0, strlen( $document_type ) ) ||
					false !== strpos( $type, '_ubl' )
				) {
					$settings_option = "wpo_wcpdf_documents_settings_{$type}";
					break;
				}
			}
			
			if ( empty( $settings_option ) ) {
				$message = sprintf(
					/* translators: settings type */
					__( '%s settings reset not supported!', 'woocommerce-pdf-invoices-packing-slips' ),
					$type
				);
				wcpdf_log_error( $message );
				wp_send_json_error( compact( 'message' ) );
			}
		}
		
		// settings already reset
		$current_settings = get_option( $settings_option, [] );
		if ( empty( $current_settings ) ) {
			$message = sprintf(
				/* translators: settings type */
				__( '%s settings are already reset!', 'woocommerce-pdf-invoices-packing-slips' ),
				$type
			);
			wcpdf_log_error( $message, 'info' );
			wp_send_json_success( compact( 'type', 'message' ) );
		}
		
		// reset settings
		$updated = update_option( $settings_option, [] );
		if ( $updated ) {
			$message = sprintf(
				/* translators: settings type */
				__( '%s settings reset successfully!', 'woocommerce-pdf-invoices-packing-slips' ),
				$type
			);
			wcpdf_log_error( $message, 'info' );
			wp_send_json_success( compact( 'type', 'message' ) );
		} else {
			$message = sprintf(
				/* translators: settings type */
				__( 'An error occurred when trying to reset the %s settings.', 'woocommerce-pdf-invoices-packing-slips' ),
				$type
			);
			wcpdf_log_error( $message );
			wp_send_json_error( compact( 'message' ) );
		}
	}
	
	public function get_setting_types() {
		$setting_types = [
			'general'   => __( 'General', 'woocommerce-pdf-invoices-packing-slips' ),
			'debug'     => __( 'Debug', 'woocommerce-pdf-invoices-packing-slips' ),
			'ubl_taxes' => __( 'UBL Taxes', 'woocommerce-pdf-invoices-packing-slips' ),
		];
		$documents = WPO_WCPDF()->documents->get_documents( 'all' );
		foreach ( $documents as $document ) {
			if ( $document->title != $document->get_title() ) {
				$title = $document->title.' ('.$document->get_title().')';
			} else {
				$title = $document->get_title();
			}
			
			foreach ( $document->output_formats as $output_format ) {
				$slug = $document->get_type();
				if ( 'pdf' !== $output_format ) {
					$slug .= "_{$output_format}";
				}
				$setting_types[$slug] = strtoupper( $output_format ) . ' ' .  $title;
			}
		}
		
		return apply_filters( 'wpo_wcpdf_setting_types', $setting_types );
	}

	public function init_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'wpo_wcpdf_settings_debug';

		$settings_fields = array(
			array(
				'type'     => 'section',
				'id'       => 'debug_settings',
				'title'    => '',
				'callback' => 'section',
			),
			array(
				'type'     => 'setting',
				'id'	   => 'document_link_access_type',
				'title'	   => __( 'Document link access type', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'select',
				'section'  => 'debug_settings',
				'args'	   => array(
					'option_name' => $option_name,
					'id'          => 'document_link_access_type',
					'default'     => 'logged_in',
					'options'     => array(
						'logged_in' => __( 'Logged in (recommended)', 'woocommerce-pdf-invoices-packing-slips' ),
						'guest'     => __( 'Guest', 'woocommerce-pdf-invoices-packing-slips' ),
						'full'      => __( 'Full', 'woocommerce-pdf-invoices-packing-slips' ),
					),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'document_link_access_type_table',
				'title'    => '',
				'callback' => array( $this, 'document_link_access_type_table' ),
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
				),
			),
			array(
				'type'     => 'setting',
				'id'	   => 'document_access_denied_redirect_page',
				'title'	   => __( 'Document access denied redirect page', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'select',
				'section'  => 'debug_settings',
				'args'	   => array(
					'option_name' => $option_name,
					'id'          => 'document_access_denied_redirect_page',
					'default'     => 'blank',
					'options'     => array(
						'blank_page'     => __( 'Blank page with message (default)', 'woocommerce-pdf-invoices-packing-slips' ),
						'login_page'     => __( 'Login page', 'woocommerce-pdf-invoices-packing-slips' ),
						'myaccount_page' => __( 'My Account page', 'woocommerce-pdf-invoices-packing-slips' ),
						'custom_page'    => __( 'Custom page (enter below)', 'woocommerce-pdf-invoices-packing-slips' ),
					),
					'description' => __( 'Select a frontend page to be used to redirect users when the document access is denied.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'document_custom_redirect_page',
				'title'    => '',
				'callback' => 'url_input',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'document_custom_redirect_page',
					'placeholder' => esc_url_raw( wc_get_page_permalink( 'shop' ) ),
					'description' => __( 'Custom external URLs not allowed.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'pretty_document_links',
				'title'    => __( 'Pretty document links', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'pretty_document_links',
					'description' => __( 'Changes the document links to a prettier URL scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'calculate_document_numbers',
				'title'    => __( 'Calculate document numbers (slow)', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'calculate_document_numbers',
					'description' => __( "Document numbers (such as invoice numbers) are generated using AUTO_INCREMENT by default. Use this setting if your database auto increments with more than 1.", 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'enable_debug',
				'title'    => __( 'Enable debug output', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'enable_debug',
					'description' => __( "Enable this option to output plugin errors if you're getting a blank page or other PDF generation issues", 'woocommerce-pdf-invoices-packing-slips' ) . '<br>' .
									 __( '<b>Caution!</b> This setting may reveal errors (from other plugins) in other places on your site too, therefor this is not recommended to leave it enabled on live sites.', 'woocommerce-pdf-invoices-packing-slips' ) . ' ' .
									 __( 'You can also add <code>&debug=true</code> to the URL to apply this on a per-order basis.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'enable_cleanup',
				'title'    => __( 'Enable automatic cleanup', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox_text_input',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name'        => $option_name,
					'id'                 => 'enable_cleanup',
					/* translators: number of days */
					'text_input_wrap'    => __( "every %s days", 'woocommerce-pdf-invoices-packing-slips' ),
					'text_input_size'    => 4,
					'text_input_id'      => 'cleanup_days',
					'text_input_default' => 7,
					'description'        => __( "Automatically clean up PDF files stored in the temporary folder (used for email attachments)", 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'html_output',
				'title'    => __( 'Output to HTML', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'html_output',
					'description' => __( 'Send the template output as HTML to the browser instead of creating a PDF.', 'woocommerce-pdf-invoices-packing-slips' ) . ' ' .
									 __( 'You can also add <code>&output=html</code> to the URL to apply this on a per-order basis.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'log_to_order_notes',
				'title'    => __( 'Log to order notes', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'log_to_order_notes',
					'description' => __( 'Log PDF document creation and mark/unmark as printed to order notes.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'disable_preview',
				'title'    => __( 'Disable document preview', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'disable_preview',
					'description' => __( 'Disables the document preview on the plugin settings pages.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
		);

		// allow plugins to alter settings fields
		$settings_fields = apply_filters( 'wpo_wcpdf_settings_fields_debug', $settings_fields, $page, $option_group, $option_name );
		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
		return;
	}
	
	public function document_link_access_type_table() {
		?>
		<table id="document-link-access-type">
			<tr>
				<td class="option"><strong><?php _e( 'Logged in', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
				<td><?php _e( "Document can be accessed by logged in users only.", 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
			</tr>
			<tr>
				<td class="option"><strong><?php _e( 'Guest', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
				<td><?php _e( 'Document can be accessed by logged in and guest users.', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
			</tr>
			<tr>
				<td class="option"><strong><?php _e( 'Full', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></td>
				<td><?php _e( 'Document can be accessed by everyone with the link.', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
			</tr>
		</table>
		<?php
	}

}

endif; // class_exists
