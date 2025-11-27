<?php

namespace WPO\IPS\EDI\Interfaces;

use WPO\IPS\EDI\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface HandlerInterface {

	/**
	 * Constructor.
	 *
	 * @param Document $document The document instance.
	 */
	public function __construct( Document $document );

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array;

}
