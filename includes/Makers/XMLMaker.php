<?php

namespace WPO\IPS\Makers;

use WPO\IPS\Vendor\Sabre\Xml\Service;
use WPO\IPS\UBL\Documents\Document;
use WPO\IPS\UBL\Exceptions\FileWriteException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\IPS\\Makers\\XMLMaker' ) ) :

class XMLMaker {

	/**
	 * Temporary base path for XML files
	 *
	 * @var string
	 */
	protected $tmp_base;
	
	/**
	 * Service
	 *
	 * @var Service
	 */
	private $service;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->service = new Service();
	}

	/**
	 * Write XML file
	 *
	 * @param string $filename
	 * @param string $contents
	 *
	 * @return string
	 * @throws FileWriteException
	 */
	public function write( string $filename, string $contents ): string {
		$full_file_name = $this->get_file_path() . $filename;
		$status         = file_put_contents( $full_file_name, $contents );

		if ( false === $status ) {
			throw new FileWriteException( 'Error writing XML file' );
		}

		return $full_file_name;
	}
	
	/**
	 * Build XML
	 *
	 * @param Document $xml_document
	 *
	 * @return mixed
	 */
	public function build( Document $xml_document ) {
		// Sabre wants namespaces in value/key format, so we need to flip it
		$namespaces                  = array_flip( $xml_document->get_namespaces() );
		$this->service->namespaceMap = $namespaces;

		return $this->service->write( 'Invoice', $xml_document->get_data() );
	}

	/**
	 * Set file path
	 *
	 * @param string $file_path
	 * 
	 * @return void
	 */
	public function set_file_path( string $file_path ): void {
		$this->tmp_base = $file_path;
	}

	/**
	 * Get file path
	 *
	 * @return string
	 */
	public function get_file_path(): string {
		if ( ! empty( $this->tmp_base ) ) {
			return $this->tmp_base;
		}

		$this->tmp_base = trailingslashit( WPO_WCPDF()->main->get_tmp_path( 'xml' ) );
		return $this->tmp_base;
	}
	
}

endif; // class_exists
