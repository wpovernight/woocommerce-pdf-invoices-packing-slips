<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingCustomerPartyHandler as BaseAccountingCustomerPartyHandler;
use WPO\IPS\EDI\EN16931;

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
		$identifier = $this->get_peppol_identifier();

		if ( ! $identifier ) {
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $identifier['id'],
			'attributes' => array(
				'schemeID' => $identifier['scheme'],
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_endpoint_id', $endpoint, $this );
	}
	
	/**
	 * Returns the PartyIdentification element for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_identification(): ?array {
		$identifier = $this->get_peppol_identifier();

		if ( ! $identifier ) {
			return null;
		}

		$party_id = array(
			'name'     => 'cac:PartyIdentification',
			'children' => array(
				array(
					'name'       => 'cbc:ID',
					'value'      => $identifier['id'],
					'attributes' => array(
						'schemeID' => $identifier['scheme'],
					),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_identification', $party_id, $this );
	}
	
	/**
	 * Gets the Peppol identifier and scheme ID for the order's customer.
	 *
	 * @return array|null Array with 'id' and 'scheme' keys, or null if invalid/missing.
	 */
	private function get_peppol_identifier(): ?array {
		$order   = $this->document->order;
		$user_id = $order->get_customer_id();

		$id     = $order->get_meta( '_peppol_endpoint_id' );
		$scheme = $order->get_meta( '_peppol_eas' );

		if ( empty( $id ) && $user_id ) {
			$id = get_user_meta( $user_id, 'peppol_endpoint_id', true );
		}
		if ( empty( $scheme ) && $user_id ) {
			$scheme = get_user_meta( $user_id, 'peppol_eas', true );
		}

		if ( empty( $id ) || empty( $scheme ) ) {
			return null;
		}

		$valid_schemes = array_keys( EN16931::get_electronic_address_schemes() );
		if ( ! in_array( $scheme, $valid_schemes, true ) ) {
			return null;
		}

		return array(
			'id'     => $id,
			'scheme' => $scheme,
		);
	}
	
}
