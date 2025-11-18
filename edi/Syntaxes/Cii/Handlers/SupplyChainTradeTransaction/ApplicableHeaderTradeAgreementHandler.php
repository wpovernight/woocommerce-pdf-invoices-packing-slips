<?php
namespace WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiHandler;

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
		$applicable_header_trade_agreement = array(
			'name'  => 'ram:ApplicableHeaderTradeAgreement',
			'value' => array_filter( array(
				$this->get_seller_trade_party(),
				$this->get_buyer_trade_party(),
				$this->get_contract_referenced_document(),
			) ),
		);

		$data[] = apply_filters( 'wpo_ips_edi_cii_applicable_header_trade_agreement', $applicable_header_trade_agreement, $data, $options, $this );

		return $data;
	}
	
	/**
	 * Get the seller trade party details.
	 *
	 * @return array|null
	 */
	public function get_seller_trade_party(): ?array {
		$name       = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_name' ) );
		$vat_number = $this->get_supplier_identifiers_data( 'vat_number' );
		
		if ( empty( $name ) ) {
			wpo_ips_edi_log( 'CII ApplicableHeaderTradeAgreementHandler: Seller name is empty. Please check your shop settings.', 'error' );
			return null;
		}
		
		if ( empty( $vat_number ) ) {
			wpo_ips_edi_log( 'CII ApplicableHeaderTradeAgreementHandler: VAT number is empty. Please check your shop settings.', 'error' );
			return null;
		}
		
		$postcode       = $this->get_supplier_identifiers_data( 'shop_address_postcode' );
		$address_line_1 = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_line_1' ) );
		$address_city   = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_city' ) );
		$country_code   = wpo_ips_edi_sanitize_string( $this->get_supplier_identifiers_data( 'shop_address_country' ) );

		$seller_trade_party = array(
			'name'  => 'ram:SellerTradeParty',
			'value' => array(
				// Seller Company Name
				array(
					'name'  => 'ram:Name',
					'value' => $name,
				),

				// Legal Organization ID (if available)
				array(
					'name'  => 'ram:SpecifiedLegalOrganization',
					'value' => array(
						array(
							'name'  => 'ram:ID',
							'value' => $vat_number,
						),
					),
				),

				// Postal Address
				array(
					'name'  => 'ram:PostalTradeAddress',
					'value' => array(
						array(
							'name'  => 'ram:PostcodeCode',
							'value' => $postcode,
						),
						array(
							'name'  => 'ram:LineOne',
							'value' => $address_line_1,
						),
						array(
							'name'  => 'ram:CityName',
							'value' => $address_city,
						),
						array(
							'name'  => 'ram:CountryID',
							'value' => $country_code,
						),
					),
				),

				// Tax Registration (VAT ID)
				array(
					'name'  => 'ram:SpecifiedTaxRegistration',
					'value' => array(
						array(
							'name'       => 'ram:ID',
							'value'      => $vat_number,
							'attributes' => array(
								'schemeID' => 'VA',
							),
						),
					),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_cii_seller_trade_party', $seller_trade_party, $this );
	}
	
	/**
	 * Get the buyer trade party details.
	 *
	 * @return array|null
	 */
	public function get_buyer_trade_party(): ?array {
		$order               = \wpo_ips_edi_get_parent_order( $this->document->order );
		$customer_party_name = $order ? $order->get_formatted_billing_full_name() : '';
		$billing_company     = $order ? $order->get_billing_company() : '';
		$vat_number          = $this->get_order_customer_vat_number( $order );

		if ( ! empty( $billing_company ) ) {
			$customer_party_name = $billing_company;
		}

		// Buyer Name
		$buyer_trade_party = array(
			'name'  => 'ram:BuyerTradeParty',
			'value' => array(
				array(
					'name'  => 'ram:Name',
					'value' => wpo_ips_edi_sanitize_string( $customer_party_name ),
				),
			),
		);

		// Legal Organization (if company)
		if ( ! empty( $billing_company ) ) {
			$legal_organization = array();
			
			if ( ! empty( $vat_number ) ) {
				$legal_organization[] = array(
					'name'  => 'ram:ID',
					'value' => $vat_number,
				);
			} else {
				wpo_ips_edi_log(
					sprintf(
						'CII ApplicableHeaderTradeAgreementHandler: VAT number is empty for buyer in order %d.',
						$order->get_id()
					),
					'error'
				);
			}

			$legal_organization[] = array(
				'name'  => 'ram:TradingBusinessName',
				'value' => wpo_ips_edi_sanitize_string( $billing_company ),
			);

			$buyer_trade_party['value'][] = array(
				'name'  => 'ram:SpecifiedLegalOrganization',
				'value' => $legal_organization,
			);
		}

		$postcode       = $order->get_billing_postcode() ?: '';
		$address_line_1 = wpo_ips_edi_sanitize_string( $order->get_billing_address_1() ?: '' );
		$address_line_2 = wpo_ips_edi_sanitize_string( $order->get_billing_address_2() ?: '' );
		$address_city   = wpo_ips_edi_sanitize_string( $order->get_billing_city() ?: '' );
		$country_code   = $order->get_billing_country() ?: '';

		// Postal Address
		$buyer_trade_party['value'][] = array(
			'name'  => 'ram:PostalTradeAddress',
			'value' => array(
				array(
					'name'  => 'ram:PostcodeCode',
					'value' => $postcode,
				),
				array(
					'name'  => 'ram:LineOne',
					'value' => $address_line_1,
				),
				array(
					'name'  => 'ram:LineTwo',
					'value' => $address_line_2,
				),
				array(
					'name'  => 'ram:CityName',
					'value' => $address_city,
				),
				array(
					'name'  => 'ram:CountryID',
					'value' => $country_code,
				),
			),
		);

		// VAT number
		if ( ! empty( $vat_number ) ) {
			$buyer_trade_party['value'][] = array(
				'name'  => 'ram:SpecifiedTaxRegistration',
				'value' => array(
					array(
						'name'       => 'ram:ID',
						'value'      => $vat_number,
						'attributes' => array(
							'schemeID' => 'VA',
						),
					),
				),
			);
		}

		return apply_filters( 'wpo_ips_edi_cii_buyer_trade_party', $buyer_trade_party, $this );
	}
	
	/**
	 * Get the contract referenced document.
	 *
	 * @return array|null
	 */
	public function get_contract_referenced_document(): ?array {
		$order        = $this->document->order;
		$reference_id = apply_filters( 'wpo_ips_edi_cii_contract_reference_id', null, $order, $this );

		if ( empty( $reference_id ) ) {
			return null;
		}

		$contract_document = array(
			'name'  => 'ram:ContractReferencedDocument',
			'value' => array(
				array(
					'name'  => 'ram:IssuerAssignedID',
					'value' => wpo_ips_edi_sanitize_string( $reference_id ),
				),
			),
		);

		return apply_filters( 'wpo_ips_edi_cii_contract_referenced_document', $contract_document, $this );
	}

}
