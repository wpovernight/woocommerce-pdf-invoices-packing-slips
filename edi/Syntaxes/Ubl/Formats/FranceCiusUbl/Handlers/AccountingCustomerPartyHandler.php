<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\FranceCiusUbl\Handlers;

use WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AccountingCustomerPartyHandler as UblAccountingCustomerPartyHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingCustomerPartyHandler extends UblAccountingCustomerPartyHandler {

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

		return (array) apply_filters(
			'wpo_ips_edi_ubl_customer_party',
			$customer_party,
			$this
		);
	}

	/**
	 * Returns the French electronic address for the customer.
	 *
	 * @return array|null
	 */
	public function get_party_endpoint_id(): ?array {
		$order = \wpo_ips_edi_get_parent_order( $this->document->order );

		if ( 'FR' !== strtoupper( (string) $order->get_billing_country() ) ) {
			return null;
		}

		$registration_number = \wpo_ips_edi_get_order_customer_registration_number( $order );

		if ( empty( $registration_number ) ) {
			wpo_ips_edi_log(
				sprintf(
					'France CIUS UBL: Customer SIREN is missing for order %d.',
					$order->get_id()
				),
				'error'
			);

			return null;
		}

		return array(
			'name'       => 'cbc:EndpointID',
			'value'      => wpo_ips_edi_sanitize_string( $registration_number ),
			'attributes' => array(
				'schemeID' => '0225',
			),
		);
	}

}
