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
 * Passing an array of order ids will return a Bulk_Document object.
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
	return new \WPO\WC\PDF_Invoices\Documents\Bulk_Document( $document_type, $order_ids );
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
 * @return WPO\WC\PDF_Invoices\Makers\PDF_Maker
 */
function wcpdf_get_pdf_maker( $html, $settings = array(), $document = null ) {
	$class = ( defined( 'WPO_WCPDF_VERSION' ) && version_compare( WPO_WCPDF_VERSION, '3.7.0-beta-1', '<' ) ) ? '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' : '\\WPO\\WC\\PDF_Invoices\\Makers\\PDF_Maker';
	
	if ( ! class_exists( $class ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/makers/class-pdf-maker.php' );
	}
	
	$class = apply_filters( 'wpo_wcpdf_pdf_maker', $class );
	
	return new $class( $html, $settings, $document );
}

/**
 * Get UBL Maker
 * Use wpo_wcpdf_ubl_maker filter to change the UBL class (which can wrap another UBL library).
 * 
 * @return WPO\WC\PDF_Invoices\Makers\UBL_Maker
 */
function wcpdf_get_ubl_maker() {
	$class = '\\WPO\\WC\\PDF_Invoices\\Makers\\UBL_Maker';
	
	if ( ! class_exists( $class ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/makers/class-ubl-maker.php' );
	}
	
	$class = apply_filters( 'wpo_wcpdf_ubl_maker', $class );
	
	return new $class();
}

/**
 * Check if the default PDF maker is used for creating PDF
 * 
 * @return bool whether the PDF maker is the default or not
 */
function wcpdf_pdf_maker_is_default() {
	$default_pdf_maker = ( defined( 'WPO_WCPDF_VERSION' ) && version_compare( WPO_WCPDF_VERSION, '3.7.0-beta-1', '<' ) ) ? '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' : '\\WPO\\WC\\PDF_Invoices\\Makers\\PDF_Maker';
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
	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: text/xml' );
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
	@$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	
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

	return $dom->saveHTML();
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

function WPO_WCPDF_Legacy() {
	return \WPO\WC\PDF_Invoices\Legacy\WPO_WCPDF_Legacy::instance();
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

