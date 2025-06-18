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
		$order       = $this->document->order;
		$user_id     = $order->get_customer_id();
		$endpoint_id = $order->get_meta( '_peppol_endpoint_id' );
		$scheme_id   = $order->get_meta( '_peppol_eas' );

		// Fallback to user meta if empty
		if ( empty( $endpoint_id ) && $user_id ) {
			$endpoint_id = get_user_meta( $user_id, 'peppol_endpoint_id', true );
		}
		if ( empty( $scheme_id ) && $user_id ) {
			$scheme_id = get_user_meta( $user_id, 'peppol_eas', true );
		}

		if ( empty( $endpoint_id ) || empty( $scheme_id ) ) {
			return null;
		}

		$valid_schemes = array_keys( EN16931::get_electronic_address_schemes() );
		if ( ! in_array( $scheme_id, $valid_schemes, true ) ) {
			return null;
		}

		$endpoint = array(
			'name'       => 'cbc:EndpointID',
			'value'      => $endpoint_id,
			'attributes' => array(
				'schemeID' => $scheme_id,
			),
		);

		return apply_filters( 'wpo_ips_edi_ubl_customer_party_endpoint_id', $endpoint, $this );
	}
	
}
