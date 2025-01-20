<?php

namespace WPO\IPS\UBL\Handlers\Common;

use WPO\IPS\UBL\Handlers\UblHandler;

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
					'value' => wpo_ips_ubl_sanitize_string( $company ),
				),
			),
			array(
				'name'  => 'cac:PostalAddress',
				'value' => array(
					array(
						'name'  => 'cbc:StreetName',
						'value' => wpo_ips_ubl_sanitize_string( get_option( 'woocommerce_store_address' ) ),
					),
					array(
						'name'  => 'cbc:CityName',
						'value' => wpo_ips_ubl_sanitize_string( get_option( 'woocommerce_store_city' ) ),
					),
					array(
						'name'  => 'cbc:PostalZone',
						'value' => get_option( 'woocommerce_store_postcode' ),
					),
					array(
						'name'  => 'cac:AddressLine',
						'value' => array(
							'name'  => 'cbc:Line',
							'value' => wpo_ips_ubl_sanitize_string( $address ),
						),
					),
					array(
						'name'  => 'cac:Country',
						'value' => array(
							'name'       => 'cbc:IdentificationCode',
							'value'      => wc_format_country_state_string( get_option( 'woocommerce_default_country', '' ) )['country'],
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

		if ( ! empty( $company ) && ! empty( $coc_number ) ) {
			$supplierPartyDetails[] = array(
				'name'  => 'cac:PartyLegalEntity',
				'value' => array(
					array(
						'name'  => 'cbc:RegistrationName',
						'value' => wpo_ips_ubl_sanitize_string( $company ),
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

		$supplierPartyDetails[] = array(
			'name'  => 'cac:Contact',
			'value' => array(
				array(
					'name'  => 'cbc:ElectronicMail',
					'value' => get_option( 'woocommerce_email_from_address' ),
				),
			),
		);

		return $supplierPartyDetails;
	}

	public function return_customer_party( $data, $options = array() ) {
		$vat_number        = apply_filters( 'wpo_wc_ubl_vat_number', wpo_wcpdf_get_order_customer_vat_number( $this->document->order ), $this->document->order );
		$customerPartyName = $customerPartyContactName = $this->document->order->get_formatted_billing_full_name();
		$billing_company   = $this->document->order->get_billing_company();

		if ( ! empty( $billing_company ) ) {
			// $customerPartyName = "{$billing_company} ({$customerPartyName})";
			// we register customer name separately as Contact too,
			// so we use the company name as the primary name
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
								'value' => wpo_ips_ubl_sanitize_string( $customerPartyName ),
							),
						),
						array(
							'name'  => 'cac:PostalAddress',
							'value' => array(
								array(
									'name'  => 'cbc:StreetName',
									'value' => wpo_ips_ubl_sanitize_string( $this->document->order->get_billing_address_1() ),
								),
								array(
									'name'  => 'cbc:CityName',
									'value' => wpo_ips_ubl_sanitize_string( $this->document->order->get_billing_city() ),
								),
								array(
									'name'  => 'cbc:PostalZone',
									'value' => $this->document->order->get_billing_postcode(),
								),
								array(
									'name'  => 'cac:AddressLine',
									'value' => array(
										'name'  => 'cbc:Line',
										'value' => wpo_ips_ubl_sanitize_string( $this->document->order->get_billing_address_1() . ' ' . $this->document->order->get_billing_address_2() ),
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
									'value' => wpo_ips_ubl_sanitize_string( $customerPartyContactName ),
								),
								array(
									'name'  => 'cbc:ElectronicMail',
									'value' => sanitize_email( $this->document->order->get_billing_email() ),
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
