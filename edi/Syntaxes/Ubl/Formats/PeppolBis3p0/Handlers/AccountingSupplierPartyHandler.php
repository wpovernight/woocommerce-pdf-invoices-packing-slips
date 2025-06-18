<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingSupplierPartyHandler as BaseAccountingSupplierPartyHandler;
use WPO\IPS\EDI\EN16931;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingSupplierPartyHandler extends BaseAccountingSupplierPartyHandler {
	
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
				$this->get_party_identification(),
				$this->get_party_name(),
				$this->get_party_postal_address(),
				$this->get_party_tax_scheme(),
				$this->get_party_legal_entity(),
				$this->get_party_contact(),
			) ),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party', $supplier_party, $this );
	}
	
	/**
	 * Returns the endpoint ID for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_endpoint_id(): ?array {
		$edi_settings = wpo_ips_edi_get_settings();
		$endpoint_id  = $edi_settings['peppol_endpoint_id'] ?? null;
		$scheme_id    = $edi_settings['peppol_eas'] ?? null;

		if ( empty( $endpoint_id ) || empty( $scheme_id ) ) {
			return null;
		}
		
		$valid_schemes = array_keys( EN16931::get_electronic_address_schemes() );
		if ( ! in_array( $scheme_id, $valid_schemes, true ) ) {
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $endpoint_id,
			'attributes' => array(
				'schemeID' => $scheme_id,
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_endpoint_id', $endpoint, $this );
	}
	
}
