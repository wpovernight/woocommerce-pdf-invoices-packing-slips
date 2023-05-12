<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Font_Synchronizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Main' ) ) :

class Main {

	private $subfolders = array( 'attachments', 'fonts', 'dompdf' );

	public function __construct() {
		add_action( 'wp_ajax_generate_wpo_wcpdf', array( $this, 'generate_pdf_ajax' ) );
		add_action( 'wp_ajax_nopriv_generate_wpo_wcpdf', array( $this, 'generate_pdf_ajax' ) );
		
		// mark/unmark printed
		add_action( 'wp_ajax_printed_wpo_wcpdf', array( $this, 'document_printed_ajax' ) );

		// email
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf_to_email' ), 99, 4 );
		add_filter( 'wpo_wcpdf_document_is_allowed', array( $this, 'disable_free' ), 10, 2 );
		add_filter( 'wp_mail', array( $this, 'set_phpmailer_validator'), 10, 1 );

		if ( isset(WPO_WCPDF()->settings->debug_settings['enable_debug']) ) {
			$this->enable_debug();
		}

		// include template specific custom functions
		$this->load_template_functions();

		// test mode
		add_filter( 'wpo_wcpdf_document_use_historical_settings', array( $this, 'test_mode_settings' ), 15, 2 );

		// page numbers & currency filters
		add_filter( 'wpo_wcpdf_get_html', array( $this, 'format_page_number_placeholders' ), 10, 2 );
		add_action( 'wpo_wcpdf_after_dompdf_render', array( $this, 'page_number_replacements' ), 9, 2 );
		add_filter( 'wpo_wcpdf_pdf_filters', array( $this, 'pdf_currency_filters' ) );
		add_filter( 'wpo_wcpdf_html_filters', array( $this, 'html_currency_filters' ) );

		// scheduled attachments cleanup (following settings on Status tab)
		add_action( 'wp_scheduled_delete', array( $this, 'schedule_temporary_files_cleanup' ) );

		// remove private data
		if ( apply_filters( 'wpo_wcpdf_remove_order_personal_data', true ) ) {
			add_action( 'woocommerce_privacy_remove_order_personal_data_meta', array( $this, 'remove_order_personal_data_meta' ), 10, 1 );
			add_action( 'woocommerce_privacy_remove_order_personal_data', array( $this, 'remove_order_personal_data' ), 10, 1 );
			add_filter( 'wpo_wcpdf_document_is_allowed', array( $this, 'disable_anonymized' ), 11, 2 );
		}
		// export private data
		add_action( 'woocommerce_privacy_export_order_personal_data_meta', array( $this, 'export_order_personal_data_meta' ), 10, 1 );

		// apply header logo height
		add_action( 'wpo_wcpdf_custom_styles', array( $this, 'set_header_logo_height' ), 9, 2 );

		// show notice of missing required directories
		add_action( 'admin_notices', array( $this, 'no_dir_notice' ), 1 );

		// add custom webhook topics for documents
		add_filter( 'woocommerce_webhook_topic_hooks', array( $this, 'wc_webhook_topic_hooks' ), 10, 2 );
		add_filter( 'woocommerce_valid_webhook_events', array( $this, 'wc_webhook_topic_events' ) );
		add_filter( 'woocommerce_webhook_topics', array( $this, 'wc_webhook_topics' ) );
		add_action( 'wpo_wcpdf_save_document', array( $this, 'wc_webhook_trigger' ), 10, 2 );
	}

	/**
	 * Attach PDF to WooCommerce email
	 */
	public function attach_pdf_to_email ( $attachments, $email_id, $order, $email = null ) {
		// check if all variables properly set
		if ( ! is_object( $order ) || ! isset( $email_id ) ) {
			return $attachments;
		}

		// allow third party emails to swap the order object
		$order = apply_filters( 'wpo_wcpdf_email_order_object', $order, $email_id, $email );

		// Skip User emails
		if ( get_class( $order ) == 'WP_User' ) {
			return $attachments;
		}

		$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : false;

		if ( ! ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) && $order_id == false ) {
			return $attachments;
		}

		// WooCommerce Booking compatibility
		if ( get_post_type( $order_id ) == 'wc_booking' && isset( $order->order ) && ! empty( $order->order ) ) {
			// $order is actually a WC_Booking object!
			$order    = $order->order;
			$order_id = $order->get_id();
		}

		// do not process low stock notifications, user emails etc!
		if ( in_array( $email_id, array( 'no_stock', 'low_stock', 'backorder', 'customer_new_account', 'customer_reset_password' ) ) ) {
			return $attachments;
		}

		// final check on order object
		if ( ! ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order' ) ) ) {
			return $attachments;
		}

		$tmp_path = $this->get_tmp_path( 'attachments' );
		if ( ! @is_dir( $tmp_path ) || ! wp_is_writable( $tmp_path ) ) {
			return $attachments;
		}

		// clear pdf files from temp folder (from http://stackoverflow.com/a/13468943/1446634)
		// array_map('unlink', ( glob( $tmp_path.'*.pdf' ) ? glob( $tmp_path.'*.pdf' ) : array() ) );

		// disable deprecation notices during email sending
		add_filter( 'wcpdf_disable_deprecation_notices', '__return_true' );

		// reload translations because WC may have switched to site locale (by setting the plugin_locale filter to site locale in wc_switch_to_site_locale())
		if ( apply_filters( 'wpo_wcpdf_allow_reload_attachment_translations', true ) ) {
			WPO_WCPDF()->translations();
			do_action( 'wpo_wcpdf_reload_attachment_translations' );
		}

		$attach_to_document_types = $this->get_documents_for_email( $email_id, $order );
		foreach ( $attach_to_document_types as $document_type ) {
			$email_order    = apply_filters( 'wpo_wcpdf_email_attachment_order', $order, $email, $document_type );
			$email_order_id = $email_order->get_id();

			do_action( 'wpo_wcpdf_before_attachment_creation', $email_order, $email_id, $document_type );

			try {
				// log document generation to order notes
				add_action( 'wpo_wcpdf_init_document', function( $document ) {
					$this->log_document_creation_to_order_notes( $document, 'email_attachment' );
					$this->log_document_creation_trigger_to_order_meta( $document, 'email_attachment' );
					$this->mark_document_printed( $document, 'email_attachment' );
				} );
				
				// prepare document
				// we use ID to force to reloading the order to make sure that all meta data is up to date.
				// this is especially important when multiple emails with the PDF document are sent in the same session
				$document = wcpdf_get_document( $document_type, (array) $email_order_id, true );
				if ( ! $document ) { // something went wrong, continue trying with other documents
					continue;
				}
				$filename = $document->get_filename();
				$pdf_path = $tmp_path . $filename;

				$lock_file = apply_filters( 'wpo_wcpdf_lock_attachment_file', true );

				// if this file already exists in the temp path, we'll reuse it if it's not older than 60 seconds
				$max_reuse_age = apply_filters( 'wpo_wcpdf_reuse_attachment_age', 60 );
				if ( file_exists( $pdf_path ) && $max_reuse_age > 0 ) {
					// get last modification date
					if ($filemtime = filemtime( $pdf_path )) {
						$time_difference = time() - $filemtime;
						if ( $time_difference < $max_reuse_age ) {
							// check if file is still being written to
							if ( $lock_file && $this->wait_for_file_lock( $pdf_path ) === false ) {
								$attachments[] = $pdf_path;
								continue;
							} else {
								// make sure this gets logged, but don't abort process
								wcpdf_log_error( "Attachment file locked (reusing: {$pdf_path})", 'critical' );
							}
						}
					}
				}

				// get pdf data & store
				$pdf_data = $document->get_pdf();

				if ( $lock_file ) {
					file_put_contents ( $pdf_path, $pdf_data, LOCK_EX );
				} else {
					file_put_contents ( $pdf_path, $pdf_data );					
				}

				// wait for file lock
				if ( $lock_file && $this->wait_for_file_lock( $pdf_path ) === true ) {
					wcpdf_log_error( "Attachment file locked ({$pdf_path})", 'critical' );
				}

				$attachments[] = $pdf_path;

				do_action( 'wpo_wcpdf_email_attachment', $pdf_path, $document_type, $document );
				
			} catch ( \Exception $e ) {
				wcpdf_log_error( $e->getMessage(), 'critical', $e );
				continue;
			} catch ( \Dompdf\Exception $e ) {
				wcpdf_log_error( 'DOMPDF exception: '.$e->getMessage(), 'critical', $e );
				continue;
			} catch ( \Error $e ) {
				wcpdf_log_error( $e->getMessage(), 'critical', $e );
				continue;
			}
		}

		remove_filter( 'wcpdf_disable_deprecation_notices', '__return_true' );

		return $attachments;
	}

	public function file_is_locked( $fp ) {
		if (!flock($fp, LOCK_EX|LOCK_NB, $wouldblock)) {
			if ($wouldblock) {
				return true; // file is locked
			} else {
				return true; // can't lock for whatever reason (could be locked in Windows + PHP5.3)
			}
		} else {
			flock($fp,LOCK_UN); // release lock
			return false; // not locked
		}
	}

	public function wait_for_file_lock( $path ) {
		$fp = fopen($path, 'r+');
		if ( $locked = $this->file_is_locked( $fp ) ) {
			// optional delay (ms) to double check if the write process is finished
			$delay = intval( apply_filters( 'wpo_wcpdf_attachment_locked_file_delay', 250 ) );
			if ( $delay > 0 ) {
				usleep( $delay * 1000 );
				$locked = $this->file_is_locked( $fp );
			}
		}
		fclose($fp);

		return $locked;
	}

	public function get_documents_for_email( $email_id, $order ) {
		$documents = WPO_WCPDF()->documents->get_documents();

		$attach_documents = array();
		foreach ($documents as $document) {
			$attach_documents[ $document->get_type() ] = $document->get_attach_to_email_ids();
		}
		$attach_documents = apply_filters('wpo_wcpdf_attach_documents', $attach_documents );

		$document_types = array();
		foreach ($attach_documents as $document_type => $attach_to_email_ids ) {
			// legacy settings: convert abbreviated email_ids
			foreach ($attach_to_email_ids as $key => $attach_to_email_id) {
				if ($attach_to_email_id == 'completed' || $attach_to_email_id == 'processing') {
					$attach_to_email_ids[$key] = "customer_" . $attach_to_email_id . "_order";
				}
			}

			$extra_condition = apply_filters('wpo_wcpdf_custom_attachment_condition', true, $order, $email_id, $document_type );
			if ( in_array( $email_id, $attach_to_email_ids ) && $extra_condition === true ) {
				$document_types[] = $document_type;
			}
		}

		return apply_filters( 'wpo_wcpdf_document_types_for_email', $document_types, $email_id, $order );
	}

	/**
	 * Load and generate the template output with ajax
	 */
	public function generate_pdf_ajax() {
		$guest_access = WPO_WCPDF()->settings->is_guest_access_enabled();
		if ( ! $guest_access && current_filter() == 'wp_ajax_nopriv_generate_wpo_wcpdf' ) {
			wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// handle legacy access keys
		if ( empty( $_REQUEST['access_key'] ) ) {
			foreach ( array( '_wpnonce', 'order_key' ) as $legacy_key ) {
				if ( ! empty( $_REQUEST[$legacy_key] ) ) {
					$_REQUEST['access_key'] = sanitize_text_field( $_REQUEST[$legacy_key] );
				}
			}
		}

		$valid_nonce = ! empty( $_REQUEST['access_key'] ) && ! empty( $_REQUEST['action'] ) && wp_verify_nonce( $_REQUEST['access_key'], $_REQUEST['action'] );

		// check if we have the access key set
		if ( empty( $_REQUEST['access_key'] ) ) {
			wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// Check the nonce - guest access doesn't use nonces but checks the unique order key (hash)
		if ( empty( $_REQUEST['action'] ) || ( ! $guest_access && ! $valid_nonce ) ) {
			wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// Check if all parameters are set
		if ( empty( $_REQUEST['document_type'] ) && !empty( $_REQUEST['template_type'] ) ) {
			$_REQUEST['document_type'] = $_REQUEST['template_type'];
		}

		if ( empty( $_REQUEST['order_ids'] ) ) {
			wp_die( esc_attr__( "You haven't selected any orders", 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		if( empty( $_REQUEST['document_type'] ) ) {
			wp_die( esc_attr__( 'Some of the export parameters are missing.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// debug enabled by URL
		if ( isset( $_REQUEST['debug'] ) && !( $guest_access || isset( $_REQUEST['my-account'] ) ) ) {
			$this->enable_debug();
		}

		// Generate the output
		$document_type = sanitize_text_field( $_REQUEST['document_type'] );

		$order_ids = (array) array_map( 'absint', explode( 'x', $_REQUEST['order_ids'] ) );
		
		// solo order
		$order = false;
		if ( count( $order_ids ) === 1 ) {
			$order_id = reset( $order_ids );
			$order    = wc_get_order( $order_id );
			if ( $order && $order->get_status() == 'auto-draft' ) {
				wp_die( esc_attr__( 'You have to save the order before generating a PDF document for it.', 'woocommerce-pdf-invoices-packing-slips' ) );
			} elseif ( ! $order ) {
				/* translators: %s: Order ID */
				wp_die( sprintf( esc_attr__( 'Could not find the order #%s.', 'woocommerce-pdf-invoices-packing-slips' ), $order_id ) );
			}
		}

		// Process oldest first: reverse $order_ids array if required
		$sort_order         = apply_filters( 'wpo_wcpdf_bulk_document_sort_order', 'ASC' );
		$current_sort_order = ( count( $order_ids ) > 1 && end( $order_ids ) < reset( $order_ids ) ) ? 'DESC' : 'ASC';
		if ( in_array( $sort_order, array( 'ASC', 'DESC' ) ) && $sort_order != $current_sort_order ) {
			$order_ids = array_reverse( $order_ids );
		}

		// set default is allowed
		$allowed = true;

		if ( $guest_access && ! $valid_nonce ) { // if nonce is invalid maybe we are dealing with the order key
			// Guest access with order key
			if ( count( $order_ids ) > 1 ) {
				$allowed = false;
			} else {
				if ( ! $order || ! hash_equals( $order->get_order_key(), $_REQUEST['access_key'] ) ) {
					$allowed = false;
				}
			}
		} else {
			// check if user is logged in
			if ( ! is_user_logged_in() ) {
				$allowed = false;
			}

			// Check the user privileges
			$full_permission = WPO_WCPDF()->admin->user_can_manage_document( $document_type );
			if ( ! $full_permission ) {
				if ( ! isset( $_GET['my-account'] ) && ! isset( $_GET['shortcode'] ) ) {
					$allowed = false;
				} else { // User call from my-account page or via shortcode
					// Only for single orders!
					if ( count( $order_ids ) > 1 ) {
						$allowed = false;
					}
		
					// Check if current user is owner of order IMPORTANT!!!
					if ( ! current_user_can( 'view_order', $order_ids[0] ) ) {
						$allowed = false;
					}
				}
			}
		}

		$allowed = apply_filters( 'wpo_wcpdf_check_privs', $allowed, $order_ids );

		if ( ! $allowed ) {
			wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// if we got here, we're safe to go!
		try {
			// log document creation to order notes
			if ( count( $order_ids ) > 1 && isset( $_REQUEST['bulk'] ) ) {
				add_action( 'wpo_wcpdf_init_document', function( $document ) {
					$this->log_document_creation_to_order_notes( $document, 'bulk' );
					$this->log_document_creation_trigger_to_order_meta( $document, 'bulk' );
					$this->mark_document_printed( $document, 'bulk' );
				} );
			} elseif ( isset( $_REQUEST['my-account'] ) ) {
				add_action( 'wpo_wcpdf_init_document', function( $document ) {
					$this->log_document_creation_to_order_notes( $document, 'my_account' );
					$this->log_document_creation_trigger_to_order_meta( $document, 'my_account' );
					$this->mark_document_printed( $document, 'my_account' );
				} );
			} else {
				add_action( 'wpo_wcpdf_init_document', function( $document ) {
					$this->log_document_creation_to_order_notes( $document, 'single' );
					$this->log_document_creation_trigger_to_order_meta( $document, 'single' );
					$this->mark_document_printed( $document, 'single' );
				} );
			}

			// get document
			$document = wcpdf_get_document( $document_type, $order_ids, true );

			if ( $document ) {
				do_action( 'wpo_wcpdf_document_created_manually', $document, $order_ids ); // note that $order_ids is filtered and may not be the same as the order IDs used for the document (which can be fetched from the document object itself with $document->order_ids)

				$output_format = WPO_WCPDF()->settings->get_output_format( $document_type );
				// allow URL override
				if ( isset( $_REQUEST['output'] ) && in_array( $_REQUEST['output'], array( 'html', 'pdf' ) ) ) {
					$output_format = $_REQUEST['output'];
				}
				switch ( $output_format ) {
					case 'html':
						add_filter( 'wpo_wcpdf_use_path', '__return_false' );
						$document->output_html();
						break;
					case 'pdf':
					default:
						if ( has_action( 'wpo_wcpdf_created_manually' ) ) {
							do_action( 'wpo_wcpdf_created_manually', $document->get_pdf(), $document->get_filename() );
						}
						$output_mode = WPO_WCPDF()->settings->get_output_mode( $document_type );
						$document->output_pdf( $output_mode );
						break;
				}
			} else {
				/* translators: document type */
				wp_die( sprintf( esc_html__( "Document of type '%s' for the selected order(s) could not be generated", 'woocommerce-pdf-invoices-packing-slips' ), $document_type ) );
			}
		} catch ( \Dompdf\Exception $e ) {
			$message = 'DOMPDF Exception: '.$e->getMessage();
			wcpdf_log_error( $message, 'critical', $e );
			wcpdf_output_error( $message, 'critical', $e );
		} catch ( \Exception $e ) {
			$message = 'Exception: '.$e->getMessage();
			wcpdf_log_error( $message, 'critical', $e );
			wcpdf_output_error( $message, 'critical', $e );
		} catch ( \Error $e ) {
			$message = 'Fatal error: '.$e->getMessage();
			wcpdf_log_error( $message, 'critical', $e );
			wcpdf_output_error( $message, 'critical', $e );
		}
		exit;
	}

	/**
	 * Include template specific custom functions
	 */
	private function load_template_functions() {
		$file = trailingslashit( WPO_WCPDF()->settings->get_template_path() ) . 'template-functions.php';
		if ( file_exists( $file ) ) {
			$loaded = @include_once( $file );
			if ( $loaded === false ) {
				wcpdf_log_error( sprintf( 'Failed to load template functions: %s', $file ), 'critical' );
			}
		}
	}

	/**
	 * Return tmp path for different plugin processes
	 */
	public function get_tmp_path ( $type = '' ) {
		$tmp_base = $this->get_tmp_base();

		// don't continue if we don't have an upload dir
		if ($tmp_base === false) {
			return false;
		}

		// check if tmp folder exists => if not, initialize
		if ( ! @is_dir( $tmp_base ) || ! wp_is_writable( $tmp_base ) ) {
			$this->init_tmp();
		}

		if ( empty( $type ) ) {
			return $tmp_base;
		}

		switch ( $type ) {
			case 'dompdf':
				$tmp_path = $tmp_base . 'dompdf';
				break;
			case 'font_cache':
			case 'fonts':
				$tmp_path = $tmp_base . 'fonts';
				break;
			case 'attachments':
				$tmp_path = $tmp_base . 'attachments/';
				break;
			default:
				$tmp_path = $tmp_base . $type;
				break;
		}

		// double check for existence, in case tmp_base was installed, but subfolder not created
		if ( ! is_dir( $tmp_path ) ) {
			$dir = mkdir( $tmp_path );

			if ( ! $dir ) {
				update_option( 'wpo_wcpdf_no_dir_error', $tmp_path );
				wcpdf_log_error( "Unable to create folder {$tmp_path}", 'critical' );
				return false;
			}
		} elseif( ! wp_is_writable( $tmp_path ) ) {
			update_option( 'wpo_wcpdf_no_dir_error', $tmp_path );
			wcpdf_log_error( "Temp folder {$tmp_path} not writable", 'critical' );
			return false;
		}

		return apply_filters( "wpo_wcpdf_tmp_path_{$type}", $tmp_path );
	}

	/**
	 * return the base tmp folder (usually uploads)
	 */
	public function get_tmp_base ( $append_random_string = true ) {
		// wp_upload_dir() is used to set the base temp folder, under which a
		// 'wpo_wcpdf' folder and several subfolders are created
		//
		// wp_upload_dir() will:
		// * default to WP_CONTENT_DIR/uploads
		// * UNLESS the ‘UPLOADS’ constant is defined in wp-config (http://codex.wordpress.org/Editing_wp-config.php#Moving_uploads_folder)
		//
		// May also be overridden by the wpo_wcpdf_tmp_path filter
		
		$wp_upload_base = $this->get_wp_upload_base();
		if( $wp_upload_base ) {
			if( $append_random_string && $code = $this->get_random_string() ) {
				$tmp_base = $wp_upload_base . 'wpo_wcpdf_'.$code.'/';
			} else {
				$tmp_base = $wp_upload_base . 'wpo_wcpdf/';
			}
		} else {
			$tmp_base = false;
		}

		$tmp_base = apply_filters( 'wpo_wcpdf_tmp_path', $tmp_base );
		if ($tmp_base !== false) {
			$tmp_base = trailingslashit( $tmp_base );
		}

		return $tmp_base;
	}

	/**
	 * Get WordPress uploads folder base
	 */
	public function get_wp_upload_base () {
		$upload_dir = wp_upload_dir();
		if ( ! empty($upload_dir['error']) ) {
			$wp_upload_base = false;
		} else {
			$upload_base = trailingslashit( $upload_dir['basedir'] );
			$wp_upload_base = $upload_base;
		}
		return $wp_upload_base;
	}

	/**
	 * Checks if the tmp subfolder has files
	 * 
	 * @param string $subfolder  can be 'attachments', 'fonts' or 'dompdf'
	 * 
	 * @return bool
	 */
	public function tmp_subfolder_has_files( $subfolder ) {
		$has_files = false;

		if ( empty( $subfolder ) || ! in_array( $subfolder, $this->subfolders ) ) {
			wcpdf_log_error( sprintf( 'The directory %s is not a default tmp subfolder from this plugin.', $subfolder ), 'critical' );
			return $has_files;
		}

		// we have a cached value
		if ( get_transient( "wpo_wcpdf_subfolder_{$subfolder}_has_files" ) !== false ) {
			return wc_string_to_bool( get_transient( "wpo_wcpdf_subfolder_{$subfolder}_has_files" ) );
		}

		if ( ! function_exists( 'glob' ) ) {
			wcpdf_log_error( 'PHP glob function not found.', 'critical' );
			return $has_files;
		}

		$tmp_path = untrailingslashit( $this->get_tmp_path( $subfolder ) );

		switch ( $subfolder ) {
			case 'attachments':
				if ( ! empty( glob( $tmp_path.'/*.pdf' ) ) ) {
					$has_files = true;
				}
				break;
			case 'fonts':
				if ( ! empty( glob( $tmp_path.'/*.ttf' ) ) ) {
					$has_files = true;
				}
				break;
			case 'dompdf':
				if ( ! empty( glob( $tmp_path.'/*.*' ) ) ) {
					$has_files = true;
				}
				break;
		}

		// save value to cache
		set_transient( "wpo_wcpdf_subfolder_{$subfolder}_has_files", ( true === $has_files ) ? 'yes' : 'no' , DAY_IN_SECONDS );

		return $has_files;
	}

	/**
	 * Maybe reinstall fonts
	 * 
	 * @param bool $force  force fonts reinstall
	 * 
	 * @return void
	 */
	public function maybe_reinstall_fonts( $force = false ) {
		if ( false === $this->tmp_subfolder_has_files( 'fonts' ) || true === $force ) {
			$fonts_path = untrailingslashit( $this->get_tmp_path( 'fonts' ) );

			// clear folder first
			if ( function_exists( 'glob' ) && $files = glob( $fonts_path.'/*.*' ) ) {
				$exclude_files = array( 'index.php', '.htaccess' );
				foreach ( $files as $file ) {
					if ( is_file( $file ) && ! in_array( basename( $file ), $exclude_files ) ) {
						unlink( $file );
					}
				}
			} else {
				wcpdf_log_error( "Couldn't clear fonts tmp subfolder before copy fonts.", 'critical' );
			}

			// copy fonts
			$this->copy_fonts( $fonts_path );

			// save to cache
			if ( get_transient( 'wpo_wcpdf_subfolder_fonts_has_files' ) !== false ) {
				delete_transient( 'wpo_wcpdf_subfolder_fonts_has_files' );
			}
			set_transient( 'wpo_wcpdf_subfolder_fonts_has_files', 'yes' , DAY_IN_SECONDS );
		}
	}

	/**
	 * Generate random string
	 */
	public function generate_random_string () {
		if ( function_exists( 'random_bytes' ) ) { // PHP7+
			$code = bin2hex(random_bytes(16));
		} else {
			$code = md5(uniqid(rand(), true));
		}
		// create option
		update_option( 'wpo_wcpdf_random_string', $code );
	}

	/**
	 * Get random string
	 */
	public function get_random_string () {
		$code = sanitize_text_field( get_option( 'wpo_wcpdf_random_string' ) );
		if( $code ) {
			return $code;
		} else {
			return false;
		}
	}

	/**
	 * Install/create plugin tmp folders
	 */
	public function init_tmp () {
		// generate random string if don't exist
		if( ! $this->get_random_string() ) {
			$this->generate_random_string();
		}

		// get tmp base
		$tmp_base = $this->get_tmp_base();

		// create plugin base temp folder
		if ( ! is_dir( $tmp_base ) ) {
			$dir = mkdir( $tmp_base );

			// don't continue if we don't have an upload dir
			if ( ! $dir ) {
				update_option( 'wpo_wcpdf_no_dir_error', $tmp_base );
				wcpdf_log_error( "Unable to create temp folder {$tmp_base}", 'critical' );
				return false;
			}
		} elseif( ! wp_is_writable( $tmp_base ) ) {
			update_option( 'wpo_wcpdf_no_dir_error', $tmp_base );
			wcpdf_log_error( "Temp folder {$tmp_base} not writable", 'critical' );
			return false;
		}

		// create subfolders & protect
		foreach ( $this->subfolders as $subfolder ) {
			$path = $tmp_base . $subfolder . '/';
			if ( ! is_dir( $path ) ) {
				$dir = mkdir( $path );

				// check if we have dir
				if ( ! $dir ) {
					update_option( 'wpo_wcpdf_no_dir_error', $path );
					wcpdf_log_error( "Unable to create folder {$path}", 'critical' );
					return false;
				}
			} elseif( ! wp_is_writable( $path ) ) {
				update_option( 'wpo_wcpdf_no_dir_error', $path );
				wcpdf_log_error( "Temp folder {$path} not writable", 'critical' );
				return false;
			}

			// copy font files
			if ( $subfolder == 'fonts' ) {
				$this->copy_fonts( $path, false );
			}

			// create .htaccess file and empty index.php to protect in case an open webfolder is used!
			file_put_contents( $path . '.htaccess', 'deny from all' );
			touch( $path . 'index.php' );
		}
	}

	public function no_dir_notice() {
		if( is_admin() && ( $path = get_option( 'wpo_wcpdf_no_dir_error' ) ) ) {
			// if all folders exist and are writable delete the option
			if( $this->tmp_folders_exist_and_writable() ) {
				delete_option( 'wpo_wcpdf_no_dir_error' );
			// if not, show notice
			} else {
				if ( $path ) {
					ob_start();
					?>
					<div class="error">
					<?php /* translators: 1. plugin name, 2. directory path */ ?>
						<p><?php printf( esc_html__( 'The %1$s directory %2$s couldn\'t be created or is not writable!', 'woocommerce-pdf-invoices-packing-slips' ), '<strong>PDF Invoices & Packing Slips for WooCommerce</strong>' ,'<code>' . $path . '</code>' ); ?></p>
						<p><?php esc_html_e( 'Please check your directories write permissions or contact your hosting service provider.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
						<p><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_hide_no_dir_notice', 'true' ), 'hide_no_dir_notice_nonce' ) ); ?>"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
					</div>
					<?php
					echo wp_kses_post( ob_get_clean() );
		
					// save option to hide notice
					if ( isset( $_REQUEST['wpo_wcpdf_hide_no_dir_notice'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
						// validate nonce
						if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'hide_no_dir_notice_nonce' ) ) {
							wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_hide_no_dir_notice' );
							wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
							exit;
						} else {
							delete_option( 'wpo_wcpdf_no_dir_error' );
							wp_redirect( 'admin.php?page=wpo_wcpdf_options_page' );
							exit;
						}
					}
				}
			}
		}
	}

	/**
	 * Copy contents from one directory to another
	 */
	public function copy_directory ( $old_path, $new_path ) {
		if( empty($old_path) || empty($new_path) ) return;
		if( ! is_dir($old_path) ) return;
		if( ! is_dir($new_path) ) {
			$dir = mkdir($new_path);

			// check if we have dir
			if ( ! $dir ) {
				update_option( 'wpo_wcpdf_no_dir_error', $new_path );
				wcpdf_log_error( "Unable to create folder {$new_path}", 'critical' );
				return false;
			}
		} elseif( ! wp_is_writable( $new_path ) ) {
			update_option( 'wpo_wcpdf_no_dir_error', $new_path );
			wcpdf_log_error( "Temp folder {$new_path} not writable", 'critical' );
			return false;
		}

		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		if ( ! WP_Filesystem() ) {
			wcpdf_log_error( "WP_Filesystem couldn't be initiated! Unable to copy directory contents.", 'critical' );
			return;
		}

		// we have the directories, let's try to copy
		try {
			$result = copy_dir( $old_path, $new_path );
			// delete old directory with contents
			if( $result ) {
				$wp_filesystem->delete( $old_path, true );
			}
		} catch ( \Error $e ) {
			wcpdf_log_error( "Unable to copy directory contents: ".$e->getMessage(), 'critical', $e );
			return;
		}
	}

	/**
	 * checks if the plugin tmp folders exist and are writable
	 */
	private function tmp_folders_exist_and_writable()
	{
		// tmp base
		$tmp_base = $this->get_tmp_base();
		if( ! @is_dir( $tmp_base ) || ! wp_is_writable( $tmp_base ) ) {
			return false;
		}

		// subfolders
		foreach( $this->subfolders as $type ) {
			$tmp_path = $this->get_tmp_path( $type );
			if( ! @is_dir( $tmp_path ) || ! wp_is_writable( $tmp_base ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Copy DOMPDF fonts to wordpress tmp folder
	 */
	public function copy_fonts( $path = '', $merge_with_local = true ) {
		// only copy fonts if the bundled dompdf library is used!
		$default_pdf_maker = '\\WPO\\WC\\PDF_Invoices\\PDF_Maker';
		if ( $default_pdf_maker !== apply_filters( 'wpo_wcpdf_pdf_maker', $default_pdf_maker ) ) {
			return;
		}

		if ( empty( $path ) ) {
			$path = $this->get_tmp_path( 'fonts' );
		}
		$path = trailingslashit( $path );

		// get local font dir from filtered options
		$dompdf_options = apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'defaultFont'             => 'dejavu sans',
			'tempDir'                 => $this->get_tmp_path( 'dompdf' ),
			'logOutputFile'           => $this->get_tmp_path( 'dompdf' ) . "/log.htm",
			'fontDir'                 => $path,
			'fontCache'               => $path,
			'isRemoteEnabled'         => true,
			'isFontSubsettingEnabled' => true,
			'isHtml5ParserEnabled'    => true,
		) );
		$fontDir = $dompdf_options['fontDir'];

		$synchronizer = new Font_Synchronizer();
		$synchronizer->sync( $fontDir, $merge_with_local );
	}

	public function disable_free( $allowed, $document ) {
		if( ! $document->exists() && ! empty($order = $document->order) ) {
			if ( ! is_callable( array($order, 'get_total') ) ) {
				return false;
			}
			// check order total & setting
			$order_total = $order->get_total();
			if ( $order_total == 0 && $document->get_setting('disable_free') ) {
				return false;
			} else {
				return $allowed;
			}
		} else {
			return $allowed;
		}
	}
	
	public function disable_anonymized( $allowed, $document ) {
		if ( ! empty( $document->order ) && ! empty( $anonymized = $document->order->get_meta( '_anonymized' ) ) ) {
			if ( apply_filters( 'wpo_wcpdf_disallow_anonymized_order_document', wc_string_to_bool( $anonymized ), $this ) ) {
				$allowed = false;
			}
		}
		return $allowed;
	}

	public function test_mode_settings( $use_historical_settings, $document ) {
		if ( isset( WPO_WCPDF()->settings->general_settings['test_mode'] ) ) {
			$use_historical_settings = false;
		}
		return $use_historical_settings;
	}

	/**
	 * Adds spans around placeholders to be able to make replacement (page count) and css (page number)
	 */
	public function format_page_number_placeholders ( $html, $document ) {
		$html = str_replace('{{PAGE_COUNT}}', '<span class="pagecount">^C^</span>', $html);
		$html = str_replace('{{PAGE_NUM}}', '<span class="pagenum"></span>', $html );
		return $html;
	}

	/**
	 * Replace {{PAGE_COUNT}} placeholder with total page count
	 */
	public function page_number_replacements ( $dompdf, $html ) {
		$placeholder = '^C^';
		// create placeholder version with ASCII 0 spaces (dompdf 0.8)
		$placeholder_0 = '';
		$placeholder_chars = str_split($placeholder);
		foreach ($placeholder_chars as $placeholder_char) {
			$placeholder_0 .= chr(0).$placeholder_char;
		}

		// check if placeholder is used
		if (strpos($html, $placeholder) !== false ) {
			foreach ($dompdf->get_canvas()->get_cpdf()->objects as &$object) {
				if (array_key_exists("c", $object) && strpos($object["c"], $placeholder) !== false ) {
					$object["c"] = str_replace( array($placeholder,$placeholder_0) , $dompdf->get_canvas()->get_page_count() , $object["c"] );
				} elseif (array_key_exists("c", $object) && strpos($object["c"], $placeholder_0) !== false ) {
					$object["c"] = str_replace( array($placeholder,$placeholder_0) , chr(0).$dompdf->get_canvas()->get_page_count() , $object["c"] );
				}
			}
		}

		return $dompdf;
	}

	public function pdf_currency_filters( $filters ) {
		if ( isset( WPO_WCPDF()->settings->general_settings['currency_font'] ) ) {
			$filters[] = array( 'woocommerce_currency_symbol', array( $this, 'use_currency_font' ), 10001, 2 );
			// 'wpo_wcpdf_custom_styles' is actually an action, but WP handles them with the same functions
			$filters[] = array( 'wpo_wcpdf_custom_styles', array( $this, 'currency_symbol_font_styles' ) );
		}
		return $filters;
	}

	public function html_currency_filters( $filters ) {
		// only apply these fixes if the bundled dompdf version is used!
		if ( wcpdf_pdf_maker_is_default() ) {
			$filters[] = array( 'woocommerce_currency_symbol', array( $this, 'use_currency_code' ), 10001, 2 );
		}
		return $filters;
	}

	/**
	 * Use currency symbol font (when enabled in options)
	 * @param string $currency_symbol Currency symbol
	 * @param string $currency        Currency
	 * 
	 * @return string Currency symbol
	 */
	public function use_currency_font( $currency_symbol, $currency ) {
		$currency_symbol = sprintf( '<span class="wcpdf-currency-symbol">%s</span>', $currency_symbol );
		return $currency_symbol;
	}

	/**
	 * Set currency font CSS
	 */
	public function currency_symbol_font_styles () {
		?>
		.wcpdf-currency-symbol { font-family: 'Currencies'; }
		<?php
	}
	
	/**
	 * Replace dompdf incompatible (RTL) currencies with the ISO currency code (when default dompdf is used)
	 * @param string $currency_symbol Currency symbol
	 * @param string $currency        Currency
	 * 
	 * @return string Currency symbol
	 */
	public function use_currency_code( $currency_symbol, $currency ) {
		if ( in_array( $currency, $this->get_rtl_currencies() ) ) {
			$currency_symbol = $currency;
		}
		return $currency_symbol;
	}

	/**
	 * Get all currencies that require RTL text direction support
	 * 
	 * @return array ISO currency codes
	 */
	public function get_rtl_currencies() {
		return array( 'AED', 'BHD', 'DZD', 'IQD', 'IRR', 'JOD', 'KWD', 'LBP', 'LYD', 'MAD', 'MVR', 'OMR', 'QAR', 'SAR', 'SYP', 'TND', 'YER' );
	}

	/**
	 * Apply header logo height from settings
	 */
	public function set_header_logo_height( $document_type, $document = null ) {
		if ( !empty($document) && $header_logo_height = $document->get_header_logo_height() ) {
			?>
			td.header img {
				max-height: <?php echo esc_html( $header_logo_height ); ?>;
			}
			<?php
		}
	}

	/**
	 * Schedule temporary files cleanup from paths older than 1 week (daily, hooked into wp_scheduled_delete )
	 */
	public function schedule_temporary_files_cleanup() {
		if ( ! isset( WPO_WCPDF()->settings->debug_settings['enable_cleanup'] ) ) {
			return;
		}

		$cleanup_age_days = isset( WPO_WCPDF()->settings->debug_settings['cleanup_days'] ) ? floatval( WPO_WCPDF()->settings->debug_settings['cleanup_days'] ) : 7.0;
		$delete_timestamp = time() - ( intval ( DAY_IN_SECONDS * $cleanup_age_days ) );
		$this->temporary_files_cleanup( $delete_timestamp );
	}
	
	/**
	 * Temporary files cleanup from paths
	 * @param  int    $delete_timestamp timestamp of the date/time before which to clean up files
	 * 
	 * @return array  Output message
	 */
	public function temporary_files_cleanup( $delete_timestamp = 0 ) {
		$delete_before    = ! empty( $delete_timestamp ) ? intval( $delete_timestamp ) : time();
		$paths_to_cleanup = apply_filters( 'wpo_wcpdf_cleanup_tmp_paths', array(
			$this->get_tmp_path( 'attachments' ),
			$this->get_tmp_path( 'dompdf' ),
		) );
		$excluded_files   = apply_filters( 'wpo_wcpdf_cleanup_excluded_files', array(
			'index.php',
			'.htaccess',
			'log.htm',
		) );
		$folders_level    = apply_filters( 'wpo_wcpdf_cleanup_folders_level', 3 );
		$files            = array();
		$success          = 0;
		$error            = 0;
		$output           = array();

		foreach ( $paths_to_cleanup as $path ) {
			if ( ! function_exists( 'list_files' ) ) {
				include_once( ABSPATH.'wp-admin/includes/file.php' );
			}
			if ( $listed_files = list_files( $path, $folders_level ) ) {
				$files = array_merge( $files, $listed_files );
			}
		}

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				$basename = wp_basename( $file );
				if ( ! in_array( $basename, $excluded_files ) && file_exists( $file ) ) {
					$file_timestamp = filemtime( $file );

					// delete file
					if ( $file_timestamp < $delete_before ) {
						if ( unlink( $file ) ) {
							$success++;
						} else {
							$error++;
						}
					}
				}
			}

			if ( $error > 0 ) {
				/* translators: 1,2. file count  */
				$message           = sprintf( esc_html__( 'Unable to delete %1$d files! (deleted %2$d)', 'woocommerce-pdf-invoices-packing-slips' ), $error, $success );
				$output['error']   = $message;
			} else {
				/* translators: file count */
				$message           = sprintf( esc_html__( 'Successfully deleted %d files!', 'woocommerce-pdf-invoices-packing-slips' ), $success );
				$output['success'] = $message;
			}
		} else {
			$output['success'] = esc_html__( 'Nothing to delete!', 'woocommerce-pdf-invoices-packing-slips' );
		}

		return $output;
	}

	/**
	 * Remove all invoice data when requested
	 */
	public function remove_order_personal_data_meta( $meta_to_remove ) {
		$wcpdf_private_meta = array(
			'_wcpdf_invoice_number'         => 'numeric_id',
			'_wcpdf_invoice_number_data'    => 'array',
			'_wcpdf_invoice_date'           => 'timestamp',
			'_wcpdf_invoice_date_formatted' => 'date',
		);
		return $meta_to_remove + $wcpdf_private_meta;
	}

	/**
	 * Remove references to order in number store tables when removing WC data
	 */
	public function remove_order_personal_data( $order ) {
		global $wpdb;
		// remove order ID from number stores
		$number_stores = apply_filters( "wpo_wcpdf_privacy_number_stores", array( 'invoice_number' ) );
		foreach ( $number_stores as $store_name ) {
			$order_id = $order->get_id();
			$table_name = apply_filters( "wpo_wcpdf_number_store_table_name", "{$wpdb->prefix}wcpdf_{$store_name}", $store_name, 'auto_increment' ); // i.e. wp_wcpdf_invoice_number
			$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET order_id = 0 WHERE order_id = %s", $order_id ) );
		}
	}

	/**
	 * Export all invoice data when requested
	 */
	public function export_order_personal_data_meta( $meta_to_export ) {
		$private_address_meta = array(
			// _wcpdf_invoice_number_data & _wcpdf_invoice_date are duplicates of the below and therefor not included
			'_wcpdf_invoice_number'         => esc_html__( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'_wcpdf_invoice_date_formatted' => esc_html__( 'Invoice Date', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		return $meta_to_export + $private_address_meta;
	}

	/**
	 * Set the default PHPMailer validator to 'php' ( which uses filter_var($address, FILTER_VALIDATE_EMAIL) )
	 * This avoids issues with the presence of attachments affecting email address validation in some distros of PHP 7.3
	 * See: https://wordpress.org/support/topic/invalid-address-setfrom/#post-11583815
	 * Fixed in WP5.5 due to upgrade to newer PHPMailer
	 */
	public function set_phpmailer_validator( $mailArray ) {
		if ( version_compare( PHP_VERSION, '7.3', '>=' ) && version_compare( get_bloginfo( 'version' ), '5.5-dev', '<' ) ) {
			global $phpmailer;
			if ( ! ( $phpmailer instanceof \PHPMailer ) ) {
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				require_once ABSPATH . WPINC . '/class-smtp.php';
				$phpmailer = new \PHPMailer( true );
			}
			$phpmailer::$validator = 'php';
		}

		return $mailArray;
	}
	
	/**
	 * Log document creation to order notes
	 *
	 * @param object $document
	 * @param string $trigger
	 * @return void
	 */
	public function log_document_creation_to_order_notes( $document, $trigger ) {
		$triggers = $this->get_document_triggers();
		if ( ! empty( $document ) && isset( WPO_WCPDF()->settings->debug_settings['log_to_order_notes'] ) && ! empty( $trigger ) && array_key_exists( $trigger, $triggers ) ) {
			/* translators: 1. document title, 2. creation trigger */
			$message = __( 'PDF %1$s created via %2$s.', 'woocommerce-pdf-invoices-packing-slips' );
			$note    = sprintf( $message, $document->get_title(), $triggers[$trigger] );
			$this->log_to_order_notes( $note, $document );
		}
	}
	
	/**
	 * Log document printed to order notes
	 *
	 * @param object $document
	 * @param string $trigger
	 * @return void
	 */
	public function log_document_printed_to_order_notes( $document, $trigger ) {
		$triggers = array_merge(
			[ 'manually' => __( 'manually', 'woocommerce-pdf-invoices-packing-slips' ) ],
			$this->get_document_triggers()
		);
		
		if ( ! empty( $document ) && isset( WPO_WCPDF()->settings->debug_settings['log_to_order_notes'] ) && ! empty( $trigger ) && array_key_exists( $trigger, $triggers ) ) {
			/* translators: 1. document title, 2. creation trigger */
			$message = __( '%1$s document marked as printed via %2$s.', 'woocommerce-pdf-invoices-packing-slips' );
			$note    = sprintf( $message, $document->get_title(), $triggers[$trigger] );
			$this->log_to_order_notes( $note, $document );
		}
	}
	
	/**
	 * Log document unmark printed to order notes
	 *
	 * @param object $document
	 * @param string $trigger
	 * @return void
	 */
	public function log_unmark_document_printed_to_order_notes( $document ) {
		if ( ! empty( $document ) && isset( WPO_WCPDF()->settings->debug_settings['log_to_order_notes'] ) ) {
			/* translators: 1. document title, 2. creation trigger */
			$message = __( '%1$s document unmark printed.', 'woocommerce-pdf-invoices-packing-slips' );
			$note    = sprintf( $message, $document->get_title() );
			$this->log_to_order_notes( $note, $document );
		}
	}

	/**
	 * Logs to the order notes
	 *
	 * @param string $note
	 * @param object $document
	 * @return void
	 */
	public function log_to_order_notes( $note, $document ) {
		if ( property_exists( $document, 'order_ids' ) && ! empty( $document->order_ids ) ) { // bulk document
			$order_ids = $document->order_ids;
		} else {
			$order_ids = [ $document->order->get_id() ];
		}
		
		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( empty( $order ) ) {
				continue;
			}
			if ( is_callable( array( $order, 'add_order_note' ) ) ) { // order
				$order->add_order_note( strip_tags( $note ) );
			} elseif ( $document->is_refund( $order ) ) {            // refund order
				$parent_order = $document->get_refund_parent( $order );
				if ( ! empty( $parent_order ) && is_callable( array( $parent_order, 'add_order_note' ) ) ) {
					$parent_order->add_order_note( strip_tags( $note ) );
				}
			}
		}
	}
	
	/**
	 * Logs to the order meta
	 *
	 * @param object  $document
	 * @param string  $trigger
	 * @param boolean $force
	 * @return void
	 */
	public function log_document_creation_trigger_to_order_meta( $document, $trigger, $force = false ) {
		if ( $trigger == 'bulk' && property_exists( $document, 'order_ids' ) && ! empty( $document->order_ids ) ) { // bulk document
			$order_ids = $document->order_ids;
		} else {
			$order_ids = [ $document->order->get_id() ];
		}
		
		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! empty( $order ) ) { 
				$type   = $document->get_type();
				$status = $order->get_meta( "_wcpdf_{$type}_creation_trigger" );
				 
				if ( true == $force || empty( $status ) ) {
					$order->update_meta_data( "_wcpdf_{$type}_creation_trigger", $trigger );
					$order->save_meta_data();
				}
			}
		}
	}
	
	/**
	 * Get the document triggers
	 *
	 * @return array
	 */
	public function get_document_triggers() {
		return apply_filters( 'wpo_wcpdf_document_triggers', [
			'single'           => __( 'single order action', 'woocommerce-pdf-invoices-packing-slips' ),
			'bulk'             => __( 'bulk order action', 'woocommerce-pdf-invoices-packing-slips' ),
			'my_account'       => __( 'my account', 'woocommerce-pdf-invoices-packing-slips' ),
			'email_attachment' => __( 'email attachment', 'woocommerce-pdf-invoices-packing-slips' ),
			'document_data'    => __( 'order document data (number and/or date set manually)', 'woocommerce-pdf-invoices-packing-slips' ),
		] );
	}
	
	/**
	 * Mark document printed
	 *
	 * @return void
	 */
	public function mark_document_printed( $document, $trigger ) {
		$triggers = isset( $document->latest_settings['mark_printed'] ) && is_array( $document->latest_settings['mark_printed'] ) ? $document->latest_settings['mark_printed'] : [];
		if ( ! empty( $document ) && ! $this->is_document_printed( $document ) ) {
			if ( ! empty( $order = $document->order ) && ! empty( $trigger ) && in_array( $trigger, $triggers ) && apply_filters( 'wpo_wcpdf_allow_mark_document_printed', true, $document, $trigger ) ) {
				if ( 'shop_order' === $order->get_type() ) {
					$data = [
						'date'    => time(),
						'trigger' => $trigger,
					];
					
					$order->update_meta_data( "_wcpdf_{$document->slug}_printed", $data );
					$order->save_meta_data();
					$this->log_document_printed_to_order_notes( $document, $trigger );
				}
			}
		}
	}
	
	/**
	 * Unmark document printed
	 *
	 * @return void
	 */
	public function unmark_document_printed( $document ) {
		if ( ! empty( $document ) && $this->is_document_printed( $document ) ) {
			if ( ! empty( $order = $document->order ) && apply_filters( 'wpo_wcpdf_allow_unmark_document_printed', true, $document ) ) {
				$meta_key = "_wcpdf_{$document->slug}_printed";
				if ( 'shop_order' === $order->get_type() && ! empty( $order->get_meta( $meta_key ) ) ) {				
					$order->delete_meta_data( $meta_key );
					$order->save_meta_data();
					$this->log_unmark_document_printed_to_order_notes( $document );
				}
			}
		}
	}
	
	/**
	 * AJAX request for mark/unmark document printed
	 *
	 * @return void
	 */
	public function document_printed_ajax() {
		check_ajax_referer( 'printed_wpo_wcpdf', 'security' );
		
		$data  = stripslashes_deep( $_REQUEST );
		$error = 0;
		
		if ( ! empty( $data['action'] ) && $data['action'] == "printed_wpo_wcpdf" && ! empty( $data['event'] ) && ! empty( $data['document_type'] ) && ! empty( $data['order_id'] ) && ! empty( $data['trigger'] ) ) {
			$document        = wcpdf_get_document( esc_attr( $data['document_type'] ), esc_attr( $data['order_id'] ) );
			$full_permission = WPO_WCPDF()->admin->user_can_manage_document( esc_attr( $data['document_type'] ) );
			
			if ( ! empty( $document ) && ! empty( $order = $document->order ) && $full_permission ) {
				switch ( esc_attr( $data['event'] ) ) {
					case 'mark':
						$this->mark_document_printed( $document, esc_attr( $data['trigger'] ) );
						break;
					case 'unmark':
						$this->unmark_document_printed( $document );
						break;
				}
				
				if ( is_callable( [ $order, 'get_edit_order_url' ] ) ) {
					wp_redirect( $order->get_edit_order_url() );
				} else {
					wp_redirect( admin_url( 'post.php?action=edit&post=' . esc_attr( $data['order_id'] ) ) );
				}
			} else {
				$error++;
			}
		} else {
			$error++;
		}
		
		if ( $error > 0 ) {
			/* translators: 1. document type, 2. mark/unmark */
			wp_die( sprintf( esc_html__( "Document of type '$1%s' for the selected order could not be $2%s as printed.", 'woocommerce-pdf-invoices-packing-slips' ), esc_attr( $data['document_type'] ), $event_type ) );
		}
	}
	
	/**
	 * Check if a document is printed
	 *
	 * @return bool
	 */
	public function is_document_printed( $document ) {
		$is_printed = false;
		
		if ( ! empty( $document ) && ! empty( $order = $document->order ) ) {
			if ( 'shop_order' === $order->get_type() && ! empty( $printed_data = $order->get_meta( "_wcpdf_{$document->slug}_printed" ) ) ) {	
				$is_printed = true;
			}
		}
		
		return $is_printed;
	}
	
	/**
	 * Check if a document can be manually marked as printed
	 *
	 * @return bool
	 */
	public function document_can_be_manually_marked_printed( $document ) {
		$can_be_manually_marked_printed = false;
		
		if ( empty( $document ) || ( property_exists( $document, 'is_bulk' ) && $document->is_bulk ) ) {
			return $can_be_manually_marked_printed;
		}
		
		$document->save_settings();
		
		$can_be_manually_marked_printed = false;
		$document_exists                = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
		$document_printed               = $document_exists && is_callable( array( $document, 'printed' ) ) ? $document->printed() : false;
		$triggers                       = isset( $document->latest_settings['mark_printed'] ) && is_array( $document->latest_settings['mark_printed'] ) ? $document->latest_settings['mark_printed'] : [];
		$manually_print_enabled         = in_array( 'manually', $triggers ) ? true : false;
		
		if ( $document_exists && ! $document_printed && $manually_print_enabled ) {
			$can_be_manually_marked_printed = true;
		}
		
		return apply_filters( 'wpo_wcpdf_document_can_be_manually_marked_printed', $can_be_manually_marked_printed, $document );
	}
	
	/**
	 * Get document printed data
	 *
	 * @return array
	 */
	public function get_document_printed_data( $document ) {
		$data = [];
		
		if ( ! empty( $document ) && $this->is_document_printed( $document ) && ! empty( $order = $document->order ) ) {
			if ( 'shop_order' === $order->get_type() && ! empty( $printed_data = $order->get_meta( "_wcpdf_{$document->slug}_printed" ) ) ) {	
				$data = $printed_data;
			}
		}
		
		return apply_filters( 'wpo_wcpdf_document_printed_data', $data, $document );
	}

	/**
	 * Enable PHP error output
	 */
	public function enable_debug () {
		error_reporting( E_ALL );
		ini_set( 'display_errors', 1 );
	}

	public function wc_webhook_topic_hooks( $topic_hooks, $wc_webhook ) {
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ($documents as $document) {
			$topic_hooks["order.{$document->type}-saved"] = array(
				"wpo_wcpdf_webhook_order_{$document->slug}_saved",
			);
		}
		return $topic_hooks;
	}

	public function wc_webhook_topic_events( $topic_events ) {
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ($documents as $document) {
			$topic_events[] = "{$document->type}-saved";
		}
		return $topic_events;
	}

	public function wc_webhook_topics( $topics ) {
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ($documents as $document) {
			/* translators: document title */
			$topics["order.{$document->type}-saved"] = esc_html( sprintf( __( 'Order %s Saved', 'woocommerce-pdf-invoices-packing-slips' ), $document->get_title() ) );
		}
		return $topics;
	}

	public function wc_webhook_trigger( $document, $order ) {
		do_action( "wpo_wcpdf_webhook_order_{$document->slug}_saved", $order->get_id() );
	}
	
}

endif; // class_exists

return new Main();
