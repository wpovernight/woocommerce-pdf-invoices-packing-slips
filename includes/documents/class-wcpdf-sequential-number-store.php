<?php
namespace WPO\WC\PDF_Invoices\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Sequential_Number_Store' ) ) :

/**
 * Class handling database interaction for sequential numbers
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

class Sequential_Number_Store {
	/**
	 * Name of the number store (used for table_name)
	 * @var String
	 */
	public $store_name;

	/**
	 * Number store method, either 'auto_increment' or 'calculate'
	 * @var String
	 */
	public $method;

	/**
	 * Name of the table that stores the number sequence (including the wp_wcpdf_ table prefix)
	 * @var String
	 */
	public $table_name;

	/**
	 * If table name not found in database, is new table
	 * @var Bool
	 */
	public $is_new = false;

	public function __construct( $store_name, $method = 'auto_increment' ) {
		global $wpdb;
		$this->store_name = $store_name;
		$this->method     = $method;
		$this->table_name = apply_filters( "wpo_wcpdf_number_store_table_name", "{$wpdb->prefix}wcpdf_{$store_name}", $store_name, $method ); // e.g. wp_wcpdf_invoice_number

		$this->init();
	}

	public function init() {
		global $wpdb;

		// check if table exists
		if( ! $this->store_name_exists() ) {
			$this->is_new = true;
		} else {
			// check calculated_number column if using 'calculate' method
			if ( $this->method == 'calculate' ) {
				$column_exists = $wpdb->get_var("SHOW COLUMNS FROM `{$this->table_name}` LIKE 'calculated_number'");
				if (empty($column_exists)) {
					$wpdb->query("ALTER TABLE {$this->table_name} ADD calculated_number int (16)");
				}
			}
			return; // no further business
		}

		// create table (in case of concurrent requests, this does no harm if it already exists)
		$charset_collate = $wpdb->get_charset_collate();
		// dbDelta is a sensitive kid, so we omit indentation
$sql = "CREATE TABLE {$this->table_name} (
  id int(16) NOT NULL AUTO_INCREMENT,
  order_id int(16),
  date datetime DEFAULT '1000-01-01 00:00:00' NOT NULL,
  calculated_number int (16),
  PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );
		
		// catch mysql errors
		$wpdb_data = $this->process_db_object( $wpdb );
		if ( ! empty( $wpdb_data['errors'] ) && is_array( $wpdb_data['errors'] ) ) {
			foreach ( $wpdb_data['errors'] as $error ) {
				if ( ! empty( $error['result']->errors ) && is_array( $error['result']->errors ) ) {
					foreach ( $error['result']->errors as $error_message ) {
						if ( isset( $error_message[0] ) ) {
							wcpdf_log_error( $error_message[0], 'critical' );
						}
					}
				}
			}
		}

		return $result;
	}

	// original from: https://github.com/johnbillion/query-monitor/blob/d5b622b91f18552e7105e62fa84d3102b08975a4/collectors/db_queries.php#L125-L280
	public function process_db_object( $wpdb ) {
		global $EZSQL_ERROR, $wp_the_query;

		$data = array();

		// With SAVEQUERIES defined as false, `wpdb::queries` is empty but `wpdb::num_queries` is not.
		if ( empty( $wpdb->queries ) ) {
			$data['total_qs'] += $wpdb->num_queries;
			return;
		}

		$rows = array();
		$types = array();
		$total_time = 0;
		$has_result = false;
		$has_trace = false;
		$i = 0;
		$request = trim( $wp_the_query->request ? $wp_the_query->request : '' );

		if ( method_exists( $wpdb, 'remove_placeholder_escape' ) ) {
			$request = $wpdb->remove_placeholder_escape( $request );
		}

		foreach ( $wpdb->queries as $query ) {
			$has_trace = false;
			$has_result = false;
			$callers = array();

			if ( isset( $query['query'], $query['elapsed'], $query['debug'] ) ) {
				// WordPress.com VIP.
				$sql = $query['query'];
				$ltime = $query['elapsed'];
				$stack = $query['debug'];
			} else {
				// Standard WP.
				$sql = $query[0];
				$ltime = $query[1];
				$stack = $query[2];

				// Query Monitor db.php drop-in.
				$has_trace = isset( $query['trace'] );
				$has_result = isset( $query['result'] );
			}

			// @TODO: decide what I want to do with this:
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( false !== strpos( $stack, 'wp_admin_bar' ) && ! isset( $_REQUEST['qm_display_admin_bar'] ) ) {
				continue;
			}

			if ( $has_result ) {
				$result = $query['result'];
			} else {
				$result = null;
			}

			$total_time += $ltime;

			if ( $has_trace ) {

				$trace = $query['trace'];
				$component = $query['trace']->get_component();
				$caller = $query['trace']->get_caller();
				$caller_name = $caller['display'];
				$caller = $caller['display'];

			} else {

				$trace = null;
				$component = null;
				$callers = array_reverse( explode( ',', $stack ) );
				$callers = array_map( 'trim', $callers );
				$caller = reset( $callers );
				$caller_name = $caller;

			}

			$sql = trim( $sql );
			$type = $this->get_query_type( $sql );

			if ( ! isset( $types[ $type ]['total'] ) ) {
				$types[ $type ]['total'] = 1;
			} else {
				$types[ $type ]['total']++;
			}

			if ( ! isset( $types[ $type ]['callers'][ $caller ] ) ) {
				$types[ $type ]['callers'][ $caller ] = 1;
			} else {
				$types[ $type ]['callers'][ $caller ]++;
			}

			$is_main_query = ( $request === $sql && ( false !== strpos( $stack, ' WP->main,' ) ) );

			$row = compact( 'caller', 'caller_name', 'sql', 'ltime', 'result', 'type', 'component', 'trace', 'is_main_query' );

			if ( ! isset( $trace ) ) {
				$row['stack'] = $callers;
			}

			if ( is_wp_error( $result ) ) {
				$data['errors'][] = $row;
			}

			$rows[ $i ] = $row;
			$i++;

		}

		if ( ! $has_result && ! empty( $EZSQL_ERROR ) && is_array( $EZSQL_ERROR ) ) {
			// Fallback for displaying database errors when wp-content/db.php isn't in place
			foreach ( $EZSQL_ERROR as $error ) {
				$row = array(
					'caller' => null,
					'caller_name' => null,
					'stack' => '',
					'sql' => $error['query'],
					'ltime' => 0,
					'result' => new \WP_Error( 'qmdb', $error['error_str'] ),
					'type' => '',
					'component' => false,
					'trace' => null,
					'is_main_query' => false,
				);
				$data['errors'][] = $row;
			}
		}

		$total_qs = count( $rows );

		$data['total_qs']   += $total_qs;
		$data['total_time'] += $total_time;

		$has_main_query = wp_list_filter( $rows, array(
			'is_main_query' => true,
		) );

		# @TODO put errors in here too:
		# @TODO proper class instead of (object)
		$data['dbs'][ '$wpdb' ] = (object) compact( 'rows', 'types', 'has_result', 'has_trace', 'total_time', 'total_qs', 'has_main_query' );

		return $data;
	}

	public function get_query_type( $sql ) {
		// Trim leading whitespace and brackets
		$sql = ltrim( $sql, ' \t\n\r\0\x0B(' );

		if ( 0 === strpos( $sql, '/*' ) ) {
			// Strip out leading comments such as `/*NO_SELECT_FOUND_ROWS*/` before calculating the query type
			$sql = preg_replace( '|^/\*[^\*/]+\*/|', '', $sql );
		}

		$words = preg_split( '/\b/', trim( $sql ), 2, PREG_SPLIT_NO_EMPTY );
		$type = 'Unknown';
		if ( isset( $words[0] ) ) {
			$type = strtoupper( $words[0] );
		}

		return $type;
	}

	/**
	 * Consume/create the next number and return it
	 * @param  integer $order_id WooCommerce Order ID
	 * @param  string  $date     Local date, formatted as Y-m-d H:i:s
	 * @return int               Number that was consumed/created
	 */
	public function increment( $order_id = 0, $date = null ) {
		global $wpdb;
		if ( empty( $date ) ) {
			$date = get_date_from_gmt( date( 'Y-m-d H:i:s' ) );
		}

		do_action( 'wpo_wcpdf_before_sequential_number_increment', $this, $order_id, $date );

		$data = array(
			'order_id'	=> (int) $order_id,
			'date'		=> $date,
		);

		if ( $this->method == 'auto_increment' ) {
			$wpdb->insert( $this->table_name, $data );
			$number = $wpdb->insert_id;
		} elseif ( $this->method == 'calculate' ) {
			$number = $data['calculated_number'] = $this->get_next();
			$wpdb->insert( $this->table_name, $data );
		}
		
		// return generated number
		return $number;
	}

	/**
	 * Get the number that will be used on the next increment
	 * @return int next number
	 */
	public function get_next() {
		global $wpdb;
		if ( $this->method == 'auto_increment' ) {
			// clear cache first on mysql 8.0+
			if ( $wpdb->get_var( "SHOW VARIABLES LIKE 'information_schema_stats_expiry'" ) ) {
				$wpdb->query( "SET SESSION information_schema_stats_expiry = 0" );
			}
			// get next auto_increment value
			$table_status = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$this->table_name}'");
			$next = $table_status->Auto_increment;
		} elseif ( $this->method == 'calculate' ) {
			$last_row = $wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE id = ( SELECT MAX(id) from {$this->table_name} )" );
			if ( empty( $last_row ) ) {
				$next = 1;
			} elseif ( !empty( $last_row->calculated_number ) ) {
				$next = (int) $last_row->calculated_number + 1;
			} else {
				$next = (int) $last_row->id + 1;
			}
		}
		return $next;
	}

	/**
	 * Set the number that will be used on the next increment
	 */
	public function set_next( $number = 1 ) {
		global $wpdb;

		// delete all rows
		$delete = $wpdb->query("TRUNCATE TABLE {$this->table_name}");

		// set auto_increment
		if ( $number > 1 ) {
			// if AUTO_INCREMENT is not 1, we need to make sure we have a 'highest value' in case of server restarts
			// https://serverfault.com/questions/228690/mysql-auto-increment-fields-resets-by-itself
			$highest_number = (int) $number - 1;
			$wpdb->query( $wpdb->prepare( "ALTER TABLE {$this->table_name} AUTO_INCREMENT=%d;", $highest_number ) );
			$data = array(
				'order_id'	=> 0,
				'date'		=> get_date_from_gmt( date( 'Y-m-d H:i:s' ) ),
			);
			
			if ( $this->method == 'calculate' ) {
				$data['calculated_number'] = $highest_number;
			}

			// after this insert, AUTO_INCREMENT will be equal to $number
			$wpdb->insert( $this->table_name, $data );
		} else {
			// simple scenario, no need to insert any rows
			$wpdb->query( $wpdb->prepare( "ALTER TABLE {$this->table_name} AUTO_INCREMENT=%d;", $number ) );
		}
	}

	public function get_last_date( $format = 'Y-m-d H:i:s' ) {
		global $wpdb;
		$row = $wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE id = ( SELECT MAX(id) from {$this->table_name} )" );
		$date = isset( $row->date ) ? $row->date : 'now';
		$formatted_date = date( $format, strtotime( $date ) );

		return $formatted_date;
	}

	/**
	 * @return bool
	 */
	public function store_name_exists() {
		global $wpdb;

		// check if table exists
		if( $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
			return true;
		} else {
			return false;
		}
	}

}

endif; // class_exists
