<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DueDateHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$due_date = array(
			'name'  => 'cbc:DueDate',
			'value' => $this->get_due_date(),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_due_date', $due_date, $data, $options, $this );
		return $data;
	}

}
