<?php

namespace WPO\WC\PDF_Invoices\Makers\UBL\Handlers\Ubl;

use WPO\WC\PDF_Invoices\Makers\UBL\Handlers\UblHandler;

defined( 'ABSPATH' ) or exit;

class AddressHandler extends UblHandler
{
	public function handle( $data, $options = [] )
	{
		$root = ( isset( $options['root'] ) ? $options['root'] : 'AccountingSupplierParty');

		// AccountingSupplierParty or AccountingCustomerParty
		if ( $root == 'AccountingSupplierParty' ) {
			return $this->returnSupplierParty($data, $options);
		}

		return $this->returnCustomerParty($data, $options);
	}

	public function returnSupplierParty($data, $options = [] )
	{

		$supplierParty = [
			'name' => 'cac:AccountingSupplierParty',
			'value' => [ [
				'name' => 'cbc:CustomerAssignedAccountID',
				'value' => '',
			], [
				'name' => 'cac:Party',
				'value' => $this->returnSupplierPartyDetails(),
			] ],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AccountingSupplierParty', $supplierParty, $data, $options, $this );

		return $data;
	}

	public function returnSupplierPartyDetails()
	{
		$supplierPartyDetails = [ [
			'name' => 'cac:PartyName',
			'value' => [
				'name' => 'cbc:Name',
				'value' => get_option('woocommerce_email_from_name'),
			],
			], [
			'name' => 'cac:PostalAddress',
			'value' => [ [
				'name' => 'cbc:StreetName',
				'value' => get_option('woocommerce_store_address'),
			], [
				'name' => 'cbc:CityName',
				'value' => get_option('woocommerce_store_city'),
			], [
				'name' => 'cbc:PostalZone',
				'value' => get_option('woocommerce_store_postcode'),
			], [
				'name' => 'cac:AddressLine',
				'value' => [
					'name' => 'cbc:Line',
					'value' => get_option('woocommerce_store_address'),
				],
			], [
				'name' => 'cac:Country',
				'value' => [
					'name' => 'cbc:IdentificationCode',
					'value' => get_option('woocommerce_default_country'),
					'attributes' => [
						'listID' => 'ISO3166-1:Alpha2',
						'listAgencyID' => '6',
					],
				],
			] ],
		] ];

		$settings = get_option( 'ubl_wc_general', array() );
		$company = !empty($settings['company_name']) ? $settings['company_name'] : '';
		$vat_number = !empty($settings['vat_number']) ? $settings['vat_number'] : '';
		$coc_number = !empty($settings['coc_number']) ? $settings['coc_number'] : '';

		if (!empty($vat_number)) {
			$supplierPartyDetails[] = [
				'name' => 'cac:PartyTaxScheme',
				'value' => [ [
					'name' => 'cbc:CompanyID',
					'value' => $vat_number,
					// 'attributes' => [
					//     'schemeID' => 'NL-VAT',
					//     'schemeAgencyID' => 'ZZZ',
					// ],
				], [
					'name' => 'cac:TaxScheme',
					'value' => [ [
						'name' => 'cbc:ID',
						'value' => 'VAT',
						'attributes' => [
							'schemeID' => 'UN/ECE 5153',
							'schemeAgencyID' => '6',
						],
					] ],
				] ],
			];
		}

		$supplierPartyDetails[] = [
			'name' => 'cac:Contact',
			'value' => [ [
				'name' => 'cbc:ElectronicMail',
				'value' => get_option( 'woocommerce_email_from_address' ),
			] ],
		];

		if (!empty($company) && !empty($coc_number)) {
			$supplierPartyDetails[] = [
				'name' => 'cac:PartyLegalEntity',
				'value' => [ [
					'name' => 'cbc:RegistrationName',
					'value' => $company,
				], [
					'name' => 'cbc:CompanyID',
					'value' => $coc_number,
					'attributes' => [
						'schemeID' => '0106',
					],
				] ],
			];
		}
		return $supplierPartyDetails;
	}

	public function returnCustomerParty( $data, $options = [] )
	{
		if ( ! ( $vat_number = apply_filters( 'wpo_wc_ubl_vat_number', '', $this->document->order ) ) ) {
			// Try fetching VAT Number from meta
			$vat_meta_keys = [
				'_vat_number',              // WooCommerce EU VAT Number
				'VAT Number',               // WooCommerce EU VAT Compliance
				'vat_number',               // Aelia EU VAT Assistant
				'_billing_vat_number',      // WooCommerce EU VAT Number 2.3.21+
				'_billing_eu_vat_number',   // EU VAT Number for WooCommerce (WP Whale/former Algoritmika)
				'yweu_billing_vat',         // YITH WooCommerce EU VAT
				'billing_vat',              // German Market
				'_billing_vat_id',          // Germanized Pro
				'_shipping_vat_id'          // Germanized Pro (alternative)
			];

			foreach ($vat_meta_keys as $meta_key) {
				if ( $vat_number = $this->document->order->get_meta( $meta_key ) ) {
					break;
				}
			}
		}

		$customerPartyName = $customerPartyContactName = $this->document->order->get_formatted_billing_full_name();
		if ( $billing_company = $this->document->order->get_billing_company() ) {
			// $customerPartyName = "{$billing_company} ({$customerPartyName})";
			// we register customer name separately as Contact too,
			// so we use the comapny name as the primary name
			$customerPartyName = $billing_company;
		}

		$customerParty = [
			'name' => 'cac:AccountingCustomerParty',
			'value' => [ [
				'name' => 'cbc:CustomerAssignedAccountID',
				'value' => '',
			], [
				'name' => 'cac:Party',
				'value' => [ [
					'name' => 'cac:PartyName',
					'value' => [
						'name' => 'cbc:Name',
						'value' => $customerPartyName,
					],
				], [
					'name' => 'cac:PostalAddress',
					'value' => [ [
						'name' => 'cbc:StreetName',
						'value' => $this->document->order->get_billing_address_1(),
					], [
						'name' => 'cbc:CityName',
						'value' => $this->document->order->get_billing_city(),
					], [
						'name' => 'cbc:PostalZone',
						'value' => $this->document->order->get_billing_postcode(),
					], [
						'name' => 'cac:AddressLine',
						'value' => [
							'name' => 'cbc:Line',
							'value' => $this->document->order->get_billing_address_1(),
						],
					], [
						'name' => 'cac:Country',
						'value' => [
							'name' => 'cbc:IdentificationCode',
							'value' => $this->document->order->get_billing_country(),
							'attributes' => [
								'listID' => 'ISO3166-1:Alpha2',
								'listAgencyID' => '6',
							],
						],
					] ],
				], [
					'name' => 'cac:PartyTaxScheme',
					'value' => [ [
						'name' => 'cbc:CompanyID',
						'value' => $vat_number,
						// 'attributes' => [
						//     'schemeID' => 'NL-VAT',
						//     'schemeAgencyID' => 'ZZZ',
						// ],
					], [
						'name' => 'cac:TaxScheme',
						'value' => [ [
							'name' => 'cbc:ID',
							'value' => 'VAT',
							'attributes' => [
								'schemeID' => 'UN/ECE 5153',
								'schemeAgencyID' => '6',
							],
						] ],
					] ],
				], [
					'name' => 'cac:Contact',
					'value' => [ [
						'name' => 'cbc:Name',
						'value' => $customerPartyContactName,
					], [
						'name' => 'cbc:ElectronicMail',
						'value' => $this->document->order->get_billing_email(),
					] ],
				] ],
			] ],
		];

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AccountingCustomerParty', $customerParty, $data, $options, $this );

		return $data;
	}
}