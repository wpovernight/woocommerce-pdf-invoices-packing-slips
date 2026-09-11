<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingSupplierPartyHandler as UblAccountingSupplierPartyHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingSupplierPartyHandler extends UblAccountingSupplierPartyHandler {

	/**
	 * Returns the supplier party details for the UBL document.
	 *
	 * @return array
	 */
	public function get_party(): array {
		$supplier_party = array(
			'name'  => 'cac:Party',
			'value' => array_filter( array(
				$this->get_party_endpoint_id(),
				$this->get_party_name(),
				$this->get_party_postal_address(),
				$this->get_party_tax_scheme(),
				$this->get_party_legal_entity(),
				$this->get_party_contact(),
			) ),
		);

		return (array) apply_filters(
			'wpo_ips_edi_ubl_supplier_party',
			$supplier_party,
			$this
		);
	}

	/**
	 * Returns the French electronic address for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_endpoint_id(): ?array {
		$supplier = $this->get_supplier_data();

		$country_code        = strtoupper( (string) ( $supplier['country_code'] ?? '' ) );
		$registration_number = trim( (string) ( $supplier['coc_number'] ?? '' ) );

		if ( 'FR' !== $country_code ) {
			return null;
		}

		if ( empty( $registration_number ) ) {
			wpo_ips_edi_log(
				sprintf(
					'France CIUS UBL: Supplier SIREN is missing for order %d.',
					$this->document->order->get_id()
				),
				'error'
			);

			return null;
		}

		$endpoint_id = array(
			'name'       => 'cbc:EndpointID',
			'value'      => wpo_ips_edi_sanitize_string( $registration_number ),
			'attributes' => array(
				'schemeID' => '0225',
			),
		);

		return apply_filters(
			'wpo_ips_edi_ubl_supplier_party_endpoint_id',
			$endpoint_id,
			$this
		);
	}

}
