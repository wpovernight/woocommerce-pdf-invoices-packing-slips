<?php

namespace WPO\WC\UBL\Documents;

use WPO\WC\UBL\Models\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblDocument extends Document {
	
	public function getFormat() {
		return array(
			'ublversion' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\UblVersionIdHandler::class,
			),
			'id' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\IdHandler::class,
			),
			'issuedate' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\IssueDateHandler::class,
			),
			'invoicetype' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\InvoiceTypeCodeHandler::class,
			),
			'documentcurrencycode' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\DocumentCurrencyCodeHandler::class,
			),
			'orderreference' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\OrderReferenceHandler::class,
			),
			'additionaldocumentreference' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AdditionalDocumentReferenceHandler::class,
			),
			'accountsupplierparty' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingSupplierParty',
				),
			),
			'accountingcustomerparty' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingCustomerParty',
				),
			),
			// 'delivery' => array(
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\DeliveryHandler::class,
			// ),
			// 'paymentmeans' => array(
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\PaymentMeansHandler::class,
			// ),
			// 'paymentterms' => array(
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\PaymentTermsHandler::class,
			// ),
			// 'allowancecharge' => array(
			//     'handler' => \WPO\WC\UBL\Handlers\Ubl\AllowanceChargeHandler::class,
			// ),
			'taxtotal' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\TaxTotalHandler::class,
			),
			'legalmonetarytotal' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\LegalMonetaryTotalHandler::class,
			),
			'invoicelines' => array(
				'handler' => \WPO\WC\UBL\Handlers\Ubl\InvoiceLineHandler::class,
			),
		);
	}

	public function getNamespaces() {
		return apply_filters( 'wpo_wc_ubl_document_namespaces', array(
			'cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
			'cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
			''    => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
		) );
	}

	public function getData() {
		$data = array();

		foreach ( $this->getFormat() as $key => $value ) {
			$handler = new $value['handler']($this);
			$options = isset( $value['options'] ) && is_array( $value['options'] ) ? $value['options'] : [];
			$data    = $handler->handle( $data, $options );
		}

		return apply_filters( 'wpo_wc_ubl_document_data', $data, $this );
	}
}