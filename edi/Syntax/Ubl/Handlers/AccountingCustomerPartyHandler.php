<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;
use WPO\IPS\EDI\Syntax\Ubl\Interfaces\UblPartyInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingCustomerPartyHandler extends AbstractUblHandler implements UblPartyInterface {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$accountingCustomerParty = array(
			'name'  => 'cac:AccountingCustomerParty',
			'value' => array(
				$this->get_customer_assigned_account_id(),
				$this->get_party(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_accounting_customer_party', $accountingCustomerParty, $data, $options, $this );

		return $data;
	}
	
	/**
	 * Returns the customer assigned account ID for the supplier party.
	 *
	 * @return array
	 */
	public function get_customer_assigned_account_id(): array {
		$customerAssignedAccountId = array(
			'name'  => 'cbc:CustomerAssignedAccountID',
			'value' => '',
		);
		
		return apply_filters( 'wpo_ips_edi_ubl_customer_assigned_account_id', $customerAssignedAccountId, $this );
	}

	/**
	 * Returns the customer party details for the UBL document.
	 *
	 * @return array
	 */
	public function get_party(): array {
		$customerParty = array(
			'name'  => 'cac:Party',
			'value' => array_filter( array(
				$this->get_party_identification(),
				$this->get_party_name(),
				$this->get_party_postal_address(),
				$this->get_party_tax_scheme(),
				$this->get_party_legal_entity(),
				$this->get_party_contact(),
			) ),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party', $customerParty, $this );
	}
	
	/**
	 * Returns the party identification for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_identification(): ?array {
		$vat_number = $this->get_order_customer_vat_number();

		if ( empty( $vat_number ) ) {
			return null;
		}

		$partyIdentification = array(
			'name'       => 'cac:PartyIdentification',
			'value'      => array(
				array(
					'name'       => 'cbc:ID',
					'attributes' => array(
						'schemeID' => '0088', // VAT
					),
					'value'      => $vat_number,
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_identification', $partyIdentification, $this );
	}
	
	/**
	 * Returns the party name for the customer.
	 *
	 * @return array
	 */
	public function get_party_name(): array {
		$customerPartyName = $this->document->order->get_formatted_billing_full_name();
		$billing_company   = $this->document->order->get_billing_company();

		if ( ! empty( $billing_company ) ) {
			// $customerPartyName = "{$billing_company} ({$customerPartyName})";
			// we register customer name separately as Contact too,
			// so we use the company name as the primary name
			$customerPartyName = $billing_company;
		}
		
		$partyName = array(
			'name'  => 'cac:PartyName',
			'value' => array(
				'name'  => 'cbc:Name',
				'value' => wpo_ips_edi_sanitize_string( $customerPartyName ),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_name', $partyName, $this );
	}
	
	/**
	 * Returns the party postal address for the customer.
	 *
	 * @return array
	 */
	public function get_party_postal_address(): array {
		$partyPostalAddress = array(
			'name'  => 'cac:PostalAddress',
			'value' => array(
				array(
					'name'  => 'cbc:StreetName',
					'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_billing_address_1() ),
				),
				array(
					'name'  => 'cbc:CityName',
					'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_billing_city() ),
				),
				array(
					'name'  => 'cbc:PostalZone',
					'value' => $this->document->order->get_billing_postcode(),
				),
				array(
					'name'  => 'cac:AddressLine',
					'value' => array(
						'name'  => 'cbc:Line',
						'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_billing_address_1() . ' ' . $this->document->order->get_billing_address_2() ),
					),
				),
				array(
					'name'  => 'cac:Country',
					'value' => array(
						'name'       => 'cbc:IdentificationCode',
						'value'      => $this->document->order->get_billing_country(),
						'attributes' => array(
							'listID'       => 'ISO3166-1:Alpha2',
							'listAgencyID' => '6',
						),
					),
				),
			),
		);
		
		return apply_filters( 'wpo_ips_edi_ubl_customer_party_postal_address', $partyPostalAddress, $this );
	}
	
	/**
	 * Returns the party tax scheme for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_tax_scheme(): ?array {
		$vat_number = $this->get_order_customer_vat_number();
		$values     = array();

		if ( ! empty( $vat_number ) ) {
			$values[] = array(
				'name'  => 'cbc:CompanyID',
				'value' => $vat_number,
			);
		}

		$values[] = array(
			'name'  => 'cac:TaxScheme',
			'value' => array(
				array(
					'name'       => 'cbc:ID',
					'value'      => 'VAT',
					'attributes' => array(
						'schemeID'       => 'UN/ECE 5153',
						'schemeAgencyID' => '6',
					),
				),
			),
		);

		$partyTaxScheme = array(
			'name'  => 'cac:PartyTaxScheme',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_tax_scheme', $partyTaxScheme, $this );
	}
	
	/**
	 * Returns the party legal entity for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_legal_entity(): ?array {
		$billing_company = $this->document->order->get_billing_company();
		$vat_number      = $this->get_order_customer_vat_number();

		// Only add PartyLegalEntity if there's a billing company or VAT number
		if ( empty( $billing_company ) && empty( $vat_number ) ) {
			return null;
		}

		$elements = array();

		if ( ! empty( $billing_company ) ) {
			$elements[] = array(
				'name'  => 'cbc:RegistrationName',
				'value' => wpo_ips_edi_sanitize_string( $billing_company ),
			);
		}

		if ( ! empty( $vat_number ) ) {
			$elements[] = array(
				'name'       => 'cbc:CompanyID',
				'value'      => $vat_number,
				'attributes' => array(
					'schemeID' => 'VA', // Peppol BIS recommends 'VA' for VAT
				),
			);
		}

		$partyLegalEntity = array(
			'name'  => 'cac:PartyLegalEntity',
			'value' => $elements,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_legal_entity', $partyLegalEntity, $this );
	}
	
	/**
	 * Returns the party contact information for the customer.
	 *
	 * @return array
	 */
	public function get_party_contact(): array {
		$partyContact = array(
			'name'  => 'cac:Contact',
			'value' => array(
				array(
					'name'  => 'cbc:Name',
					'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_formatted_billing_full_name() ),
				),
				array(
					'name'  => 'cbc:ElectronicMail',
					'value' => sanitize_email( $this->document->order->get_billing_email() ),
				),
			),
		);
		
		return apply_filters( 'wpo_ips_edi_ubl_customer_party_contact', $partyContact, $this );
	}
	
}
