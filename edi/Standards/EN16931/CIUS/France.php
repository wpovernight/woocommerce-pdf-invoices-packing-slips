<?php

namespace WPO\IPS\EDI\Standards\EN16931\CIUS;

use WPO\IPS\EDI\Standards\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class France extends EN16931 {

	public static string $slug    = 'france-cius';
	public static string $name    = 'France CIUS';
	public static string $version = '1.4'; // https://www.e-invoicing-france.eu/documentation/xp-z12-012/AFNOR-FE-XP-Z12-012-Versions

	/**
	 * Get the French invoicing context (Cadre de Facturation) codes.
	 *
	 * @return array
	 */
	public static function get_cadre_de_facturation(): array {
		$defaults = array(
			'B1' => __( 'Standard invoice for goods (B1)', 'woocommerce-pdf-invoices-packing-slips' ),
			'S1' => __( 'Standard invoice for services (S1)', 'woocommerce-pdf-invoices-packing-slips' ),
			'M1' => __( 'Standard invoice for mixed operations (M1)', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		$extra = (array) apply_filters( 'wpo_ips_edi_fr_cadre_de_facturation_options', array() );

		return $extra + $defaults;
	}

}
