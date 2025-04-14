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
	
	/**
	 * Filesystem method.
	 * @var string
	 */
	public string $system_enabled = 'wp'; // Default to WP Filesystem API
	
	/**
	 * Suppress errors.
	 * @var bool
	 */
	public bool $suppress_errors = false;
	
	/**
	 * WP_Filesystem instance.
	 * @var \WP_Filesystem_Base|null
	 */
	public ?\WP_Filesystem_Base $wp_filesystem = null;
	
	/**
	 * Singleton instance.
	 * @var self|null
	 */
	protected static ?self $_instance = null;

	/**
	 * Singleton instance.
	 * @return self
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
		$this->suppress_errors                = apply_filters( 'wpo_wcpdf_file_system_suppress_errors', true );
		$debug_settings                       = get_option( 'wpo_wcpdf_settings_debug', array() );
		$debug_settings['file_system_method'] = apply_filters( 'wpo_wcpdf_filesystem_method', $debug_settings['file_system_method'] ?? 'wp' ); // Override the filesystem method via code snippet
		$this->system_enabled                 = ( 'php' === $debug_settings['file_system_method'] ) ? 'php' : 'wp';
		
		if ( 'wp' === $this->system_enabled ) {
			$this->initialize_wp_filesystem();
		} else {
			if ( ! defined( 'FS_CHMOD_FILE' ) ) {
				define( 'FS_CHMOD_FILE', ( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ) );
			}
		}
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
	public function initialize_wp_filesystem(): void {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		global $wp_filesystem;

		if ( ! WP_Filesystem() || ! $wp_filesystem ) {
			wcpdf_log_error( 'WP_Filesystem initialization failed. Falling back to PHP methods.', 'warning' );
			$this->system_enabled = $this->change_setting_value( 'php' );
			return;
		}

		$this->wp_filesystem = $wp_filesystem;

		// Ensure the filesystem method is 'direct', otherwise log a warning
		$filesystem_method = get_filesystem_method();
		if ( 'direct' !== $filesystem_method ) {
			wcpdf_log_error( "This plugin only supports the direct filesystem method. Current method: {$filesystem_method}", 'warning' );
			$this->system_enabled = $this->change_setting_value( 'php' );
		}
	}
	
	/**
	 * Change the filesystem setting value
	 * @param string $default
	 * @return string
	 */
	protected function change_setting_value( string $default = 'wp' ): string {
		$debug_settings               = get_option( 'wpo_wcpdf_settings_debug', array() );
		$debug_settings['filesystem'] = in_array( $default, array( 'wp', 'php' ), true ) ? $default : 'wp';
		
		update_option( 'wpo_wcpdf_settings_debug', $debug_settings );
		
		return $debug_settings['filesystem'];
	}

	/**
	 * Get file contents
	 * @param string $filename
	 * @return string|bool
	 */
	public function get_contents( string $filename ) {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->get_contents( $filename ) :
			( $this->suppress_errors ? @file_get_contents( $filename ) : file_get_contents( $filename ) );
	}

	/**
	 * Check if file is readable
	 * @param string $filename
	 * @return bool
	 */
	public function is_readable( string $filename ): bool {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->is_readable( $filename ) :
			( $this->suppress_errors ? @is_readable( $filename ) : is_readable( $filename ) );
	}

	/**
	 * Write file contents
	 * @param string $filename
	 * @param string $contents
	 * @param int|false $mode
	 * @return int|bool
	 */
	public function put_contents( string $filename, string $contents, $mode = false ) {
		if ( empty( $filename ) || empty( $contents ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->put_contents( $filename, $contents, $mode ) :
			( $this->suppress_errors ? @file_put_contents( $filename, $contents ) : file_put_contents( $filename, $contents ) );
	}

	/**
	 * Check if file exists
	 * @param string $filename
	 * @return bool
	 */
	public function exists( string $filename ): bool {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->exists( $filename ) :
			( $this->suppress_errors ? @file_exists( $filename ) : file_exists( $filename ) );
	}

	/**
	 * Check if directory exists
	 * @param string $filename
	 * @return bool
	 */
	public function is_dir( string $filename ): bool {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->is_dir( $filename ) :
			( $this->suppress_errors ? @is_dir( $filename ) : is_dir( $filename ) );
	}

	/**
	 * Create a directory
	 * @param string $path
	 * @return bool
	 */
	public function mkdir( string $path ): bool {
		if ( empty( $path ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->mkdir( $path ) :
			( $this->suppress_errors ? @mkdir( $path ) : mkdir( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
	}
	
	/**
	 * Check if file is writable
	 * @param string $filename
	 * @return bool
	 */
	public function is_writable( string $filename ): bool {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->is_writable( $filename ) :
			( 'Windows' === PHP_OS_FAMILY ?
				wp_is_writable( $filename ) :
				( $this->suppress_errors ? @is_writable( $filename ) : is_writable( $filename ) ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable
			);
	}

	/**
	 * Delete a file
	 * @param string $filename
	 * @param bool $recursive
	 * @return bool
	 */
	public function delete( string $filename, bool $recursive = false ): bool {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->delete( $filename, $recursive ) :
			( $this->suppress_errors ? @unlink( $filename ) : unlink( $filename ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
	}

	/**
	 * Get directory listing
	 * @param string $path
	 * @return array|bool
	 */
	public function dirlist( string $path ) {
		if ( empty( $path ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->dirlist( $path ) :
			( $this->suppress_errors ? @scandir( $path ) : scandir( $path ) );
	}

	/**
	 * Get file modification time
	 * @param string $filename
	 * @return int|bool
	 */
	public function mtime( string $filename ) {
		if ( empty( $filename ) ) {
			return false;
		}
		return $this->is_wp_filesystem() ?
			$this->wp_filesystem->mtime( $filename ) :
			( $this->suppress_errors ? @filemtime( $filename ) : filemtime( $filename ) );
	}
}

endif; // Class exists check
