<?php

declare(strict_types=1);

namespace WPO\IPS\EDI\Syntaxes\Ubl\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface UblPartyInterface {

	/**
     * Returns the party name.
     * {cac:PartyName}
     */
    public function get_party_name(): ?array;
	
	/**
     * Returns the party postal address.
     * {cac:PostalAddress}
     */
    public function get_party_postal_address(): ?array;
	
	/**
     * Returns the party tax scheme.
     * {cac:PartyTaxScheme}
     */
    public function get_party_tax_scheme(): ?array;
	
	/**
     * Returns the party legal entity.
     * {cac:PartyLegalEntity}
     */
    public function get_party_legal_entity(): ?array;
	
	/**
     * Returns the party contact information.
     * {cac:Contact}
     */
    public function get_party_contact(): ?array;

}
