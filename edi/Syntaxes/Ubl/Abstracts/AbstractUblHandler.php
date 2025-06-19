<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Abstracts;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class AbstractUblHandler extends AbstractHandler {

	/**
	 * Returns the due date days for the document.
	 *
	 * @return int
	 */
	public function get_due_date_days(): int {
		return is_callable( array( $this->document->order_document, 'get_setting' ) )
			? absint( $this->document->order_document->get_setting( 'due_date_days' ) )
			: 0;
	}

}
