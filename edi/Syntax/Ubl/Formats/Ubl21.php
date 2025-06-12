<?php

namespace WPO\IPS\EDI\Syntax\Ubl\Formats;

use WPO\IPS\EDI\Abstracts\AbstractFormat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Ubl21 extends AbstractFormat {
	
	public string $slug = 'ubl-2-1';
	public string $name = 'UBL 2.1';
	
	/**
	 * Get the invoice structure
	 *
	 * @return array
	 */
	public function get_invoice_structure(): array {
		return array(
			'ubl_version_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Ubl\Handlers\UblVersionIdHandler::class,
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
