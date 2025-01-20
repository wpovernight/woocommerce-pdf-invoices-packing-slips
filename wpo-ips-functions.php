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
	return wcpdf_get_document( 'invoice', $order, $init );
}

function wcpdf_get_packing_slip( $order, $init = false ) {
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

function wcpdf_pdf_headers( $filename, $mode = 'inline', $pdf = null ) {
	switch ( $mode ) {
		case 'download':
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: Keep-Alive' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Pragma: public' );
			break;
		case 'inline':
		default:
			header( 'Content-type: application/pdf' );
			header( 'Content-Disposition: inline; filename="' . $filename . '"' );
			break;
	}
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
 * @return string
 */
function wcpdf_get_document_file( object $document, string $output_format = 'pdf', $error_handling = 'exception' ): string {
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
	
	if ( ! $document->is_enabled( $output_format ) ) {
		$error_message = "The {$output_format} output format is not enabled for this document: {$document->get_title()}.";
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	$tmp_path = WPO_WCPDF()->main->get_tmp_path( 'attachments' );

	if ( ! @is_dir( $tmp_path ) || ! wp_is_writable( $tmp_path ) ) {
		$error_message = "Couldn't get the attachments temporary folder path: {$tmp_path}.";
		return wcpdf_error_handling( $error_message, $error_handling, true, 'critical' );
	}

	$function = "get_document_{$output_format}_attachment";

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
		error_log( $log_string );
		wcpdf_log_error( $log_string, 'warning' );
	} else {
		_deprecated_function( $function, $version, $replacement );
	}
}

/**
 * Logger function to capture errors thrown by this plugin, uses the WC Logger when possible (WC3.0+)
 */
function wcpdf_log_error( $message, $level = 'error', $e = null ) {
	if ( function_exists( 'wc_get_logger' ) ) {
		$logger  = wc_get_logger();
		$context = array( 'source' => 'wpo-wcpdf' );

		if ( is_callable( array( $e, 'getFile' ) ) && is_callable( array( $e, 'getLine' ) ) ) {
			$message = sprintf( '%s (%s:%d)', $message, $e->getFile(), $e->getLine() );
		}

		if ( apply_filters( 'wcpdf_log_stacktrace', false ) && is_callable( array( $e, 'getTraceAsString' ) ) ) {
			$message .= "\n" . $e->getTraceAsString();
		}
		// The `log` method accepts any valid level as its first argument.
		// debug     - 'Detailed debug information'
		// info      - 'Interesting events'
		// notice    - 'Normal but significant events'
		// warning   - 'Exceptional occurrences that are not errors'
		// error     - 'Runtime errors that do not require immediate'
		// critical  - 'Critical conditions'
		// alert     - 'Action must be taken immediately'
		// emergency - 'System is unusable'.
		$logger->log( $level, $message, $context );
	} else {
		error_log( "WCPDF error ({$level}): {$message}" );
	}
}

function wcpdf_output_error( $message, $level = 'error', $e = null ) {
	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		esc_html_e( 'Error creating PDF, please contact the site owner.', 'woocommerce-pdf-invoices-packing-slips' );
		return;
	}
	?>
	<div style="border: 2px solid red; padding: 5px;">
		<h3><?php echo wp_kses_post( $message ); ?></h3>
		<?php if ( is_callable( array( $e, 'getFile' ) ) && is_callable( array( $e, 'getLine' ) ) ): ?>
		<pre><?php echo esc_html( $e->getFile() ); ?> (<?php echo esc_html( $e->getLine() ); ?>)</pre>
		<?php endif ?>
		<?php if ( is_callable( array( $e, 'getTraceAsString' ) ) ) : ?>
		<pre><?php echo esc_html( $e->getTraceAsString() ); ?></pre>
		<?php endif ?>
	</div>
	<?php
}

/**
 * Error handling function
 *
 * @param string $message
 * @param string $handling_type
 * @param bool   $log_error
 * @param string $log_level
 * @return mixed
 * @throws Exception
 */
function wcpdf_error_handling( string $message, string $handling_type = 'exception', bool $log_error = true, string $log_level = 'error' ) {
	if ( $log_error ) {
		wcpdf_log_error( $message, $log_level );
	}

	switch ( $handling_type ) {
		case 'exception':
			throw new \Exception( $message );
			break;
		case 'output':
			wcpdf_output_error( $message, $log_level );
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
 * Catch MySQL errors
 *
 * Inspired from here: https://github.com/johnbillion/query-monitor/blob/d5b622b91f18552e7105e62fa84d3102b08975a4/collectors/db_queries.php#L125-L280
 *
 * With SAVEQUERIES constant defined as 'false', '$wpdb->queries' is empty and '$EZSQL_ERROR' is used instead.
 * Using the Query Monitor plugin, the SAVEQUERIES constant is defined as 'true'
 * More info about this constant can be found here: https://wordpress.org/support/article/debugging-in-wordpress/#savequeries
 *
 * @param  object $wpdb
 * @return array  errors found
 */
function wcpdf_catch_db_object_errors( $wpdb ) {
	global $EZSQL_ERROR;

	$errors = array();

	// using '$wpdb->queries'.
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
	// fallback to '$EZSQL_ERROR'.
	if ( empty( $errors ) && ! empty( $EZSQL_ERROR ) && is_array( $EZSQL_ERROR ) ) {
		foreach ( $EZSQL_ERROR as $error ) {
			$errors[] = $error['error_str'];
		}
	}

	// log errors.
	if ( ! empty( $errors ) ) {
		foreach ( $errors as $error_message ) {
			wcpdf_log_error( $error_message, 'critical' );
		}
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
		wp_die( $message );
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
			finfo_close( $finfo );
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
		$image_data = @file_get_contents( $src );

		if ( $image_data ) {
			if ( function_exists( 'finfo_open' ) ) {
				$finfo = finfo_open( FILEINFO_MIME_TYPE );

				if ( $finfo ) {
					$mime_type = finfo_buffer( $finfo, $image_data );
					finfo_close( $finfo );
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
 * Base64 encode file from URL or local path
 *
 * @param string $src
 *
 * @return string|bool
 */
function wpo_wcpdf_base64_encode_file( string $src ) {
	if ( empty( $src ) ) {
		return false;
	}

	$file_data = @file_get_contents( $src );
	return base64_encode( $file_data ) ?? false;
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
		$parsed_url = parse_url( $path );
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
		if ( @is_readable( $path ) ) {
			return true;
		} else {
			// Fallback to fopen if first check fails
			$handle = @fopen( $path, 'r' );

			if ( $handle ) {
				fclose( $handle );
				return true;
			} else {
				wcpdf_log_error( 'Failed to open local file with both methods: ' . $path, 'critical' );
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
 * Dynamic string translation
 *
 * @param string $string
 * @param string $textdomain
 * @return string
 */
function wpo_wcpdf_dynamic_translate( string $string, string $textdomain ): string {
	$log_enabled		= isset( WPO_WCPDF()->settings->debug_settings['log_missing_translations'] );
	$log_message        = "Missing translation for: {$string} in textdomain: {$textdomain}";
	$multilingual_class = '\WPO\WC\PDF_Invoices_Pro\Multilingual_Full';
	$translation        = '';
	
	if ( empty( $string ) ) {
		if ( $log_enabled ) {
			wcpdf_log_error( $log_message, 'warning' );
		}
		return $string;
	}
	
	// Check for multilingual support class
	if ( class_exists( $multilingual_class ) && method_exists( $multilingual_class, 'maybe_get_string_translation' ) ) {
		$translation = $multilingual_class::maybe_get_string_translation( $string, $textdomain );
	}
	
	// If multilingual didn't change the string, fall back to native translate()
	if ( ( empty( $translation ) || $translation === $string ) && function_exists( 'translate' ) ) {
		$translation = translate( $string, $textdomain );
	}
	
	// Log missing translations for debugging if it's still untranslated
	if ( $translation === $string && $log_enabled ) {
		wcpdf_log_error( $log_message, 'warning' );
	}
	
	return $translation ?: $string;
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
