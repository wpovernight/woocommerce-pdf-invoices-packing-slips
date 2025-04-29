<?php
namespace WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeAgreement;

use WPO\IPS\EInvoice\Abstracts\AbstractHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BuyerTradePartyHandler extends AbstractHandler {

	public function handle( $data, $options = array() ) {
		$order = $this->document->order;

		$customerPartyName = $customerPartyContactName = $order ? $order->get_formatted_billing_full_name() : '';
		$billingCompany    = $order ? $order->get_billing_company() : '';

		if ( ! empty( $billingCompany ) ) {
			$customerPartyName = $billingCompany;
		}

		$vatNumber = apply_filters( 'wpo_ips_einvoice_cii_vat_number', wpo_wcpdf_get_order_customer_vat_number( $order ), $order );

		// Buyer Name
		$buyerTradeParty = array(
			'name'  => 'ram:BuyerTradeParty',
			'value' => array(
				array(
					'name'  => 'ram:Name',
					'value' => wpo_ips_ubl_sanitize_string( $customerPartyName ),
				),
			),
		);

		// Legal Organization (if company)
		if ( ! empty( $billingCompany ) ) {
			$legalOrganization = array();
			
			if ( ! empty( $vatNumber ) ) {
				$legalOrganization[] = array(
					'name'  => 'ram:ID',
					'value' => $vatNumber,
				);
			}

			$legalOrganization[] = array(
				'name'  => 'ram:TradingBusinessName',
				'value' => wpo_ips_ubl_sanitize_string( $billingCompany ),
			);

			$buyerTradeParty['value'][] = array(
				'name'  => 'ram:SpecifiedLegalOrganization',
				'value' => $legalOrganization,
			);
		}

		// Postal Address
		$buyerTradeParty['value'][] = array(
			'name'  => 'ram:PostalTradeAddress',
			'value' => array(
				array(
					'name'  => 'ram:PostcodeCode',
					'value' => $order ? $order->get_billing_postcode() : '',
				),
				array(
					'name'  => 'ram:LineOne',
					'value' => wpo_ips_ubl_sanitize_string( $order ? $order->get_billing_address_1() : '' ),
				),
				// Optional LineTwo
				array(
					'name'  => 'ram:LineTwo',
					'value' => wpo_ips_ubl_sanitize_string( $order ? $order->get_billing_address_2() : '' ),
				),
				array(
					'name'  => 'ram:CityName',
					'value' => wpo_ips_ubl_sanitize_string( $order ? $order->get_billing_city() : '' ),
				),
				array(
					'name'  => 'ram:CountryID',
					'value' => $order ? $order->get_billing_country() : '',
				),
			),
		);

		// Defined Trade Contact
		$buyerTradeParty['value'][] = array(
			'name'  => 'ram:DefinedTradeContact',
			'value' => array(
				array(
					'name'  => 'ram:PersonName',
					'value' => wpo_ips_ubl_sanitize_string( $customerPartyContactName ),
				),
				array(
					'name'  => 'ram:TelephoneUniversalCommunication',
					'value' => array(
						array(
							'name'  => 'ram:CompleteNumber',
							'value' => $order ? $order->get_billing_phone() : '',
						),
					),
				),
				array(
					'name'  => 'ram:EmailURIUniversalCommunication',
					'value' => array(
						array(
							'name'  => 'ram:URIID',
							'value' => sanitize_email( $order ? $order->get_billing_email() : '' ),
						),
					),
				),
			),
		);

		// VAT number
		if ( ! empty( $vatNumber ) ) {
			$buyerTradeParty['value'][] = array(
				'name'  => 'ram:SpecifiedTaxRegistration',
				'value' => array(
					array(
						'name'       => 'ram:ID',
						'value'      => $vatNumber,
						'attributes' => array(
							'schemeID' => 'VA',
						),
					),
				),
			);
		}

		$data[] = apply_filters( 'wpo_ips_einvoice_cii_handle_BuyerTradeParty', $buyerTradeParty, $data, $options, $this );

		return $data;
	}
	
}
