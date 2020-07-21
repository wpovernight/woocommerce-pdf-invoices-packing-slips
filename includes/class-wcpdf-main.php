<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Main' ) ) :

class Main {

	function __construct()	{
		add_action( 'wp_ajax_generate_wpo_wcpdf', array($this, 'generate_pdf_ajax' ) );
		add_action( 'wp_ajax_nopriv_generate_wpo_wcpdf', array($this, 'generate_pdf_ajax' ) );

		// email
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf_to_email' ), 99, 4 );
		add_filter( 'wpo_wcpdf_custom_attachment_condition', array( $this, 'disable_free_attachment'), 1001, 4 );
		add_filter( 'wp_mail', array( $this, 'set_phpmailer_validator'), 10, 1 );

		if ( isset(WPO_WCPDF()->settings->debug_settings['enable_debug']) ) {
			$this->enable_debug();
		}

		// include template specific custom functions
		$template_path = WPO_WCPDF()->settings->get_template_path();
		if ( file_exists( $template_path . '/template-functions.php' ) ) {
			require_once( $template_path . '/template-functions.php' );
		}

		// test mode
		add_filter( 'wpo_wcpdf_document_use_historical_settings', array( $this, 'test_mode_settings' ), 15, 2 );

		// page numbers & currency filters
		add_filter( 'wpo_wcpdf_get_html', array($this, 'format_page_number_placeholders' ), 10, 2 );
		add_action( 'wpo_wcpdf_after_dompdf_render', array($this, 'page_number_replacements' ), 9, 2 );
		if ( isset( WPO_WCPDF()->settings->general_settings['currency_font'] ) ) {
			add_action( 'wpo_wcpdf_before_pdf', array($this, 'use_currency_font' ), 10, 2 );
		}

		// scheduled attachments cleanup (following settings on Status tab)
		add_action( 'wp_scheduled_delete', array( $this, 'attachments_cleanup') );

		// remove private data
		add_action( 'woocommerce_privacy_remove_order_personal_data_meta', array( $this, 'remove_order_personal_data_meta' ), 10, 1 );
		add_action( 'woocommerce_privacy_remove_order_personal_data', array( $this, 'remove_order_personal_data' ), 10, 1 );
		// export private data
		add_action( 'woocommerce_privacy_export_order_personal_data_meta', array( $this, 'export_order_personal_data_meta' ), 10, 1 );

		// apply header logo height
		add_action( 'wpo_wcpdf_custom_styles', array( $this, 'set_header_logo_height' ), 9, 2 );
	}

	/**
	 * Attach PDF to WooCommerce email
	 */
	public function attach_pdf_to_email ( $attachments, $email_id, $order, $email = null ) {
		// check if all variables properly set
		if ( !is_object( $order ) || !isset( $email_id ) ) {
			return $attachments;
		}

		// Skip User emails
		if ( get_class( $order ) == 'WP_User' ) {
			return $attachments;
		}

		$order_id = WCX_Order::get_id( $order );

		if ( ! ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) && $order_id == false ) {
			return $attachments;
		}

		// WooCommerce Booking compatibility
		if ( get_post_type( $order_id ) == 'wc_booking' && isset($order->order) ) {
			// $order is actually a WC_Booking object!
			$order = $order->order;
			$order_id = WCX_Order::get_id( $order );
		}

		// do not process low stock notifications, user emails etc!
		if ( in_array( $email_id, array( 'no_stock', 'low_stock', 'backorder', 'customer_new_account', 'customer_reset_password' ) ) ) {
			return $attachments;
		}

		// final check on order object
		if ( ! ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) ) {
			return $attachments;
		}

		$tmp_path = $this->get_tmp_path('attachments');

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
			$email_order_id = WCX_Order::get_id( $email_order );

			do_action( 'wpo_wcpdf_before_attachment_creation', $email_order, $email_id, $document_type );

			try {
				// prepare document
				// we use ID to force to reloading the order to make sure that all meta data is up to date.
				// this is especially important when multiple emails with the PDF document are sent in the same session
				$document = wcpdf_get_document( $document_type, (array) $email_order_id, true );
				if ( !$document ) { // something went wrong, continue trying with other documents
					continue;
				}
				$filename = $document->get_filename();
				$pdf_path = $tmp_path . $filename;

				$lock_file = apply_filters( 'wpo_wcpdf_lock_attachment_file', true );

				// if this file already exists in the temp path, we'll reuse it if it's not older than 60 seconds
				$max_reuse_age = apply_filters( 'wpo_wcpdf_reuse_attachment_age', 60 );
				if ( file_exists($pdf_path) && $max_reuse_age > 0 ) {
					// get last modification date
					if ($filemtime = filemtime($pdf_path)) {
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
		$guest_access = isset( WPO_WCPDF()->settings->debug_settings['guest_access'] );
		if ( !$guest_access && current_filter() == 'wp_ajax_nopriv_generate_wpo_wcpdf') {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// Check the nonce - guest access doesn't use nonces but checks the unique order key (hash)
		if( empty( $_GET['action'] ) || ( !$guest_access && !check_admin_referer( $_GET['action'] ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// Check if all parameters are set
		if ( empty( $_GET['document_type'] ) && !empty( $_GET['template_type'] ) ) {
			$_GET['document_type'] = $_GET['template_type'];
		}

		if ( empty( $_GET['order_ids'] ) ) {
			wp_die( __( "You haven't selected any orders", 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		if( empty( $_GET['document_type'] ) ) {
			wp_die( __( 'Some of the export parameters are missing.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// debug enabled by URL
		if ( isset( $_GET['debug'] ) && !( $guest_access || isset( $_GET['my-account'] ) ) ) {
			$this->enable_debug();
		}

		// Generate the output
		$document_type = sanitize_text_field( $_GET['document_type'] );

		$order_ids = (array) array_map( 'absint', explode( 'x', $_GET['order_ids'] ) );
		// Process oldest first: reverse $order_ids array
		$order_ids = array_reverse( $order_ids );

		// set default is allowed
		$allowed = true;


		if ( $guest_access && isset( $_GET['order_key'] ) ) {
			// Guest access with order key
			if ( count( $order_ids ) > 1 ) {
				$allowed = false;
			} else {
				$order = wc_get_order( $order_ids[0] );
				if ( !$order || ! hash_equals( $order->get_order_key(), $_GET['order_key'] ) ) {
					$allowed = false;
				}
			}
		} else {
			// check if user is logged in
			if ( ! is_user_logged_in() ) {
				$allowed = false;
			}

			// Check the user privileges
			if( !( current_user_can( 'manage_woocommerce_orders' ) || current_user_can( 'edit_shop_orders' ) ) && !isset( $_GET['my-account'] ) ) {
				$allowed = false;
			}

			// User call from my-account page
			if ( !current_user_can('manage_options') && isset( $_GET['my-account'] ) ) {
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

		$allowed = apply_filters( 'wpo_wcpdf_check_privs', $allowed, $order_ids );

		if ( ! $allowed ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-pdf-invoices-packing-slips' ) );
		}

		// if we got here, we're safe to go!
		try {
			$document = wcpdf_get_document( $document_type, $order_ids, true );

			if ( $document ) {
				$output_format = WPO_WCPDF()->settings->get_output_format( $document_type );
				// allow URL override
				if ( isset( $_GET['output'] ) && in_array( $_GET['output'], array( 'html', 'pdf' ) ) ) {
					$output_format = $_GET['output'];
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
				wp_die( sprintf( __( "Document of type '%s' for the selected order(s) could not be generated", 'woocommerce-pdf-invoices-packing-slips' ), $document_type ) );
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
	 * Return tmp path for different plugin processes
	 */
	public function get_tmp_path ( $type = '' ) {
		$tmp_base = $this->get_tmp_base();

		// don't continue if we don't have an upload dir
		if ($tmp_base === false) {
			return false;
		}

		// check if tmp folder exists => if not, initialize
		if ( !@is_dir( $tmp_base ) ) {
			$this->init_tmp( $tmp_base );
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
		if ( !@is_dir( $tmp_path ) ) {
			@mkdir( $tmp_path );
		}

		return $tmp_path;
	}

	/**
	 * return the base tmp folder (usually uploads)
	 */
	public function get_tmp_base () {
		// wp_upload_dir() is used to set the base temp folder, under which a
		// 'wpo_wcpdf' folder and several subfolders are created
		//
		// wp_upload_dir() will:
		// * default to WP_CONTENT_DIR/uploads
		// * UNLESS the ‘UPLOADS’ constant is defined in wp-config (http://codex.wordpress.org/Editing_wp-config.php#Moving_uploads_folder)
		//
		// May also be overridden by the wpo_wcpdf_tmp_path filter

		$upload_dir = wp_upload_dir();
		if (!empty($upload_dir['error'])) {
			$tmp_base = false;
		} else {
			$upload_base = trailingslashit( $upload_dir['basedir'] );
			$tmp_base = $upload_base . 'wpo_wcpdf/';
		}

		$tmp_base = apply_filters( 'wpo_wcpdf_tmp_path', $tmp_base );
		if ($tmp_base !== false) {
			$tmp_base = trailingslashit( $tmp_base );
		}

		return $tmp_base;
	}

	/**
	 * Install/create plugin tmp folders
	 */
	public function init_tmp ( $tmp_base ) {
		// create plugin base temp folder
		mkdir( $tmp_base );

		if (!is_dir($tmp_base)) {
			wcpdf_log_error( "Unable to create temp folder {$tmp_base}", 'critical' );
		}

		// create subfolders & protect
		$subfolders = array( 'attachments', 'fonts', 'dompdf' );
		foreach ( $subfolders as $subfolder ) {
			$path = $tmp_base . $subfolder . '/';
			if ( !is_dir( $path ) ) {
				mkdir( $path );
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

	/**
	 * Copy DOMPDF fonts to wordpress tmp folder
	 */
	public function copy_fonts ( $path, $merge_with_local = true ) {
		$path = trailingslashit( $path );
		$dompdf_font_dir = WPO_WCPDF()->plugin_path() . "/vendor/dompdf/dompdf/lib/fonts/";

		// get local font dir from filtered options
		$dompdf_options = apply_filters( 'wpo_wcpdf_dompdf_options', array(
			'defaultFont'				=> 'dejavu sans',
			'tempDir'					=> $this->get_tmp_path('dompdf'),
			'logOutputFile'				=> $this->get_tmp_path('dompdf') . "/log.htm",
			'fontDir'					=> $this->get_tmp_path('fonts'),
			'fontCache'					=> $this->get_tmp_path('fonts'),
			'isRemoteEnabled'			=> true,
			'isFontSubsettingEnabled'	=> true,
			'isHtml5ParserEnabled'		=> true,
		) );
		$fontDir = $dompdf_options['fontDir'];

		// merge font family cache with local/custom if present
		$font_cache_files = array(
			'cache'			=> 'dompdf_font_family_cache.php',
			'cache_dist'	=> 'dompdf_font_family_cache.dist.php',
		);
		foreach ( $font_cache_files as $font_cache_name => $font_cache_filename ) {
			$plugin_fonts = @require $dompdf_font_dir . $font_cache_filename;
			if ( $merge_with_local && is_readable( $path . $font_cache_filename ) ) {
				$local_fonts = @require $path . $font_cache_filename;
				if (is_array($local_fonts) && is_array($plugin_fonts)) {
					// merge local & plugin fonts, plugin fonts overwrite (update) local fonts
					// while custom local fonts are retained
					$local_fonts = array_merge($local_fonts, $plugin_fonts);
					// create readable array with $fontDir in place of the actual folder for portability
					$fonts_export = var_export($local_fonts,true);
					$fonts_export = str_replace('\'' . $fontDir , '$fontDir . \'', $fonts_export);
					$cacheData = sprintf("<?php return %s;%s?>", $fonts_export, PHP_EOL );
					// write file with merged cache data
					file_put_contents($path . $font_cache_filename, $cacheData);
				} else { // empty local file
					copy( $dompdf_font_dir . $font_cache_filename, $path . $font_cache_filename );
				}
			} else {
				// we couldn't read the local font cache file so we're simply copying over plugin cache file
				copy( $dompdf_font_dir . $font_cache_filename, $path . $font_cache_filename );
			}
		}

		// first try the easy way with glob!
		if ( function_exists('glob') ) {
			$files = glob($dompdf_font_dir."*.*");
			foreach($files as $file){
				$filename = basename($file);
				if( !is_dir($file) && is_readable($file) && !in_array($filename, $font_cache_files)) {
					$dest = $path . $filename;
					copy($file, $dest);
				}
			}
		} else {
			// fallback method using font cache file (glob is disabled on some servers with disable_functions)
			$extensions = array( '.ttf', '.ufm', '.ufm.php', '.afm', '.afm.php' );
			$fontDir = untrailingslashit($dompdf_font_dir);
			$plugin_fonts = @require $dompdf_font_dir . $font_cache_files['cache'];

			foreach ($plugin_fonts as $font_family => $filenames) {
				foreach ($filenames as $filename) {
					foreach ($extensions as $extension) {
						$file = $filename.$extension;
						if (file_exists($file)) {
							$dest = $path . basename($file);
							copy($file, $dest);
						}
					}
				}
			}
		}
	}

	public function disable_free_attachment( $attach, $order, $email_id, $document_type ) {
		// prevent fatal error for non-order objects
		if ( !method_exists( $order, 'get_total' ) ) {
			return false;
		}

		$document_settings = WPO_WCPDF()->settings->get_document_settings( $document_type );

		// check order total & setting
		$order_total = $order->get_total();
		if ( $order_total == 0 && isset( $document_settings['disable_free'] ) ) {
			return false;
		}

		return $attach;
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

	/**
	 * Use currency symbol font (when enabled in options)
	 */
	public function use_currency_font ( $document_type, $document ) {
		add_filter( 'woocommerce_currency_symbol', array( $this, 'wrap_currency_symbol' ), 10001, 2);
		add_action( 'wpo_wcpdf_custom_styles', array($this, 'currency_symbol_font_styles' ) );
	}

	public function wrap_currency_symbol( $currency_symbol, $currency ) {
		$currency_symbol = sprintf( '<span class="wcpdf-currency-symbol">%s</span>', $currency_symbol );
		return $currency_symbol;
	}

	public function currency_symbol_font_styles () {
		?>
		.wcpdf-currency-symbol { font-family: 'Currencies'; }
		<?php
	}

	/**
	 * Apply header logo height from settings
	 */
	public function set_header_logo_height( $document_type, $document = null ) {
		if ( !empty($document) && $header_logo_height = $document->get_header_logo_height() ) {
			?>
			td.header img {
				max-height: <?php echo $header_logo_height; ?>;
			}
			<?php
		}
	}

	/**
	 * Remove attachments older than 1 week (daily, hooked into wp_scheduled_delete )
	 */
	public function attachments_cleanup() {
		if ( !function_exists("glob") || !function_exists('filemtime') ) {
			// glob is required
			return;
		}

		
		if ( !isset( WPO_WCPDF()->settings->debug_settings['enable_cleanup'] ) ) {
			return;
		}


		$cleanup_age_days = isset(WPO_WCPDF()->settings->debug_settings['cleanup_days']) ? floatval(WPO_WCPDF()->settings->debug_settings['cleanup_days']) : 7.0;
		$delete_timestamp = time() - ( intval ( DAY_IN_SECONDS * $cleanup_age_days ) );

		$tmp_path = $this->get_tmp_path('attachments');

		if ( $files = glob( $tmp_path.'*.pdf' ) ) { // get all pdf files
			foreach( $files as $file ) {
				if( is_file( $file ) ) {
					$file_timestamp = filemtime( $file );
					if ( !empty( $file_timestamp ) && $file_timestamp < $delete_timestamp ) {
						@unlink($file);
					}
				}
			}
		}
	}

	/**
	 * Remove all invoice data when requested
	 */
	public function remove_order_personal_data_meta( $meta_to_remove ) {
		$wcpdf_private_meta = array(
			'_wcpdf_invoice_number'			=> 'numeric_id',
			'_wcpdf_invoice_number_data'	=> 'array',
			'_wcpdf_invoice_date'			=> 'timestamp',
			'_wcpdf_invoice_date_formatted'	=> 'date',
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
			'_wcpdf_invoice_number'			=> __( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'_wcpdf_invoice_date_formatted'	=> __( 'Invoice Date', 'woocommerce-pdf-invoices-packing-slips' ),
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
	 * Enable PHP error output
	 */
	public function enable_debug () {
		error_reporting( E_ALL );
		ini_set( 'display_errors', 1 );
	}

}

endif; // class_exists

return new Main();
