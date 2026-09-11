<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers;

use WPO\IPS\EDI\Standards\EN16931\CIUS\France;
use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\TaxTotalHandler as UblTaxTotalHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TaxTotalHandler extends UblTaxTotalHandler {

	/**
	 * Get the grouped order tax data.
	 *
	 * @return array
	 */
	protected function get_grouped_order_tax_data(): array {
		$grouped_tax_data   = parent::get_grouped_order_tax_data();
		$allowed_categories = array_keys( France::get_5305() );

		foreach ( $grouped_tax_data as $item ) {
			$category = strtoupper( trim( (string) ( $item['category'] ?? '' ) ) );

			if ( '' === $category || in_array( $category, $allowed_categories, true ) ) {
				continue;
			}

			wpo_ips_edi_log(
				sprintf(
					'France CIUS UBL: VAT category "%s" is not allowed for order %d.',
					$category,
					$this->document->order->get_id()
				),
				'error'
			);
		}

		return $grouped_tax_data;
	}
}
