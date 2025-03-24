<?php
namespace WPO\IPS\Helpers;

defined( 'ABSPATH' ) || exit;

class DatabaseHelper {

	public bool $has_identifier_escape = false;
	public ?object $wpdb               = null;
	protected bool $hide_wpdb_errors   = true;
	protected static ?self $_instance  = null;

	/**
	 * Get the singleton instance.
	 * 
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		
		$this->wpdb                  = $wpdb;
		$this->hide_wpdb_errors      = apply_filters( 'wpo_ips_hide_wpdb_errors', true );
		$this->has_identifier_escape = version_compare( get_bloginfo( 'version' ), '6.2', '>=' );
		
		if ( $this->hide_wpdb_errors ) {
			$this->wpdb->hide_errors();
		}
	}

	/**
	 * Sanitize an identifier (table or column name).
	 * 
	 * @param string $name
	 * @return string
	 */
	public function sanitize_identifier( string $name ): string {
		return preg_replace( '/[^a-zA-Z0-9_]/', '', $name );
	}

	/**
	 * Prepare a query with identifiers and values.
	 * 
	 * @param string $query
	 * @param array $identifiers
	 * @param array $values
	 * @return string|void
	 */
	public function prepare_identifier_query( string $query, array $identifiers = array(), array $values = array() ) {
		if ( $this->has_identifier_escape ) {
			return $this->wpdb->prepare( $query, ...array_merge( $identifiers, $values ) );
		}

		foreach ( $identifiers as &$id ) {
			$id = '' . $this->sanitize_identifier( $id ) . '';
		}

		// Replace %i with sanitized identifiers manually
		$segments = explode( '%i', $query );
		$query    = array_shift( $segments );

		foreach ( $segments as $index => $segment ) {
			$query .= $identifiers[ $index ] . $segment;
		}

		return $this->wpdb->prepare( $query, ...$values );
	}

	/**
	 * Check if a table exists.
	 * 
	 * @param string $table_name
	 * @return bool
	 */
	public function table_exists( string $table_name ): bool {
		$sanitized = $this->sanitize_identifier( $table_name );

		if ( $this->has_identifier_escape ) {
			$sql = $this->wpdb->prepare( "SHOW TABLES LIKE %i", $sanitized );
		} else {
			$sql = $this->wpdb->prepare( "SHOW TABLES LIKE %s", $sanitized );
		}

		return $this->wpdb->get_var( $sql ) === $sanitized;
	}
	
	/**
	 * Get tables matching a pattern.
	 * 
	 * @param string $like_pattern
	 * @return array
	 */
	public function get_tables_like( string $like_pattern ): array {
		$sql = $this->wpdb->prepare( "SHOW TABLES LIKE %s", $like_pattern );
	
		return $this->wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
	}
	
	public function drop_table_if_exists( string $table_name ): bool {
		if ( $this->has_identifier_escape ) {
			$query = $this->wpdb->prepare( "DROP TABLE IF EXISTS %i", $table_name );
		} else {
			$table_safe = $this->sanitize_identifier( $table_name );
			$query      = "DROP TABLE IF EXISTS `{$table_safe}`";
		}
	
		$result = $this->wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
	
		if ( ! $result ) {
			$this->log_wpdb_error( __METHOD__ );
		}
	
		return (bool) $result;
	}	
	
	public function rename_table( string $from, string $to ): bool {
		if ( $this->has_identifier_escape ) {
			$query = $this->wpdb->prepare( "ALTER TABLE %i RENAME TO %i", $from, $to );
		} else {
			$from_safe = $this->sanitize_identifier( $from );
			$to_safe   = $this->sanitize_identifier( $to );
			$query     = "ALTER TABLE `{$from_safe}` RENAME `{$to_safe}`";
		}
	
		$result = $this->wpdb->query( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.*
	
		if ( ! $result ) {
			$this->log_wpdb_error( __METHOD__ );
		}
	
		return (bool) $result;
	}	
	
	public function log_wpdb_error( string $context ): void {
		if ( ! empty( $this->wpdb->last_error ) ) {
			$message = sprintf(
				'Database error in %s: %s',
				$context,
				$this->wpdb->last_error
			);
	
			if ( function_exists( 'wcpdf_log_error' ) ) {
				wcpdf_log_error( $message );
			} else {
				error_log( $message );
			}
		}
	
		if ( ! $this->hide_wpdb_errors ) {
			$this->wpdb->show_errors();
		}
	}	
		
}
