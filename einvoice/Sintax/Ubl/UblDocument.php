<?php

namespace WPO\IPS\EInvoice\Sintax\Ubl;

use WPO\IPS\EInvoice\Abstracts\AbstractDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblDocument extends AbstractDocument {
	
	public string $sintax = 'ubl';

	public function get_root_element() {
		return apply_filters( 'wpo_wc_ubl_document_root_element', 'Invoice', $this );
	}
	
	public function get_additional_root_elements() {
		return apply_filters( 'wpo_wc_ubl_document_additional_root_elements', array(), $this );
	}

	public function get_format() {
		$format = apply_filters( 'wpo_wc_ubl_document_format' , array(
			'ubl_version_id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\UblVersionIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\IdHandler::class,
			),
			'issue_date' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\IssueDateHandler::class,
			),
			'invoice_type_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\InvoiceTypeCodeHandler::class,
			),
			'document_currency_code' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\DocumentCurrencyCodeHandler::class,
			),
			'buyer_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\BuyerReferenceHandler::class,
			),
			'order_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\OrderReferenceHandler::class,
			),
			'additional_document_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\AdditionalDocumentReferenceHandler::class,
			),
			'accounting_supplier_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\AddressHandler::class,
				'options' => array(
					'root' => 'cac:AccountingSupplierParty',
				),
			),
			'accounting_customer_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\AddressHandler::class,
				'options' => array(
					'root' => 'cac:AccountingCustomerParty',
				),
			),
			'delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\DeliveryHandler::class,
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\PaymentMeansHandler::class,
			),
			'payment_terms' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\PaymentTermsHandler::class,
			),
			'allowance_charge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\AllowanceChargeHandler::class,
			),
			'tax_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\TaxTotalHandler::class,
			),
			'legal_monetary_total' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\LegalMonetaryTotalHandler::class,
			),
			'invoice_line' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Ubl\Handlers\InvoiceLineHandler::class,
			),
		), $this );

		foreach ( $format as $key => $element ) {
			if ( false === $element['enabled'] ) {
				unset( $format[ $key ] );
			}
		}

		return $format;
	}

	public function get_namespaces() {
		return apply_filters( 'wpo_wc_ubl_document_namespaces', array(
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			''    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		), $this );
	}

}
