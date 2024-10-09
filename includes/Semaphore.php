<?php

namespace WPO\IPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Semaphore' ) ) :

class Semaphore {

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
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", $this->option_name );

		if ( 1 === (int) $wpdb->get_var( $sql ) ) {
			$this->log( 'Lock option (' . $this->option_name . ', ' . $wpdb->options . ') already existed in the database', 'debug' );
			return 1;
		}

		$sql = $wpdb->prepare( "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES(%s, '0', 'no');", $this->option_name );

		$rows_affected = $wpdb->query( $sql );

		if ( $rows_affected > 0 ) {
			$this->log( 'Lock option (' . $this->option_name . ', ' . $wpdb->options . ') was created in the database', 'debug' );
		} else {
			$this->log( 'Lock option (' . $this->option_name . ', ' . $wpdb->options . ') failed to be created in the database (could already exist)', 'notice' );
		}

		return ( $rows_affected > 0 ) ? 2 : 0;
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

		global $wpdb;

		$time_now      = time();
		$retries       = $retries > 0 ? $retries : $this->retries;
		$acquire_until = $time_now + $this->locked_for;

		$sql = $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value < %d", $acquire_until, $this->option_name, $time_now );

		if ( 1 === $wpdb->query( $sql ) ) {
			$this->log( 'Lock (' . $this->option_name . ', ' . $wpdb->options . ') acquired', 'info' );
			$this->acquired = true;
			return true;
		}

		// See if the failure was caused by the row not existing (we check this only after failure, because it should only occur once on the site)
		if ( ! $this->ensure_database_initialised() ) {
			return false;
		}

		do {
			// Now that the row has been created, try again
			if ( 1 === $wpdb->query( $sql ) ) {
				$this->log( 'Lock (' . $this->option_name . ', ' . $wpdb->options . ') acquired after initialising the database', 'info' );
				$this->acquired = true;
				return true;
			}
			$retries--;
			if ( $retries >= 0 ) {
				$this->log( 'Lock (' . $this->option_name . ', ' . $wpdb->options . ') not yet acquired; sleeping', 'debug' );
				sleep( 1 );
				// As a second has passed, update the time we are aiming for
				$time_now = time();
				$acquire_until = $time_now + $this->locked_for;
				$sql = $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value < %d", $acquire_until, $this->option_name, $time_now );
			}
		} while ( $retries >= 0 );

		$this->log( 'Lock (' . $this->option_name . ', ' . $wpdb->options . ') could not be acquired (it is locked)', 'info' );

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

		global $wpdb;
		$sql = $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = '0' WHERE option_name = %s", $this->option_name );

		$this->log( 'Lock option (' . $this->option_name . ', ' . $wpdb->options . ') released', 'info' );

		$result = (int) $wpdb->query( $sql ) === 1;

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

		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s", $this->option_name ) );

		$this->log( 'Lock option (' . $this->option_name . ', ' . $wpdb->options . ') was deleted from the database' );
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
		global $wpdb;

		$option_prefix = $legacy ? self::$legacy_option_prefix : self::$option_prefix;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = '0'",
				$wpdb->esc_like( $option_prefix ) . '%'
			)
		);
	}

	/**
	 * Count the number of released locks in the database
	 *
	 * @param bool $legacy - whether to count legacy locks
	 *
	 * @return int - the number of released locks
	 */
	public static function count_released_locks( bool $legacy = false ): int {
		global $wpdb;

		$option_prefix = $legacy ? self::$legacy_option_prefix : self::$option_prefix;

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = '0'",
				$wpdb->esc_like( $option_prefix ) . '%'
			)
		);

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
