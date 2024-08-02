<?php

namespace WPO\IPS\UBL\Transformers;

use WPO\IPS\UBL\Models\DateTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DateTimeTransformer {

	/**
	 * @return DateTime
	 */
	public function transform( \WC_Abstract_Order $item ) {
		$model           = new DateTime();
		$model->date     = $item->get_date_paid()->date( 'Y-m-d' );
		$model->time     = $item->get_date_paid()->date( 'H:i:s' );
		$model->timezone = $item->get_date_paid()->date( 'e' );

		return $model;
	}

}
