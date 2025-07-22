<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;
use WPO\IPS\EDI\Syntaxes\Ubl\Interfaces\UblPartyInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingSupplierPartyHandler extends AbstractUblHandler implements UblPartyInterface {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$accounting_supplier_party = array(
			'name'  => 'cac:AccountingSupplierParty',
			'value' => array(
				$this->get_party(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_accounting_supplier_party', $accounting_supplier_party, $data, $options, $this );

		return $data;
	}
	
	/**
	 * Returns the supplier party details for the UBL document.
	 *
	 * @return array
	 */
	public function get_party(): array {
		$supplier_party = array(
			'name'  => 'cac:Party',
			'value' => array_filter( array(
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
	 * Returns the party name for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_name(): ?array {
		$company = $this->get_supplier_identifiers_data( 'shop_name' );
		
		if ( empty( $company ) ) {
			wpo_ips_edi_log( 'UBL PartyName: Supplier company name is missing.', 'error' );
			return null;
		}
		
		$party_name = array(
			'name'  => 'cac:PartyName',
			'value' => array(
				'name'  => 'cbc:Name',
				'value' => wpo_ips_edi_sanitize_string( $company ),
			),
		);
		
		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_name', $party_name, $this );
	}
	
	/**
	 * Returns the party postal address for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_postal_address(): ?array {
		$address_line   = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_line_1' ) );
		$city_name      = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_city' ) );
		$postal_zone    = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_postcode' ) );
		$country_code   = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_country' ) );

		$postal_address = array(
			'name'  => 'cac:PostalAddress',
			'value' => array(
				array(
					'name'  => 'cbc:StreetName',
					'value' => $address_line,
				),
				array(
					'name'  => 'cbc:CityName',
					'value' => $city_name,
				),
				array(
					'name'  => 'cbc:PostalZone',
					'value' => $postal_zone,
				),
				array(
					'name'  => 'cac:AddressLine',
					'value' => array(
						'name'  => 'cbc:Line',
						'value' => $address_line,
					),
				),
				array(
					'name'  => 'cac:Country',
					'value' => array(
						'name'  => 'cbc:IdentificationCode',
						'value' => $country_code,
					),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_postal_address', $postal_address, $this );
	}
	
	/**
	 * Returns the party tax scheme for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_tax_scheme(): ?array {
		$vat_number = $this->get_supplier_identifiers_data( 'vat_number' );
		$values     = array();

		if ( ! empty( $vat_number ) ) {
			if ( ! wpo_ips_edi_vat_number_has_country_prefix( $vat_number ) ) {
				wpo_ips_edi_log( 'UBL PartyTaxScheme: VAT number does not have a country prefix for supplier.', 'error' );
			}
			
			$values[] = array(
				'name'  => 'cbc:CompanyID',
				'value' => $vat_number,
			);
		} else {
			wpo_ips_edi_log( 'UBL PartyTaxScheme: Supplier VAT number is missing.', 'error' );
			return null;
		}

		$values[] = array(
			'name'  => 'cac:TaxScheme',
			'value' => array(
				array(
					'name'  => 'cbc:ID',
					'value' => 'VAT',
				),
			),
		);

		$party_tax_scheme = array(
			'name'  => 'cac:PartyTaxScheme',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_tax_scheme', $party_tax_scheme, $this );
	}
	
	/**
	 * Returns the party legal entity for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_legal_entity(): ?array {
		$company    = $this->get_supplier_identifiers_data( 'shop_name' );
		$coc_number = $this->get_supplier_identifiers_data( 'coc_number' );
		
		if ( empty( $company ) && empty( $coc_number ) ) {
			wpo_ips_edi_log( 'UBL PartyLegalEntity: Both company name and CoC number are missing for supplier.', 'error' );
			return null;
		}

		$party_legal_entity = array(
			'name'  => 'cac:PartyLegalEntity',
			'value' => array(
				array(
					'name'  => 'cbc:RegistrationName',
					'value' => wpo_ips_edi_sanitize_string( $company ),
				),
				array(
					'name'  => 'cbc:CompanyID',
					'value' => $coc_number,
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_legal_entity', $party_legal_entity, $this );
	}
	
	/**
	 * Returns the party contact information for the supplier.
	 *
	 * @return array|null
	 */
	public function get_party_contact(): ?array {
		$email_address = $this->get_supplier_identifiers_data( 'shop_email_address' );

		if ( empty( $email_address ) ) {
			wpo_ips_edi_log( 'UBL PartyContact: Supplier email address is missing.', 'error' );
			return null;
		}

		$party_contact = array(
			'name'  => 'cac:Contact',
			'value' => array(
				array(
					'name'  => 'cbc:ElectronicMail',
					'value' => $email_address,
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_supplier_party_contact', $party_contact, $this );
	}
	
}
