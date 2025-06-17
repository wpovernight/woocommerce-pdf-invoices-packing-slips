<?php
namespace WPO\IPS\EDI\Syntax\Ubl\Handlers;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AccountingSupplierPartyHandler extends AbstractUblHandler {

	/**
	 * Handle the data and return the formatted output.
	 *
	 * @param array $data    The data to be handled.
	 * @param array $options Additional options for handling.
	 * @return array
	 */
	public function handle( array $data, array $options = array() ): array {
		$accountingSupplierParty = array(
			'name'  => 'cac:AccountingSupplierParty',
			'value' => array(
				$this->get_customer_assigned_account_id(),
				$this->get_party(),
			),
		);

		$data[] = apply_filters( 'wpo_ips_edi_ubl_accounting_supplier_party', $accountingSupplierParty, $data, $options, $this );

		return $data;
	}

	/**
	 * Returns the customer assigned account ID for the supplier party.
	 *
	 * @return array
	 */
	private function get_customer_assigned_account_id(): array {
		$customerAssignedAccountId = array(
			'name'  => 'cbc:CustomerAssignedAccountID',
			'value' => '',
		);
		
		return apply_filters( 'wpo_ips_edi_ubl_customer_assigned_account_id', $customerAssignedAccountId, $this );
	}
	
	/**
	 * Returns the supplier party details for the UBL document.
	 *
	 * @return array
	 */
	private function get_party(): array {
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
		
		$party = array(
			'name'  => 'cac:Party',
			'value' => $supplierPartyDetails,
		);

		return apply_filters( 'wpo_ips_edi_ubl_accounting_supplier_party', $party, $this );
	}
	
}
