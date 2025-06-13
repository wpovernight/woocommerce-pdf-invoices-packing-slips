<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeAgreement;

use WPO\IPS\EDI\Syntax\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SellerTradePartyHandler extends AbstractCiiHandler {

	public function handle( array $data, array $options = array() ): array {
		$sellerTradeParty = array(
			'name'  => 'ram:SellerTradeParty',
			'value' => array(
				// Seller Company Name
				array(
					'name'  => 'ram:Name',
					'value' => wpo_ips_edi_sanitize_string( $this->get_shop_data( 'name' ) ),
				),

				// Legal Organization ID (if available)
				array(
					'name'  => 'ram:SpecifiedLegalOrganization',
					'value' => array(
						array(
							'name'  => 'ram:ID',
							'value' => $this->get_shop_data( 'coc_number' ),
						),
					),
				),

				// Trade Contact (phone and email)
				array(
					'name'  => 'ram:DefinedTradeContact',
					'value' => array(
						array(
							'name'  => 'ram:TelephoneUniversalCommunication',
							'value' => array(
								array(
									'name'  => 'ram:CompleteNumber',
									'value' => $this->get_shop_data( 'phone_number' ),
								),
							),
						),
						array(
							'name'  => 'ram:EmailURIUniversalCommunication',
							'value' => array(
								array(
									'name'  => 'ram:URIID',
									'value' => get_option( 'woocommerce_email_from_address' ), //TODO: wait Mohamad create the respective function
								),
							),
						),
					),
				),

				// Postal Address
				array(
					'name'  => 'ram:PostalTradeAddress',
					'value' => array(
						array(
							'name'  => 'ram:PostcodeCode',
							'value' => $this->get_shop_data( 'address_postcode' ),
						),
						array(
							'name'  => 'ram:LineOne',
							'value' => wpo_ips_edi_sanitize_string( $this->get_shop_data( 'address_line_1' ) ),
						),
						array(
							'name'  => 'ram:CityName',
							'value' => wpo_ips_edi_sanitize_string( $this->get_shop_data( 'address_city' ) ),
						),
						array(
							'name'  => 'ram:CountryID',
							'value' => wc_format_country_state_string( $this->get_shop_data( 'address_country' ) )['country'],
						),
					),
				),

				// Tax Registration (VAT ID)
				array(
					'name'  => 'ram:SpecifiedTaxRegistration',
					'value' => array(
						array(
							'name'       => 'ram:ID',
							'value'      => $this->get_shop_data( 'vat_number' ),
							'attributes' => array(
								'schemeID' => 'VA',
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_seller_trade_party', $sellerTradeParty, $data, $options, $this );

		return $data;
	}

}
