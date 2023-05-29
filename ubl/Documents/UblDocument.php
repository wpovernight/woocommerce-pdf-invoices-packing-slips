<?php

namespace WPO\WC\UBL\Documents;

use WPO\WC\UBL\Models\Order;

defined( 'ABSPATH' ) or exit;

class UblDocument extends Document
{
	public function getFormat()
	{
		return [
			'ublversion' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\UblVersionIdHandler::class,
			],
			'id' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\IdHandler::class,
			],
			'issuedate' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\IssueDateHandler::class,
			],
			'invoicetype' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\InvoiceTypeCodeHandler::class,
			],
			'documentcurrencycode' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\DocumentCurrencyCodeHandler::class,
			],
			'orderreference' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\OrderReferenceHandler::class,
			],
			'additionaldocumentreference' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AdditionalDocumentReferenceHandler::class,
			],
			'accountsupplierparty' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AddressHandler::class,
				'options' => [
					'root' => 'AccountingSupplierParty',
				],
			],
			'accountingcustomerparty' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AddressHandler::class,
				'options' => [
					'root' => 'AccountingCustomerParty',
				],
			],
			// 'delivery' => [
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\DeliveryHandler::class,
			// ],
			// 'paymentmeans' => [
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\PaymentMeansHandler::class,
			// ],
			// 'paymentterms' => [
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\PaymentTermsHandler::class,
			// ],
			// 'allowancecharge' => [
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\AllowanceChargeHandler::class,
			// ],
			'taxtotal' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\TaxTotalHandler::class,
			],
			'legalmonetarytotal' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\LegalMonetaryTotalHandler::class,
			],
			'invoicelines' => [
				'handler' => \WPO\WC\UBL\Handlers\Ubl\InvoiceLineHandler::class,
			],
		];
	}

	public function getNamespaces()
	{
		return apply_filters( 'wpo_wc_ubl_document_namespaces', [
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			''    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		] );
	}

	public function getData()
	{
		$data = [];

		foreach( $this->getFormat() as $key => $value ) {
			$handler = new $value['handler']($this);
			$options = ( isset($value['options']) && is_array($value['options']) ? $value['options'] : [] );
			$data = $handler->handle($data, $options);
		}

		return apply_filters( 'wpo_wc_ubl_document_data', $data, $this );
	}
}