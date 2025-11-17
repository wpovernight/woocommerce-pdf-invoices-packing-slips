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
		$order               = \wpo_ips_edi_get_parent_order( $this->document->order );
		$customer_party_name = $order->get_formatted_billing_full_name();
		$billing_company     = $order->get_billing_company();

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
		$order       = \wpo_ips_edi_get_parent_order( $this->document->order );
		$address_1   = wpo_ips_edi_sanitize_string( $order->get_billing_address_1() );
		$address_2   = wpo_ips_edi_sanitize_string( $order->get_billing_address_2() );
		$city        = wpo_ips_edi_sanitize_string( $order->get_billing_city() );
		$postcode    = wpo_ips_edi_sanitize_string( $order->get_billing_postcode() );
		$country     = $order->get_billing_country();
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
						'name'  => 'cbc:IdentificationCode',
						'value' => $country,
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
		$order      = \wpo_ips_edi_get_parent_order( $this->document->order );
		$vat_number = $this->get_order_customer_vat_number( $order );

		// B2C (no VAT): omit PartyTaxScheme entirely
		if ( empty( $vat_number ) ) {
			return null;
		}

		if ( ! wpo_ips_edi_vat_number_has_country_prefix( $vat_number ) ) {
			wpo_ips_edi_log(
				sprintf(
					'UBL PartyTaxScheme: VAT number does not have a country prefix for customer in order %d.',
					$order->get_id()
				),
				'error'
			);
		}

		$values   = array();
		$values[] = array(
			'name'  => 'cbc:CompanyID',
			'value' => strtoupper( preg_replace( '/\s+/', '', $vat_number ) ),
		);
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

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_tax_scheme', $party_tax_scheme, $this );
	}
	
	/**
	 * Returns the party legal entity for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_legal_entity(): ?array {
		$order             = \wpo_ips_edi_get_parent_order( $this->document->order );
		$billing_company   = $order->get_billing_company();
		$billing_name      = $order->get_formatted_billing_full_name();
		$registration_name = ! empty( $billing_company ) ? $billing_company : $billing_name;
		$vat_number        = $this->get_order_customer_vat_number( $order );

		if ( empty( $registration_name ) ) {
			wpo_ips_edi_log(
				sprintf(
					'UBL PartyLegalEntity: Registration name is missing for customer in order %d.',
					$order->get_id()
				),
				'error'
			);
			return null;
		}

		$values   = array();
		$values[] = array(
			'name'  => 'cbc:RegistrationName',
			'value' => wpo_ips_edi_sanitize_string( $registration_name ),
		);

		// CompanyID is optional; add only when VAT exists
		if ( ! empty( $vat_number ) ) {
			if ( ! wpo_ips_edi_vat_number_has_country_prefix( $vat_number ) ) {
				wpo_ips_edi_log(
					sprintf(
						'UBL PartyLegalEntity: VAT number does not have a country prefix for customer in order %d.',
						$order->get_id()
					),
					'error'
				);
			}
			$values[] = array(
				'name'  => 'cbc:CompanyID',
				'value' => strtoupper( preg_replace( '/\s+/', '', $vat_number ) ),
			);
		}

		$party_legal_entity = array(
			'name'  => 'cac:PartyLegalEntity',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_legal_entity', $party_legal_entity, $this );
	}
	
	/**
	 * Returns the party contact information for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_contact(): ?array {
		$order = \wpo_ips_edi_get_parent_order( $this->document->order );
		$name  = wpo_ips_edi_sanitize_string( $order->get_formatted_billing_full_name() );
		$email = sanitize_email( $order->get_billing_email() );

		if ( empty( $name ) && empty( $email ) ) {
			return null;
		}

		$values = array();

		if ( ! empty( $name ) ) {
			$values[] = array(
				'name'  => 'cbc:Name',
				'value' => $name,
			);
		} else {
			wpo_ips_edi_log(
				sprintf(
					'UBL PartyContact: Customer name is missing or invalid in order %d.',
					$order->get_id()
				),
				'error'
			);
		}

		if ( ! empty( $email ) ) {
			$values[] = array(
				'name'  => 'cbc:ElectronicMail',
				'value' => $email,
			);
		} else {
			wpo_ips_edi_log(
				sprintf(
					'UBL PartyContact: Customer email is missing or invalid in order %d.',
					$order->get_id()
				),
				'error'
			);
		}

		$party_contact = array(
			'name'  => 'cac:Contact',
			'value' => $values,
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_contact', $party_contact, $this );
	}
	
}
