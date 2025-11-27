<?php

namespace WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0;

use WPO\IPS\EDI\Syntaxes\Ubl\Abstracts\AbstractUblFormat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends AbstractUblFormat {

	public string $type = 'invoice';
	public string $slug = 'peppol-bis-3p0-invoice';
	public string $name = 'PEPPOL BIS 3.0 Invoice';

	/**
	 * Get the type code
	 *
	 * @return string
	 */
	public function get_type_code(): string {
		return '380';
	}
	
	/**
	 * Get the quantity role
	 *
	 * @return string
	 */
	public function get_quantity_role(): string {
		return 'Invoiced';
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
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\UblVersionIdHandler::class,
			),
			'customization_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers\CustomizationIdHandler::class,
			),
			'profile_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers\ProfileIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\IdHandler::class,
			),
			'issue_date' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\IssueDateHandler::class,
			),
			'due_date' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\DueDateHandler::class,
			),
			'type_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\TypeCodeHandler::class,
			),
			'note' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\NoteHandler::class,
			),
			'document_currency_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\DocumentCurrencyCodeHandler::class,
			),
			'buyer_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\BuyerReferenceHandler::class,
			),
			'order_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\OrderReferenceHandler::class,
			),
			'billing_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\BillingReferenceHandler::class,
			),
			'despatch_document_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\DespatchDocumentReferenceHandler::class,
			),
			'receipt_document_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\ReceiptDocumentReferenceHandler::class,
			),
			'additional_document_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AdditionalDocumentReferenceHandler::class,
			),
			'accounting_supplier_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers\AccountingSupplierPartyHandler::class,
			),
			'accounting_customer_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Formats\PeppolBis3p0\Handlers\AccountingCustomerPartyHandler::class,
			),
			'delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\DeliveryHandler::class,
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\PaymentMeansHandler::class,
			),
			'payment_terms' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\PaymentTermsHandler::class,
			),
			'allowance_charge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\AllowanceChargeHandler::class,
			),
			'tax_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\TaxTotalHandler::class,
			),
			'legal_monetary_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\LegalMonetaryTotalHandler::class,
			),
			'line' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Ubl\Handlers\LineHandler::class,
			),
		);
	}

}
