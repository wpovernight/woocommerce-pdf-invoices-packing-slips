<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IssueDateHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$issue_date = array(
			'name'  => 'cbc:IssueDate',
			'value' => $this->document->order_document->get_date()->date_i18n( 'Y-m-d' ),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_issue_date', $issue_date, $data, $options, $this );

		return $data;
	}

}
