<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Document getter functions
|--------------------------------------------------------------------------
|
| Global functions to get the document object for an order
|
*/

function wcpdf_filter_order_ids( $order_ids, $document_type ) {
	$order_ids = apply_filters( 'wpo_wcpdf_process_order_ids', $order_ids, $document_type );
	// filter out trashed orders.
	foreach ( $order_ids as $key => $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! empty( $order ) && is_callable( array( $order, 'get_status' ) ) && $order->get_status() == 'trash' ) {
			unset( $order_ids[ $key ] );
		}
	}
	return $order_ids;
}

/**
 * Get the document object for an order
 *
 * @param string $document_type
 * @param mixed  $order
 * Passing an order object will return the document object for that order.
 * Passing an array of order ids will return a BulkDocument object.
 * Passing a single order ID within an array retrieves the document object for that order and refreshes the order object to ensure the data is up-to-date.
 * Passing null will return a document object without an order.
 *
 * @param bool   $init
 *
 * @return object|false
 */
function wcpdf_get_document( string $document_type, $order, bool $init = false ) {
	if ( ! empty( $order ) ) {
		if ( ! is_object( $order ) && ! is_array( $order ) && is_numeric( $order ) ) {
			$order = array( absint( $order ) ); // convert single order id to array.
		}
		if ( is_object( $order ) ) {
			// we filter order_ids for objects too:
			// an order object may need to be converted to several refunds for example.
			$order_ids          = array( $order->get_id() );
			$filtered_order_ids = wcpdf_filter_order_ids( $order_ids, $document_type );

			// check if something has changed.
			$order_id_diff = array_diff( $filtered_order_ids, $order_ids );
			if ( empty( $order_id_diff ) && count( $order_ids ) == count( $filtered_order_ids ) ) {
				// nothing changed, load document with Order object.
				do_action( 'wpo_wcpdf_process_template_order', $document_type, $order->get_id() );
				$document = WPO_WCPDF()->documents->get_document( $document_type, $order );

				if ( ! $document || ! is_callable( array( $document, 'is_allowed' ) ) || ! $document->is_allowed() ) {
					return apply_filters( 'wcpdf_get_document', false, $document_type, $order, $init );
				}

				if ( $init && ! $document->exists() ) {
					$document->init();
					$document->save();
				}
				return apply_filters( 'wcpdf_get_document', $document, $document_type, $order, $init );
			} else {
				// order ids array changed, continue processing that array.
				$order_ids = $filtered_order_ids;
			}
		} elseif ( is_array( $order ) ) {
			$order_ids = wcpdf_filter_order_ids( $order, $document_type );
		} else {
			return apply_filters( 'wcpdf_get_document', false, $document_type, $order, $init );
		}

		if ( empty( $order_ids ) ) {
			// No orders to export for this document type.
			return apply_filters( 'wcpdf_get_document', false, $document_type, $order, $init );
		}

		// if we only have one order, it's simple.
		if ( count( $order_ids ) == 1 ) {
			$order_id = array_pop( $order_ids );
			$order    = wc_get_order( $order_id );

			do_action( 'wpo_wcpdf_process_template_order', $document_type, $order_id );

			$document = WPO_WCPDF()->documents->get_document( $document_type, $order );

			if ( ! $document || ! $document->is_allowed() ) {
				return apply_filters( 'wcpdf_get_document', false, $document_type, $order, $init );
			}

			if ( $init && ! $document->exists() ) {
				$document->init();
				$document->save();
			}
		// otherwise we use bulk class to wrap multiple documents in one.
		} else {
			$document = wcpdf_get_bulk_document( $document_type, $order_ids );
		}
	} else {
		// orderless document (used as wrapper for bulk, for example).
		$document = WPO_WCPDF()->documents->get_document( $document_type, $order );
	}

	return apply_filters( 'wcpdf_get_document', $document, $document_type, $order, $init );
}

function wcpdf_get_bulk_document( $document_type, $order_ids ) {
	return new \WPO\IPS\Documents\BulkDocument( $document_type, $order_ids );
}

function wcpdf_get_invoice( $order, $init = false ) {
	wcpdf_deprecated_function( __FUNCTION__, '4.6.3', 'wcpdf_get_document( \'invoice\', $order, $init )' );
	return wcpdf_get_document( 'invoice', $order, $init );
}

function wcpdf_get_packing_slip( $order, $init = false ) {
	wcpdf_deprecated_function( __FUNCTION__, '4.6.3', 'wcpdf_get_document( \'packing-slip\', $order, $init )' );
	return wcpdf_get_document( 'packing-slip', $order, $init );
}

function wcpdf_get_bulk_actions() {
	$actions   = array();
	$documents = WPO_WCPDF()->documents->get_documents( 'enabled', 'any' );

	foreach ( $documents as $document ) {
		foreach ( $document->output_formats as $output_format ) {
			$slug = $document->get_type();
			if ( 'pdf' !== $output_format ) {
				$slug .= "_{$output_format}";
			}

			if ( $document->is_enabled( $output_format ) ) {
				$actions[$slug] = strtoupper( $output_format ) . ' ' . $document->get_title();
			}
		}
	}

	return apply_filters( 'wpo_wcpdf_bulk_actions', $actions );
}

/**
 * Load HTML into (pluggable) PDF library, DomPDF 1.0.2 by default
 * Use wpo_wcpdf_pdf_maker filter to change the PDF class (which can wrap another PDF library).
 *
 * @param string       $html
 * @param array        $settings
 * @param null|object  $document
 * @return WPO\IPS\Makers\PDFMaker
 */
function wcpdf_get_pdf_maker( $html, $settings = array(), $document = null ) {
	$class = '\\WPO\\IPS\\Makers\\PDFMaker';

	if ( ! class_exists( $class ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/Makers/PDFMaker.php' );
	}

	$class = apply_filters( 'wpo_wcpdf_pdf_maker', $class );

	return new $class( $html, $settings, $document );
}

/**
 * Get UBL Maker
 * Use wpo_wcpdf_ubl_maker filter to change the UBL class (which can wrap another UBL library).
 *
 * @return WPO\IPS\Makers\UBLMaker
 */
function wcpdf_get_ubl_maker() {
	$class = '\\WPO\\IPS\\Makers\\UBLMaker';

	if ( ! class_exists( $class ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/Makers/UBLMaker.php' );
	}

	$class = apply_filters( 'wpo_wcpdf_ubl_maker', $class );

	return new $class();
}

/**
 * Check if UBL is available
 *
 * @return bool
 */
function wcpdf_is_ubl_available(): bool {
	// Check `sabre/xml` library here: https://packagist.org/packages/sabre/xml
	return apply_filters( 'wpo_wcpdf_ubl_available', WPO_WCPDF()->is_dependency_version_supported( 'php' ) );
}

/**
 * Check if the default PDF maker is used for creating PDF
 *
 * @return bool whether the PDF maker is the default or not
 */
function wcpdf_pdf_maker_is_default() {
	$default_pdf_maker = '\\WPO\\IPS\\Makers\\PDFMaker';

	return $default_pdf_maker == apply_filters( 'wpo_wcpdf_pdf_maker', $default_pdf_maker );
}

/**
 * Send PDF headers for inline viewing or file download.
 *
 * @param string      $filename PDF file name
 * @param string      $mode     Delivery mode ('inline' or 'download')
 * @param string|null $pdf      PDF string
 */
function wcpdf_pdf_headers( string $filename, string $mode = 'inline', ?string $pdf = null ) {
	// Decide whether to display inline or prompt a download
	$disposition  = ( $mode === 'download' ) ? 'attachment' : 'inline';
	$content_type = ( $mode === 'download' ) ? 'application/octet-stream' : 'application/pdf';

	// PDF-specific headers
	header( "Content-Type: $content_type" );
	header( "Content-Disposition: $disposition; filename=\"" . rawurlencode( $filename ) . "\"" );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Accept-Ranges: bytes' );

	// Cache control headers
	header( 'Cache-Control: public, must-revalidate, max-age=0' );
	header( 'Pragma: public' );
	header( 'Expires: 0' );

	// Allows other developers or code to hook in
	do_action( 'wpo_wcpdf_headers', $filename, $mode, $pdf );
}

function wcpdf_ubl_headers( $filename, $size ) {
	$charset = apply_filters( 'wcpdf_ubl_headers_charset', 'UTF-8' );

	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: text/xml; charset=' . $charset );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Connection: Keep-Alive' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );
	header( 'Content-Length: ' . $size );

	do_action( 'wpo_after_ubl_headers', $filename, $size );
}

/**
 * Get the document file
 *
 * @param  object $document
 * @param  string $output_format
 * @param  string $error_handling
 * @return string|false
 */
function wcpdf_get_document_file( object $document, string $output_format = 'pdf', string $error_handling = 'exception' ) {
	$default_output_format = 'pdf';

	if ( ! $document ) {
		$error_message = 'No document object provided.';
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	if ( empty( $output_format ) ) {
		$output_format = $default_output_format;
	}

	if ( ! in_array( $output_format, $document->output_formats ) ) {
		$error_message = "Invalid output format: {$output_format}. Expected one of: " . implode( ', ', $document->output_formats );
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	if ( is_callable( array( $document, 'is_enabled' ) ) && ! $document->is_enabled( $output_format ) ) {
		$error_message = "The {$output_format} output format is not enabled for this document: {$document->get_title()}.";
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	$tmp_path = WPO_WCPDF()->main->get_tmp_path( 'attachments' );

	if ( ! WPO_WCPDF()->file_system->is_dir( $tmp_path ) || ! WPO_WCPDF()->file_system->is_writable( $tmp_path ) ) {
		$error_message = "Couldn't get the attachments temporary folder path: {$tmp_path}.";
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	$function = "get_document_{$output_format}_attachment"; // 'get_document_pdf_attachment' or 'get_document_ubl_attachment'

	if ( ! is_callable( array( WPO_WCPDF()->main, $function ) ) ) {
		$error_message = "The {$function} method is not callable on WPO_WCPDF()->main.";
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	$file_path = WPO_WCPDF()->main->$function( $document, $tmp_path );

	return apply_filters( 'wpo_wcpdf_get_document_file', $file_path, $document, $output_format );
}

/**
 * Get the document output format extension
 *
 * @param  string $output_format
 * @return string
 */
function wcpdf_get_document_output_format_extension( string $output_format ): string {
	$output_formats = array(
		'pdf' => '.pdf',
		'ubl' => '.xml',
	);

	return isset( $output_formats[ $output_format ] ) ? $output_formats[ $output_format ] : $output_formats['pdf'];
}

/**
 * Wrapper for deprecated functions so we can apply some extra logic.
 *
 * @since  2.0
 * @param  string $function
 * @param  string $version
 * @param  string $replacement
 */
function wcpdf_deprecated_function( $function, $version, $replacement = null ) {
	if ( apply_filters( 'wcpdf_disable_deprecation_notices', false ) ) {
		return;
	}

	// if the deprecated function is called from one of our filters, $this should be $document.
	$filter               = current_filter();
	$global_wcpdf_filters = array( 'wp_ajax_generate_wpo_wcpdf' );

	if ( ! empty( $filter ) && ! empty( $replacement ) && ! in_array( $filter, $global_wcpdf_filters ) && false !== strpos( $filter, 'wpo_wcpdf' ) && false !== strpos( $replacement, '$this' ) ) {
		$replacement =  str_replace( '$this', '$document', $replacement );
		$replacement = "{$replacement} - check that the \$document parameter is included in your action or filter ($filter)!";
	}

	$is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ) && DOING_AJAX;

	if ( $is_ajax ) {
		do_action( 'deprecated_function_run', $function, $replacement, $version );
		$log_string  = "The {$function} function is deprecated since version {$version}.";
		$log_string .= $replacement ? " Replace with {$replacement}." : '';
		wcpdf_log_error( $log_string, 'warning' );
	} else {
		_deprecated_function( esc_html( $function ), esc_html( $version ), esc_html( $replacement ) );
	}
}

/**
 * Logs errors thrown by this plugin.
 * Uses the WooCommerce logger when available (WC 3.0+), otherwise falls back to PHP error_log().
 *
 * @param string           $message Error message to log.
 * @param string           $level   Log level: debug, info, notice, warning, error, critical, alert, emergency.
 * @param \Throwable|null  $e       (Optional) Exception or error object.
 * @return void
 */
function wcpdf_log_error( string $message, string $level = 'error', ?\Throwable $e = null ): void {
	/**
	 * Appends exception details to the message if available.
	 *
	 * @param string          $message
	 * @param \Throwable|null $e
	 * @return string
	 */
	$format_message = static function ( string $message, ?\Throwable $e ): string {
		if ( $e instanceof \Throwable ) {
			$message = sprintf( '%s (%s:%d)', $message, $e->getFile(), $e->getLine() );

			if ( apply_filters( 'wcpdf_log_stacktrace', false ) && is_callable( array( $e, 'getTraceAsString' ) ) ) {
				$message .= "\n" . $e->getTraceAsString();
			}
		}
		return $message;
	};

	$message = $format_message( $message, $e );

	if ( ! function_exists( 'wc_get_logger' ) ) {
		error_log( '[WPO_WCPDF] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return;
	}

	$logger  = wc_get_logger();
	$context = array( 'source' => 'wpo-wcpdf' );

	$logger->log( $level, $message, $context );
}

/**
 * Outputs an error message in the frontend.
 *
 * @param string          $message Error message to display.
 * @param string          $level   Log level (unused here, but kept for consistency).
 * @param \Throwable|null $e       (Optional) Exception or error object.
 * @return void
 */
function wcpdf_output_error( string $message, string $level = 'error', ?\Throwable $e = null ): void {
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		esc_html_e( 'Error creating PDF, please contact the site owner.', 'woocommerce-pdf-invoices-packing-slips' );
		return;
	}

	echo '<div style="border: 2px solid red; padding: 5px;">';
	echo '<h3>' . wp_kses_post( $message ) . '</h3>';

	if ( $e instanceof \Throwable ) {
		echo '<pre>' . esc_html( $e->getFile() ) . ' (' . esc_html( (string) $e->getLine() ) . ')</pre>';
		echo '<pre>' . esc_html( $e->getTraceAsString() ) . '</pre>';
	}

	echo '</div>';
}

/**
 * Handles errors by either throwing an exception or outputting the error, optionally logging it first.
 *
 * @param string $message        The error message.
 * @param string $handling_type  How to handle the error: 'exception' (default) or 'output'.
 * @param bool   $log_error      Whether to log the error via wcpdf_log_error().
 * @param string $log_level      Log level to use when logging the error.
 * @return bool Always returns false when not throwing.
 * @throws \Exception When handling_type is 'exception'.
 */
function wcpdf_error_handling( string $message, string $handling_type = 'exception', bool $log_error = true, string $log_level = 'error' ): bool {
	if ( $log_error ) {
		wcpdf_log_error( $message, $log_level );
	}

	switch ( $handling_type ) {
		case 'exception':
			throw new \Exception( esc_html( $message ) );
		case 'output':
			wcpdf_output_error( $message, $log_level );
			break;
		default:
			// Unexpected handling type
			wcpdf_log_error( sprintf( 'Unknown error handling type: %s', $handling_type ), 'warning' );
			break;
	}

	return false;
}

/**
 * Date formatting function
 *
 * @param object $document
 * @param string $date_type Optional. A date type to be filtered eg. 'invoice_date', 'order_date_created', 'order_date_modified', 'order_date', 'order_date_paid', 'order_date_completed', 'current_date', 'document_date', 'packing_slip_date'.
 */
function wcpdf_date_format( $document = null, $date_type = null ) {
	return apply_filters( 'wpo_wcpdf_date_format', wc_date_format(), $document, $date_type );
}

/**
 * Catch MySQL errors from $wpdb and log them.
 * 
 * Inspired from here: https://github.com/johnbillion/query-monitor/blob/d5b622b91f18552e7105e62fa84d3102b08975a4/collectors/db_queries.php#L125-L280
 *
 * With SAVEQUERIES constant defined as 'false', '$wpdb->queries' is empty and '$EZSQL_ERROR' is used instead.
 * Using the Query Monitor plugin, the SAVEQUERIES constant is defined as 'true'
 * More info about this constant can be found here: https://wordpress.org/support/article/debugging-in-wordpress/#savequeries
 *
 * @param  \wpdb  $wpdb
 * @param  string $context Optional prefix for messages (e.g. __METHOD__).
 * @return array  List of error strings logged.
 */
function wcpdf_catch_db_object_errors( \wpdb $wpdb, string $context = '' ): array {
	global $EZSQL_ERROR;

	static $seen = array(); // avoid duplicate logs in the same request
	$errors      = array();

	// Using $wpdb->queries (if SAVEQUERIES is true and a collector populates results)
	if ( ! empty( $wpdb->queries ) && is_array( $wpdb->queries ) ) {
		foreach ( $wpdb->queries as $query ) {
			$result = isset( $query['result'] ) ? $query['result'] : null;
			if ( is_wp_error( $result ) && is_array( $result->errors ) ) {
				foreach ( $result->errors as $error ) {
					$errors[] = reset( $error );
				}
			}
		}
	}

	// Fallback to $EZSQL_ERROR (wpdb::print_error collects here)
	if ( empty( $errors ) && ! empty( $EZSQL_ERROR ) && is_array( $EZSQL_ERROR ) ) {
		foreach ( $EZSQL_ERROR as $error ) {
			if ( ! empty( $error['error_str'] ) ) {
				$errors[] = $error['error_str'];
			}
		}
	}

	// Log (with optional context) and dedupe per request
	foreach ( $errors as $msg ) {
		$line = '' !== $context ? "{$context}: {$msg}" : $msg;
		$key  = md5( $line );
		
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		
		$seen[ $key ] = true;
		wcpdf_log_error( $line, 'critical' );
	}

	return $errors;
}

/**
 * String convert encoding.
 *
 * @param  string $string
 * @param  string $tool
 * @return string
 */
function wcpdf_convert_encoding( $string, $tool = 'mb_convert_encoding' ) {
	if ( empty( $string ) ) {
		return $string;
	}

	$tool          = apply_filters( 'wpo_wcpdf_convert_encoding_tool', $tool );
	$from_encoding = apply_filters( 'wpo_wcpdf_convert_from_encoding', 'UTF-8', $tool );

	switch ( $tool ) {
		case 'mb_convert_encoding':
			$to_encoding = apply_filters( 'wpo_wcpdf_convert_to_encoding', 'HTML-ENTITIES', $tool );

			// provided by composer 'symfony/polyfill-mbstring' library.
			// it uses 'iconv()', must have 'libiconv' configured instead of 'glibc' library.
			if ( class_exists( '\\Symfony\\Polyfill\\Mbstring\\Mbstring' ) ) {
				$string = \Symfony\Polyfill\Mbstring\Mbstring::mb_convert_encoding( $string, $to_encoding, $from_encoding );
			}
			break;
		case 'uconverter':
			$to_encoding = apply_filters( 'wpo_wcpdf_convert_to_encoding', 'HTML-ENTITIES', $tool );

			// only for PHP 8.2+.
			if ( version_compare( PHP_VERSION, '8.1', '>' ) && class_exists( 'UConverter' ) && extension_loaded( 'intl' ) ) {
				$string = UConverter::transcode( $string, $to_encoding, $from_encoding );
			}
			break;
		case 'iconv':
			$to_encoding = apply_filters( 'wpo_wcpdf_convert_to_encoding', 'ISO-8859-1', $tool );

			// provided by composer 'symfony/polyfill-iconv' library.
			if ( class_exists( '\\Symfony\\Polyfill\\Iconv\\Iconv' ) ) {
				$string = \Symfony\Polyfill\Iconv\Iconv::iconv( $from_encoding, $to_encoding, $string );

			// default server library.
			// must have 'libiconv' configured instead of 'glibc' library.
			} elseif ( function_exists( 'iconv' ) ) {
				$string = iconv( $from_encoding, $to_encoding, $string );
			}
			break;
	}

	return $string;
}

/**
 * Sanitize HTML content, prevents XSS attacks.
 *
 * @param string $html
 * @param string $context
 * @param array  $allow_tags
 *
 * @return string
 */
function wpo_wcpdf_sanitize_html_content( string $html, string $context = '', array $allow_tags = array() ): string {
	if ( empty( $html ) ) {
		return $html;
	}

	// default allowed tags
	$allow_tags = array_merge( apply_filters( 'wpo_wcpdf_sanitize_html_default_allow_tags', array(
		// tag   => allowed attributes eg. array( 'href', 'title' ) in case of a <a> tag.
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'p'      => array(),
	), $context ), $allow_tags );

	$safe_tags = array(
		'b'          => array(),
		'blockquote' => array(),
		'br'         => array(),
		'em'         => array(),
		'i'          => array(),
		'li'         => array(),
		'ol'         => array(),
		'p'          => array(),
		'strong'     => array(),
		'u'          => array(),
		'ul'         => array(),
		'span'       => array( 'style' ),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'h5'         => array(),
		'h6'         => array(),
		'div'        => array( 'style' ),
		'table'      => array( 'border', 'cellspacing', 'cellpadding' ),
		'tr'         => array(),
		'td'         => array( 'colspan', 'rowspan' ),
		'th'         => array( 'colspan', 'rowspan', 'scope' ),
		'thead'      => array(),
		'tbody'      => array(),
		'tfoot'      => array(),
		'code'       => array(),
		'pre'        => array(),
		'dl'         => array(),
		'dt'         => array(),
		'dd'         => array(),
		'hr'         => array(),
		'sup'        => array(),
		'sub'        => array(),
		'figure'     => array(),
		'figcaption' => array(),
		'abbr'       => array( 'title' ),
	);

	$filtered_tags = array();

	foreach ( $allow_tags as $tag => $attributes ) {
		if ( array_key_exists( $tag, $safe_tags ) ) {
			$safe_attributes       = array_intersect( $attributes, $safe_tags[ $tag ] );
			$filtered_tags[ $tag ] = ! empty( $safe_attributes ) ? $safe_attributes : array();
		}
	}

	if ( empty( $filtered_tags ) ) {
		return $html;
	}

	$dom = new \DOMDocument();

	// clean up special chars
	if ( apply_filters( 'wpo_wcpdf_convert_encoding', function_exists( 'htmlspecialchars_decode' ) ) ) {
		$html = htmlspecialchars_decode( wcpdf_convert_encoding( $html ), ENT_QUOTES );
	}

	libxml_use_internal_errors( true ); // suppress malformed HTML errors
	@$dom->loadHTML( '<div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	$extra_wrapper = $dom->getElementsByTagName( 'div' )->item( 0 );
	$content       = ! empty( $extra_wrapper ) ? $extra_wrapper->parentNode->removeChild( $extra_wrapper ) : null;

	if ( ! empty( $content ) ) {
		// Clear DOM by removing all nodes from it.
		while ( $dom->firstChild ) {
			$dom->removeChild( $dom->firstChild );
		}

		// Append the content to the DOM to remove the extra DIV wrapper.
		while ( $content->firstChild ) {
			$dom->appendChild( $content->firstChild );
		}
	}

	$xpath = new \DOMXPath( $dom );

	// iterate over all nodes.
	foreach ( $xpath->query( '//*' ) as $node ) {
		// check if the node is allowed.
		if ( array_key_exists( $node->nodeName, $filtered_tags ) ) {
			// if the node is allowed, check each attribute.
			foreach ( $node->attributes as $attr ) {
				if ( ! in_array( $attr->nodeName, $filtered_tags[ $node->nodeName ] ) ) {
					$node->removeAttribute( $attr->nodeName );
				}
			}
		} else {
			// if the node is not allowed, remove it but try to preserve text.
			if ( $node->parentNode ) {
				$fragment = $dom->createDocumentFragment();

				while ( $node->childNodes->length > 0 ) {
					$fragment->appendChild( $node->childNodes->item( 0 ) );
				}

				if ( $fragment->hasChildNodes() ) {
					$node->parentNode->replaceChild( $fragment, $node );
				} else {
					$node->parentNode->removeChild( $node );
				}
			}
		}
	}

	$html = $dom->saveHTML();

	if ( empty( $html ) ) {
		return '';
	}

	return trim( $html );
}

/**
 * Sanitize phone number
 *
 * @param string $text
 *
 * @return string
 */
function wpo_wcpdf_sanitize_phone_number( string $text ): string {
	return preg_replace( '/[^0-9\+\-\(\)\s\.x]/', '', $text );
}

/**
 * Safe redirect or die.
 *
 * @param  string          $url
 * @param  string|WP_Error $message
 * @return void
 */
function wcpdf_safe_redirect_or_die( $url = '', $message = '' ) {
	if ( ! empty( $url ) ) {
		wp_safe_redirect( $url );
		exit;
	} else {
		wp_die( esc_html( $message ) );
	}
}

/**
 * Parse document date for WP_Query.
 *
 * @param array $wp_query_args
 * @param array $query_args
 *
 * @return array
 */
function wpo_wcpdf_parse_document_date_for_wp_query( array $wp_query_args, array $query_vars ): array {
	$documents = WPO_WCPDF()->documents->get_documents();

	if ( ! empty( $documents ) ) {
		foreach ( $documents as $document ) {
			if ( ! empty( $query_vars[ "wcpdf_{$document->slug}_date" ] ) ) {
				$wp_query_args = ( new \WC_Order_Data_Store_CPT() )->parse_date_for_wp_query( $query_vars[ "wcpdf_{$document->slug}_date" ], "_wcpdf_{$document->slug}_date", $wp_query_args );

				if ( isset( $wp_query_args[ "wcpdf_{$document->slug}_date" ] ) ) {
					unset( $wp_query_args[ "wcpdf_{$document->slug}_date" ] );
				}
			}
		}
	}

	return $wp_query_args;
}

/**
 * Get multilingual languages.
 *
 * @return array
 */
function wpo_wcpdf_get_multilingual_languages(): array {
	$languages = array();

	// refers to WPML or Polylang only
	if ( function_exists( 'icl_get_languages' ) ) {
		// use this instead of function call for development outside of WPML
		// $icl_get_languages = 'a:3:{s:2:"en";a:8:{s:2:"id";s:1:"1";s:6:"active";s:1:"1";s:11:"native_name";s:7:"English";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"English";s:13:"language_code";s:2:"en";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/en.png";s:3:"url";s:23:"http://yourdomain/about";}s:2:"fr";a:8:{s:2:"id";s:1:"4";s:6:"active";s:1:"0";s:11:"native_name";s:9:"Français";s:7:"missing";s:1:"0";s:15:"translated_name";s:6:"French";s:13:"language_code";s:2:"fr";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/fr.png";s:3:"url";s:29:"http://yourdomain/fr/a-propos";}s:2:"it";a:8:{s:2:"id";s:2:"27";s:6:"active";s:1:"0";s:11:"native_name";s:8:"Italiano";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"Italian";s:13:"language_code";s:2:"it";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/it.png";s:3:"url";s:26:"http://yourdomain/it/circa";}}';
		// $icl_get_languages = unserialize($icl_get_languages);

		$icl_get_languages = icl_get_languages( 'skip_missing=0' );

		foreach ( $icl_get_languages as $lang => $data ) {
			$languages[ $data['language_code'] ] = $data['native_name'];
		}
	}

	return apply_filters( 'wpo_wcpdf_multilingual_languages', $languages );
}

/**
 * Get image mime type
 *
 * @param string $src
 * @return string
 */
function wpo_wcpdf_get_image_mime_type( string $src ): string {
	$mime_type = '';

	if ( empty( $src ) ) {
		return $mime_type;
	}

	// Check if 'getimagesize' function exists and try to get mime type for local files
	if ( function_exists( 'getimagesize' ) && ! filter_var( $src, FILTER_VALIDATE_URL ) ) {
		$image_info = @getimagesize( $src );

		if ( $image_info && isset( $image_info['mime'] ) ) {
			$mime_type = $image_info['mime'];
		}
	}

	// Fallback to 'finfo_file' if mime type is empty for local files only (no remote files allowed)
	if ( empty( $mime_type ) && function_exists( 'finfo_open' ) && ! filter_var( $src, FILTER_VALIDATE_URL ) ) {
		$finfo = finfo_open( FILEINFO_MIME_TYPE );

		if ( $finfo ) {
			$mime_type = finfo_file( $finfo, $src );
			
			if ( PHP_VERSION_ID < 80100 ) {
				finfo_close( $finfo );
			}
		}
	}

	// Handle remote files
	if ( empty( $mime_type ) && filter_var( $src, FILTER_VALIDATE_URL ) ) {
		$context = stream_context_create( array(
			'http' => array(
				'method'        => 'HEAD',
				'ignore_errors' => true,
			),
			'https' => array(
				'method'           => 'HEAD',
				'ignore_errors'    => true,
				'verify_peer'      => false,
				'verify_peer_name' => false,
			),
		) );

		$headers = @get_headers( $src, 1, $context );

		if ( $headers ) {
			if ( isset( $headers['Content-Type'] ) ) {
				$mime_type = is_array( $headers['Content-Type'] ) ? $headers['Content-Type'][0] : $headers['Content-Type'];
			}
		}
	}

	// Fetch the actual image data if MIME type is still unknown (remote files)
	if ( empty( $mime_type ) && filter_var( $src, FILTER_VALIDATE_URL ) ) {
		$response = wp_remote_get( $src );

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$image_data = wp_remote_retrieve_body( $response );

			if ( $image_data && function_exists( 'finfo_open' ) ) {
				$finfo = finfo_open( FILEINFO_MIME_TYPE );

				if ( $finfo ) {
					$mime_type = finfo_buffer( $finfo, $image_data );
					
					if ( PHP_VERSION_ID < 80100 ) {
						finfo_close( $finfo );
					}
				}
			}
		}
	}

	// Determine using WP functions
	if ( empty( $mime_type ) ) {
		$path      = wp_parse_url( $src, PHP_URL_PATH );
		$file_info = wp_check_filetype( $path );
		$mime_type = $file_info['type'] ?? '';
	}

	// Last chance, determine from file extension
	if ( empty( $mime_type ) ) {
		$path      = parse_url( $src, PHP_URL_PATH );
		$extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		switch ( $extension ) {
			case 'jpg':
			case 'jpeg':
				$mime_type = 'image/jpeg';
				break;
			case 'png':
				$mime_type = 'image/png';
				break;
			case 'gif':
				$mime_type = 'image/gif';
				break;
			case 'bmp':
				$mime_type = 'image/bmp';
				break;
			case 'webp':
				$mime_type = 'image/webp';
				break;
			case 'svg':
				$mime_type = 'image/svg+xml';
				break;
		}
	}

	return $mime_type;
}

/**
 * Base64 encode file from local path
 *
 * @param string $local_path
 *
 * @return string|bool
 */
function wpo_wcpdf_base64_encode_file( string $local_path ) {
	if ( empty( $local_path ) ) {
		return false;
	}

	$file_data = WPO_WCPDF()->file_system->get_contents( $local_path );

	return $file_data ? base64_encode( $file_data ) : false;
}

/**
 * Check if a file is readable
 *
 * @param string $path
 * @return bool
 */
function wpo_wcpdf_is_file_readable( string $path ): bool {
	if ( empty( $path ) ) {
		return false;
	}

	// Check if the path is a URL
	if ( filter_var( $path, FILTER_VALIDATE_URL ) ) {
		$parsed_url = wp_parse_url( $path );
		$args	    = array();

		// Check if the URL is localhost
		if (
			'localhost' === $parsed_url['host']                                             ||
			'127.0.0.1' === $parsed_url['host']                                             ||
			( preg_match( '/^192\.168\./', $parsed_url['host'] ) === 1 )                    || // 192.168.*
			( preg_match( '/^10\./', $parsed_url['host'] ) === 1 )                          || // 10.*
			( preg_match( '/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $parsed_url['host'] ) === 1 ) || // 172.16.* to 172.31.*
			getenv( 'DISABLE_SSL_VERIFY' ) === 'true'
		) {
			$args['sslverify'] = false;
		}

		$args     = apply_filters( 'wpo_wcpdf_url_remote_head_args', $args, $parsed_url, $path );
		$response = wp_safe_remote_head( $path, $args );

		if ( is_wp_error( $response ) ) {
			wcpdf_log_error( 'Failed to access file URL: ' . $path . ' Error: ' . $response->get_error_message(), 'critical' );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		return ( $status_code === 200 );

	// Local path file check
	} else {
		if ( WPO_WCPDF()->file_system->is_readable( $path ) ) {
			return true;
		} else {
			// Fallback to checking file readability by attempting to open it
			$file_contents = WPO_WCPDF()->file_system->get_contents( $path );

			if ( $file_contents ) {
				return true;
			} else {
				wcpdf_log_error( 'Failed to open local file: ' . $path, 'critical' );
				return false;
			}
		}
	}
}

/**
 * Get image source in base64 format
 *
 * @param string $src
 *
 * @return string
 */
function wpo_wcpdf_get_image_src_in_base64( string $src ): string {
	if ( empty( $src ) ) {
		return $src;
	}

	$mime_type = wpo_wcpdf_get_image_mime_type( $src );

	if ( empty( $mime_type ) ) {
		wcpdf_log_error( 'Unable to determine image mime type for file: ' . $src, 'critical' );
		return $src;
	}

	$image_base64 = wpo_wcpdf_base64_encode_file( $src );

	if ( ! $image_base64 ) {
		wcpdf_log_error( 'Unable to encode image source to base64:' . $src, 'critical' );
		return $src;
	}

	return 'data:' . $mime_type . ';base64,' . $image_base64;
}

/**
 * Determine if the checkout is a block.
 *
 * @return bool
 */
function wpo_wcpdf_checkout_is_block(): bool {
	$checkout_page_id = wc_get_page_id( 'checkout' );

	$is_block = $checkout_page_id &&
		function_exists( 'has_block' ) &&
		has_block( 'woocommerce/checkout', $checkout_page_id );

	if ( ! $is_block ) {
		$is_block = class_exists( '\\WC_Blocks_Utils' ) &&
			count( \WC_Blocks_Utils::get_blocks_from_page( 'woocommerce/checkout', 'checkout' ) ) > 0;
	}

	if ( ! $is_block ) {
		$is_block = class_exists( '\\Automattic\\WooCommerce\\Blocks\\Utils\\CartCheckoutUtils' ) &&
			is_callable( array( '\\Automattic\\WooCommerce\\Blocks\\Utils\\CartCheckoutUtils', 'is_checkout_block_default' ) ) &&
			\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
	}

	return $is_block;
}

/**
 * Get the default table headers for the Simple template.
 *
 * @param object $document
 * @return array
 */
function wpo_wcpdf_get_simple_template_default_table_headers( $document ): array {
	$headers = array(
		'product'  => __( 'Product', 'woocommerce-pdf-invoices-packing-slips' ),
		'quantity' => __( 'Quantity', 'woocommerce-pdf-invoices-packing-slips' ),
		'price'    => __( 'Price', 'woocommerce-pdf-invoices-packing-slips' ),
	);

	if ( 'packing-slip' === $document->get_type() ) {
		unset( $headers['price'] );
	}

	return apply_filters( 'wpo_wcpdf_simple_template_default_table_headers', $headers, $document );
}

/**
 * Get the WP_Filesystem instance
 *
 * @return WP_Filesystem|false
 * @throws RuntimeException
 */
function wpo_wcpdf_get_wp_filesystem() {
	wcpdf_deprecated_function( 'wpo_wcpdf_get_wp_filesystem', '4.2.0', '\WPO\IPS\Compatibility\FileSystem::instance()->wp_filesystem' );

	if ( class_exists( '\\WPO\\IPS\\Compatibility\\FileSystem' ) ) {
		$filesystem = \WPO\IPS\Compatibility\FileSystem::instance();
		$filesystem->initialize_wp_filesystem();
		return $filesystem->wp_filesystem ?? false;
	}

	return false;
}

/**
 * Escapes a URL, filesystem path, or base64 string for safe output in HTML.
 *
 * @param string $url_path_or_base64
 * @return string
 */
function wpo_wcpdf_escape_url_path_or_base64( string $url_path_or_base64 ): string {
	// Check if it's a URL
	if ( 0 === strpos( $url_path_or_base64, 'http' ) ) {
		return esc_url( $url_path_or_base64 );
	}

	// Check if it's a base64 string
	if ( preg_match( '/^data:[a-zA-Z0-9\/\-\.\+]+;base64,/', $url_path_or_base64 ) ) {
		return esc_attr( $url_path_or_base64 );
	}

	// Otherwise, assume it's a filesystem path
	return esc_attr( wp_normalize_path( $url_path_or_base64 ) );
}

/**
 * Dynamic string translation
 *
 * @param string $string
 * @param string $textdomain
 * @return string
 */
function wpo_wcpdf_dynamic_translate( string $string, string $textdomain ): string {
	static $cache       = array();
	static $logged      = array();

	$cache_key          = md5( $textdomain . '::' . $string );
	$log_enabled        = ! empty( WPO_WCPDF()->settings->debug_settings['log_missing_translations'] );
	$multilingual_class = '\WPO\WC\PDF_Invoices_Pro\Multilingual_Full';
	$translation        = $string;

	// Return early if empty string
	if ( '' === $string ) {
		if ( $log_enabled && ! isset( $logged[ $cache_key ] ) ) {
			wcpdf_log_error( "Skipping translation for empty string in textdomain: {$textdomain}", 'warning' );
			$logged[ $cache_key ] = true;
		}
		return $string;
	}

	// Check cache
	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	// Attempt to get a translation from multilingual class
	if ( class_exists( $multilingual_class ) && method_exists( $multilingual_class, 'maybe_get_string_translation' ) ) {
		$translation = $multilingual_class::maybe_get_string_translation( $string, $textdomain );
	}

	// If not translated yet, try native translate() first, then custom filters
	if ( $translation === $string && function_exists( 'translate' ) ) {
		$translation = translate( $string, $textdomain ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain, WordPress.WP.I18n.LowLevelTranslationFunction
	}

	// If still not translated, try custom filters
	if ( $translation === $string ) {
		$translation = wpo_wcpdf_gettext( $string, $textdomain );
	}

	// Log a warning if no translation is found and debug logging is enabled
	if ( $translation === $string && $log_enabled && ! isset( $logged[ $cache_key ] ) ) {
		wcpdf_log_error( "Missing translation for: {$string} in textdomain: {$textdomain}", 'warning' );
		$logged[ $cache_key ] = true;
	}

	// Store in cache and return
	$cache[ $cache_key ] = $translation;
	return $cache[ $cache_key ];
}

/**
 * Get text translation
 *
 * @param string $string
 * @param string $textdomain
 * @return string
 */
function wpo_wcpdf_gettext( string $string, string $textdomain ): string {
	$filtered = apply_filters( 'wpo_wcpdf_gettext', $string, $textdomain );

	if ( ! empty( $filtered ) && $filtered !== $string ) {
		$translation = $filtered;
	} else {
		// standard WP gettext filters
		$translation = apply_filters( 'gettext', $string, $string, $textdomain );
		$translation = apply_filters( "gettext_{$textdomain}", $translation, $string, $textdomain );
	}

	return $translation;
}

/**
 * Check if the order is VAT exempt.
 *
 * @param \WC_Abstract_Order $order
 * @return bool
 */
function wpo_wcpdf_order_is_vat_exempt( \WC_Abstract_Order $order ): bool {
	if ( 'shop_order_refund' === $order->get_type() ) {
		$order = wc_get_order( $order->get_parent_id() );

		if ( ! $order ) {
			return false;
		}
	}

	// Check if order is VAT exempt based on order meta
	$vat_exempt_meta_key = apply_filters( 'wpo_wcpdf_order_vat_exempt_meta_key', 'is_vat_exempt', $order );
	$is_vat_exempt       = apply_filters(  'woocommerce_order_is_vat_exempt', 'yes' === $order->get_meta( $vat_exempt_meta_key ), $order );

	// Fallback to customer VAT exemption if order is not exempt
	if ( ! $is_vat_exempt && apply_filters( 'wpo_wcpdf_order_vat_exempt_fallback_to_customer', true, $order ) ) {
		$customer_id = $order->get_customer_id();

		if ( $customer_id ) {
			$customer      = new \WC_Customer( $customer_id );
			$is_vat_exempt = $customer->is_vat_exempt();
		}
	}

	// Check VAT exemption for EU orders based on VAT number and tax details
	if ( ! $is_vat_exempt && apply_filters( 'wpo_wcpdf_order_vat_exempt_fallback_to_customer_vat_number', true, $order ) ) {
		$is_eu_order = in_array(
			$order->get_billing_country(),
			WC()->countries->get_european_union_countries( 'eu_vat' ),
			true
		);

		if ( $is_eu_order && $order->get_total() > 0 && $order->get_total_tax() == 0 ) {
			$vat_number    = wpo_wcpdf_get_order_customer_vat_number( $order );
			$is_vat_exempt = ! empty( $vat_number );
		}
	}

	return apply_filters( 'wpo_wcpdf_is_vat_exempt_order', $is_vat_exempt, $order );
}

/**
 * Retrieve the customer VAT number from order meta.
 *
 * @param \WC_Abstract_Order $order
 * @return string|null
 */
function wpo_wcpdf_get_order_customer_vat_number( \WC_Abstract_Order $order ): ?string {
	$vat_meta_keys = apply_filters( 'wpo_wcpdf_order_customer_vat_number_meta_keys', array(
		'_vat_number',            // WooCommerce EU VAT Number
		'_billing_vat_number',    // WooCommerce EU VAT Number 2.3.21+
		'VAT Number',             // WooCommerce EU VAT Compliance
		'_eu_vat_evidence',       // Aelia EU VAT Assistant
		'_billing_eu_vat_number', // EU VAT Number for WooCommerce (WP Whale/former Algoritmika)
		'yweu_billing_vat',       // YITH WooCommerce EU VAT
		'billing_vat',            // German Market
		'_billing_vat_id',        // Germanized Pro
		'_shipping_vat_id',       // Germanized Pro (alternative)
		'_billing_dic',           // EU/UK VAT Manager for WooCommerce
	), $order );

	$vat_number = null;

	foreach ( $vat_meta_keys as $meta_key ) {
		$meta_value = $order->get_meta( $meta_key );

		// Handle multidimensional VAT data (e.g., Aelia EU VAT Assistant)
		if ( '_eu_vat_evidence' === $meta_key && is_array( $meta_value ) ) {
			$meta_value = $meta_value['exemption']['vat_number'] ?? '';
		}

		if ( $meta_value ) {
			$vat_number = $meta_value;
			break;
		}
	}

	return apply_filters( 'wpo_wcpdf_order_customer_vat_number', $vat_number, $order, $meta_key ?? null );
}

/**
 * Prepare an identifier query for use with $wpdb->prepare().
 *
 * @param string $query
 * @param array  $identifiers Identifiers for %i placeholders.
 * @param array  $values      Regular values for %s, %d, etc.
 * @return string|void
 */
function wpo_wcpdf_prepare_identifier_query( string $query, array $identifiers = array(), array $values = array() ) {
	global $wpdb;

	$has_identifier_escape = version_compare( get_bloginfo( 'version' ), '6.2', '>=' );

	if ( $has_identifier_escape ) {
		// Combine both arrays in the order the placeholders appear
		$all_placeholders = array();
		$identifier_index = 0;
		$value_index      = 0;
		$split            = preg_split( '/(%[a-zA-Z])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE );

		foreach ( $split as $part ) {
			if ( '%i' === $part ) {
				$all_placeholders[] = $identifiers[ $identifier_index++ ] ?? null;
			} elseif ( preg_match( '/^%[sdfb]/', $part ) ) {
				$all_placeholders[] = $values[ $value_index++ ] ?? null;
			}
		}

		$total_placeholders = substr_count( $query, '%i' ) + (int) preg_match_all( '/%[sdfb]/', $query, $matches );
		if ( count( $all_placeholders ) !== $total_placeholders ) {
			wcpdf_log_error(
				sprintf(
					"The number of passed identifiers/values (%d) does not match the number of placeholders (%d).\nQuery: %s\nIdentifiers: %s\nValues: %s",
					count( $all_placeholders ),
					$total_placeholders,
					$query,
					wp_json_encode( $identifiers ),
					wp_json_encode( $values )
				),
				'critical'
			);
			return;
		}

		return $wpdb->prepare( $query, ...$all_placeholders ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	// Fallback for < 6.2: replace %i manually
	foreach ( $identifiers as &$id ) {
		$id = '`' . wpo_wcpdf_sanitize_identifier( $id ) . '`';
	}

	// Replace %i manually, leave others for prepare()
	$segments = explode( '%i', $query );
	$query    = array_shift( $segments );

	foreach ( $segments as $index => $segment ) {
		$query .= $identifiers[ $index ] . $segment;
	}

	return $wpdb->prepare( $query, ...$values ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

/**
 * Sanitize a database identifier (e.g., table or column name).
 *
 * @param string $identifier The identifier to sanitize.
 * @return string The sanitized identifier.
 */
function wpo_wcpdf_sanitize_identifier( string $identifier ): string {
	$pattern = apply_filters( 'wpo_wcpdf_prepare_identifier_regex', '/[^a-zA-Z0-9_\-]/' );
	return preg_replace( $pattern, '', $identifier );
}

/**
 * Get the latest stable and prerelease versions from GitHub.
 *
 * @param string $owner
 * @param string $repo
 * @param int    $cache_duration
 * @return array {
 *     @type array $stable   Latest stable release.
 *     @type array $unstable Latest valid pre-release.
 * }
 */
function wpo_wcpdf_get_latest_releases_from_github( string $owner = 'wpovernight', string $repo = 'woocommerce-pdf-invoices-packing-slips', int $cache_duration = 1800 ): array {
	$option_key   = 'wpo_latest_releases_' . md5( $owner . '/' . $repo );
	$empty_result = array( 'stable' => array(), 'unstable' => array() );
	$cached       = get_option( $option_key );

	if ( $cached && isset( $cached['timestamp'], $cached['data'] ) ) {
		if ( ( time() - $cached['timestamp'] ) < $cache_duration ) {
			return $cached['data'];
		}
	}

	$url      = "https://api.github.com/repos/$owner/$repo/releases?per_page=10";
	$response = wp_remote_get(
		$url,
		array(
			'headers' => array(
				'User-Agent' => sprintf(
					'%s (%s)',
					wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
					home_url()
				),
			),
			'timeout' => 15,
			'accept'  => 'application/vnd.github.v3+json',
		)
	);

	if ( is_wp_error( $response ) ) {
		return $empty_result;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return $empty_result;
	}

	$releases = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $releases ) ) {
		return $empty_result;
	}

	$stable   = array();
	$unstable = array();

	foreach ( $releases as $release ) {
		$tag  = $release['tag_name'];
		$name = ltrim( $release['name'], 'v' );

		if ( preg_match( '/-pr\d+/i', $tag ) ) {
			continue;
		}

		$release_data = apply_filters( 'wpo_wcpdf_github_release_data', array(
			'name'     => $name,
			'tag'      => $tag,
			'url'      => $release['html_url'],
			'zipball'  => $release['zipball_url'],
			'download' => "https://github.com/{$owner}/{$repo}/releases/download/{$tag}/{$repo}.{$name}.zip"
		), $release, $owner, $repo );

		if ( ! $release['prerelease'] && empty( $stable ) ) {
			$stable = $release_data;

			// Once we find the first stable, we stop.
			break;
		}

		if ( $release['prerelease'] && empty( $unstable ) ) {
			$unstable = $release_data;
		}
	}

	$data = array(
		'stable'   => $stable,
		'unstable' => $unstable,
	);

	// Check if a new prerelease is available
	$last_seen_option_key = 'wpo_last_seen_prerelease_' . md5( $owner . '/' . $repo );
	$last_seen_tag        = get_option( $last_seen_option_key );

	if ( ! empty( $unstable['tag'] ) && $unstable['tag'] !== $last_seen_tag ) {
		update_option( $last_seen_option_key, $unstable['tag'], false );

		/**
		 * Fires when a new GitHub prerelease becomes available.
		 *
		 * @param array  $unstable The new prerelease data.
		 * @param string $owner    GitHub repo owner.
		 * @param string $repo     GitHub repo name.
		 */
		do_action( 'wpo_wcpdf_new_github_prerelease_available', $unstable, $owner, $repo );
	}

	update_option( $option_key, array(
		'timestamp' => time(),
		'data'      => $data,
	), false );

	return $data;
}

/**
 * Get the latest plugin version from the WordPress.org API.
 *
 * @param string $plugin_slug
 * @return string|false
 */
function wpo_wcpdf_get_latest_plugin_version( string $plugin_slug ) {
	// Ensure plugin update info is loaded
	if ( ! function_exists( 'get_site_transient' ) ) {
		require_once ABSPATH . 'wp-includes/option.php';
	}

	$update_plugins = get_site_transient( 'update_plugins' );

	if ( isset( $update_plugins->response[ $plugin_slug ] ) ) {
		return $update_plugins->response[ $plugin_slug ]->new_version;
	}

	// No update available or plugin not found
	return false;
}

/**
 * Write UBL file
 *
 * @param \WPO\IPS\Documents\OrderDocument $document
 * @param bool $attachment
 * @param bool $contents_only
 *
 * @return string|false
 */
function wpo_ips_write_ubl_file( \WPO\IPS\Documents\OrderDocument $document, bool $attachment = false, bool $contents_only = false ) {
	$ubl_maker = wcpdf_get_ubl_maker();

	if ( ! $ubl_maker ) {
		return wcpdf_error_handling( 'UBL Maker not available. Cannot write UBL file.' );
	}

	if ( $attachment ) {
		$tmp_path = WPO_WCPDF()->main->get_tmp_path( 'attachments' );

		if ( ! $tmp_path ) {
			return wcpdf_error_handling( 'Temporary path not available. Cannot write UBL file.' );
		}

		$ubl_maker->set_file_path( $tmp_path );
	}

	$ubl_document = new \WPO\IPS\UBL\Documents\UblDocument();
	$ubl_document->set_order_document( $document );

	$builder  = new \WPO\IPS\UBL\Builders\SabreBuilder();
	$contents = apply_filters( 'wpo_ips_ubl_contents',
		$builder->build( $ubl_document ),
		$ubl_document,
		$document
	);

	if ( empty( $contents ) ) {
		return wcpdf_error_handling( 'Failed to build UBL contents.' );
	}

	if ( $contents_only ) {
		return $contents;
	}

	$filename = apply_filters( 'wpo_ips_ubl_filename',
		$document->get_filename(
			'download',
			array( 'output' => 'ubl' )
		),
		$document
	);

	$full_filename = $ubl_maker->write( $filename, $contents );

	return $full_filename;
}

/**
 * Get the country name from the country code.
 *
 * @param string $country_code
 *
 * @return string Country name or empty string if not found.
 */
function wpo_wcpdf_get_country_name_from_code( string $country_code ): string {
	$country_code = strtoupper( trim( $country_code ) );
	return \WC()->countries->get_countries()[ $country_code ] ?? '';
}

/**
 * Get the state name from state code and country code.
 *
 * @param string $state_code
 * @param string $country_code
 *
 * @return string State name or empty string if not found.
 */
function wpo_wcpdf_get_state_name_from_code( string $state_code, string $country_code ): string {
	$state_code = $state_name = strtoupper( trim( $state_code ) );
	$states     = wpo_wcpdf_get_country_states( $country_code );

	if ( ! empty( $state_code ) && is_array( $states ) && isset( $states[ $state_code ] ) ) {
		$state_name = $states[ $state_code ];
	}

	return $state_name ?? '';
}

/**
 * Get the address format for a given country.
 *
 * @param string $country_code Country code, like the NL.
 *
 * @return string
 */
function wpo_wcpdf_get_country_address_format( string $country_code ): string {
	$country_code    = strtoupper( trim( $country_code ) );
	$address_formats = \WC()->countries->get_address_formats();

	return ! empty( $country_code ) && ! empty( $address_formats[ $country_code ] )
		? $address_formats[ $country_code ]
		: $address_formats['default'];
}

/**
 * Get the states for a given country code.
 *
 * @param string $country_code
 *
 * @return array
 */
function wpo_wcpdf_get_country_states( string $country_code ): array {
	$states = array();

	if ( ! empty( $country_code ) ) {
		$country_code = strtoupper( trim( $country_code ) );
		$states       = \WC()->countries->get_states( $country_code );
	}

	return $states ?: array();
}

/**
 * Get the formatted address.
 *
 * @param array $address
 *
 * @return string
 */
function wpo_wcpdf_format_address( array $address ): string {
	// Set default values for missing address fields.
	$address['country_code']    = strtoupper( $address['country_code'] ?? '' );
	$address['state_code']      = strtoupper( $address['state_code'] ?? '' );
	$address['country']         = wpo_wcpdf_get_country_name_from_code( $address['country_code'] );
	$address['state']           = wpo_wcpdf_get_state_name_from_code( $address['state_code'], $address['country_code'] );
	$address['state_upper']     = strtoupper( $address['state'] );
	$address['city_upper']      = strtoupper( $address['city'] ?? '' );
	$address['last_name_upper'] = strtoupper( $address['last_name'] ?? '' );
	$address['postcode_upper']  = strtoupper( $address['postcode'] ?? '' );

	// Filter the address before formatting.
	$address = apply_filters( 'wpo_wcpdf_format_address', $address );

	// Get the country address format
	$address_format = wpo_wcpdf_get_country_address_format( $address['country_code'] );

	// Replace placeholders
	$formatted_address = preg_replace_callback(
		'/\{([a-zA-Z0-9_]+)}/',
		function ( $matches ) use ( $address ) {
			return $address[ $matches[1] ] ?? '';
		},
		$address_format
	);

	// Normalize commas and remove extra line breaks.
	$formatted_address = preg_replace(
		array(
			'/,\s*,+/', // Remove consecutive commas
			'/,\s*$/',  // Remove trailing commas
			'/\n\s*\n/' // Remove empty lines
		),
		array( ',', '', "\n" ),
		$formatted_address
	);

	// Trim newline characters from beginning and end.
	$formatted_address = trim( $formatted_address, "\n" );

	// Add additional info if provided.
	if ( ! empty( $address['additional'] ) ) {
		$formatted_address .= "\n" . $address['additional'];
	}

	// Convert to HTML line breaks.
	$formatted_address = nl2br( ltrim( $formatted_address, "\r\n" ) );

	// Remove any new lines.
	$formatted_address = str_replace( "\n", '', $formatted_address );

	return esc_html( $formatted_address );
}


/**
 * Formats a document number by applying a prefix, suffix, and optional padding,
 * with support for dynamic placeholders based on order and document dates.
 *
 * Available placeholders in prefix and suffix:
 * - [order_year], [order_month], [order_day]
 * - [invoice_year], [invoice_month], [invoice_day] (uses $document->slug)
 * - [order_number]
 * - [order_date="{date_format}"], [invoice_date="{date_format}"] (with $document->slug as type)
 *
 * @param int|null                         $plain_number The base document number (unformatted).
 * @param string|null                      $prefix       The prefix string (may contain placeholders).
 * @param string|null                      $suffix       The suffix string (may contain placeholders).
 * @param int|null                         $padding      Number of digits for zero-padding the base number.
 * @param \WPO\IPS\Documents\OrderDocument $document     The document object (e.g. invoice or credit note).
 * @param \WC_Abstract_Order               $order        The WooCommerce order associated with the document.
 *
 * @return string The fully formatted document number.
 */
function wpo_wcpdf_format_document_number( ?int $plain_number, ?string $prefix, ?string $suffix, ?int $padding, \WPO\IPS\Documents\OrderDocument $document, \WC_Abstract_Order $order ): string {
	// Get dates
	$order_date = $order->get_date_created();

	// Order date can be empty when order is being saved, fallback to current time
	if ( empty( $order_date ) && function_exists( 'wc_string_to_datetime' ) ) {
		$order_date = wc_string_to_datetime( date_i18n( 'Y-m-d H:i:s' ) );
	}

	$document_date = $document->get_date();
	// fallback to order date if no document date available
	if ( empty( $document_date ) ) {
		$document_date = $order_date;
	}

	// load replacement values
	$order_year     = $order_date->date_i18n( 'Y' );
	$order_month    = $order_date->date_i18n( 'm' );
	$order_day      = $order_date->date_i18n( 'd' );
	$document_year  = $document_date->date_i18n( 'Y' );
	$document_month = $document_date->date_i18n( 'm' );
	$document_day   = $document_date->date_i18n( 'd' );

	// get order number
	if ( is_callable( array( $order, 'get_order_number' ) ) ) { // order
		$order_number = $order->get_order_number();
	} elseif ( $document->is_refund( $order ) ) { // refund order
		$parent_order = $document->get_refund_parent( $order );

		if ( ! empty( $parent_order ) && is_callable( array( $parent_order, 'get_order_number' ) ) ) {
			$order_number = $parent_order->get_order_number();
		}
	} else {
		$order_number = '';
	}

	// get format settings
	$formats = array(
		'prefix' => $prefix,
		'suffix' => $suffix,
	);

	// make replacements
	foreach ( $formats as $key => $value ) {
		if ( empty( $value ) ) {
			continue;
		}

		$value = str_replace( '[order_year]', $order_year, $value );
		$value = str_replace( '[order_month]', $order_month, $value );
		$value = str_replace( '[order_day]', $order_day, $value );
		$value = str_replace( "[{$document->slug}_year]", $document_year, $value );
		$value = str_replace( "[{$document->slug}_month]", $document_month, $value );
		$value = str_replace( "[{$document->slug}_day]", $document_day, $value );
		$value = str_replace( '[order_number]', $order_number, $value );

		// replace date tag in the form [invoice_date="{$date_format}"] or [order_date="{$date_format}"]
		$date_types = array( 'order', $document->slug );
		foreach ( $date_types as $date_type ) {
			if ( false !== strpos( $value, "[{$date_type}_date=" ) ) {
				preg_match_all( "/\[{$date_type}_date=\"(.*?)\"\]/", $value, $document_date_tags );

				if ( ! empty( $document_date_tags[1] ) ) {
					foreach ( $document_date_tags[1] as $match_id => $date_format ) {
						if ( 'order' === $date_type ) {
							$value = str_replace( $document_date_tags[0][ $match_id ], $order_date->date_i18n( $date_format ), $value );
						} else {
							$value = str_replace( $document_date_tags[0][ $match_id ], $document_date->date_i18n( $date_format ), $value );
						}
					}
				}
			}
		}
		$formats[ $key ] = $value;
	}

	// Padding
	$padding_string = '';
	if ( function_exists( 'ctype_digit' ) ) { // requires the Ctype extension
		if ( ctype_digit( (string) $padding ) ) {
			$padding_string = (string) $padding;
		}
	} elseif ( ! empty( $padding ) ) {
		$padding_string = (string) $padding;
	}

	if ( ! empty( $padding_string ) ) {
		$plain_number = sprintf( '%0' . $padding_string . 'd', $plain_number );
	}

	// Add prefix & suffix
	return $formats['prefix'] . $plain_number . $formats['suffix'];
}

/**
 * Outputs item meta data.
 *
 * This is a customized version of the WooCommerce function `wc_display_item_meta()`,
 * which uses the `get_all_formatted_meta_data()` method instead of `get_formatted_meta_data()`.
 *
 * @param WC_Order_Item $item Order item object.
 * @param array         $args Optional. Display arguments.
 *
 * @return string|void Meta data HTML output or void if echoed directly.
 */

function wpo_ips_display_item_meta( \WC_Order_Item $item, array $args = array() ) {
	$strings = array();
	$html    = '';
	$args    = wp_parse_args(
		$args,
		array(
			'before'       => '<ul class="wc-item-meta"><li>',
			'after'        => '</li></ul>',
			'separator'    => '</li><li>',
			'echo'         => true,
			'autop'        => false,
			'label_before' => '<strong class="wc-item-meta-label">',
			'label_after'  => ':</strong> ',
		)
	);

	$meta_data = method_exists( $item, 'get_all_formatted_meta_data' )
		? $item->get_all_formatted_meta_data()
		: $item->get_formatted_meta_data();

	foreach ( $meta_data as $meta_id => $meta ) {
		$value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
		$strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;
	}

	if ( $strings ) {
		$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
	}

	$html = apply_filters(
		'wpo_ips_display_item_meta_html',
		apply_filters( 'woocommerce_display_item_meta', $html, $item, $args ),
		$item,
		$args
	);

	if ( $args['echo'] ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	} else {
		return $html;
	}
}
