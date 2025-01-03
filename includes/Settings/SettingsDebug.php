<?php
namespace WPO\IPS\Settings;

use WPO\IPS\Tables\NumberStoreListTable;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Settings\\SettingsDebug' ) ) :

class SettingsDebug {

	protected static $_instance = null;
	public $sections;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		// Show a notice if the plugin requirements are not met.
		add_action( 'admin_init', array( $this, 'handle_server_requirement_notice' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'wpo_wcpdf_settings_output_debug', array( $this, 'output' ), 10, 1 );

		add_action( 'wp_ajax_wpo_wcpdf_debug_tools', array( $this, 'ajax_process_settings_debug_tools' ) );
		add_action( 'wp_ajax_wpo_wcpdf_danger_zone_tools', array( $this, 'ajax_process_danger_zone_tools' ) );
	}

	public function output( $active_section ) {
		$active_section = ! empty( $active_section ) ? $active_section : 'settings';
		$sections       = $this->get_settings_sections();

		?>
		<div class="wcpdf_debug_settings_sections">
			<h2 class="nav-tab-wrapper">
				<?php
					foreach ( $sections as $section => $title ) {
						$active = ( $section === $active_section ) ? 'nav-tab-active' : '';
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
				$this->display_tools();
				break;
			case 'numbers':
				$this->display_numbers();
				break;
			default:
				do_action( 'wpo_wcpdf_settings_debug_section_output', $active_section );
				break;
		}

		do_action( 'wpo_wcpdf_settings_debug_after_output', $active_section, $sections );
	}

	public function display_settings() {
		settings_fields( 'wpo_wcpdf_settings_debug' );
		do_settings_sections( 'wpo_wcpdf_settings_debug' );

		submit_button();
	}

	/**
	 * Display the server requirement page.
	 *
	 * @return void
	 */
	public function display_status(): void {
		$server_configs = $this->get_server_config();
		include WPO_WCPDF()->plugin_path() . '/views/advanced-status.php';
	}

	/**
	 * Display the advanced tools page.
	 *
	 * @return void
	 */
	public function display_tools(): void {
		include WPO_WCPDF()->plugin_path() . '/views/advanced-tools.php';
	}

	public function display_numbers() {
		global $wpdb;

		$number_store_tables            = $this->get_number_store_tables();
		$invoice_number_store_doc_types = $this->get_additional_invoice_number_store_document_types();
		$store_name                     = 'invoice_number';

		if ( isset( $_GET['table_name'] ) ) {
			$selected_table_name = esc_attr( $_GET['table_name'] );
		} else {
			$_GET['table_name'] = $selected_table_name = apply_filters( 'wpo_wcpdf_number_store_table_name', "{$wpdb->prefix}wcpdf_{$store_name}", $store_name, null ); // i.e. wp_wcpdf_invoice_number or wp_wcpdf_invoice_number_2021
		}

		if ( ! isset( $number_store_tables[ $_GET['table_name'] ] ) ) {
			$_GET['table_name'] = $selected_table_name = null;
		}

		$document_type = $this->get_document_type_from_store_table_name( esc_attr( $_GET['table_name'] ) );

		$list_table = new NumberStoreListTable();
		$list_table->prepare_items();

		include( WPO_WCPDF()->plugin_path() . '/views/advanced-numbers.php' );
	}

	public function get_number_store_tables() {
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

					if ( empty( $store_name ) || empty( $full_store_name ) ) {
						continue;
					}

					// strip '_number' and other remaining suffixes
					$suffix       = substr( $full_store_name, strpos( $full_store_name, '_number' ) + strlen( '_number' ) );
					$clean_suffix = ! empty( $suffix ) ? trim( str_replace( '_number', '', $suffix ), '_' ) : $suffix;
					$name         = substr( $store_name, 0, strpos( $store_name, '_number' ) );
					$title        = '';

					if ( ! empty( $name ) ) {
						$title = ! empty( $document_titles[ $name ] ) ? $document_titles[ $name ] : ucwords( str_replace( array( "__", "_", "-" ), ' ', $name ) );
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

	public function get_document_type_from_store_table_name( $table_name ) {
		$document_type = '';

		if ( empty( $table_name ) ) {
			return $document_type;
		}

		// strip the default prefix
		$store_name = $full_store_name = substr( $table_name, strpos( $table_name, 'wcpdf_' ) + strlen( 'wcpdf_' ) );

		// strip year suffix, if present
		if ( is_numeric( substr( $full_store_name, -4 ) ) ) {
			$store_name = trim( substr( $full_store_name, 0, -4 ), '_' );
		}

		if ( ! empty( $store_name ) && ! empty( $full_store_name ) ) {
			$name          = substr( $store_name, 0, strpos( $store_name, '_number' ) );
			$document_type = ! empty( $name ) ? str_replace( '_', '-', $name ) : '';
		}

		return $document_type;
	}

	public function get_additional_invoice_number_store_document_types() {
		$additional_doc_types = array();
		$documents            = WPO_WCPDF()->documents->get_documents();

		foreach ( $documents as $document ) {
			if ( in_array( $document->get_type(), array( 'proforma', 'credit-note' ) ) && $document->is_enabled() && is_callable( array( $document, 'get_number_sequence' ) ) ) {
				$number_sequence = $document->get_number_sequence( '', $document );
				if ( 'invoice_number' === $number_sequence ) {
					$additional_doc_types[] = $document->get_type();
				}
			}
		}

		return $additional_doc_types;
	}

	private function generate_random_string( $data ) {
		if ( ! empty( WPO_WCPDF()->main->get_random_string() ) ) {
			$old_path = WPO_WCPDF()->main->get_tmp_base();
		} else {
			$old_path = WPO_WCPDF()->main->get_tmp_base( false );
		}

		WPO_WCPDF()->main->generate_random_string();
		$new_path = WPO_WCPDF()->main->get_tmp_base();
		WPO_WCPDF()->main->copy_directory( $old_path, $new_path );
		WPO_WCPDF()->main->maybe_reinstall_fonts( true );

		$message = esc_html__( 'Temporary folder moved to', 'woocommerce-pdf-invoices-packing-slips' ) . ': ' . wp_normalize_path( $new_path );

		wcpdf_log_error( $message, 'info' );
		wp_send_json_success( compact( 'message' ) );
	}

	private function install_fonts( $data ) {
		WPO_WCPDF()->main->maybe_reinstall_fonts( true );

		$message = esc_html__( 'Fonts reinstalled!', 'woocommerce-pdf-invoices-packing-slips' );
		wcpdf_log_error( $message, 'info' );
		wp_send_json_success( compact( 'message' ) );
	}

	private function reschedule_yearly_reset( $data ) {
		WPO_WCPDF()->settings->schedule_yearly_reset_numbers();

		$message = esc_html__( 'Yearly reset numbering system rescheduled!', 'woocommerce-pdf-invoices-packing-slips' );
		wcpdf_log_error( $message, 'info' );
		wp_send_json_success( compact( 'message' ) );
	}

	private function clear_tmp( $data ) {
		$output  = WPO_WCPDF()->main->temporary_files_cleanup( time() );
		$message = reset( $output );

		switch ( key( $output ) ) {
			case 'error':
				wcpdf_log_error( $message );
				wp_send_json_error( compact( 'message' ) );
				break;
			case 'success':
				wcpdf_log_error( $message, 'info' );
				wp_send_json_success( compact( 'message' ) );
				break;
			default:
				exit;
		}
	}

	private function clear_released_semaphore_locks( $data ) {
		\WPO\IPS\Semaphore::cleanup_released_locks();

		$message = esc_html__( 'Released semaphore locks have been cleaned up!', 'woocommerce-pdf-invoices-packing-slips' );
		wcpdf_log_error( $message, 'info' );
		wp_send_json_success( compact( 'message' ) );
	}

	private function clear_released_legacy_semaphore_locks( $data ) {
		\WPO\IPS\Semaphore::cleanup_released_locks( true );

		$message = esc_html__( 'Released legacy semaphore locks have been cleaned up!', 'woocommerce-pdf-invoices-packing-slips' );
		wcpdf_log_error( $message, 'info' );
		wp_send_json_success( compact( 'message' ) );
	}

	private function clear_extensions_license_cache( $data ) {
		WPO_WCPDF()->settings->upgrade->clear_extensions_license_cache();

		$message = __( "Extensions' license cache cleared successfully!", 'woocommerce-pdf-invoices-packing-slips' );
		wcpdf_log_error( $message, 'info' );
		wp_send_json_success( compact( 'message' ) );
	}

	public function ajax_process_settings_debug_tools() {
		check_ajax_referer( 'wpo_wcpdf_debug_nonce', 'nonce' );

		$data = stripslashes_deep( $_REQUEST );

		if ( empty( $data['action'] ) || 'wpo_wcpdf_debug_tools' !== $data['action'] || empty( $data['debug_tool'] ) ) {
			return;
		}

		$debug_tool = esc_attr( $data['debug_tool'] );

		if ( is_callable( array( $this, $debug_tool ) ) ) {
			// all except danger tools and wizard
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

	public function ajax_process_danger_zone_tools() {
		check_ajax_referer( 'wpo_wcpdf_debug_nonce', 'nonce' );

		$request = stripslashes_deep( $_POST );

		if ( ! isset( $request['document_type'] ) || ! isset( $request['date_from'] ) || ! isset( $request['date_to'] ) ) {
			$message = __( 'One or more request parameters missing.', 'woocommerce-pdf-invoices-packing-slips' );
			wp_send_json_error( compact( 'message' ) );
		}

		$from_date          = strtotime( $request['date_from'] . ' 00:00:00' );
		$to_date            = strtotime( $request['date_to'] . ' 23:59:59' );
		$document_type      = esc_attr( $request['document_type'] );
		$document_types     = ! empty( $document_type ) && ( 'all' !== $document_type ) ? array( $document_type ) : array();
		$document_title     = ! empty( $document_type ) && ( 'all' !== $document_type ) ? ucwords( str_replace( '-', ' ', $document_type ) ) . ' ' : ' ';
		$page_count         = isset( $request['page_count'] ) ? absint( $request['page_count'] ) : 1;
		$document_count     = isset( $request['document_count'] ) ? absint( $request['document_count'] ) : 0;
		$delete_or_renumber = isset( $request['delete_or_renumber'] ) ? esc_attr( $request['delete_or_renumber'] ) : false;
		$message            = ( 'delete' === $delete_or_renumber ) ? ' ' . $document_title . __( 'documents deleted.', 'woocommerce-pdf-invoices-packing-slips' ) : ' ' . $document_title . __( 'documents renumbered.', 'woocommerce-pdf-invoices-packing-slips' );
		$finished           = false;

		$args = array(
			'return'         => 'ids',
			'type'           => 'shop_order',
			'limit'          => -1,
			'order'          => 'ASC',
			'paginate'       => true,
			'posts_per_page' => 50,
			'page'           => $page_count,
		);

		$date_type     = isset( $request['date_type'] ) ? esc_attr( $request['date_type'] ) : '';
		$wc_date_types = array(
			'date_created',
			'date_modified',
			'date_completed',
			'date_paid',
		);

		if ( in_array( $date_type, $wc_date_types ) ) {
			$date_arg      = $date_type;
		} elseif ( 'document_date' === $date_type ) {
			$document_slug = str_replace( '-', '_', $document_type );
			$date_arg      = "wcpdf_{$document_slug}_date";
		} else {
			$date_arg      = '';
		}

		if ( empty( $date_arg ) ) {
			$message = __( 'Wrong date type selected.', 'woocommerce-pdf-invoices-packing-slips' );
			wp_send_json_error( compact( 'message' ) );
		}

		$args[ $date_arg ] = $from_date . '...' . $to_date;

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '6.8.0', '>=' ) && WPO_WCPDF()->order_util->custom_orders_table_usage_is_enabled() ) { // Woo >= 6.8.0 + HPOS
			$args = wpo_wcpdf_parse_document_date_for_wp_query( $args, $args );
		} else {
			add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'wpo_wcpdf_parse_document_date_for_wp_query', 10, 2 );
		}

		$results = wc_get_orders( $args );

		if ( ! is_object( $results ) ) {
			$message = __( 'Unexpected results from the orders query.', 'woocommerce-pdf-invoices-packing-slips' );
			wp_send_json_error( compact( 'message' ) );
		}

		$order_ids = $results->orders;

		if ( ! empty( $order_ids ) && ! empty( $document_type ) && $delete_or_renumber ) {
			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( empty( $order ) ) {
					continue;
				}

				if ( 'all' === $document_type ) {
					$documents = WPO_WCPDF()->documents->get_documents( 'all' );

					if ( is_array( $documents ) ) {
						foreach ( $documents as $document ) {
							$document_types[] = $document->get_type();
						}
					}
				}

				if ( ! empty( $document_types ) ) {
					foreach ( $document_types as $type ) {
						if ( 'credit-note' === $type && function_exists( 'WPO_WCPDF_Pro' ) ) {
							remove_filter( 'wpo_wcpdf_document_is_allowed', array( WPO_WCPDF_Pro()->functions, 'is_pro_document_allowed' ), 2, 2 );
						}

						$document = wcpdf_get_document( $type, $order );

						if ( 'credit-note' === $type && function_exists( 'WPO_WCPDF_Pro' ) ) {
							add_filter( 'wpo_wcpdf_document_is_allowed', array( WPO_WCPDF_Pro()->functions, 'is_pro_document_allowed' ), 2, 2 );
						}

						if ( ! is_object( $document ) ) {
							continue;
						}

						$return = $this->renumber_or_delete_document( $document, $delete_or_renumber );

						if ( $return ) {
							$document_count++;
						}
					}
				}
			}
			$page_count++;

		// no more order IDs
		} else {
			$finished = true;
		}

		$response = array(
			'finished'      => $finished,
			'pageCount'     => $page_count,
			'documentCount' => $document_count,
			'message'       => $message,
		);

		wp_send_json_success( $response );
	}

	private function renumber_or_delete_document( $document, $delete_or_renumber ) {
		$return = false;

		if ( $document && $document->exists() ) {
			switch ( $delete_or_renumber ) {
				case 'renumber':
					if ( is_callable( array( $document, 'initiate_number' ) ) ) {
						$document->initiate_number( true );
						$return = true;
					} elseif ( is_callable( array( $document, 'init_number' ) ) ) {
						$document->init_number();
						$return = true;
					}

					if ( $return ) {
						$document->save();
					}
					break;
				case 'delete':
					if ( is_callable( array( $document, 'delete' ) ) ) {
						$document->delete();
						$return = true;
					}
					break;
			}
		}

		return $return;
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
					'description' => sprintf(
						/* translators: 1. AUTO_INCREMENT, 2. one */
						__( 'Document numbers (such as invoice numbers) are generated using %1$s by default. Use this setting if your database auto increments with more than %2$s.', 'woocommerce-pdf-invoices-packing-slips' ),
						'<code>AUTO_INCREMENT</code>',
						'<code>1</code>'
					),
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
					'description' => __( "Enable this option to output plugin errors if you're getting a blank page or other PDF generation issues.", 'woocommerce-pdf-invoices-packing-slips' ) . '<br>' .
									 __( '<b>Caution!</b> This setting may reveal errors (from other plugins) in other places on your site too, therefore this is not recommended to leave it enabled on live sites.', 'woocommerce-pdf-invoices-packing-slips' ) . ' ' .
									 sprintf(
										/* translators: &debug=true */
										__( 'You can also add %s to the URL to apply this on a per-order basis.', 'woocommerce-pdf-invoices-packing-slips' ),
										'<code>&debug=true</code>'
									),
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
				'id'       => 'embed_images',
				'title'    => __( 'Embed Images', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'embed_images',
					'description' => __( 'Embed images only if you are experiencing issues with them loading in your PDF. Please note that this option can significantly increase the file size.', 'woocommerce-pdf-invoices-packing-slips' ),
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
					'description' => __( 'Log PDF document creation, deletion, and mark/unmark as printed to order notes.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'log_missing_translations',
				'title'    => __( 'Log missing translations', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'log_missing_translations',
					'description' => __( 'Enable this option to log dynamic strings that could not be translated. This can help you identify which strings need to be registered for translation.', 'woocommerce-pdf-invoices-packing-slips' ),
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
			array(
				'type'     => 'setting',
				'id'       => 'semaphore_logs',
				'title'    => __( 'Enable semaphore logs', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'semaphore_logs',
					'description' => __( 'Our plugin uses a semaphore class that prevents race conditions in multiple places in the code. Enable this setting only if you are having issues with document numbers, yearly reset or documents being assigned to the wrong order.', 'woocommerce-pdf-invoices-packing-slips' ),
				)
			),
			array(
				'type'     => 'setting',
				'id'       => 'enable_danger_zone_tools',
				'title'    => __( 'Enable danger zone tools', 'woocommerce-pdf-invoices-packing-slips' ),
				'callback' => 'checkbox',
				'section'  => 'debug_settings',
				'args'     => array(
					'option_name' => $option_name,
					'id'          => 'enable_danger_zone_tools',
					'description' => __( 'Enables the danger zone tools. The actions performed by these tools are irreversible!', 'woocommerce-pdf-invoices-packing-slips' ),
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

	/**
	 * Get the server configuration.
	 *
	 * @return array
	 */
	public function get_server_config(): array {
		$memory_limit  = function_exists( 'wc_let_to_num' ) ? wc_let_to_num( WP_MEMORY_LIMIT ) : woocommerce_let_to_num( WP_MEMORY_LIMIT );
		$php_mem_limit = function_exists( 'memory_get_usage' ) ? @ini_get( 'memory_limit' ) : '-';
		$gmagick       = extension_loaded( 'gmagick' );
		$imagick       = extension_loaded( 'imagick' );
		$xc            = extension_loaded( 'xcache' );
		$apc           = extension_loaded( 'apc' );
		$zop           = extension_loaded( 'Zend OPcache' );
		$op            = extension_loaded( 'opcache' );
		$dom           = extension_loaded( 'DOM' );
		$mbstring      = extension_loaded( 'mbstring' );
		$gd            = extension_loaded( 'gd' );
		$zlib          = extension_loaded( 'zlib' );
		$fileinfo      = extension_loaded( 'fileinfo' );

		$server_configs = array(
			'PHP version' => array(
				'required' => __( '7.4 or superior', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'    => PHP_VERSION,
				'result'   => WPO_WCPDF()->is_dependency_version_supported( 'php' ),
			),
			'DOMDocument extension' => array(
				'required' => true,
				'value'    => phpversion( 'DOM' ),
				'result'   => $dom,
			),
			'MBString extension' => array(
				'required' => true,
				'value'    => phpversion( 'mbstring' ),
				'result'   => $mbstring,
				'fallback' => __( 'Recommended, will use fallback functions', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'GD' => array(
				'required' => true,
				'value'    => phpversion( 'gd' ),
				'result'   => $gd,
				'fallback' => __( 'Required if you have images in your documents', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'WebP Support' => array(
				'required' => __( 'Required when using .webp images', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'    => null,
				'result'   => function_exists( 'imagecreatefromwebp' ),
				'fallback' => __( 'Required if you have .webp images in your documents', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'Zlib' => array(
				'required' => __( 'To compress PDF documents', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'    => phpversion( 'zlib' ),
				'result'   => $zlib,
				'fallback' => __( 'Recommended to compress PDF documents', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'opcache' => array(
				'required' => __( 'For better performances', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'    => null,
				'result'   => false,
				'fallback' => __( 'Recommended for better performances', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'GMagick or IMagick' => array(
				'required' => __( 'Better with transparent PNG images', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'    => $imagick ? 'IMagick ' . phpversion( 'imagick' ) : ( $gmagick ? 'GMagick ' . phpversion( 'gmagick' ) : null ),
				'result'   => $gmagick || $imagick,
				'fallback' => __( 'Recommended for better performances', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'glob()' => array(
				'required' => __( 'Required to detect custom templates and to clear the temp folder periodically', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'    => null,
				'result'   => function_exists( 'glob' ),
				'fallback' => __( 'Check PHP disable_functions', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'WP Memory Limit' => array(
				/* translators: <a> tags */
				'required' => __( 'Recommended: 128MB (especially for plugin-heavy setups)', 'woocommerce-pdf-invoices-packing-slips' ) . '<br/>' . sprintf(
						/* translators: 1: opening anchor tag, 2: closing anchor tag */
						__( 'See: %1$sIncreasing the WordPress Memory Limit%2$s', 'woocommerce-pdf-invoices-packing-slips' ),
						'<a href="https://docs.woocommerce.com/document/increasing-the-wordpress-memory-limit/" target="_blank">',
						'</a>'
					),
				'value'    => sprintf( 'WordPress: %s, PHP: %s', WP_MEMORY_LIMIT, $php_mem_limit ),
				'result'   => $memory_limit > 67108864,
			),
			'allow_url_fopen' => array (
				'required' => __( 'Allow remote stylesheets and images', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'	   => null,
				'result'   => (bool) ini_get( 'allow_url_fopen' ),
				'fallback' => __( 'allow_url_fopen disabled', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'fileinfo' => array (
				'required' => __( 'Necessary to verify the MIME type of local images.', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'	   => null,
				'result'   => $fileinfo,
				'fallback' => __( 'fileinfo disabled', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'base64_decode'	=> array (
				'required' => __( 'To compress and decompress font and image data', 'woocommerce-pdf-invoices-packing-slips' ),
				'value'	   => null,
				'result'   => function_exists( 'base64_decode' ),
				'fallback' => __( 'base64_decode disabled', 'woocommerce-pdf-invoices-packing-slips' ),
			),
		);

		if ( $imagick ) {
			$gmagick_imagick_position = array_search( 'GMagick or IMagick', array_keys( $server_configs ) ) + 1;
			$image_magick_config      = array(
				'ImageMagick' => array(
					'required' => __( 'Required for IMagick', 'woocommerce-pdf-invoices-packing-slips' ),
					'value'    => ( $imagick && class_exists( '\\Imagick' ) ) ? esc_attr( \Imagick::getVersion()['versionString'] ) : null,
					'result'   => $imagick,
					'fallback' => __( 'ImageMagick library, integrated via the IMagick PHP extension for advanced image processing capabilities', 'woocommerce-pdf-invoices-packing-slips' ),
				),
			);

			$server_configs = array_slice( $server_configs, 0, $gmagick_imagick_position, true ) + $image_magick_config + array_slice( $server_configs, $gmagick_imagick_position, null, true );
		}

		if ( $xc || $apc || $zop || $op ) {
			$server_configs['opcache']['result'] = true;
			if ( $xc ) {
				$server_configs['opcache']['value'] = 'XCache ' . phpversion( 'xcache' );
			} elseif ( $apc ) {
				$server_configs['opcache']['value'] = 'APC ' . phpversion( 'apc' );
			} elseif ( $zop ) {
				$server_configs['opcache']['value'] = 'Zend OPCache ' . phpversion( 'Zend OPcache' );
			} else {
				$server_configs['opcache']['value'] = 'PHP OPCache ' . phpversion( 'opcache' );
			}
		}

		return apply_filters( 'wpo_wcpdf_server_configs', $server_configs );
	}

	/**
	 * Set a notice to be displayed if the server requirements are not met.
	 *
	 * @return void
	 */
	public function handle_server_requirement_notice(): void {
		// Return if the notice has been dismissed.
		if ( get_option( 'wpo_wcpdf_dismiss_requirements_notice', false ) ) {
			return;
		}

		// Handle dismissal action.
		if ( isset( $_GET['wpo_dismiss_requirements_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'dismiss_requirements_notice' ) ) {
				update_option( 'wpo_wcpdf_dismiss_requirements_notice', true );
				wp_redirect( remove_query_arg( array( 'wpo_dismiss_requirements_notice', '_wpnonce' ) ) );
				exit;
			} else {
				wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_dismiss_requirements_notice' );
				return;
			}
		}

		// Check if the server requirements are met.
		$show_requirement_notice = false;
		$server_configs          = $this->get_server_config();

		foreach ( $server_configs as $config_name => $config ) {
			if ( in_array( $config_name, array( 'opcache', 'GMagick or IMagick', 'WP Memory Limit' ), true ) ) {
				continue;
			}

			if ( ! $config['result'] ) {
				$show_requirement_notice = true;
				break;
			}
		}

		// Return if the server requirements are met.
		if ( ! $show_requirement_notice ) {
			return;
		}

		// Display the notice.
		add_action( 'admin_notices', array( $this, 'display_server_requirement_notice' ) );
	}

	/**
	 * Display a notice informing the user that the server requirements are not met.
	 *
	 * @return void
	 */
	public function display_server_requirement_notice(): void {
		$status_page_url = admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=debug&section=status' );
		$dismiss_url     = wp_nonce_url( add_query_arg( 'wpo_dismiss_requirements_notice', true ), 'dismiss_requirements_notice' );
		$notice_message  = sprintf(
			/* translators: 1: Plugin name, 2: Open anchor tag, 3: Close anchor tag */
			__( 'Your server does not meet the requirements for %1$s. Please check the %2$sStatus page%3$s for more information.', 'woocommerce-pdf-invoices-packing-slips' ),
			'<strong>PDF Invoices & Packing Slips for WooCommerce</strong>',
			'<a href="' . esc_url( $status_page_url ) . '">',
			'</a>'
		);

		?>

		<div class="notice notice-warning">
			<p><?php echo wp_kses_post( $notice_message ); ?></p>
			<p><a href="<?php echo esc_url( $dismiss_url ); ?>" class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
		</div>

		<?php
	}

	private function get_settings_sections(): array {
		return apply_filters( 'wpo_wcpdf_settings_debug_sections', array(
			'settings' => __( 'Settings', 'woocommerce-pdf-invoices-packing-slips' ),
			'status'   => __( 'Status', 'woocommerce-pdf-invoices-packing-slips' ),
			'tools'    => __( 'Tools', 'woocommerce-pdf-invoices-packing-slips' ),
			'numbers'  => __( 'Numbers', 'woocommerce-pdf-invoices-packing-slips' ),
		) );
	}

}

endif; // class_exists
