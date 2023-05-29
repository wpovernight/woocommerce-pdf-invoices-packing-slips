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

function wcpdf_get_document( $document_type, $order, $init = false ) {
	// $order can be one of the following:
	// - WC Order object
	// - array of order ids
	// - null if order not loaded or loaded later
	if ( ! empty( $order ) ) {
		if ( ! is_object( $order ) && ! is_array( $order ) && is_numeric( $order ) ) {
			$order = array( absint( $order ) ); // convert single order id to array.
		}
		if ( is_object( $order ) ) {
			// we filter order_ids for objects too:
			// an order object may need to be converted to several refunds for example.
			$order_ids = array( $order->get_id() );
			$filtered_order_ids = wcpdf_filter_order_ids( $order_ids, $document_type );
			// check if something has changed.
			$order_id_diff = array_diff( $filtered_order_ids, $order_ids );
			if ( empty( $order_id_diff ) && count( $order_ids ) == count( $filtered_order_ids ) ) {
				// nothing changed, load document with Order object.
				do_action( 'wpo_wcpdf_process_template_order', $document_type, $order->get_id() );
				$document = WPO_WCPDF()->documents->get_document( $document_type, $order );

				if ( ! $document->is_allowed() ) {
					return false;
				}

				if ( $init && ! $document->exists() ) {
					$document->init();
					$document->save();
				}
				return $document;
			} else {
				// order ids array changed, continue processing that array.
				$order_ids = $filtered_order_ids;
			}
		} elseif ( is_array( $order ) ) {
			$order_ids = wcpdf_filter_order_ids( $order, $document_type );
		} else {
			return false;
		}

		if ( empty( $order_ids ) ) {
			// No orders to export for this document type.
			return false;
		}

		// if we only have one order, it's simple.
		if ( count( $order_ids ) == 1 ) {
			$order_id = array_pop( $order_ids );
			do_action( 'wpo_wcpdf_process_template_order', $document_type, $order_id );
			$order = wc_get_order( $order_id );

			$document = WPO_WCPDF()->documents->get_document( $document_type, $order );

			if ( ! $document->is_allowed() ) {
				return false;
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

	return $document;
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

/**
 * Load HTML into (pluggable) PDF library, DomPDF 1.0.2 by default
 * Use wpo_wcpdf_pdf_maker filter to change the PDF class (which can wrap another PDF library).
 * 
 * @param string       $html
 * @param array        $settings
 * @param null|object  $document
 * @return WPO\WC\PDF_Invoices\PDF_Maker
 */
function wcpdf_get_pdf_maker( $html, $settings = array(), $document = null ) {
	if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' ) ) {
		include_once( WPO_WCPDF()->plugin_path() . '/includes/class-wcpdf-pdf-maker.php' );
	}
	$class = apply_filters( 'wpo_wcpdf_pdf_maker', '\\WPO\\WC\\PDF_Invoices\\PDF_Maker' );
	return new $class( $html, $settings, $document );
}

/**
 * Check if the default PDF maker is used for creating PDF
 * 
 * @return bool whether the PDF maker is the default or not
 */
function wcpdf_pdf_maker_is_default() {
	$default_pdf_maker = '\\WPO\\WC\\PDF_Invoices\\PDF_Maker';
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
	$filter = current_filter();
	$global_wcpdf_filters = array( 'wp_ajax_generate_wpo_wcpdf' );
	if ( ! in_array( $filter, $global_wcpdf_filters ) && strpos( $filter, 'wpo_wcpdf' ) !== false && strpos( $replacement, '$this' ) !== false ) {
		$replacement = str_replace( '$this', '$document', $replacement );
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
		$logger = wc_get_logger();
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

