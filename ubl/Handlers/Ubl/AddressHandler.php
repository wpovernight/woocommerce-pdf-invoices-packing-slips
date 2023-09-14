<?php

namespace WPO\WC\UBL\Handlers\Ubl;

use WPO\WC\UBL\Handlers\UblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AddressHandler extends UblHandler {
	
	public function handle( $data, $options = array() ) {
		$root = isset( $options['root'] ) ? $options['root'] : 'AccountingSupplierParty';

		// AccountingSupplierParty or AccountingCustomerParty
		if ( 'AccountingSupplierParty' === $root ) {
			return $this->return_supplier_party( $data, $options );
		}

		return $this->return_customer_party( $data, $options );
	}

	public function return_supplier_party( $data, $options = array() ) {

		$supplierParty = array(
			'name'  => 'cac:AccountingSupplierParty',
			'value' => array(
				array(
					'name'  => 'cbc:CustomerAssignedAccountID',
					'value' => '',
				),
				array(
					'name'  => 'cac:Party',
					'value' => $this->return_supplier_party_details(),
				),
			),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AccountingSupplierParty', $supplierParty, $data, $options, $this );

		return $data;
	}

	public function return_supplier_party_details() {
		$company    = ! empty( $this->document->order_document ) ? $this->document->order_document->get_shop_name()       : '';
		$address    = ! empty( $this->document->order_document ) ? $this->document->order_document->get_shop_address()    : get_option( 'woocommerce_store_address' );
		$vat_number = ! empty( $this->document->order_document ) ? $this->document->order_document->get_shop_vat_number() : '';
		$coc_number = ! empty( $this->document->order_document ) ? $this->document->order_document->get_shop_coc_number() : '';
		
		$supplierPartyDetails = array(
			array(
				'name'  => 'cac:PartyName',
				'value' => array(
					'name'  => 'cbc:Name',
					'value' => $company,
				),
			),
			array(
				'name'  => 'cac:PostalAddress',
				'value' => array(
					array(
						'name'  => 'cbc:StreetName',
						'value' => get_option( 'woocommerce_store_address' ),
					),
					array(
						'name'  => 'cbc:CityName',
						'value' => get_option( 'woocommerce_store_city' ),
					),
					array(
						'name'  => 'cbc:PostalZone',
						'value' => get_option( 'woocommerce_store_postcode' ),
					),
					array(
						'name'  => 'cac:AddressLine',
						'value' => array(
							'name'  => 'cbc:Line',
							'value' => $address,
						),
					),
					array(
						'name'  => 'cac:Country',
						'value' => array(
							'name'       => 'cbc:IdentificationCode',
							'value'      => get_option( 'woocommerce_default_country' ),
							'attributes' => array(
								'listID'       => 'ISO3166-1:Alpha2',
								'listAgencyID' => '6',
							),
						),
					),
				),
			),
		);

		if ( ! empty( $vat_number ) ) {
			$supplierPartyDetails[] = array(
				'name'  => 'cac:PartyTaxScheme',
				'value' => array(
					array(
						'name'  => 'cbc:CompanyID',
						'value' => $vat_number,
					),
					array(
						'name'  => 'cac:TaxScheme',
						'value' => array(
							array(
								'name'       => 'cbc:ID',
								'value'      => 'VAT',
								'attributes' => array(
									'schemeID'       => 'UN/ECE 5153',
									'schemeAgencyID' => '6',
								),
							),
						),
					),
				),
			);
		}

		$supplierPartyDetails[] = array(
			'name'  => 'cac:Contact',
			'value' => array(
				array(
					'name'  => 'cbc:ElectronicMail',
					'value' => get_option( 'woocommerce_email_from_address' ),
				),
			),
		);

		if ( ! empty( $company ) && ! empty( $coc_number ) ) {
			$supplierPartyDetails[] = array(
				'name'  => 'cac:PartyLegalEntity',
				'value' => array(
					array(
						'name'  => 'cbc:RegistrationName',
						'value' => $company,
					),
					array(
						'name'       => 'cbc:CompanyID',
						'value'      => $coc_number,
						'attributes' => array(
							'schemeID' => '0106',
						),
					),
				),
			);
		}
		return $supplierPartyDetails;
	}

	public function return_customer_party( $data, $options = array() ) {
		$vat_number = apply_filters( 'wpo_wc_ubl_vat_number', '', $this->document->order );
		
		if ( empty( $vat_number ) ) {
			// Try fetching VAT Number from meta
			$vat_meta_keys = array(
				'_vat_number',              // WooCommerce EU VAT Number
				'VAT Number',               // WooCommerce EU VAT Compliance
				'vat_number',               // Aelia EU VAT Assistant
				'_billing_vat_number',      // WooCommerce EU VAT Number 2.3.21+
				'_billing_eu_vat_number',   // EU VAT Number for WooCommerce (WP Whale/former Algoritmika)
				'yweu_billing_vat',         // YITH WooCommerce EU VAT
				'billing_vat',              // German Market
				'_billing_vat_id',          // Germanized Pro
				'_shipping_vat_id'          // Germanized Pro (alternative)
			);

			foreach ( $vat_meta_keys as $meta_key ) {
				if ( $vat_number = $this->document->order->get_meta( $meta_key ) ) {
					break;
				}
			}
		}

		$customerPartyName = $customerPartyContactName = $this->document->order->get_formatted_billing_full_name();
		$billing_company   = $this->document->order->get_billing_company();
		
		if ( ! empty( $billing_company ) ) {
			// $customerPartyName = "{$billing_company} ({$customerPartyName})";
			// we register customer name separately as Contact too,
			// so we use the comapny name as the primary name
			$customerPartyName = $billing_company;
		}

		$customerParty = array(
			'name'  => 'cac:AccountingCustomerParty',
			'value' => array(
				array(
					'name'  => 'cbc:CustomerAssignedAccountID',
					'value' => '',
				),
				array(
					'name'  => 'cac:Party',
					'value' => array(
						array(
							'name'  => 'cac:PartyName',
							'value' => array(
								'name'  => 'cbc:Name',
								'value' => $customerPartyName,
							),
						), 
						array(
							'name'  => 'cac:PostalAddress',
							'value' => array(
								array(
									'name'  => 'cbc:StreetName',
									'value' => $this->document->order->get_billing_address_1(),
								),
								array(
									'name'  => 'cbc:CityName',
									'value' => $this->document->order->get_billing_city(),
								),
								array(
									'name'  => 'cbc:PostalZone',
									'value' => $this->document->order->get_billing_postcode(),
								),
								array(
									'name'  => 'cac:AddressLine',
									'value' => array(
										'name'  => 'cbc:Line',
										'value' => $this->document->order->get_billing_address_1() .'<br/>'.$this->document->order->get_billing_address_2(),
									),
								),
								array(
									'name'  => 'cac:Country',
									'value' => array(
										'name'       => 'cbc:IdentificationCode',
										'value'      => $this->document->order->get_billing_country(),
										'attributes' => array(
											'listID'       => 'ISO3166-1:Alpha2',
											'listAgencyID' => '6',
										),
									),
								),
							),
						),
						array(
							'name'  => 'cac:PartyTaxScheme',
							'value' => array(
								array(
									'name'  => 'cbc:CompanyID',
									'value' => $vat_number,
								),
								array(
									'name'  => 'cac:TaxScheme',
									'value' => array(
										array(
											'name'       => 'cbc:ID',
											'value'      => 'VAT',
											'attributes' => array(
												'schemeID'       => 'UN/ECE 5153',
												'schemeAgencyID' => '6',
											),
										),
									),
								),
							),
						),
						array(
							'name'  => 'cac:Contact',
							'value' => array(
								array(
									'name'  => 'cbc:Name',
									'value' => $customerPartyContactName,
								),
								array(
									'name'  => 'cbc:ElectronicMail',
									'value' => $this->document->order->get_billing_email(),
								),
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_wc_ubl_handle_AccountingCustomerParty', $customerParty, $data, $options, $this );

		return $data;
	}
}