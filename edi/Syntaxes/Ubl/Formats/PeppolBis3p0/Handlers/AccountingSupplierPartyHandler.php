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
	 * Returns the seller identifier for VAT category O invoices.
	 *
	 * @return array|null
	 */
	public function get_party_identification(): ?array {
		if ( ! $this->has_tax_category( 'O' ) ) {
			return null;
		}

		$supplier     = $this->get_supplier_data();
		$vat_number   = (string) ( $supplier['vat_number'] ?? '' );
		$country_code = (string) ( $supplier['country_code'] ?? '' );

		if ( empty( $vat_number ) ) {
			return null;
		}

		$identifier = $vat_number;
		$scheme     = '';
		$icd_codes  = EN16931::get_icd();
		$candidates = wpo_ips_edi_build_peppol_endpoint_candidates_from_vat( $country_code, $vat_number );

		foreach ( $candidates as $candidate ) {
			$candidate_scheme = (string) ( $candidate['eas'] ?? '' );
			$candidate_value  = (string) ( $candidate['value'] ?? '' );

			if (
				'' === $candidate_scheme ||
				'' === $candidate_value ||
				! array_key_exists( $candidate_scheme, $icd_codes )
			) {
				continue;
			}

			$scheme     = $candidate_scheme;
			$identifier = $candidate_value;
			break;
		}

		$id = array(
			'name'  => 'cbc:ID',
			'value' => wpo_ips_edi_sanitize_string( $identifier ),
		);

		if ( '' !== $scheme ) {
			$id['attributes'] = array(
				'schemeID' => $scheme,
			);
		}

		$party_identification = array(
			'name'  => 'cac:PartyIdentification',
			'value' => array(
				$id,
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_identification', $party_identification, $this );
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

}
