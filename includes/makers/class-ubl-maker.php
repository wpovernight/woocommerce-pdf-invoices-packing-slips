<?php

namespace WPO\WC\PDF_Invoices\Makers;

use \WPO\WC\UBL\Exceptions\FileWriteException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Makers\\UBL_Maker' ) ) :

class UBL_Maker {
	
	protected $tmp_base;

	public function setup() {}

	public function write( $filename, $contents ) {
		$fullFileName = $this->getFilePath() . $filename;
		$status = file_put_contents($fullFileName, $contents);

		if ( $status === false ) {
			throw new FileWriteException( 'Error writing UBL file' );
		}

		return $fullFileName;
	}

	public function setFilePath( $filePath ) {
		$this->tmp_base = $filePath;
	}

	public function getFilePath() {
		if ( ! empty( $this->tmp_base ) ) {
			return $this->tmp_base;
		}

		$this->tmp_base = trailingslashit( WPO_WCPDF()->main->get_tmp_path('ubl') );
		return $this->tmp_base;
	}
}

endif; // class_exists
