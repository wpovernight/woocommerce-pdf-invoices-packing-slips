<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class IssueDateHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$issueDate = [
			'name' => 'cbc:IssueDate',
			'value' => $this->document->order_document->get_date()->date_i18n('Y-m-d'),
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_IssueDate', $issueDate, $data, $options, $this );

		return $data;
	}
}