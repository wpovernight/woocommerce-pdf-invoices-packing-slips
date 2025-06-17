<?php

namespace WPO\IPS\EDI\Syntax\Ubl\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface UblPartyInterface {

	/**
	 * Returns the party name.
	 * {cac:PartyName}
	 *
	 * @return array
	 */
	public function get_party_name(): array;
	
	/**
	 * Returns the party postal address.
	 * {cac:PostalAddress}
	 *
	 * @return array
	 */
	public function get_party_postal_address(): array;
	
	/**
	 * Returns the party tax scheme.
	 * {cac:PartyTaxScheme}
	 *
	 * @return array
	 */
	public function get_party_tax_scheme(): array;
	
	/**
	 * Returns the party contact information.
	 * {cac:Contact}
	 *
	 * @return array
	 */
	public function get_party_contact(): array;

}
