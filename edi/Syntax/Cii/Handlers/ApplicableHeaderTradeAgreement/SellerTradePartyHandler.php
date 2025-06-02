<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeAgreement;

use WPO\IPS\EDI\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SellerTradePartyHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$sellerTradeParty = array(
			'name'  => 'ram:SellerTradeParty',
			'value' => array(
				// Seller Company Name
				array(
					'name'  => 'ram:Name',
					'value' => wpo_ips_edi_sanitize_string(
						! empty( $this->document->order_document )
							? $this->document->order_document->get_shop_name()
							: get_bloginfo( 'name' )
					),
				),

				// Legal Organization ID (if available)
				array(
					'name'  => 'ram:SpecifiedLegalOrganization',
					'value' => array(
						array(
							'name'  => 'ram:ID',
							'value' => ! empty( $this->document->order_document ) 
								? $this->document->order_document->get_shop_coc_number() 
								: '',
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
									'value' => get_option( 'woocommerce_store_phone' ),
								),
							),
						),
						array(
							'name'  => 'ram:EmailURIUniversalCommunication',
							'value' => array(
								array(
									'name'  => 'ram:URIID',
									'value' => get_option( 'woocommerce_email_from_address' ),
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
							'value' => get_option( 'woocommerce_store_postcode' ),
						),
						array(
							'name'  => 'ram:LineOne',
							'value' => wpo_ips_edi_sanitize_string( get_option( 'woocommerce_store_address' ) ),
						),
						array(
							'name'  => 'ram:CityName',
							'value' => wpo_ips_edi_sanitize_string( get_option( 'woocommerce_store_city' ) ),
						),
						array(
							'name'  => 'ram:CountryID',
							'value' => wc_format_country_state_string( get_option( 'woocommerce_default_country', '' ) )['country'],
						),
					),
				),

				// Tax Registration (VAT ID)
				array(
					'name'  => 'ram:SpecifiedTaxRegistration',
					'value' => array(
						array(
							'name'       => 'ram:ID',
							'value'      => ! empty( $this->document->order_document )
								? $this->document->order_document->get_shop_vat_number()
								: '',
							'attributes' => array(
								'schemeID' => 'VA',
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_handle_SellerTradeParty', $sellerTradeParty, $data, $options, $this );

		return $data;
	}

}
