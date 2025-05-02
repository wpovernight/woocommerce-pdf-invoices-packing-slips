<?php

namespace WPO\IPS\EInvoice\Sintax\Cii;

use WPO\IPS\EInvoice\Abstracts\AbstractDocument;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class CiiDocument extends AbstractDocument {
	
	public string $sintax = 'cii';

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
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ExchangedDocumentContextHandler::class,
			),
			
			// Exchanged Document
			'exchanged_document' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ExchangedDocumentHandler::class,
			),
			
			// Header Trade Agreement
			'seller_trade_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeAgreement\SellerTradePartyHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			'buyer_trade_party' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeAgreement\BuyerTradePartyHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			'contract_referenced_document' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeAgreement\ContractReferencedDocumentHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeAgreement',
				),
			),
			
			// Header Trade Delivery
			'header_trade_delivery' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\HeaderTradeDeliveryHandler::class,
			),
			
			// Header Trade Settlement
			'payment_reference' => array(
				'enabled' => false,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentReferenceHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'payment_means' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentMeansHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'trade_tax' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeSettlement\TradeTaxHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'payment_terms' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeSettlement\PaymentTermsHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			'monetary_summation' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\ApplicableHeaderTradeSettlement\MonetarySummationHandler::class,
				'options' => array(
					'root' => 'ram:ApplicableHeaderTradeSettlement',
				),
			),
			
			// Line Items
			'included_supply_chain_trade_line_item' => array(
				'enabled' => true,
				'handler' => \WPO\IPS\EInvoice\Sintax\Cii\Handlers\SupplyChainTradeTransaction\InvoiceLineHandler::class,
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
	
	/**
	 * Get the date format code for CII documents.
	 * 
	 * @return string The default date format code.
	 */
	public function get_date_format_code(): string {
		return apply_filters( 'wpo_ips_einvoice_cii_document_date_format_code', '102', $this );
	}
	
	/**
	 * Validate CII date format
	 *
	 * @param string $value  The date value to validate.
	 * @param string $format The date format (102, 610, or 616).
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_cii_date( string $value, string $format = '102' ): bool {
		$allowed_formats = array( '102', '610', '616' );
	
		if ( ! in_array( $format, $allowed_formats, true ) ) {
			return false;
		}
	
		// Only validate structure if format is 102 (YYYYMMDD)
		if ( $format === '102' ) {
			if ( strlen( $value ) !== 8 || ! ctype_digit( $value ) ) {
				return false;
			}
	
			$year  = (int) substr( $value, 0, 4 );
			$month = (int) substr( $value, 4, 2 );
			$day   = (int) substr( $value, 6, 2 );
	
			if ( $year <= 0 || $month < 1 || $month > 12 || $day < 1 || $day > 31 ) {
				return false;
			}
		}
	
		// No structural checks required for 610 (YYYYMM) or 616 (YYYYWW)
		return true;
	}
	
	/**
	 * Converts a CII date format code to a PHP date format.
	 *
	 * @param string $code CII date format code (e.g. 102, 610, 616).
	 * @return string PHP-compatible date format string.
	 */
	public function get_php_date_format_from_code( string $code ): string {
		switch ( $code ) {
			case '102': // Full date: YYYYMMDD
				return 'Ymd';
			case '610': // Year + Month: YYYYMM
				return 'Ym';
			case '616': // Year + Week: YYYYWW (ISO)
				return 'oW';
			default:
				return 'Ymd'; // Fallback
		}
	}

}
