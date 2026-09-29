<?php

namespace WPO\IPS\EDI\Interfaces;

use WPO\IPS\EDI\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface BuilderInterface {

	/**
	 * Build the EDI document.
	 *
	 * @param Document $document The EDI document to build.
	 * @return string The serialized XML string.
	 */
	public function build( Document $document ): string;

}
