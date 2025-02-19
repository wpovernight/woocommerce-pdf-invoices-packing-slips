<?php
namespace WPO\IPS\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( '\\WPO\\IPS\\Tables\\NumberStoreListTable' ) ) :

class NumberStoreListTable extends \WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 2.0
	 */
	public $per_page = 50;

	/**
	 * The arguments for the data set
	 *
	 * @var array
	 * @since 2.0
	 */
	public $args = array();

	/**
	 * Get things started
	 *
	 * @since 2.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'number',
			'plural'   => 'numbers',
			'ajax'     => false
		) );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 2.0
	 *
	 * @param object $item Contains all the data of the numbers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$order = $this->get_base_order( wc_get_order( $item->order_id ) );
		$value = '-';

		if ( ! empty( $order ) ) {
			switch ( $column_name ) {
				case 'number':
					$value = $item->id;
					break;
				case 'type':
					$value          = '<span class="item-number number-gapped">' . __( 'gapped', 'woocommerce-pdf-invoices-packing-slips' ) . '</span>';
					$document_types = isset( $item->document_types ) && is_array( $item->document_types ) ? $item->document_types : array();

					// document using invoice number, eg. proforma
					if ( count( $document_types ) > 1 ) {
						foreach ( $document_types as $key => $doc_type ) {
							if ( 'invoice' === $doc_type ) {
								unset( $document_types[ $key ] );
							}
						}
					}

					$document_type = reset( $document_types );

					if ( ! empty( $document_type ) ) {
						$document_slug = str_replace( '-', '_', $document_type );
						$number_data   = $order->get_meta( "_wcpdf_{$document_slug}_number_data", true );
						$saved_number  = isset( $number_data['number'] ) ? $number_data['number'] : null;
						$order_id      = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $item->order_id;

						// all documents using parent order
						if ( ! empty( $saved_number ) && absint( $saved_number ) === absint( $item->id ) ) {
							$value = '<span class="item-number number-doc-type">' . $document_type . '</span>';
						// credit notes may have meta saved in the refund order
						} elseif ( 'credit-note' === $document_type && absint( $order_id ) !== absint( $item->order_id ) ) {
							$value = sprintf(
								'<span class="item-number number-doc-type">%s</span><p style="margin-top:6px;"><span class="item-number number-refund">%s #%s</span></p>',
								$document_type,
								__( 'refund:', 'woocommerce-pdf-invoices-packing-slips' ),
								$item->order_id
							);
						}
					}
					break;
				case 'calculated_number':
					$value = isset( $item->calculated_number ) ? $item->calculated_number : '-';
					break;
				case 'date':
					$value = $item->date;
					break;
				case 'order':
					$order_number = is_callable( array( $order, 'get_order_number' ) ) ? $order->get_order_number() : $item->order_id;
					$order_id     = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $item->order_id;
					$url          = sprintf( 'post.php?post=%s&action=edit', $order_id );
					$value        = sprintf( '<a href="%s">#%s</a>', $url, $order_number );
					break;
				case 'order_status':
					$value = sprintf(
						'<mark class="order-status %s"><span>%s</span></mark>',
						esc_attr( sanitize_html_class( 'status-' . $order->get_status() ) ),
						esc_html( wc_get_order_status_name( $order->get_status() ) )
					);
					break;
				default:
					$value = isset( $item->$column_name ) ? $item->$column_name : null;
					break;
			}
		}

		return apply_filters( 'wpo_wcpdf_number_tools_column_content_' . $column_name, $value, $item );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 2.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'number'            => __( 'Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'type'              => __( 'Type', 'woocommerce-pdf-invoices-packing-slips' ),
			'calculated_number' => __( 'Calculated', 'woocommerce-pdf-invoices-packing-slips' ),
			'date'              => __( 'Date', 'woocommerce-pdf-invoices-packing-slips' ),
			'order'             => __( 'Order', 'woocommerce-pdf-invoices-packing-slips' ),
			'order_status'      => __( 'Order Status', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		if ( ! isset( WPO_WCPDF()->settings->debug_settings['calculate_document_numbers'] ) ) {
			unset( $columns['calculated_number'] );
		}

		return apply_filters( 'wpo_wcpdf_number_tools_columns', $columns );
	}

	/**
	 * Get the sortable columns
	 *
	 * @since 2.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'number' => array( 'id', true ),
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 2.0
	 * @return array Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 2.0
	 * @return int Current page number
	 */
	public function get_paged( $request ) {
		return isset( $request['paged'] ) ? absint( $request['paged'] ) : 1;
	}

	/**
	 * Build all the number data
	 *
	 * @since 2.0
	  * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $numbers All the data for number list table
	 */
	public function get_numbers() {
		$request = stripslashes_deep( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		extract( WPO_WCPDF()->settings->debug->filter_fetch_request_data( $request ) );

		$document_type                  = WPO_WCPDF()->settings->debug->get_document_type_from_store_table_name( $table_name );
		$invoice_number_store_doc_types = WPO_WCPDF()->settings->debug->get_additional_invoice_number_store_document_types();

		if (
			empty( $document_type ) ||
			( 'invoice' !== $document_type && in_array( $document_type, $invoice_number_store_doc_types ) ) ||
			empty( $table_name ) ||
			as_has_scheduled_action( 'wpo_wcpdf_number_table_data_fetch' )
		) {
			return array(); // using `invoice_number`
		}

		$option_name = "wpo_wcpdf_number_data::{$table_name}";
		$results     = get_option( $option_name, array() );

		if ( ! empty( $results ) ) {

			// we have a search request, return results by search term
			if ( isset( $request['s'] ) ) {
				$results = WPO_WCPDF()->settings->debug->search_number_in_table_data( $table_name, esc_attr( $request['s'] ) );
			}

			// include document types
			foreach ( $results as $key => $result ) {
				$result         = (array) $result;
				$document_types = array( $document_type );
				$order_id       = isset( $result['order_id'] ) ? absint( $result['order_id'] ) : 0;

				if ( 0 === $order_id ) {
					continue;
				}

				if ( 'invoice' === $document_type && ! empty( $invoice_number_store_doc_types ) ) {
					$document_types = array_merge( $document_types, $invoice_number_store_doc_types );
				}

				$results[ $key ]->document_types = $document_types;
			}

			// maybe sort the data
			$results = WPO_WCPDF()->settings->debug->sort_number_table_data( $results, $order, $orderby );
		}

		return $results;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 2.0
	 * @uses self::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses self::get_pagenum()
	 * @return void
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$this->process_bulk_action();

		$items        = $this->get_numbers();
		$total_items  = count( $items );
		$per_page     = apply_filters( 'wpo_wcpdf_number_store_list_table_per_page', $this->per_page );
		$current_page = $this->get_pagenum();
		$data         = array_slice( $items, ( ( $current_page - 1 ) * $per_page ), $per_page );

		// Setup pagination
		$this->set_pagination_args( array(
			'total_pages' => ceil( $total_items / $per_page ),
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );

		$this->items = $data;
	}

	/**
	 * Get the parent order for refunds
	 *
	 * @since 2.4
	 * @param $order WC_Order
	 * @return $order WC_Order
	 */
	public function get_base_order( $order ) {
		if ( is_callable( array( $order, 'get_type' ) ) && 'shop_order_refund' === $order->get_type() ) {
			return wc_get_order( $order->get_parent_id() );
		} else {
			return $order;
		}
	}

}

endif; // class_exists
