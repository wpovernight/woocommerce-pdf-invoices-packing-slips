<?php
namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingCustomerPartyHandler as BaseAccountingCustomerPartyHandler;
use WPO\IPS\EDI\Standards\EN16931;

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
		$endpoint = $this->get_endpoint();

		if ( ! $endpoint ) {
			wpo_ips_edi_log( 'UBL/Peppol EndpointID: Endpoint ID or scheme is missing for customer.', 'error' );
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $endpoint['endpoint_id'],
			'attributes' => array(
				'schemeID' => $endpoint['endpoint_eas'],
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_endpoint_id', $endpoint, $this );
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

		if ( empty( $registration_name ) ) {
			wpo_ips_edi_log(
				sprintf(
					'UBL/Peppol PartyLegalEntity: Registration name is missing for customer in order ID %d.',
					$this->document->order->get_id()
				),
				'error'
			);
			return null;
		}

		$party_legal_entity = array(
			'name'  => 'cac:PartyLegalEntity',
			'value' => array(
				'name'  => 'cbc:RegistrationName',
				'value' => wpo_ips_edi_sanitize_string( $registration_name ),
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_legal_entity', $party_legal_entity, $this );
	}

	/**
	 * Gets the Peppol Endpoint ID and scheme for the customer from user meta.
	 *
	 * @return array|null Array with 'endpoint_id' and 'endpoint_eas' keys, or null if invalid/missing.
	 */
	private function get_endpoint(): ?array {
		$order        = \wpo_ips_edi_get_parent_order( $this->document->order );
		$endpoint_id  = $order->get_meta( '_peppol_endpoint_id' );
		$endpoint_eas = $order->get_meta( '_peppol_endpoint_eas' );
		$customer_id  = is_callable( array( $order, 'get_customer_id' ) )
			? $order->get_customer_id()
			: 0;

		if ( empty( $endpoint_id ) && $customer_id > 0 ) {
			$endpoint_id = get_user_meta( $customer_id, 'peppol_endpoint_id', true );
		}
		if ( empty( $endpoint_eas ) && $customer_id > 0 ) {
			$endpoint_eas = get_user_meta( $customer_id, 'peppol_endpoint_eas', true );
		}

		if ( empty( $endpoint_id ) || empty( $endpoint_eas ) ) {
			return null;
		}

		$eas_schemes = EN16931::get_eas();
		if ( ! array_key_exists( $endpoint_eas, $eas_schemes ) ) {
			return null;
		}

		return array(
			'endpoint_id'  => $endpoint_id,
			'endpoint_eas' => $endpoint_eas,
		);
	}

}
