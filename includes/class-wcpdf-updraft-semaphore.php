<?php

namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Updraft_Semaphore_3_0' ) ) :

class Updraft_Semaphore_3_0 {

	// Time after which the lock will expire (in seconds)
	protected $locked_for;

	// Name for the lock in the WP options table
	protected $option_name;

	// Lock status - a boolean
	protected $acquired = false;

	// An array of loggers
	protected $loggers = array();

	// Context for loggers
	protected $context = array();

	/**
	 * Constructor. Instantiating does not lock anything, but sets up the details for future operations.
	 *
	 * @param String  $name		  - a unique (across the WP site) name for the lock. Should be no more than 51 characters in length (because of the use of the WP options table, with some further characters used internally)
	 * @param Integer $locked_for - time (in seconds) after which the lock will expire if not released. This needs to be positive if you don't want bad things to happen.
	 * @param Array	  $loggers	  - an array of loggers
	 * @param Array   $context    - context for loggers
	 */
	public function __construct( $name, $locked_for = 300, $loggers = array(), $context = array() ) {
		$this->option_name = 'wpo_wcpdf_lock/' . $name;
		$this->locked_for  = $locked_for;
		$this->loggers     = $loggers;
		$this->context     = $context;
	}

	/**
	 * Attempt to acquire the lock using MySQL's GET_LOCK. If it was already acquired, then nothing extra will be done (the method will be a no-op).
	 *
	 * @param Integer $retries - how many times to retry (after a 1 second sleep each time)
	 *
	 * @return Boolean - whether the lock was successfully acquired or not
	 */
	public function lock( $retries = 0 ) {
		if ( $this->acquired ) {
			return true;
		}

		if ( $this->get_lock() ) {
			$this->log( 'Lock (' . $this->option_name . ') acquired', 'info' );
			$this->acquired = true;
			return true;
		}

		while ( $retries-- > 0 ) {
			sleep( 1 );
			if ( $this->get_lock() ) {
				$this->log( 'Lock (' . $this->option_name . ') acquired after retry', 'info' );
				$this->acquired = true;
				return true;
			}
		}

		$this->log( 'Lock (' . $this->option_name . ') could not be acquired (it is locked)', 'info' );

		return false;
	}

	/**
	 * Get the lock using MySQL's GET_LOCK.
	 *
	 * @return Boolean - whether the lock was successfully set or not
	 */
	private function get_lock() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT GET_LOCK(%s, %d)", $this->option_name, $this->locked_for );

		return $wpdb->get_var( $sql ) === '1';
	}

	/**
	 * Check if the lock is currently locked
	 *
	 * @return Boolean - whether the lock is currently locked or not
	 */
	public function is_locked() {
		return $this->acquired;
	}

	/**
	 * Release the lock using MySQL's RELEASE_LOCK.
	 *
	 * @return Boolean - if it returns false, then the lock was apparently not locked by us (and the caller will most likely therefore ignore the result, whatever it is).
	 */
	public function release() {
		if ( ! $this->acquired ) {
			return false;
		}

		global $wpdb;

		$sql = $wpdb->prepare( "SELECT RELEASE_LOCK(%s)", $this->option_name );

		$result = $wpdb->get_var( $sql ) === '1';

		if ( $result ) {
			$this->log( 'Lock (' . $this->option_name . ') released', 'info' );
		} else {
			$this->log( 'Lock (' . $this->option_name . ') failed to release', 'error' );
		}

		$this->acquired = false;

		return $result;
	}

	/**
	 * Check if a lock is set in the database without acquiring it.
	 *
	 * @return Boolean - whether the lock is currently set or not
	 */
	public function is_lock_set() {
		global $wpdb;

		$sql         = $wpdb->prepare( "SELECT IS_USED_LOCK(%s)", $this->option_name );
		$lock_status = $wpdb->get_var( $sql );

		return ! is_null( $lock_status );
	}

	/**
	 * Captures and logs any given messages
	 *
	 * @param String $message - the error message
	 * @param String $level	  - the message level (debug, notice, info, warning, error)
	 * @param Array  $context - Optional. Additional information for log handlers.
	 */
	public function log( $message, $level = 'info', $context = array() ) {
		$context = ! empty( $context ) ? $context : $this->context;

		if ( ! empty( $this->loggers ) ) {
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
	 * @param Array $loggers - the loggers for this task
	 */
	public function set_loggers( $loggers ) {
		$this->loggers = array();
		foreach ( $loggers as $logger ) {
			$this->add_logger( $logger );
		}
	}

	/**
	 * Add a logger to loggers list
	 *
	 * @param Callable $logger - a logger (a method with a callable function 'log', taking string parameters $level $message)
	 */
	public function add_logger( $logger ) {
		$this->loggers[] = $logger;
	}

	/**
	 * Return the current list of loggers
	 *
	 * @return Array
	 */
	public function get_loggers() {
		return $this->loggers;
	}

}

endif; // class_exists
