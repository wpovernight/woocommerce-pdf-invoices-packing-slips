<?php
namespace WPO\WC\PDF_Invoices\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Sequential_Number_Store' ) ) :

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
		if( ! $this->store_name_exists() ) {
			$this->is_new = true;
		} else {
			// check calculated_number column if using 'calculate' method
			if ( $this->method == 'calculate' ) {
				$column_exists = $this->wpdb->get_var("SHOW COLUMNS FROM `{$this->table_name}` LIKE 'calculated_number'");
				if ( empty( $column_exists ) ) {
					$this->wpdb->query("ALTER TABLE {$this->table_name} ADD calculated_number int (16)");
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
  PRIMARY KEY  (id)
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
			$date = get_date_from_gmt( date( 'Y-m-d H:i:s' ) );
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
	 * Get the number that will be used on the next increment
	 * @return int next number
	 */
	public function get_next() {
		if ( $this->method == 'auto_increment' ) {
			// clear cache first on mysql 8.0+
			if ( $this->wpdb->get_var( "SHOW VARIABLES LIKE 'information_schema_stats_expiry'" ) ) {
				$this->wpdb->query( "SET SESSION information_schema_stats_expiry = 0" );
			}
			// get next auto_increment value
			$table_status = $this->wpdb->get_row("SHOW TABLE STATUS LIKE '{$this->table_name}'");
			$next         = $table_status->Auto_increment;
		} elseif ( $this->method == 'calculate' ) {
			$last_row = $this->wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE id = ( SELECT MAX(id) from {$this->table_name} )" );
			if ( empty( $last_row ) ) {
				$next = 1;
			} elseif ( ! empty( $last_row->calculated_number ) ) {
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
		// delete all rows
		$delete = $this->wpdb->query("TRUNCATE TABLE {$this->table_name}");

		// set auto_increment
		if ( $number > 1 ) {
			// if AUTO_INCREMENT is not 1, we need to make sure we have a 'highest value' in case of server restarts
			// https://serverfault.com/questions/228690/mysql-auto-increment-fields-resets-by-itself
			$highest_number = (int) $number - 1;
			$this->wpdb->query( $this->wpdb->prepare( "ALTER TABLE {$this->table_name} AUTO_INCREMENT=%d;", $highest_number ) );
			$data = array(
				'order_id' => 0,
				'date'     => get_date_from_gmt( date( 'Y-m-d H:i:s' ) ),
			);
			
			if ( $this->method == 'calculate' ) {
				$data['calculated_number'] = $highest_number;
			}

			// after this insert, AUTO_INCREMENT will be equal to $number
			$this->wpdb->insert( $this->table_name, $data );
		} else {
			// simple scenario, no need to insert any rows
			$this->wpdb->query( $this->wpdb->prepare( "ALTER TABLE {$this->table_name} AUTO_INCREMENT=%d;", $number ) );
		}
	}

	public function get_last_date( $format = 'Y-m-d H:i:s' ) {
		$row  = $this->wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE id = ( SELECT MAX(id) from {$this->table_name} )" );
		$date = isset( $row->date ) ? $row->date : 'now';
		$formatted_date = date( $format, strtotime( $date ) );

		return $formatted_date;
	}

	/**
	 * @return bool
	 */
	public function store_name_exists() {
		// check if table exists
		if ( $this->wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name ) {
			return true;
		} else {
			return false;
		}
	}

}

endif; // class_exists
