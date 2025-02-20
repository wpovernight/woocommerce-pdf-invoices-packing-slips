<?php
/**
 * WordPress FileSystem compatibility class.
 *
 * @since 4.2
 */

namespace WPO\IPS\Compatibility;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( '\\WPO\\IPS\\Compatibility\\FileSystem' ) ) :

class FileSystem {

	protected static $_instance = null;
	public $system_enabled      = 'wp'; // Default to WP Filesystem API
	public $suppress_errors     = false;
	public $wp_filesystem       = null;

	/**
	 * Singleton instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$debug_settings = get_option( 'wpo_wcpdf_settings_debug', array() );
		
		if ( isset( $debug_settings['filesystem'] ) ) {
			$this->system_enabled = 'php';
		} else {
			$this->initialize_wp_filesystem();
		}
		
		$this->suppress_errors = apply_filters( 'wpo_wcpdf_file_system_suppress_errors', true );
	}
	
	/**
	 * Check if WP_Filesystem is enabled.
	 * @return bool
	 */
	public function is_wp_filesystem(): bool {
		return ( 'wp' === $this->system_enabled );
	}
	
	/**
	 * Check if PHP file functions are being used.
	 * @return bool
	 */
	public function is_php_filesystem(): bool {
		return ( 'php' === $this->system_enabled );
	}

	/**
	 * Initialize WP_Filesystem
	 * @return void
	 */
	protected function initialize_wp_filesystem(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		global $wp_filesystem;

		if ( ! WP_Filesystem() || ! $wp_filesystem ) {
			wcpdf_log_error( 'WP_Filesystem initialization failed. Falling back to PHP methods.', 'warning' );
			$this->system_enabled = 'php';
			return;
		}

		$this->wp_filesystem = $wp_filesystem;

		// Ensure the filesystem method is 'direct', otherwise log a warning
		$filesystem_method = get_filesystem_method();
		if ( 'direct' !== $filesystem_method ) {
			wcpdf_log_error( "This plugin only supports the direct filesystem method. Current method: {$filesystem_method}", 'warning' );
			$this->system_enabled = 'php';
		}
	}

	/**
	 * Get file contents
	 * @param string $filename
	 * @return string|bool
	 */
	public function get_contents( string $filename ) {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->get_contents( $filename ) 
			: ( $this->suppress_errors ? @file_get_contents( $filename ) : file_get_contents( $filename ) );
	}

	/**
	 * Check if file is readable
	 * @param string $filename
	 * @return bool
	 */
	public function is_readable( string $filename ): bool {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->is_readable( $filename ) 
			: ( $this->suppress_errors ? @is_readable( $filename ) : is_readable( $filename ) );
	}

	/**
	 * Write file contents
	 * @param string $filename
	 * @param string $contents
	 * @param int|false $mode
	 * @return int|bool
	 */
	public function put_contents( string $filename, string $contents, $mode = false ) {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->put_contents( $filename, $contents, $mode ) 
			: ( $this->suppress_errors ? @file_put_contents( $filename, $contents ) : file_put_contents( $filename, $contents ) );
	}

	/**
	 * Check if file exists
	 * @param string $filename
	 * @return bool
	 */
	public function exists( string $filename ): bool {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->exists( $filename ) 
			: ( $this->suppress_errors ? @file_exists( $filename ) : file_exists( $filename ) );
	}

	/**
	 * Check if directory exists
	 * @param string $filename
	 * @return bool
	 */
	public function is_dir( string $filename ): bool {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->is_dir( $filename ) 
			: ( $this->suppress_errors ? @is_dir( $filename ) : is_dir( $filename ) );
	}

	/**
	 * Create a directory
	 * @param string $path
	 * @return bool
	 */
	public function mkdir( string $path ): bool {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->mkdir( $path ) 
			: ( $this->suppress_errors ? @mkdir( $path ) : mkdir( $path ) );
	}
	
	/**
	 * Check if file is writable
	 * @param string $filename
	 * @return bool
	 */
	public function is_writable( string $filename ): bool {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->is_writable( $filename ) 
			: ( $this->suppress_errors ? @is_writable( $filename ) : is_writable( $filename ) );
	}

	/**
	 * Delete a file
	 * @param string $filename
	 * @return bool
	 */
	public function delete( string $filename ): bool {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->delete( $filename ) 
			: ( $this->suppress_errors ? @unlink( $filename ) : unlink( $filename ) );
	}

	/**
	 * Get directory listing
	 * @param string $path
	 * @return array|bool
	 */
	public function dirlist( string $path ) {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->dirlist( $path ) 
			: ( $this->suppress_errors ? @scandir( $path ) : scandir( $path ) );
	}

	/**
	 * Get file modification time
	 * @param string $filename
	 * @return int|bool
	 */
	public function mtime( string $filename ) {
		return $this->is_wp_filesystem() 
			? $this->wp_filesystem->mtime( $filename ) 
			: ( $this->suppress_errors ? @filemtime( $filename ) : filemtime( $filename ) );
	}
}

endif; // Class exists check
