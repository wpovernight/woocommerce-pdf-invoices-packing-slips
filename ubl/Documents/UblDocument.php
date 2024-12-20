<?php

namespace WPO\IPS\UBL\Documents;

use WPO\IPS\UBL\Models\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UblDocument extends Document {
	
	public function get_root_element() {
		return apply_filters( 'wpo_wc_ubl_document_root_element', 'Invoice', $this );
	}

	public function get_format() {
		$format = apply_filters( 'wpo_wc_ubl_document_format' , array(
			'ublversion' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\UblVersionIdHandler::class,
			),
			'id' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\IdHandler::class,
			),
			'issuedate' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\IssueDateHandler::class,
			),
			'invoicetype' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Invoice\InvoiceTypeCodeHandler::class,
			),
			'documentcurrencycode' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\DocumentCurrencyCodeHandler::class,
			),
			'orderreference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\OrderReferenceHandler::class,
			),
			'additionaldocumentreference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\AdditionalDocumentReferenceHandler::class,
			),
			'accountsupplierparty' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingSupplierParty',
				),
			),
			'accountingcustomerparty' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\AddressHandler::class,
				'options' => array(
					'root' => 'AccountingCustomerParty',
				),
			),
			'delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\DeliveryHandler::class,
			),
			'paymentmeans' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\PaymentMeansHandler::class,
			),
			'paymentterms' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\PaymentTermsHandler::class,
			),
			'allowancecharge' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\UBL\Handlers\Common\AllowanceChargeHandler::class,
			),
			'taxtotal' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\TaxTotalHandler::class,
			),
			'legalmonetarytotal' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Common\LegalMonetaryTotalHandler::class,
			),
			'invoicelines' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\UBL\Handlers\Invoice\InvoiceLineHandler::class,
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
