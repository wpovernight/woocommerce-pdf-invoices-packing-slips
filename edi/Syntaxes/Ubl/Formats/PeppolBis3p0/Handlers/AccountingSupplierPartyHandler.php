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
		$identifier = $this->get_peppol_identifier();

		if ( ! $identifier ) {
			wpo_ips_edi_log( 'Peppol identifier or scheme ID is missing or invalid for supplier EndpointID.', 'error' );
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $identifier['id'],
			'attributes' => array(
				'schemeID' => $identifier['scheme'],
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_endpoint_id', $endpoint, $this );
	}

	/**
	 * Returns the PartyIdentification element for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_identification(): ?array {
		$identifier = $this->get_peppol_identifier();

		if ( ! $identifier ) {
			wpo_ips_edi_log( 'Peppol identifier or scheme ID is missing or invalid for supplier PartyIdentification.', 'error' );
			return null;
		}

		$party_id = array(
			'name'  => 'cac:PartyIdentification',
			'value' => array(
				array(
					'name'       => 'cbc:ID',
					'value'      => $identifier['id'],
					'attributes' => array(
						'schemeID' => $identifier['scheme'],
					),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_identification', $party_id, $this );
	}

	/**
	 * Gets the Peppol identifier and scheme ID for the supplier from plugin settings.
	 *
	 * @return array|null Array with 'id' and 'scheme' keys, or null if invalid/missing.
	 */
	private function get_peppol_identifier(): ?array {
		$settings = wpo_ips_edi_get_settings();

		$id     = $settings['peppol_endpoint_id'] ?? null;
		$scheme = $settings['peppol_eas'] ?? null;

		if ( empty( $id ) || empty( $scheme ) ) {
			return null;
		}

		$schemes = EN16931::get_electronic_address_schemes();
		if ( ! array_key_exists( $scheme, $schemes ) ) {
			return null;
		}

		return array(
			'id'     => $id,
			'scheme' => $scheme,
		);
	}

}
