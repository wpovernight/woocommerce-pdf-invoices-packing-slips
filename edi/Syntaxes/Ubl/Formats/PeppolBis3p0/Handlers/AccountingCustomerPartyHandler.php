<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingCustomerPartyHandler as BaseAccountingCustomerPartyHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingCustomerPartyHandler extends BaseAccountingCustomerPartyHandler {

	/**
	 * Returns the customer party details for the UBL document.
	 *
	 * @return array
	 */
	public function get_party(): array {
		$customer_party = array(
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

		return apply_filters( 'wpo_ips_edi_ubl_customer_party', $customer_party, $this );
	}
	
	/**
	 * Returns the endpoint ID for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_endpoint_id(): ?array {
		$endpoint_id = $this->get_order_customer_vat_number();

		if ( empty( $endpoint_id ) ) {
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $endpoint_id,
			'attributes' => array(
				'schemeID' => '0002', // Scheme ID for VAT number
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_endpoint_id', $endpoint, $this );
	}
	
}
