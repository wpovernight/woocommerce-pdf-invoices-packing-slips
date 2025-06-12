<?php

namespace WPO\IPS\EDI\Syntax\Cii\Formats;

use WPO\IPS\EDI\Abstracts\AbstractFormat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CiiD16B extends AbstractFormat {
	
	public string $slug   = 'cii-d16b';
	public string $name   = 'CII D16B';
	public string $syntax = 'cii';

	/**
	 * Get the invoice root element
	 *
	 * @return string
	 */
	public function get_invoice_root_element(): string {
		return 'rsm:CrossIndustryInvoice';
	}
	
	/**
	 * Get the invoice additional attributes
	 *
	 * @return array
	 */
	public function get_invoice_additional_attributes(): array {
		return array();
	}
	
	/**
	 * Get the invoice namespaces
	 *
	 * @return array
	 */
	public function get_invoice_namespaces(): array {
		return array(
			'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
			'qdt' => 'urn:un:unece:uncefact:data:standard:QualifiedDataType:100',
			'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
			'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
		);
	}
	
	/**
	 * Get the invoice structure
	 *
	 * @return array
	 */
	public function get_invoice_structure(): array {
		return array(
			// Exchanged Document Context
			'exchanged_document_context' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ExchangedDocumentContextHandler::class,
			),
			
			// Exchanged Document
			'exchanged_document' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ExchangedDocumentHandler::class,
			),
			
			// Header Trade Agreement
			'seller_trade_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeAgreement\SellerTradePartyHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			'buyer_trade_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeAgreement\BuyerTradePartyHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			'contract_referenced_document' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeAgreement\ContractReferencedDocumentHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			
			// Header Trade Delivery
			'header_trade_delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\HeaderTradeDeliveryHandler::class,
			),
			
			// Header Trade Settlement
			'payment_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentReferenceHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentMeansHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'trade_tax' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement\TradeTaxHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'payment_terms' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentTermsHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'monetary_summation' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\ApplicableHeaderTradeSettlement\MonetarySummationHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			
			// Line Items
			'included_supply_chain_trade_line_item' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntax\Cii\Handlers\SupplyChainTradeTransaction\IncludedSupplyChainTradeLineItemHandler::class,
				'options' => array(
					'root' => 'rsm:SupplyChainTradeTransaction',
				),
			),
		);
	}

}
