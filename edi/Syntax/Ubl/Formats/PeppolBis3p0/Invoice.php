<?php

namespace WPO\IPS\EDI\Syntax\Ubl\Formats\PeppolBis3p0;

use WPO\IPS\EDI\Syntax\Ubl\Abstracts\AbstractUblFormat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends AbstractUblFormat {

	public string $type = 'invoice';
	public string $slug = 'peppol-bis-3p0';
	public string $name = 'PEPPOL BIS 3.0';

	/**
	 * Get the invoice type code
	 *
	 * @return string
	 */
	public function get_type_code(): string {
		return '380';
	}

	/**
	 * Get the format root element
	 *
	 * @return string
	 */
	public function get_root_element(): string {
		return 'Invoice';
	}
	
	/**
	 * Get the format additional attributes
	 *
	 * @return array
	 */
	public function get_additional_attributes(): array {
		return array();
	}

	/**
	 * Get the format namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array {
		return array(
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			''    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		);
	}
	
	/**
	 * Get the format structure
	 *
	 * @return array
	 */
	public function get_structure(): array {
		return array(
			'ubl_version_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\UblVersionIdHandler::class,
			),
			'customization_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Formats\PeppolBis3p0\Handlers\CustomizationIdHandler::class,
			),
			'profile_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Formats\PeppolBis3p0\Handlers\ProfileIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\IdHandler::class,
			),
			'issue_date' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\IssueDateHandler::class,
			),
			'invoice_type_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\InvoiceTypeCodeHandler::class,
			),
			'document_currency_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\DocumentCurrencyCodeHandler::class,
			),
			'buyer_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\BuyerReferenceHandler::class,
			),
			'order_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\OrderReferenceHandler::class,
			),
			'additional_document_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\AdditionalDocumentReferenceHandler::class,
			),
			'accounting_supplier_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\AddressHandler::class,
				'options' => array(
					'root' => 'cac:AccountingSupplierParty',
				),
			),
			'accounting_customer_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\AddressHandler::class,
				'options' => array(
					'root' => 'cac:AccountingCustomerParty',
				),
			),
			'delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\DeliveryHandler::class,
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\PaymentMeansHandler::class,
			),
			'payment_terms' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\PaymentTermsHandler::class,
			),
			'allowance_charge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\AllowanceChargeHandler::class,
			),
			'tax_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\TaxTotalHandler::class,
			),
			'legal_monetary_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\LegalMonetaryTotalHandler::class,
			),
			'invoice_line' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\InvoiceLineHandler::class,
			),
		);
	}

}
