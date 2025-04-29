<?php

namespace WPO\IPS\EInvoice\Formats\Cii;

use WPO\IPS\EInvoice\Abstracts\AbstractDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CiiDocument extends AbstractDocument {

	public function get_root_element() {
		return apply_filters( 'wpo_ips_einvoice_cii_document_root_element', 'rsm:CrossIndustryInvoice', $this );
	}
	
	public function get_additional_root_elements() {
		return apply_filters( 'wpo_ips_einvoice_cii_document_additional_root_elements', array(), $this );
	}
	
	public function get_format() {
		$format = apply_filters( 'wpo_ips_einvoice_cii_document_format', array(
			// Exchanged Document Context
			'exchanged_document_context' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ExchangedDocumentContextHandler::class,
			),
			
			// Exchanged Document
			'exchanged_document' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ExchangedDocumentHandler::class,
			),
			
			// Header Trade Agreement
			'seller_trade_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeAgreement\SellerTradePartyHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			'buyer_trade_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeAgreement\BuyerTradePartyHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			'contract_referenced_document' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeAgreement\ContractReferencedDocumentHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			
			// Header Trade Delivery
			'header_trade_delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\HeaderTradeDeliveryHandler::class,
			),
			
			// Header Trade Settlement
			'payment_reference' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentReferenceHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentMeansHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'trade_tax' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement\TradeTaxHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'payment_terms' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentTermsHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'monetary_summation' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\ApplicableHeaderTradeSettlement\MonetarySummationHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			
			// Line Items
			'included_supply_chain_trade_line_item' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Formats\Cii\Handlers\SupplyChainTradeTransaction\InvoiceLineHandler::class,
				'options' => array(
					'root' => 'rsm:SupplyChainTradeTransaction',
				),
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
		return apply_filters( 'wpo_ips_einvoice_cii_document_namespaces', array(
			'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
			'qdt' => 'urn:un:unece:uncefact:data:standard:QualifiedDataType:100',
			'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
			'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
		), $this );
	}

}
