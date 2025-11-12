<?php

namespace WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B;

use WPO\IPS\EDI\Syntaxes\Cii\Abstracts\AbstractCiiFormat;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Invoice extends AbstractCiiFormat {
	
	public string $type = 'invoice';
	public string $slug = 'cii-invoice-d16b';
	public string $name = 'CII Invoice D16B';
	
	/**
	 * Get the context
	 *
	 * @return string
	 */
	public function get_context(): string {
		return 'urn:cen.eu:en16931:2017';
	}
	
	/**
	 * Get the type code
	 *
	 * @return string
	 */
	public function get_type_code(): string {
		return '380';
	}

	/**
	 * Get the root element
	 *
	 * @return string
	 */
	public function get_root_element(): string {
		return 'rsm:CrossIndustryInvoice';
	}
	
	/**
	 * Get the additional attributes
	 *
	 * @return array
	 */
	public function get_additional_attributes(): array {
		return array();
	}
	
	/**
	 * Get the namespaces
	 *
	 * @return array
	 */
	public function get_namespaces(): array {
		return array(
			'rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
			'qdt' => 'urn:un:unece:uncefact:data:standard:QualifiedDataType:100',
			'udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
			'ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
		);
	}
	
	/**
	 * Get the structure
	 *
	 * @return array
	 */
	public function get_structure(): array {
		return array(
			// Exchanged Document Context
			'exchanged_document_context' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Handlers\ExchangedDocumentContextHandler::class,
			),
			
			// Exchanged Document
			'exchanged_document' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Handlers\ExchangedDocumentHandler::class,
			),
			
			// Header Trade Delivery
			'header_trade_delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Handlers\HeaderTradeDeliveryHandler::class,
			),
			
			// Supply Chain Trade Transaction
			'included_supply_chain_trade_line_item' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction\IncludedSupplyChainTradeLineItemHandler::class,
				'options' => array(
					'root' => 'rsm:SupplyChainTradeTransaction',
				),
			),
			
				'applicable_header_trade_agreement' => array(
					'enabled' => true,
					'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction\ApplicableHeaderTradeAgreementHandler::class,
					'options' => array(
						'root' => 'rsm:SupplyChainTradeTransaction',
					),
				),
				
				'applicable_header_trade_delivery' => array(
					'enabled' => true,
					'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Handlers\SupplyChainTradeTransaction\ApplicableHeaderTradeDeliveryHandler::class,
					'options' => array(
						'root' => 'rsm:SupplyChainTradeTransaction',
					),
				),
				
				'applicable_header_trade_settlement' => array(
					'enabled' => true,
					'handler' => \WPO\IPS\EDI\Syntaxes\Cii\Formats\CiiD16B\Handlers\SupplyChainTradeTransaction\ApplicableHeaderTradeSettlementHandler::class,
					'options' => array(
						'root' => 'rsm:SupplyChainTradeTransaction',
					),
				),
		);
	}

}
