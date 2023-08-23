<?php

namespace WPO\WC\PDF_Invoices\Makers;

use WPO\WC\UBL\Exceptions\FileWriteException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Makers\\UBL_Maker' ) ) :

class UBL_Maker {
	
	protected $tmp_base;

	public function write( $filename, $contents ) {
		$full_file_name = $this->get_file_path() . $filename;
		$status         = file_put_contents( $full_file_name, $contents );

		if ( false === $status ) {
			throw new FileWriteException( 'Error writing UBL file' );
		}

		return $full_file_name;
	}

	public function set_file_path( $file_path ) {
		$this->tmp_base = $file_path;
	}

	public function get_file_path() {
		if ( ! empty( $this->tmp_base ) ) {
			return $this->tmp_base;
		}

		$this->tmp_base = trailingslashit( WPO_WCPDF()->main->get_tmp_path( 'ubl' ) );
		return $this->tmp_base;
	}
}

endif; // class_exists
