<?php

namespace WPO\IPS;

use WPO\IPS\Helpers\DatabaseHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Semaphore' ) ) :

class Semaphore {
	
	/**
	 * Database helper object
	 * @var DatabaseHelper
	 */
	private $db_helper;

	/**
	 * Prefix for the lock in the WP options table
	 *
	 * @var string
	 */
	protected static $option_prefix = 'wpo_ips_semaphore_lock_';

	/**
	 * Legacy option prefix for the lock in the WP options table
	 *
	 * @var string
	 */
	protected static $legacy_option_prefix = 'updraft_lock_wpo_wcpdf_';

	/**
	 * Hook name suffix for the cleanup of released locks
	 *
	 * @var string
	 */
	protected static $hook_name_suffix = 'cleanup';

	/**
	 * Name for the lock in the WP options table
	 *
	 * @var string
	 */
	protected $option_name;

	/**
	 * Time after which the lock will expire (in seconds)
	 *
	 * @var int
	 */
	protected $locked_for;

	/**
	 * Number of retries to acquire the lock
	 *
	 * @var int
	 */
	protected $retries;

	/**
	 * An array of loggers
	 *
	 * @var array
	 */
	protected $loggers = array();

	/**
	 * Context for loggers
	 *
	 * @var array
	 */
	protected $context = array();

	/**
	 * Lock status - a boolean
	 *
	 * @var boolean
	 */
	protected $acquired = false;

	/**
	 * Constructor. Instantiating does not lock anything, but sets up the details for future operations.
	 *
	 * @param string $name        - a unique (across the WP site) name for the lock. Should be no more than 51 characters in length (because of the use of the WP options table, with some further characters used internally)
	 * @param int    $locked_for  - time (in seconds) after which the lock will expire if not released. This needs to be positive if you don't want bad things to happen.
	 * @param int    $retries     - how many times to retry (after a 1 second sleep each time)
	 * @param array  $loggers     - an array of loggers
	 * @param array  $context     - an array of context for the loggers
	 */
	public function __construct( string $name, int $locked_for = 300, int $retries = 0, array $loggers = array(), array $context = array() ) {
		$this->db_helper   = WPO_WCPDF()->database_helper = WPO_WCPDF()->database_helper ?? DatabaseHelper::instance();
		$this->option_name = self::$option_prefix . $name;
		$this->locked_for  = apply_filters( self::$option_prefix . 'time', $locked_for > 0 ? $locked_for : 300, $this->option_name );
		$this->retries     = apply_filters( self::$option_prefix . 'retries', $retries > 0 ? $retries : 0, $this->option_name );
		$this->loggers     = apply_filters( self::$option_prefix . 'loggers', empty( $loggers ) ? array( wc_get_logger() ) : $loggers, $this->option_name );
		$this->context     = apply_filters( self::$option_prefix . 'context', empty( $context ) ? array( 'source' => 'wpo-ips-semaphore' ) : $context, $this->option_name );
	}

	/**
	 * Internal function to make sure that the lock is set up in the database
	 *
	 * @return int - 0 means 'failed' (which could include that someone else concurrently created it); 1 means 'already existed'; 2 means 'exists, because we created it). The intention is that non-zero results mean that the lock exists.
	 */
	private function ensure_database_initialised(): int {
		$db_helper          = $this->db_helper;
		$wpdb               = $db_helper->wpdb;
		$options_table_safe = $db_helper->sanitize_identifier( $wpdb->options );
		$option_name_safe   = $db_helper->sanitize_identifier( $this->option_name );

		// Check if the lock option already exists
		$select_query = $this->db_helper->prepare_identifier_query(
			"SELECT COUNT(*) FROM %i WHERE option_name = %s",
			array( $options_table_safe ),
			array( $option_name_safe )
		);

		$existing_option = $wpdb->get_var( $select_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

		if ( 1 === (int) $existing_option ) {
			$this->log( "Lock option ({$option_name_safe}, {$options_table_safe}) already existed in the database", 'debug' );
			return 1;
		}

		// Insert the lock option with a default value of 0
		$insert_query = $this->db_helper->prepare_identifier_query(
			"INSERT INTO %i (option_name, option_value, autoload) VALUES (%s, '0', 'no')",
			array( $options_table_safe ),
			array( $option_name_safe )
		);

		$rows_affected = $wpdb->query( $insert_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

		if ( $rows_affected > 0 ) {
			$this->log( "Lock option ({$option_name_safe}, {$options_table_safe}) was created in the database", 'debug' );
			return 2;
		} else {
			$this->db_helper->log_wpdb_error( __METHOD__ );
			$this->log( "Lock option ({$option_name_safe}, {$options_table_safe}) failed to be created in the database", 'notice' );
			return 0;
		}
	}

	/**
	 * Attempt to acquire the lock. If it was already acquired, then nothing extra will be done (the method will be a no-op).
	 *
	 * @param int $retries - how many times to retry (after a 1 second sleep each time)
	 *
	 * @return bool - whether the lock was successfully acquired or not
	 */
	public function lock( int $retries = 0 ): bool {
		if ( $this->acquired ) {
			return true;
		}
	
		$db_helper        = $this->db_helper;
		$wpdb             = $db_helper->wpdb;
		$table_name       = $wpdb->options;
		$table_name_safe  = $db_helper->sanitize_identifier( $table_name );
		$option_name_safe = $db_helper->sanitize_identifier( $this->option_name );
		$time_now         = time();
		$retries          = $retries > 0 ? $retries : $this->retries;
		$acquire_until    = $time_now + $this->locked_for;
	
		// First attempt to acquire the lock
		$query = $db_helper->prepare_identifier_query(
			"UPDATE %i SET option_value = %s WHERE option_name = %s AND option_value < %d",
			array( $table_name_safe ),
			array( $acquire_until, $option_name_safe, $time_now )
		);
	
		$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
	
		if ( 1 === (int) $result ) {
			$this->log( "Lock ({$option_name_safe}, {$table_name_safe}) acquired", 'info' );
			$this->acquired = true;
			return true;
		}
	
		// See if the failure was caused by the row not existing (we check this only after failure, because it should only occur once on the site)
		if ( ! $this->ensure_database_initialised() ) {
			return false;
		}
	
		do {
			$query = $db_helper->prepare_identifier_query(
				"UPDATE %i SET option_value = %s WHERE option_name = %s AND option_value < %d",
				array( $table_name_safe ),
				array( $acquire_until, $option_name_safe, $time_now )
			);
	
			$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
	
			if ( 1 === (int) $result ) {
				$this->log( "Lock ({$option_name_safe}, {$table_name_safe}) acquired after initialisation", 'info' );
				$this->acquired = true;
				return true;
			}
	
			$retries--;
	
			if ( $retries >= 0 ) {
				$this->log( "Lock ({$option_name_safe}, {$table_name_safe}) not yet acquired; sleeping", 'debug' );
				sleep( 1 );
				$time_now      = time();
				$acquire_until = $time_now + $this->locked_for;
			}
		} while ( $retries >= 0 );
	
		$this->log( "Lock ({$option_name_safe}, {$table_name_safe}) could not be acquired (it is locked)", 'info' );
		$db_helper->log_wpdb_error( __METHOD__ );
		
		return false;
	}

	/**
	 * Release the lock
	 *
	 * N.B. We don't attempt to unlock it unless we locked it. i.e. Lost locks are left to expire rather than being forced. (If we want to force them, we'll need to introduce a new parameter).
	 *
	 * @return bool - if it returns false, then the lock was apparently not locked by us (and the caller will most likely therefore ignore the result, whatever it is).
	 */
	public function release(): bool {
		if ( ! $this->acquired ) {
			return false;
		}

		$db_helper          = $this->db_helper;
		$wpdb               = $db_helper->wpdb;
		$options_table_safe = $db_helper->sanitize_identifier( $wpdb->options );
		$option_name_safe   = $db_helper->sanitize_identifier( $this->option_name );

		$this->log( "Lock option ({$option_name_safe}, {$options_table_safe}) released", 'info' );

		$query = $db_helper->prepare_identifier_query(
			"UPDATE %i SET option_value = '0' WHERE option_name = %s",
			array( $options_table_safe ),
			array( $option_name_safe )
		);

		$result = $wpdb->query( $query ) === 1; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

		$this->acquired = false;

		return $result;
	}


	/**
	 * Cleans up the DB of any residual data. This should not be used as part of ordinary unlocking; only as part of deinstalling, or if you otherwise know that the lock will not be used again.
	 * If calling this, it's redundant to first unlock (and a no-op to attempt to do so afterwards).
	 *
	 * @return void
	 */
	public function delete(): void {
		$this->acquired = false;

		$db_helper          = $this->db_helper;
		$wpdb               = $db_helper->wpdb;
		$options_table_safe = $db_helper->sanitize_identifier( $wpdb->options );
		$option_name_safe   = $this->option_name;

		$query = $db_helper->prepare_identifier_query(
			"DELETE FROM %i WHERE option_name = %s",
			array( $options_table_safe ),
			array( $option_name_safe )
		);

		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*

		$this->log( "Lock option ({$option_name_safe}, {$options_table_safe}) was deleted from the database" );
	}

	/**
	 * Captures and logs any given messages
	 *
	 * @param string $message - the error message
	 * @param string $level   - the message level (debug, notice, info, warning, error)
	 * @param array  $context - Optional. Additional information for log handlers.
	 *
	 * @return void
	 */
	public function log( string $message, string $level = 'info', array $context = array() ): void {
		$context      = ! empty( $context ) ? $context : $this->context;
		$logs_enabled = isset( WPO_WCPDF()->settings->debug_settings['semaphore_logs'] );

		if ( ! empty( $this->loggers ) && $logs_enabled ) {
			foreach ( $this->loggers as $logger ) {
				if ( ! empty( $context ) ) {
					$logger->log( $level, $message, $context );
				} else {
					$logger->log( $level, $message );
				}
			}
		}
	}

	/**
	 * Sets the list of loggers for this instance (removing any others).
	 *
	 * @param array $loggers - the loggers for this task
	 *
	 * @return void
	 */
	public function set_loggers( array $loggers = array() ): void {
		$this->loggers = array();
		foreach ( $loggers as $logger ) {
			$this->add_logger( $logger );
		}
	}

	/**
	 * Add a logger to loggers list
	 *
	 * @param object $logger - a logger (a method with a callable function 'log', taking string parameters $level $message)
	 */
	public function add_logger( object $logger ) {
		$this->loggers[] = $logger;
	}

	/**
	 * Return the current list of loggers
	 *
	 * @return array - the list of loggers
	 */
	public function get_loggers(): array {
		return $this->loggers;
	}

	/**
	 * Cleanup released locks from the database
	 *
	 * @param bool $legacy - whether to cleanup legacy locks
	 *
	 * @return void
	 */
	public static function cleanup_released_locks( bool $legacy = false ): void {
		$db_helper          = WPO_WCPDF()->database_helper;
		$wpdb               = $db_helper->wpdb;
		$options_table_safe = $db_helper->sanitize_identifier( $wpdb->options );
		$option_prefix      = $legacy ? self::$legacy_option_prefix : self::$option_prefix;
	
		$query = $db_helper->prepare_identifier_query(
			"DELETE FROM %i WHERE option_name LIKE %s AND option_value = '0'",
			array( $options_table_safe ),
			array( $wpdb->esc_like( $option_prefix ) . '%' )
		);
	
		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
	}

	/**
	 * Count the number of released locks in the database
	 *
	 * @param bool $legacy - whether to count legacy locks
	 *
	 * @return int - the number of released locks
	 */
	public static function count_released_locks( bool $legacy = false ): int {
		$db_helper          = WPO_WCPDF()->database_helper;
		$wpdb               = $db_helper->wpdb;
		$options_table_safe = $db_helper->sanitize_identifier( $wpdb->options );
		$option_prefix      = $legacy ? self::$legacy_option_prefix : self::$option_prefix;
	
		$query = $db_helper->prepare_identifier_query(
			"SELECT COUNT(*) FROM %i WHERE option_name LIKE %s AND option_value = '0'",
			array( $options_table_safe ),
			array( $wpdb->esc_like( $option_prefix ) . '%' )
		);
	
		$count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
	
		return $count;
	}

	/**
	 * Get the hook name for the cleanup of released locks
	 *
	 * @return string - the hook name
	 */
	private static function get_cleanup_hook_name(): string {
		return self::$option_prefix . self::$hook_name_suffix;
	}

	/**
	 * Check if the cleanup of released locks is scheduled
	 *
	 * @return bool - whether the cleanup is scheduled
	 */
	public static function is_cleanup_scheduled(): bool {
		return function_exists( 'as_next_scheduled_action' ) && as_next_scheduled_action( self::get_cleanup_hook_name() );
	}

	/**
	 * Get the next scheduled cleanup of released locks
	 *
	 * @return object|null - the next scheduled cleanup action or null
	 */
	public static function get_cleanup_action(): ?object {
		$action = null;

		if ( self::is_cleanup_scheduled() ) {
			$args = array(
				'hook'    => self::get_cleanup_hook_name(),
				'status'  => \ActionScheduler_Store::STATUS_PENDING,
				'orderby' => 'timestamp',
				'order'   => 'ASC',
				'limit'   => 1,
			);

			$actions = as_get_scheduled_actions( $args );

			if ( ! empty( $actions ) && 1 === count( $actions ) ) {
				$action = reset( $actions );
			}
		}

		return $action;
	}

	/**
	 * Schedule the cleanup of released locks
	 *
	 * @return void
	 */
	public static function schedule_semaphore_cleanup(): void {
		if ( ! self::is_cleanup_scheduled() ) {
			$interval = apply_filters( self::get_cleanup_hook_name() . '_interval', 30 * DAY_IN_SECONDS ); // default: every 30 days
			as_schedule_recurring_action( time(), $interval, self::get_cleanup_hook_name() );
		}
	}

	/**
	 * Initialize the cleanup of released locks
	 *
	 * @return void
	 */
	public static function init_cleanup(): void {
		// Schedule cleanup of released locks
		self::schedule_semaphore_cleanup();

		// Cleanup released locks
		add_action( self::get_cleanup_hook_name(), array( __CLASS__, 'cleanup_released_locks' ) );
	}

}

endif; // class_exists
