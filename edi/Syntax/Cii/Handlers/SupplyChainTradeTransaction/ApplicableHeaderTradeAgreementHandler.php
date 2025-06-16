<?php
namespace WPO\IPS\EDI\Syntax\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntax\Cii\Abstracts\AbstractCiiHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ApplicableHeaderTradeAgreementHandler extends AbstractCiiHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$applicableHeaderTradeAgreement = array(
			'name'  => 'ram:ApplicableHeaderTradeAgreement',
			'value' => array(
				$this->getSellerTradeParty(),
				$this->getBuyerTradeParty(),
				$this->getContractReferencedDocument(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_agreement', $applicableHeaderTradeAgreement, $data, $options, $this );

		return $data;
	}
	
	/**
	 * Get the seller trade party details.
	 *
	 * @return array
	 */
	private function getSellerTradeParty(): array {
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

		return apply_filters( 'wpo_ips_edi_cii_seller_trade_party', $sellerTradeParty, $this );
	}
	
	/**
	 * Get the buyer trade party details.
	 *
	 * @return array
	 */
	private function getBuyerTradeParty(): array {
		$order             = $this->document->order;
		$customerPartyName = $customerPartyContactName = $order ? $order->get_formatted_billing_full_name() : '';
		$billingCompany    = $order ? $order->get_billing_company() : '';

		if ( ! empty( $billingCompany ) ) {
			$customerPartyName = $billingCompany;
		}

		$vatNumber = apply_filters( 'wpo_ips_edi_cii_vat_number', wpo_wcpdf_get_order_customer_vat_number( $order ), $order );

		// Buyer Name
		$buyerTradeParty = array(
			'name'  => 'ram:BuyerTradeParty',
			'value' => array(
				array(
					'name'  => 'ram:Name',
					'value' => wpo_ips_edi_sanitize_string( $customerPartyName ),
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
				'value' => wpo_ips_edi_sanitize_string( $billingCompany ),
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
					'value' => wpo_ips_edi_sanitize_string( $order ? $order->get_billing_address_1() : '' ),
				),
				// Optional LineTwo
				array(
					'name'  => 'ram:LineTwo',
					'value' => wpo_ips_edi_sanitize_string( $order ? $order->get_billing_address_2() : '' ),
				),
				array(
					'name'  => 'ram:CityName',
					'value' => wpo_ips_edi_sanitize_string( $order ? $order->get_billing_city() : '' ),
				),
				array(
					'name'  => 'ram:CountryID',
					'value' => $order ? $order->get_billing_country() : '',
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

		return apply_filters( 'wpo_ips_edi_cii_buyer_trade_party', $buyerTradeParty, $this );
	}
	
	/**
	 * Get the contract referenced document.
	 *
	 * @return array
	 */
	private function getContractReferencedDocument(): array {
		$order        = $this->document->order;
		$reference_id = apply_filters( 'wpo_ips_edi_cii_contract_reference_id', null, $order, $this );

		if ( empty( $reference_id ) ) {
			return array(); // Don't output anything if empty
		}

		$contractDocument = array(
			'name'  => 'ram:ContractReferencedDocument',
			'value' => array(
				array(
					'name'  => 'ram:IssuerAssignedID',
					'value' => wpo_ips_edi_sanitize_string( $reference_id ),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_cii_contract_referenced_document', $contractDocument, $this );
	}

}
