<?php

namespace WPO\WC\UBL\Documents;

use WPO\WC\UBL\Models\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblDocument extends Document {
	
	public function get_format() {
		$format = apply_filters( 'wpo_wc_ubl_document_format' , array(
			'ublversion' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\UblVersionIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\IdHandler::class,
			),
			'issuedate' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\IssueDateHandler::class,
			),
			'invoicetype' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\InvoiceTypeCodeHandler::class,
			),
			'documentcurrencycode' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\DocumentCurrencyCodeHandler::class,
			),
			'orderreference' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\OrderReferenceHandler::class,
			),
			'additionaldocumentreference' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AdditionalDocumentReferenceHandler::class,
			),
			'accountsupplierparty' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingSupplierParty',
				),
			),
			'accountingcustomerparty' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingCustomerParty',
				),
			),
			'delivery' => array(
				'enabled' => false,
			    'handler' => \WPO\WC\UBL\Handlers\Ubl\DeliveryHandler::class,
			),
			'paymentmeans' => array(
				'enabled' => false,
			    'handler' => \WPO\WC\UBL\Handlers\Ubl\PaymentMeansHandler::class,
			),
			'paymentterms' => array(
				'enabled' => false,
			    'handler' => \WPO\WC\UBL\Handlers\Ubl\PaymentTermsHandler::class,
			),
			'allowancecharge' => array(
				'enabled' => false,
			    'handler' => \WPO\WC\UBL\Handlers\Ubl\AllowanceChargeHandler::class,
			),
			'taxtotal' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\TaxTotalHandler::class,
			),
			'legalmonetarytotal' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\LegalMonetaryTotalHandler::class,
			),
			'invoicelines' => array(
				'enabled' => true,
				'handler' => \WPO\WC\UBL\Handlers\Ubl\InvoiceLineHandler::class,
			),
		) );
		
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
		) );
	}

	public function get_data() {
		$data = array();

		foreach ( $this->get_format() as $key => $value ) {
			$handler = new $value['handler']($this);
			$options = isset( $value['options'] ) && is_array( $value['options'] ) ? $value['options'] : array();
			$data    = $handler->handle( $data, $options );
		}

		return apply_filters( 'wpo_wc_ubl_document_data', $data, $this );
	}
}