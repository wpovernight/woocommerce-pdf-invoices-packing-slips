<?php

namespace WPO\IPS\EInvoice\Syntax\Ubl\Formats;

use WPO\IPS\EInvoice\Abstracts\AbstractFormat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblTwoDotOne extends AbstractFormat {
	
	public string $slug = 'ubltwodotone';
	public string $name = 'UBL 2.1';
	
	/**
	 * Get the invoice structure
	 *
	 * @return array
	 */
	private function get_invoice_structure(): array {
		return array(
			'ubl_version_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\UblVersionIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\IdHandler::class,
			),
			'issue_date' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\IssueDateHandler::class,
			),
			'invoice_type_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\InvoiceTypeCodeHandler::class,
			),
			'document_currency_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\DocumentCurrencyCodeHandler::class,
			),
			'buyer_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\BuyerReferenceHandler::class,
			),
			'order_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\OrderReferenceHandler::class,
			),
			'additional_document_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\AdditionalDocumentReferenceHandler::class,
			),
			'accounting_supplier_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\AddressHandler::class,
				'options' => array(
					'root' => 'cac:AccountingSupplierParty',
				),
			),
			'accounting_customer_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\AddressHandler::class,
				'options' => array(
					'root' => 'cac:AccountingCustomerParty',
				),
			),
			'delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\DeliveryHandler::class,
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\PaymentMeansHandler::class,
			),
			'payment_terms' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\PaymentTermsHandler::class,
			),
			'allowance_charge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\AllowanceChargeHandler::class,
			),
			'tax_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\TaxTotalHandler::class,
			),
			'legal_monetary_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\LegalMonetaryTotalHandler::class,
			),
			'invoice_line' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Syntax\Ubl\Handlers\InvoiceLineHandler::class,
			),
		);
	}

}
