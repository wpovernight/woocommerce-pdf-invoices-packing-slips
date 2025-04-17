<?php
namespace WPO\IPS\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\SequentialNumberStore' ) ) :

/**
 * Class handling database interaction for sequential numbers
 */

class SequentialNumberStore {

	/**
	 * WordPress database object
	 * @var object
	 */
	private $wpdb;
	/**
	 * Name of the number store (used for table_name)
	 * @var string
	 */
	public $store_name;

	/**
	 * Number store method, either 'auto_increment' or 'calculate'
	 * @var string
	 */
	public $method;

	/**
	 * Name of the table that stores the number sequence (including the wp_wcpdf_ table prefix)
	 * @var string
	 */
	public $table_name;

	/**
	 * If table name not found in database, is new table
	 * @var bool
	 */
	public $is_new = false;

	public function __construct( $store_name, $method = 'auto_increment' ) {
		global $wpdb;
		$this->wpdb       = $wpdb;
		$this->store_name = $store_name;
		$this->method     = $method;
		$this->table_name = apply_filters( "wpo_wcpdf_number_store_table_name", "{$this->wpdb->prefix}wcpdf_{$this->store_name}", $this->store_name, $this->method ); // e.g. wp_wcpdf_invoice_number

		$this->init();
	}

	public function init() {
		// check if table exists
		if ( ! $this->store_name_exists() ) {
			$this->is_new = true;
		} else {
			// Check calculated_number column if using 'calculate' method
			if ( 'calculate' === $this->method ) {
				$table_name = $this->table_name;

				// Check if the column exists
				$column_check_query = wpo_wcpdf_prepare_identifier_query(
					"SHOW COLUMNS FROM %i LIKE %s",
					array( $table_name ),
					array( 'calculated_number' )
				);

				$column_exists = $this->wpdb->get_var( $column_check_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

				if ( empty( $column_exists ) ) {
					// Add calculated_number column
					$alter_query = wpo_wcpdf_prepare_identifier_query(
						"ALTER TABLE %i ADD `calculated_number` INT(16)",
						array( $table_name )
					);

					$this->wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
				}
			}

			return; // no further business
		}

		// create table (in case of concurrent requests, this does no harm if it already exists)
		$charset_collate = $this->wpdb->get_charset_collate();
		// dbDelta is a sensitive kid, so we omit indentation
$sql = "CREATE TABLE {$this->table_name} (
  id int(16) NOT NULL AUTO_INCREMENT,
  order_id int(16),
  date datetime DEFAULT '1000-01-01 00:00:00' NOT NULL,
  calculated_number int (16),
  PRIMARY KEY  (id),
  KEY order_id (order_id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// catch mysql errors
		wcpdf_catch_db_object_errors( $this->wpdb );

		return;
	}

	/**
	 * Consume/create the next number and return it
	 * @param  integer $order_id WooCommerce Order ID
	 * @param  string  $date     Local date, formatted as Y-m-d H:i:s
	 * @return int               Number that was consumed/created
	 */
	public function increment( $order_id = 0, $date = null ) {
		if ( empty( $date ) ) {
			$date = get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ) );
		}

		do_action( 'wpo_wcpdf_before_sequential_number_increment', $this, $order_id, $date );

		$data = array(
			'order_id' => absint( $order_id ),
			'date'     => $date,
		);

		if ( $this->method == 'auto_increment' ) {
			$this->wpdb->insert( $this->table_name, $data );
			$number = $this->wpdb->insert_id;
		} elseif ( $this->method == 'calculate' ) {
			$number = $data['calculated_number'] = $this->get_next();
			$this->wpdb->insert( $this->table_name, $data );
		}

		// return generated number
		return $number;
	}
	
	/**
	 * Get the number that will be used on the next increment.
	 *
	 * @return int Next number
	 */
	public function get_next(): int {
		$table_name      = $this->table_name;
		$table_name_safe = wpo_wcpdf_sanitize_identifier( $table_name );
		$next            = 1;

		if ( 'auto_increment' === $this->method ) {
			// Clear cache on MySQL 8.0+ for accurate Auto_increment
			if ( $this->wpdb->get_var( "SHOW VARIABLES LIKE 'information_schema_stats_expiry'" ) ) {
				$this->wpdb->query( "SET SESSION information_schema_stats_expiry = 0" );
			}

			$query = $this->wpdb->prepare(
				"SHOW TABLE STATUS LIKE %s",
				$table_name_safe
			);

			// Get next auto_increment value
			$table_status = $this->wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			$next = isset( $table_status->Auto_increment ) ? $table_status->Auto_increment : $next;

		} elseif ( 'calculate' === $this->method ) {
			$query = wpo_wcpdf_prepare_identifier_query(
				"SELECT * FROM %i WHERE id = ( SELECT MAX(id) FROM %i )",
				array( $table_name, $table_name )
			);

			$last_row = $this->wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			if ( ! empty( $last_row->calculated_number ) ) {
				$next = (int) $last_row->calculated_number + 1;
			} elseif ( ! empty( $last_row->id ) ) {
				$next = (int) $last_row->id + 1;
			}
		}

		return (int) $next;
	}
	
	/**
	 * Set the number that will be used on the next increment.
	 *
	 * @param int $number
	 * @return void
	 */
	public function set_next( int $number = 1 ): void {
		$table_name = $this->table_name;
		$wpdb       = $this->wpdb;

		// Delete all rows
		$truncate_query = wpo_wcpdf_prepare_identifier_query(
			"TRUNCATE TABLE %i",
			array( $table_name )
		);
		$wpdb->query( $truncate_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

		// Set AUTO_INCREMENT
		if ( $number > 1 ) {
			// if AUTO_INCREMENT is not 1, we need to make sure we have a 'highest value' in case of server restarts
			// https://serverfault.com/questions/228690/mysql-auto-increment-fields-resets-by-itself
			$highest_number = (int) $number - 1;

			$alter_query = wpo_wcpdf_prepare_identifier_query(
				"ALTER TABLE %i AUTO_INCREMENT = %d", 
				array( $table_name ), 
				array( $highest_number )
			);

			$wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			$data = array(
				'order_id' => 0,
				'date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ) ),
			);

			if ( 'calculate' === $this->method ) {
				$data['calculated_number'] = $highest_number;
			}

			// After this insert, AUTO_INCREMENT will be equal to $number
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$table_name,
				$data
			);
		} else {
			// Simple scenario, just set the AUTO_INCREMENT
			$alter_query = wpo_wcpdf_prepare_identifier_query(
				"ALTER TABLE %i AUTO_INCREMENT = %d", 
				array( $table_name ), 
				array( $number )
			);

			$wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		}
	}
	
	/**
	 * Get the last date in the number store
	 *
	 * @param string $format
	 * @return string
	 */
	public function get_last_date( string $format = 'Y-m-d H:i:s' ): string {
		$table_name = $this->table_name;

		$query = wpo_wcpdf_prepare_identifier_query(
			"SELECT * FROM %i WHERE id = ( SELECT MAX(id) FROM %i )",
			array( $table_name, $table_name )
		);

		$row  = $this->wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$date = isset( $row->date ) ? $row->date : 'now';

		return gmdate( $format, strtotime( $date ) );
	}
	
	/**
	 * Check if the number store table exists.
	 *
	 * @return bool
	 */
	public function store_name_exists(): bool {
		$table_name      = $this->table_name;
		$table_name_safe = wpo_wcpdf_sanitize_identifier( $table_name );
		
		$query  = $this->wpdb->prepare( "SHOW TABLES LIKE %s", $table_name_safe );

		$result = $this->wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

		return $result === $table_name_safe;
	}

}

endif; // class_exists
