<?php
namespace WPO\IPS\Documents;

use WPO\IPS\Helpers\DatabaseHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Documents\\SequentialNumberStore' ) ) :

/**
 * Class handling database interaction for sequential numbers
 */

class SequentialNumberStore {

	/**
	 * Database helper object
	 * @var DatabaseHelper
	 */
	private $db_helper;
	
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

	/**
	 * Constructor
	 * @param string $store_name Name of the number store
	 * @param string $method     Method used to generate numbers, either 'auto_increment' or 'calculate'
	 */
	public function __construct( $store_name, $method = 'auto_increment' ) {
		$this->db_helper  = WPO_WCPDF()->database_helper = WPO_WCPDF()->database_helper ?? DatabaseHelper::instance();
		$this->store_name = $store_name;
		$this->method     = $method;
		$this->table_name = apply_filters( "wpo_wcpdf_number_store_table_name", "{$this->db_helper->wpdb->prefix}wcpdf_{$this->store_name}", $this->store_name, $this->method ); // e.g. wp_wcpdf_invoice_number

		$this->init();
	}

	/**
	 * Initialize the number store
	 * 
	 * @return void
	 */
	public function init(): void {
		$db_helper       = $this->db_helper;
		$wpdb	         = $db_helper->wpdb;
		$table_name      = $this->table_name;
		$table_name_safe = $db_helper->sanitize_identifier( $table_name );
		$table_exists    = $db_helper->table_exists( $table_name );
		
		// check if table exists
		if ( ! $table_exists ) {
			$this->is_new = true;
		} else {
			// Check calculated_number column if using 'calculate' method
			if ( 'calculate' === $this->method ) {
				$column_check_query = $db_helper->prepare_identifier_query(
					"SHOW COLUMNS FROM %i LIKE %s",
					array( $table_name_safe ),
					array( 'calculated_number' )
				);

				$column_exists = $wpdb->get_var( $column_check_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

				if ( empty( $column_exists ) ) {
					$alter_query = $db_helper->prepare_identifier_query(
						"ALTER TABLE %i ADD `calculated_number` INT(16)",
						array( $table_name_safe )
					);

					$wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
				}
			}

			return; // no further business
		}

		// create table (in case of concurrent requests, this does no harm if it already exists)
		$charset_collate = $wpdb->get_charset_collate();
		// dbDelta is a sensitive kid, so we omit indentation
$sql = "CREATE TABLE {$table_name_safe} (
  id int(16) NOT NULL AUTO_INCREMENT,
  order_id int(16),
  date datetime DEFAULT '1000-01-01 00:00:00' NOT NULL,
  calculated_number int (16),
  PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// catch mysql errors
		$db_helper->catch_errors();

		return;
	}
	
	/**
	 * Consume/create the next number and return it
	 * 
	 * @param  int     $order_id WooCommerce Order ID
	 * @param  string|null $date     Local date, formatted as Y-m-d H:i:s
	 * @return int      Number that was consumed/created
	 */
	public function increment( int $order_id = 0, ?string $date = null ): int {
		if ( empty( $date ) ) {
			$date = get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ) );
		}

		do_action( 'wpo_wcpdf_before_sequential_number_increment', $this, $order_id, $date );

		$db_helper = $this->db_helper;
		$wpdb	   = $db_helper->wpdb;
		$data      = array(
			'order_id' => absint( $order_id ),
			'date'     => $date,
		);

		if ( 'auto_increment' === $this->method ) {
			$inserted = $wpdb->insert( $this->table_name, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$number   = $wpdb->insert_id;

			if ( false === $inserted ) {
				$db_helper->log_wpdb_error( __METHOD__ );
			}
		} elseif ( 'calculate' === $this->method ) {
			$number   = $data['calculated_number'] = $this->get_next();
			$inserted = $wpdb->insert( $this->table_name, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			if ( false === $inserted ) {
				$db_helper->log_wpdb_error( __METHOD__ );
			}
		} else {
			$number = 0;
		}

		return (int) $number;
	}
	
	/**
	 * Get the number that will be used on the next increment.
	 *
	 * @return int Next number
	 */
	public function get_next(): int {
		$db_helper       = $this->db_helper;
		$wpdb	         = $db_helper->wpdb;
		$table_name      = $this->table_name;
		$table_name_safe = $db_helper->sanitize_identifier( $table_name );
		$next            = 1;

		if ( 'auto_increment' === $this->method ) {
			// Clear cache on MySQL 8.0+ for accurate Auto_increment
			if ( $wpdb->get_var( "SHOW VARIABLES LIKE 'information_schema_stats_expiry'" ) ) {
				$wpdb->query( "SET SESSION information_schema_stats_expiry = 0" );
			}

			$query = $this->db_helper->wpdb->prepare(
				"SHOW TABLE STATUS LIKE %s",
				$table_name_safe
			);

			$table_status = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

			if ( is_null( $table_status ) || ! isset( $table_status->Auto_increment ) ) {
				$db_helper->log_wpdb_error( __METHOD__ );
				return $next;
			}

			$next = (int) $table_status->Auto_increment;

		} elseif ( 'calculate' === $this->method ) {
			$query = $db_helper->prepare_identifier_query(
				"SELECT * FROM %i WHERE id = ( SELECT MAX(id) FROM %i )",
				array( $table_name_safe, $table_name_safe )
			);

			$last_row = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
			
			if ( is_null( $last_row ) ) {
				$db_helper->log_wpdb_error( __METHOD__ );
				return $next;
			}

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
		$db_helper       = $this->db_helper;
		$wpdb	         = $db_helper->wpdb;
		$table_name      = $this->table_name;
		$table_name_safe = $db_helper->sanitize_identifier( $table_name );

		// Delete all rows
		$truncate_query = $db_helper->prepare_identifier_query(
			"TRUNCATE TABLE %i",
			array( $table_name_safe )
		);

		$wpdb->query( $truncate_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

		// Set AUTO_INCREMENT
		if ( $number > 1 ) {
			// if AUTO_INCREMENT is not 1, we need to make sure we have a 'highest value' in case of server restarts
			// https://serverfault.com/questions/228690/mysql-auto-increment-fields-resets-by-itself
			$highest_number = $number - 1;

			$alter_query = $db_helper->prepare_identifier_query(
				"ALTER TABLE %i AUTO_INCREMENT = %d",
				array( $table_name_safe ),
				array( $highest_number )
			);

			$wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

			$data = array(
				'order_id' => 0,
				'date'     => get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ) ),
			);

			if ( 'calculate' === $this->method ) {
				$data['calculated_number'] = $highest_number;
			}

			$inserted = $wpdb->insert( $table_name, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			
			if ( false === $inserted ) {
				$db_helper->log_wpdb_error( __METHOD__ );
			}

		} else {
			$alter_query = $db_helper->prepare_identifier_query(
				"ALTER TABLE %i AUTO_INCREMENT = %d",
				array( $table_name_safe ),
				array( $number )
			);

			$wpdb->query( $alter_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
		}
	}
	
	/**
	 * Get the last date in the number store.
	 *
	 * @param string $format
	 * @return string
	 */
	public function get_last_date( string $format = 'Y-m-d H:i:s' ): string {
		$db_helper       = $this->db_helper;
		$wpdb	         = $db_helper->wpdb;
		$table_name      = $this->table_name;
		$table_name_safe = $db_helper->sanitize_identifier( $table_name );

		$query = $db_helper->prepare_identifier_query(
			"SELECT * FROM %i WHERE id = ( SELECT MAX(id) FROM %i )",
			array( $table_name_safe, $table_name_safe )
		);

		$row  = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
		$date = isset( $row->date ) ? $row->date : 'now';

		return gmdate( $format, strtotime( $date ) );
	}

}

endif; // class_exists
