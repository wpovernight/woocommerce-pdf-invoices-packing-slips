<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblHandler;
use WPO\IPS\EDI\Syntaxes\Ubl\Interfaces\UblPartyInterface;

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
		$accounting_customer_party = array(
			'name'  => 'cac:AccountingCustomerParty',
			'value' => array(
				$this->get_party(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_accounting_customer_party', $accounting_customer_party, $data, $options, $this );

		return $data;
	}

	/**
	 * Returns the customer party details for the UBL document.
	 *
	 * @return array
	 */
	public function get_party(): array {
		$customer_party = array(
			'name'  => 'cac:Party',
			'value' => array_filter( array(
				$this->get_party_name(),
				$this->get_party_postal_address(),
				$this->get_party_tax_scheme(),
				$this->get_party_legal_entity(),
				$this->get_party_contact(),
			) ),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party', $customer_party, $this );
	}
	
	/**
	 * Returns the party name for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_name(): ?array {
		$customer_party_name = $this->document->order->get_formatted_billing_full_name();
		$billing_company   = $this->document->order->get_billing_company();

		if ( ! empty( $billing_company ) ) {
			// $customer_party_name = "{$billing_company} ({$customer_party_name})";
			// we register customer name separately as Contact too,
			// so we use the company name as the primary name
			$customer_party_name = $billing_company;
		}
		
		$party_name = array(
			'name'  => 'cac:PartyName',
			'value' => array(
				'name'  => 'cbc:Name',
				'value' => wpo_ips_edi_sanitize_string( $customer_party_name ),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_name', $party_name, $this );
	}
	
	/**
	 * Returns the party postal address for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_postal_address(): ?array {
		$address_1   = wpo_ips_edi_sanitize_string( $this->document->order->get_billing_address_1() );
		$address_2   = wpo_ips_edi_sanitize_string( $this->document->order->get_billing_address_2() );
		$city        = wpo_ips_edi_sanitize_string( $this->document->order->get_billing_city() );
		$postcode    = $this->document->order->get_billing_postcode();
		$country     = $this->document->order->get_billing_country();
		$addressLine = trim( "{$address_1} {$address_2}" );

		$postal_address = array(
			'name'  => 'cac:PostalAddress',
			'value' => array(
				array(
					'name'  => 'cbc:StreetName',
					'value' => $address_1,
				),
				array(
					'name'  => 'cbc:CityName',
					'value' => $city,
				),
				array(
					'name'  => 'cbc:PostalZone',
					'value' => $postcode,
				),
				array(
					'name'  => 'cac:AddressLine',
					'value' => array(
						'name'  => 'cbc:Line',
						'value' => $addressLine,
					),
				),
				array(
					'name'  => 'cac:Country',
					'value' => array(
						'name'       => 'cbc:IdentificationCode',
						'value'      => $country,
						'attributes' => array(
							'listID'       => 'ISO3166-1:Alpha2',
							'listAgencyID' => '6',
						),
					),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_postal_address', $postal_address, $this );
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

		$party_tax_scheme = array(
			'name'  => 'cac:PartyTaxScheme',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_tax_scheme', $party_tax_scheme, $this );
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
				'name'  => 'cbc:CompanyID',
				'value' => $vat_number,
			);
		}

		$party_legal_entity = array(
			'name'  => 'cac:PartyLegalEntity',
			'value' => $elements,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_legal_entity', $party_legal_entity, $this );
	}
	
	/**
	 * Returns the party contact information for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_contact(): ?array {
		$name  = wpo_ips_edi_sanitize_string( $this->document->order->get_formatted_billing_full_name() );
		$email = sanitize_email( $this->document->order->get_billing_email() );

		if ( empty( $name ) && empty( $email ) ) {
			return null;
		}

		$values = array();

		if ( ! empty( $name ) ) {
			$values[] = array(
				'name'  => 'cbc:Name',
				'value' => $name,
			);
		}

		if ( ! empty( $email ) ) {
			$values[] = array(
				'name'  => 'cbc:ElectronicMail',
				'value' => $email,
			);
		}

		$party_contact = array(
			'name'  => 'cac:Contact',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_contact', $party_contact, $this );
	}
	
}
