<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AddressHandler extends AbstractUblHandler {

	public function handle( $data, $options = array() ) {
		$root = isset( $options['root'] ) ? $options['root'] : 'cac:AccountingSupplierParty';

		// cac:AccountingSupplierParty or cac:AccountingCustomerParty
		if ( 'cac:AccountingSupplierParty' === $root ) {
			return $this->return_supplier_party( $data, $options );
		}

		return $this->return_customer_party( $data, $options );
	}

	public function return_supplier_party( $data, $options = array() ) {

		$supplierParty = array(
			array(
				'name'  => 'cbc:CustomerAssignedAccountID',
				'value' => '',
			),
			array(
				'name'  => 'cac:Party',
				'value' => $this->return_supplier_party_details(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_accounting_supplier_party', $supplierParty, $data, $options, $this );

		return $data;
	}

	public function return_supplier_party_details() {
		$company    = $this->get_shop_data( 'name' );
		$address    = $this->get_shop_data( 'address_line_1' );
		$vat_number = $this->get_shop_data( 'vat_number' );
		$coc_number = $this->get_shop_data( 'coc_number' );

		$supplierPartyDetails = array(
			array(
				'name'  => 'cac:PartyName',
				'value' => array(
					'name'  => 'cbc:Name',
					'value' => wpo_ips_edi_sanitize_string( $company ),
				),
			),
			array(
				'name'  => 'cac:PostalAddress',
				'value' => array(
					array(
						'name'  => 'cbc:StreetName',
						'value' => wpo_ips_edi_sanitize_string( $address ),
					),
					array(
						'name'  => 'cbc:CityName',
						'value' => wpo_ips_edi_sanitize_string( $this->get_shop_data( 'address_city' ) ),
					),
					array(
						'name'  => 'cbc:PostalZone',
						'value' => $this->get_shop_data( 'address_postcode' ),
					),
					array(
						'name'  => 'cac:AddressLine',
						'value' => array(
							'name'  => 'cbc:Line',
							'value' => wpo_ips_edi_sanitize_string( $address ),
						),
					),
					array(
						'name'  => 'cac:Country',
						'value' => array(
							'name'       => 'cbc:IdentificationCode',
							'value'      => wc_format_country_state_string( $this->get_shop_data( 'address_country' ) )['country'],
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
						'value' => wpo_ips_edi_sanitize_string( $company ),
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
					'value' => get_option( 'woocommerce_email_from_address' ), //TODO: wait Mohamad create the respective function
				),
			),
		);

		return $supplierPartyDetails;
	}

	public function return_customer_party( $data, $options = array() ) {
		$vat_number        = apply_filters( 'wpo_ips_edi_ubl_vat_number', wpo_wcpdf_get_order_customer_vat_number( $this->document->order ), $this->document->order );
		$customerPartyName = $customerPartyContactName = $this->document->order->get_formatted_billing_full_name();
		$billing_company   = $this->document->order->get_billing_company();

		if ( ! empty( $billing_company ) ) {
			// $customerPartyName = "{$billing_company} ({$customerPartyName})";
			// we register customer name separately as Contact too,
			// so we use the company name as the primary name
			$customerPartyName = $billing_company;
		}

		$customerParty = array(
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
							'value' => wpo_ips_edi_sanitize_string( $customerPartyName ),
						),
					),
					array(
						'name'  => 'cac:PostalAddress',
						'value' => array(
							array(
								'name'  => 'cbc:StreetName',
								'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_billing_address_1() ),
							),
							array(
								'name'  => 'cbc:CityName',
								'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_billing_city() ),
							),
							array(
								'name'  => 'cbc:PostalZone',
								'value' => $this->document->order->get_billing_postcode(),
							),
							array(
								'name'  => 'cac:AddressLine',
								'value' => array(
									'name'  => 'cbc:Line',
									'value' => wpo_ips_edi_sanitize_string( $this->document->order->get_billing_address_1() . ' ' . $this->document->order->get_billing_address_2() ),
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
								'value' => wpo_ips_edi_sanitize_string( $customerPartyContactName ),
							),
							array(
								'name'  => 'cbc:ElectronicMail',
								'value' => sanitize_email( $this->document->order->get_billing_email() ),
							),
						),
					),
				),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_accounting_customer_party', $customerParty, $data, $options, $this );

		return $data;
	}
}
