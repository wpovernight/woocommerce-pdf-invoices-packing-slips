<?php
namespace WPO\WC\PDF_Invoices\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Tables\\Number_Store_List_Table' ) ) :
	
class Number_Store_List_Table extends \WP_List_Table {
	
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

		$this->process_bulk_action();
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
		switch ( $column_name ) {
			case 'number' :
				$value = $item->id;
				break;
			case 'type' :
				$value = isset( $item->document_title ) ? esc_attr( $item->document_title ) : '-';
				break;
			case 'calculated_number' :
				$value = isset( $item->calculated_number ) ? $item->calculated_number : '-';
				break;
			case 'date' :
				$value = $item->date;
				break;	
			case 'order' :
				if ( ! empty( $item->order_id ) ) {
					$order        = $this->get_base_order( wc_get_order( $item->order_id ) );
					$order_number = is_callable( array( $order, 'get_order_number' ) ) ? $order->get_order_number() : $item->order_id;
					$order_id     = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $item->order_id;
					$url          = sprintf( 'post.php?post=%s&action=edit', $order_id );
					$value        = sprintf( '<a href="%s">#%s</a>', $url, $order_number );
					
					if ( absint( $order_id ) !== absint( $item->order_id ) ) {
						$value .= sprintf( ' (%s #%s)', __( 'Refund:', 'woocommerce-pdf-invoices-packing-slips' ), $item->order_id );
					}
				} else {
					$value = '-';
				}
				break;
			case 'order_status' :
				$order = $this->get_base_order( wc_get_order( $item->order_id ) );
				
				if ( ! empty( $order ) ) {
					$value = sprintf(
						'<mark class="order-status %s"><span>%s</span></mark>',
						esc_attr( sanitize_html_class( 'status-' . $order->get_status() ) ),
						esc_html( wc_get_order_status_name( $order->get_status() ) )
					);
				} else {
					$value = '<strong>' . __( 'Unknown', 'woocommerce-pdf-invoices-packing-slips' ) . '</strong>';
				}
				break;
			default:
				$value = isset( $item->$column_name ) ? $item->$column_name : null;
				break;
		}
		
		if ( empty( $value ) ) {
			$value = '-';
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
		
		$table_name    = isset( $_GET['table_name'] ) ? sanitize_text_field( $_GET['table_name'] ) : null;
		$document_type = WPO_WCPDF()->settings->debug->get_document_type_from_store_table_name( $table_name );
		
		if ( empty( $document_type ) || 'invoice' !== $document_type ) {
			unset( $columns['type'] );
		}
		
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
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @since 2.0
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
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
		global $wpdb;

		$results                        = array();
		$paged                          = $this->get_paged();
		$offset                         = $this->per_page * ( $paged - 1 );
		$search                         = $this->get_search();
		$table_name                     = isset( $_GET['table_name'] ) ? sanitize_text_field( $_GET['table_name'] ) : null;
		$order                          = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby                        = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';
		$document_type                  = WPO_WCPDF()->settings->debug->get_document_type_from_store_table_name( $table_name );
		$invoice_number_store_doc_types = WPO_WCPDF()->settings->debug->get_additional_invoice_number_store_document_types();
		
		if ( 'invoice' !== $document_type && in_array( $document_type, $invoice_number_store_doc_types ) ) {
			return array(); // using `invoice_number`
		}

		if ( ! empty( $table_name ) ) {
			if ( $search ) {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE `id` LIKE '$search' OR `order_id` LIKE '$search' ORDER BY $orderby $order LIMIT %d OFFSET %d", $this->per_page, $offset ) );
			} else {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $this->per_page, $offset ) );
			}
		} else {
			$results = array();
		}
		
		// add document title or 'Deleted'
		if ( ! empty( $results ) && ! empty( $document_type ) ) {
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
				
				foreach ( $document_types as $doc_type ) {
					$document = wcpdf_get_document( $doc_type, wc_get_order( $order_id ) );
					
					if ( $document && is_callable( array( $document, 'get_number' ) ) ) {
						$number_obj = $document->get_number();
						
						if ( ! empty( $number_obj ) && ! empty( $number_obj->number ) ) {
							if ( isset( $result['id'] ) && absint( $result['id'] ) === absint( $number_obj->number ) ) {
								$results[ $key ]->document_title = $document->get_title();
							}
						}
					}
				}
			}
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

		$this->items = $this->get_numbers();
		$total_items = count( $this->items );
		$per_page    = apply_filters( 'wpo_wcpdf_number_store_list_table_per_page', $this->per_page );

		// Setup pagination
		$this->set_pagination_args( array(
			'total_pages' => ceil( $total_items / $per_page ),
			'total_items' => $total_items,
			'per_page'    => $per_page
		) );
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
