<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingSupplierPartyHandler as BaseAccountingSupplierPartyHandler;
use WPO\IPS\EDI\Standards\EN16931;

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
		$endpoint = $this->get_endpoint();

		if ( ! $endpoint ) {
			wpo_ips_edi_log( 'UBL/Peppol EndpointID: Endpoint ID or scheme is missing for supplier.', 'error' );
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $endpoint['id'],
			'attributes' => array(
				'schemeID' => $endpoint['scheme'],
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
		$identifier = $this->get_legal_identifier();

		if ( ! $identifier ) {
			wpo_ips_edi_log( 'UBL/Peppol PartyIdentification: Identifier or scheme ID is missing for supplier.', 'error' );
			return null;
		}

		$party_id = array(
			'name'  => 'cac:PartyIdentification',
			'value' => array(
				array(
					'name'       => 'cbc:ID',
					'value'      => $identifier['legal_identifier'],
					'attributes' => array(
						'schemeID' => $identifier['legal_identifier_icd'],
					),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_identification', $party_id, $this );
	}
	
	/**
	 * Returns the party legal entity for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_legal_entity(): ?array {
		$company    = $this->get_supplier_identifiers_data( 'shop_name' );
		$identifier = $this->get_legal_identifier();

		if ( empty( $company ) ) {
			wpo_ips_edi_log( 'UBL/Peppol PartyLegalEntity: Company name is missing for supplier.', 'error' );
			return null;
		}

		$values = array(
			array(
				'name'  => 'cbc:RegistrationName',
				'value' => wpo_ips_edi_sanitize_string( $company ),
			),
		);

		// CompanyID is optional here: include when available, but don't fail the invoice.
		if ( ! empty( $identifier['legal_identifier'] ) && ! empty( $identifier['legal_identifier_icd'] ) ) {
			$values[] = array(
				'name'       => 'cbc:CompanyID',
				'value'      => $identifier['legal_identifier'],
				'attributes' => array(
					'schemeID' => $identifier['legal_identifier_icd'],
				),
			);
		} else {
			wpo_ips_edi_log( 'UBL/Peppol PartyLegalEntity: Legal Identifier missing for supplier (CompanyID omitted).', 'warning' );
		}

		$party_legal_entity = array(
			'name'  => 'cac:PartyLegalEntity',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_legal_entity', $party_legal_entity, $this );
	}

	/**
	 * Gets the Peppol Endpoint ID and scheme for the supplier from plugin settings.
	 *
	 * @return array|null Array with 'id' and 'scheme' keys, or null if invalid/missing.
	 */
	private function get_endpoint(): ?array {
		$id     = wpo_ips_edi_get_settings( 'peppol_endpoint_id' );
		$scheme = wpo_ips_edi_get_settings( 'peppol_endpoint_eas' );

		if ( empty( $id ) || empty( $scheme ) ) {
			return null;
		}

		$eas_schemes = EN16931::get_eas();
		if ( ! array_key_exists( $scheme, $eas_schemes ) ) {
			return null;
		}

		return array(
			'id'     => $id,
			'scheme' => $scheme,
		);
	}
	
	/**
	 * Gets the Peppol Legal Identifier and scheme for the supplier from plugin settings.
	 *
	 * @return array|null Array with 'id' and 'scheme' keys, or null if invalid/missing.
	 */
	private function get_legal_identifier(): ?array {
		$legal_identifier     = wpo_ips_edi_get_settings( 'peppol_legal_identifier' );
		$legal_identifier_icd = wpo_ips_edi_get_settings( 'peppol_legal_identifier_icd' );

		if ( empty( $legal_identifier ) || empty( $legal_identifier_icd ) ) {
			return null;
		}

		$icd_schemes = EN16931::get_icd();
		if ( ! array_key_exists( $legal_identifier_icd, $icd_schemes ) ) {
			return null;
		}
		
		$legal_identifier_value = $this->get_supplier_identifiers_data( $legal_identifier );
		
		if (
			'vat_number' === $legal_identifier &&
			! empty( $legal_identifier_value ) &&
			\wpo_ips_edi_vat_number_has_country_prefix( $legal_identifier_value )
		) {
			$legal_identifier_value = substr( $legal_identifier_value, 2 );
		}

		return array(
			'legal_identifier'     => $legal_identifier_value,
			'legal_identifier_icd' => $legal_identifier_icd,
		);
	}

}
