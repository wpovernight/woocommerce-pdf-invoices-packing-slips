<?php

namespace WPO\IPS\Makers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Makers\\EDIMaker' ) ) :

class EDIMaker {

	protected string $tmp_base;

	/**
	 * Write the EDI file to the filesystem
	 *
	 * @param string $filename
	 * @param string $contents
	 * @return string Full path to the written file
	 * @throws \Exception If there was an error writing the file
	 */
	public function write( string $filename, string $contents ): string {
		$full_file_name = $this->get_file_path() . $filename;
		$status         = WPO_WCPDF()->get_instance( 'file_system' )->put_contents( $full_file_name, $contents, FS_CHMOD_FILE );

		if ( false === $status ) {
			throw new \Exception( 'Error writing UBL file' );
		}

		return $full_file_name;
	}

	/**
	 * Set the file path for the EDI files
	 *
	 * @param string $file_path
	 * @return void
	 */
	public function set_file_path( string $file_path ): void {
		$this->tmp_base = $file_path;
	}

	/**
	 * Get the file path for the EDI files
	 *
	 * @return string
	 */
	public function get_file_path(): string {
		if ( ! empty( $this->tmp_base ) ) {
			return $this->tmp_base;
		}

		$this->tmp_base = trailingslashit( WPO_WCPDF()->get_instance( 'main' )->get_tmp_path( 'xml' ) );
		return $this->tmp_base;
	}
	
}

endif; // class_exists
