<?php

namespace WPO\IPS\EDI\Abstracts;

use WPO\IPS\EDI\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractBuilder {

	/**
	 * Build the EDI document.
	 *
	 * @param Document $document The EDI document to build.
	 * @return string The serialized XML string.
	 */
	abstract public function build( Document $document ): string;

}
