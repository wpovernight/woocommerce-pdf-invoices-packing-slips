<?php

namespace WPO\IPS\EDI\Standards;

use WPO\IPS\EDI\Abstracts\AbstractStandard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EN16931 extends AbstractStandard {

	public static string $slug    = 'en16931';
	public static string $name    = 'EN16931';
	public static string $version = '16.0';

	/**
	 * Get the VAT category codes according to code list.
	 * 
	 * - Column [VAT CAT]
	 * 
	 * @return array
	 */
	public static function get_vat_cat(): array {
		$defaults = array(
			'VAT' => __( 'Value added tax (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_vat_cat', array() );

		return $extra + $defaults;
	}

	/**
	 * Get the Duty, tax or fee categories according to code list.
	 * 
	 * - Column [5305]
	 *
	 * @return array
	 */
	public static function get_5305(): array {
		$defaults = array(
			'AE' => __( 'VAT Reverse Charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'E'  => __( 'Exempt from tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'G'  => __( 'Free export item, tax not charged', 'woocommerce-pdf-invoices-packing-slips' ),
			'K'  => __( 'VAT exempt for EEA intra-community supply of goods and services', 'woocommerce-pdf-invoices-packing-slips' ),
			'L'  => __( 'Canary Islands general indirect tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'M'  => __( 'Tax for production, services and importation in Ceuta and Melilla', 'woocommerce-pdf-invoices-packing-slips' ),
			'O'  => __( 'Services outside scope of tax', 'woocommerce-pdf-invoices-packing-slips' ),
			'S'  => __( 'Standard rate', 'woocommerce-pdf-invoices-packing-slips' ),
			'Z'  => __( 'Zero rated goods', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_5305', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get the VAT exemption reason codes according to code list.
	 * 
	 * - Column [VATEX]
	 *
	 * @return array
	 */
	public static function get_vatex(): array {
		$defaults = array(
			// EU VAT exemptions
			'VATEX-EU-79-C'          => __( 'Exempt based on article 79, point c of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132'           => __( 'Exempt based on article 132 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1A'        => __( 'Exempt based on article 132, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1B'        => __( 'Exempt based on article 132, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1C'        => __( 'Exempt based on article 132, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1D'        => __( 'Exempt based on article 132, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1E'        => __( 'Exempt based on article 132, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1F'        => __( 'Exempt based on article 132, section 1 (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1G'        => __( 'Exempt based on article 132, section 1 (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1H'        => __( 'Exempt based on article 132, section 1 (h) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1I'        => __( 'Exempt based on article 132, section 1 (i) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1J'        => __( 'Exempt based on article 132, section 1 (j) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1K'        => __( 'Exempt based on article 132, section 1 (k) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1L'        => __( 'Exempt based on article 132, section 1 (l) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1M'        => __( 'Exempt based on article 132, section 1 (m) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1N'        => __( 'Exempt based on article 132, section 1 (n) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1O'        => __( 'Exempt based on article 132, section 1 (o) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1P'        => __( 'Exempt based on article 132, section 1 (p) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-132-1Q'        => __( 'Exempt based on article 132, section 1 (q) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-135-1'         => __( 'Exempt based on article 135, section 1 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143'           => __( 'Exempt based on article 143 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1A'        => __( 'Exempt based on article 143, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1B'        => __( 'Exempt based on article 143, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1C'        => __( 'Exempt based on article 143, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1D'        => __( 'Exempt based on article 143, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1E'        => __( 'Exempt based on article 143, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1F'        => __( 'Exempt based on article 143, section 1 (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1FA'       => __( 'Exempt based on article 143, section 1 (fa) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1G'        => __( 'Exempt based on article 143, section 1 (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1H'        => __( 'Exempt based on article 143, section 1 (h) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1I'        => __( 'Exempt based on article 143, section 1 (i) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1J'        => __( 'Exempt based on article 143, section 1 (j) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1K'        => __( 'Exempt based on article 143, section 1 (k) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-143-1L'        => __( 'Exempt based on article 143, section 1 (l) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-144'           => __( 'Exempt based on article 144 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-146-1E'        => __( 'Exempt based on article 146 section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148'           => __( 'Exempt based on article 148 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-A'         => __( 'Exempt based on article 148, section (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-B'         => __( 'Exempt based on article 148, section (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-C'         => __( 'Exempt based on article 148, section (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-D'         => __( 'Exempt based on article 148, section (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-E'         => __( 'Exempt based on article 148, section (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-F'         => __( 'Exempt based on article 148, section (f) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-148-G'         => __( 'Exempt based on article 148, section (g) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151'           => __( 'Exempt based on article 151 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1A'        => __( 'Exempt based on article 151, section 1 (a) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1AA'       => __( 'Exempt based on article 151, section 1 (aa) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1B'        => __( 'Exempt based on article 151, section 1 (b) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1C'        => __( 'Exempt based on article 151, section 1 (c) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1D'        => __( 'Exempt based on article 151, section 1 (d) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-151-1E'        => __( 'Exempt based on article 151, section 1 (e) of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-153'           => __( 'Exempt based on article 153 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-159'           => __( 'Exempt based on article 159 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-309'           => __( 'Exempt based on article 309 of Council Directive 2006/112/EC', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-AE'            => __( 'Reverse charge', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-D'             => __( 'Travel agents VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-F'             => __( 'Second hand goods VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-G'             => __( 'Export outside the EU', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-I'             => __( 'Works of art VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-IC'            => __( 'Intra-community supply', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-J'             => __( 'Collectors items and antiques VAT scheme.', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-EU-O'             => __( 'Not subject to VAT', 'woocommerce-pdf-invoices-packing-slips' ),

			// France specific VAT exemptions
			'VATEX-FR-AE'            => __( 'Exempt based on 2 of article 283 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-1'      => __( 'Exempt based on 1 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-2'      => __( 'Exempt based on 2 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-3'      => __( 'Exempt based on 3 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-4'      => __( 'Exempt based on 4 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-5'      => __( 'Exempt based on 5 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-7'      => __( 'Exempt based on 7 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261-8'      => __( 'Exempt based on 8 of article 261 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261A'       => __( 'Exempt based on article 261 A of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261B'       => __( 'Exempt based on article 261 B of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261C-1'     => __( 'Exempt based on 1° of article 261 C of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261C-2'     => __( 'Exempt based on 2° of article 261 C of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261C-3'     => __( 'Exempt based on 3° of article 261 C of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261D-1'     => __( 'Exempt based on 1° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261D-1BIS'  => __( 'Exempt based on 1°bis of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261D-2'     => __( 'Exempt based on 2° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261D-3'     => __( 'Exempt based on 3° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261D-4'     => __( 'Exempt based on 4° of article 261 D of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261E-1'     => __( 'Exempt based on 1° of article 261 E of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI261E-2'     => __( 'Exempt based on 2° of article 261 E of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI277A'       => __( 'Exempt based on article 277 A of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI275'        => __( 'Exempt based on article 275 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CGI295'        => __( 'Exempt based on article 295 of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-CNWVAT'        => __( 'France domestic Credit Notes without VAT, due to supplier forfeit of VAT for discount', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-FRANCHISE'     => __( 'France domestic VAT franchise in base', 'woocommerce-pdf-invoices-packing-slips' ),
			'VATEX-FR-298SEXDECIESA' => __( 'Exempt based on article 298 sexdecies A of the Code Général des Impôts (CGI ; General tax code)', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_vatex', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get VATEX remarks according to code list.
	 * 
	 * - Column [VATEX]
	 *
	 * @return array
	 */
	public static function get_vatex_remarks(): array {
		/* translators: %s: tax category code */
		$reason_common_remark             = __( 'Only use with tax category code %s', 'woocommerce-pdf-invoices-packing-slips' );
		$domestic_invoicing_france_remark = __( 'Only for domestic invoicing in France', 'woocommerce-pdf-invoices-packing-slips' );

		$defaults = array(
			'scheme'   => array(),
			'category' => array(),
			'reason'   => array(
				// EU VAT exemption remarks
				'VATEX-EU-AE'            => sprintf( $reason_common_remark, '<code>AE</code>' ),
				'VATEX-EU-D'             => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-F'             => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-G'             => sprintf( $reason_common_remark, '<code>G</code>' ),
				'VATEX-EU-I'             => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-IC'            => sprintf( $reason_common_remark, '<code>K</code>' ),
				'VATEX-EU-J'             => sprintf( $reason_common_remark, '<code>E</code>' ),
				'VATEX-EU-O'             => sprintf( $reason_common_remark, '<code>O</code>' ),
				
				// France specific VAT exemption remarks
				'VATEX-FR-FRANCHISE'     => __( 'For domestic invoicing in France', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CNWVAT'        => __( 'For domestic Credit Notes only in France', 'woocommerce-pdf-invoices-packing-slips' ),
				'VATEX-FR-CGI261-1'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261-2'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261-3'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261-4'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261-5'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261-7'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261-8'      => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261A'       => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261B'       => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261C-1'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261C-2'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261C-3'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261D-1'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261D-1BIS'  => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261D-2'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261D-3'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261D-4'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261E-1'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI261E-2'     => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI277A'       => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI275'        => $domestic_invoicing_france_remark,
				'VATEX-FR-298SEXDECIESA' => $domestic_invoicing_france_remark,
				'VATEX-FR-CGI295'        => $domestic_invoicing_france_remark,
				'VATEX-FR-AE'            => $domestic_invoicing_france_remark,
			),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_vatex_remarks', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get the Electronic address scheme identifier according to code list.
	 * 
	 * - Column [EAS]
	 *
	 * @return array
	 */
	public static function get_eas(): array {
		$defaults = array(
			'0002' => __( 'System Information et Repertoire des Entreprise et des Etablissements: SIRENE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0007' => __( 'Organisationsnummer', 'woocommerce-pdf-invoices-packing-slips' ),
			'0009' => __( 'SIRET-CODE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0037' => __( 'LY-tunnus', 'woocommerce-pdf-invoices-packing-slips' ),
			'0060' => __( 'Data Universal Numbering System (D-U-N-S Number)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0088' => __( 'EAN Location Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0096' => __( 'The Danish Business Authority - P-number (DK:P)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0097' => __( 'FTI - Ediforum Italia, (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0106' => __( 'Vereniging van Kamers van Koophandel en Fabrieken in Nederland (Association of Chambers of Commerce and Industry in the Netherlands), Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0130' => __( 'Directorates of the European Commission', 'woocommerce-pdf-invoices-packing-slips' ),
			'0135' => __( 'SIA Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0142' => __( 'SECETI Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0147' => __( 'Standard Company Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0151' => __( 'Australian Business Number (ABN) Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0154' => __( 'Identification number of economic subjects: (ICO)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0158' => __( 'Identification number of economic subject (ICO) Act on State Statistics of 29 November 2001, § 27', 'woocommerce-pdf-invoices-packing-slips' ),
			'0170' => __( 'Teikoku Company Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0177' => __( 'Odette International Limited', 'woocommerce-pdf-invoices-packing-slips' ),
			'0183' => __( "Numéro d'identification suisse des enterprises (IDE), Swiss Unique Business Identification Number (UIDB)", 'woocommerce-pdf-invoices-packing-slips' ),
			'0184' => __( 'DIGSTORG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0188' => __( 'Corporate Number of The Social Security and Tax Number System', 'woocommerce-pdf-invoices-packing-slips' ),
			'0190' => __( "Dutch Originator's Identification Number", 'woocommerce-pdf-invoices-packing-slips' ),
			'0191' => __( 'Centre of Registers and Information Systems of the Ministry of Justice', 'woocommerce-pdf-invoices-packing-slips' ),
			'0192' => __( 'Enhetsregisteret ved Bronnoysundregisterne', 'woocommerce-pdf-invoices-packing-slips' ),
			'0193' => __( 'UBL.BE party identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0194' => __( 'KOIOS Open Technical Dictionary', 'woocommerce-pdf-invoices-packing-slips' ),
			'0195' => __( 'Singapore UEN identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0196' => __( 'Kennitala - Iceland legal id for individuals and legal entities', 'woocommerce-pdf-invoices-packing-slips' ),
			'0198' => __( 'ERSTORG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0199' => __( 'Global legal entity identifier (GLEIF)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0200' => __( 'Legal entity code (Lithuania)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0201' => __( 'Codice Univoco Unità Organizzativa iPA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0202' => __( 'Indirizzo di Posta Elettronica Certificata', 'woocommerce-pdf-invoices-packing-slips' ),
			'0203' => __( 'eDelivery Network Participant identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0204' => __( 'Leitweg-ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'0205' => __( 'CODDEST', 'woocommerce-pdf-invoices-packing-slips' ),
			'0208' => __( "Numero d'entreprise / ondernemingsnummer / Unternehmensnummer", 'woocommerce-pdf-invoices-packing-slips' ),
			'0209' => __( 'GS1 identification keys', 'woocommerce-pdf-invoices-packing-slips' ),
			'0210' => __( 'CODICE FISCALE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0211' => __( 'PARTITA IVA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0212' => __( 'Finnish Organization Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0213' => __( 'Finnish Organization Value Add Tax Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0215' => __( 'Net service ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'0216' => __( 'OVTcode', 'woocommerce-pdf-invoices-packing-slips' ),
			'0217' => __( 'The Netherlands Chamber of Commerce and Industry establishment number', 'woocommerce-pdf-invoices-packing-slips' ),
			'0218' => __( 'Unified registration number (Latvia)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0221' => __( 'The registered number of the qualified invoice issuer', 'woocommerce-pdf-invoices-packing-slips' ),
			'0225' => __( 'FRCTC ELECTRONIC ADDRESS', 'woocommerce-pdf-invoices-packing-slips' ),
			'0230' => __( 'National e-Invoicing Framework', 'woocommerce-pdf-invoices-packing-slips' ),
			'0235' => __( 'UAE Tax Identification Number (TIN)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0240' => __( 'Register of legal persons (in French : Répertoire des personnes morales)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0244' => __( 'Tax Identification (Tax ID), Nigeria', 'woocommerce-pdf-invoices-packing-slips' ),
			'9910' => __( 'Hungary VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9913' => __( 'Business Registers Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'9914' => __( 'Österreichische Umsatzsteuer-Identifikationsnummer', 'woocommerce-pdf-invoices-packing-slips' ),
			'9915' => __( 'Österreichisches Verwaltungs bzw. Organisationskennzeichen', 'woocommerce-pdf-invoices-packing-slips' ),
			'9918' => __( 'SOCIETY FOR WORLDWIDE INTERBANK FINANCIAL, TELECOMMUNICATION S.W.I.F.T', 'woocommerce-pdf-invoices-packing-slips' ),
			'9919' => __( 'Kennziffer des Unternehmensregisters', 'woocommerce-pdf-invoices-packing-slips' ),
			'9920' => __( 'Agencia Española de Administración Tributaria', 'woocommerce-pdf-invoices-packing-slips' ),
			'9922' => __( 'Andorra VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9923' => __( 'Albania VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9924' => __( 'Bosnia and Herzegovina VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9925' => __( 'Belgium VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9926' => __( 'Bulgaria VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9927' => __( 'Switzerland VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9928' => __( 'Cyprus VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9929' => __( 'Czech Republic VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9930' => __( 'Germany VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9931' => __( 'Estonia VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9932' => __( 'United Kingdom VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9933' => __( 'Greece VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9934' => __( 'Croatia VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9935' => __( 'Ireland VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9936' => __( 'Liechtenstein VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9937' => __( 'Lithuania VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9938' => __( 'Luxemburg VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9939' => __( 'Latvia VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9940' => __( 'Monaco VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9941' => __( 'Montenegro VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9942' => __( 'Macedonia, the former Yugoslav Republic of VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9943' => __( 'Malta VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9944' => __( 'Netherlands VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9945' => __( 'Poland VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9946' => __( 'Portugal VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9947' => __( 'Romania VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9948' => __( 'Serbia VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9949' => __( 'Slovenia VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9950' => __( 'Slovakia VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9951' => __( 'San Marino VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9952' => __( 'Turkey VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9953' => __( 'Holy See (Vatican City State) VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9957' => __( 'French VAT number', 'woocommerce-pdf-invoices-packing-slips' ),
			'9959' => __( 'Employer Identification Number (EIN, USA)', 'woocommerce-pdf-invoices-packing-slips' ),
			'AN'   => __( 'O.F.T.P. (ODETTE File Transfer Protocol)', 'woocommerce-pdf-invoices-packing-slips' ),
			'AQ'   => __( 'X.400 address for mail text', 'woocommerce-pdf-invoices-packing-slips' ),
			'AS'   => __( 'AS2 exchange', 'woocommerce-pdf-invoices-packing-slips' ),
			'AU'   => __( 'File Transfer Protocol', 'woocommerce-pdf-invoices-packing-slips' ),
			'EM'   => __( 'Electronic mail (SMPT)', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_eas', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get the Identifier scheme code according to code list.
	 * 
	 * - Column [ICD]
	 *
	 * @return array
	 */
	public static function get_icd(): array {
		$defaults = array(
			'0002' => __( 'System Information et Repertoire des Entreprise et des Etablissements: SIRENE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0003' => __( 'Codification Numerique des Etablissments Financiers En Belgique', 'woocommerce-pdf-invoices-packing-slips' ),
			'0004' => __( 'NBS/OSI NETWORK', 'woocommerce-pdf-invoices-packing-slips' ),
			'0005' => __( 'USA FED GOV OSI NETWORK', 'woocommerce-pdf-invoices-packing-slips' ),
			'0006' => __( 'USA DOD OSI NETWORK', 'woocommerce-pdf-invoices-packing-slips' ),
			'0007' => __( 'Organisationsnummer', 'woocommerce-pdf-invoices-packing-slips' ),
			'0008' => __( 'LE NUMERO NATIONAL', 'woocommerce-pdf-invoices-packing-slips' ),
			'0009' => __( 'SIRET-CODE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0010' => __( 'Organizational Identifiers for Structured Names under ISO 9541 Part 2', 'woocommerce-pdf-invoices-packing-slips' ),
			'0011' => __( 'International Code Designator for the Identification of OSI-based, Amateur Radio Organizations, Network Objects and Application Services.', 'woocommerce-pdf-invoices-packing-slips' ),
			'0012' => __( 'European Computer Manufacturers Association: ECMA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0013' => __( 'VSA FTP CODE (FTP = File Transfer Protocol)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0014' => __( "NIST/OSI Implememts' Workshop", 'woocommerce-pdf-invoices-packing-slips' ),
			'0015' => __( 'Electronic Data Interchange: EDI', 'woocommerce-pdf-invoices-packing-slips' ),
			'0016' => __( 'EWOS Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0017' => __( 'COMMON LANGUAGE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0018' => __( 'SNA/OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0019' => __( 'Air Transport Industry Services Communications Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0020' => __( 'European Laboratory for Particle Physics: CERN', 'woocommerce-pdf-invoices-packing-slips' ),
			'0021' => __( 'SOCIETY FOR WORLDWIDE INTERBANK FINANCIAL, TELECOMMUNICATION S.W.I.F.T.', 'woocommerce-pdf-invoices-packing-slips' ),
			'0022' => __( 'OSF Distributed Computing Object Identification', 'woocommerce-pdf-invoices-packing-slips' ),
			'0023' => __( 'Nordic University and Research Network: NORDUnet', 'woocommerce-pdf-invoices-packing-slips' ),
			'0024' => __( 'Digital Equipment Corporation: DEC', 'woocommerce-pdf-invoices-packing-slips' ),
			'0025' => __( 'OSI ASIA-OCEANIA WORKSHOP', 'woocommerce-pdf-invoices-packing-slips' ),
			'0026' => __( 'NATO ISO 6523 ICDE coding scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0027' => __( 'Aeronautical Telecommunications Network (ATN)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0028' => __( 'International Standard ISO 6523', 'woocommerce-pdf-invoices-packing-slips' ),
			'0029' => __( 'The All-Union Classifier of Enterprises and Organisations', 'woocommerce-pdf-invoices-packing-slips' ),
			'0030' => __( 'AT&T/OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0031' => __( 'EDI Partner Identification Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0032' => __( 'Telecom Australia', 'woocommerce-pdf-invoices-packing-slips' ),
			'0033' => __( 'S G W OSI Internetwork', 'woocommerce-pdf-invoices-packing-slips' ),
			'0034' => __( 'Reuter Open Address Standard', 'woocommerce-pdf-invoices-packing-slips' ),
			'0035' => __( 'ISO 6523 - ICD', 'woocommerce-pdf-invoices-packing-slips' ),
			'0036' => __( 'TeleTrust Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0037' => __( 'LY-tunnus', 'woocommerce-pdf-invoices-packing-slips' ),
			'0038' => __( 'The Australian GOSIP Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0039' => __( 'The OZ DOD OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0040' => __( 'Unilever Group Companies', 'woocommerce-pdf-invoices-packing-slips' ),
			'0041' => __( 'Citicorp Global Information Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0042' => __( 'DBP Telekom Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0043' => __( 'HydroNETT', 'woocommerce-pdf-invoices-packing-slips' ),
			'0044' => __( 'Thai Industrial Standards Institute (TISI)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0045' => __( 'ICI Company Identification System', 'woocommerce-pdf-invoices-packing-slips' ),
			'0046' => __( 'FUNLOC', 'woocommerce-pdf-invoices-packing-slips' ),
			'0047' => __( 'BULL ODI/DSA/UNIX Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0048' => __( 'OSINZ', 'woocommerce-pdf-invoices-packing-slips' ),
			'0049' => __( 'Auckland Area Health', 'woocommerce-pdf-invoices-packing-slips' ),
			'0050' => __( 'Firmenich', 'woocommerce-pdf-invoices-packing-slips' ),
			'0051' => __( 'AGFA-DIS', 'woocommerce-pdf-invoices-packing-slips' ),
			'0052' => __( 'Society of Motion Picture and Television Engineers (SMPTE)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0053' => __( 'Migros_Network M_NETOPZ', 'woocommerce-pdf-invoices-packing-slips' ),
			'0054' => __( 'ISO6523 - ICDPCR', 'woocommerce-pdf-invoices-packing-slips' ),
			'0055' => __( 'Energy Net', 'woocommerce-pdf-invoices-packing-slips' ),
			'0056' => __( 'Nokia Object Identifiers (NOI)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0057' => __( 'Saint Gobain', 'woocommerce-pdf-invoices-packing-slips' ),
			'0058' => __( 'Siemens Corporate Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0059' => __( 'DANZNET', 'woocommerce-pdf-invoices-packing-slips' ),
			'0060' => __( 'Data Universal Numbering System (D-U-N-S Number)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0061' => __( 'SOFFEX OSI', 'woocommerce-pdf-invoices-packing-slips' ),
			'0062' => __( 'KPN OVN', 'woocommerce-pdf-invoices-packing-slips' ),
			'0063' => __( 'ascomOSINet', 'woocommerce-pdf-invoices-packing-slips' ),
			'0064' => __( 'UTC: Uniforme Transport Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0065' => __( 'SOLVAY OSI CODING', 'woocommerce-pdf-invoices-packing-slips' ),
			'0066' => __( 'Roche Corporate Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0067' => __( 'ZellwegerOSINet', 'woocommerce-pdf-invoices-packing-slips' ),
			'0068' => __( 'Intel Corporation OSI', 'woocommerce-pdf-invoices-packing-slips' ),
			'0069' => __( 'SITA Object Identifier Tree', 'woocommerce-pdf-invoices-packing-slips' ),
			'0070' => __( 'DaimlerChrysler Corporate Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0071' => __( 'LEGO /OSI NETWORK', 'woocommerce-pdf-invoices-packing-slips' ),
			'0072' => __( 'NAVISTAR/OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0073' => __( 'ICD Formatted ATM address', 'woocommerce-pdf-invoices-packing-slips' ),
			'0074' => __( 'ARINC', 'woocommerce-pdf-invoices-packing-slips' ),
			'0075' => __( 'Alcanet/Alcatel-Alsthom Corporate Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0076' => __( 'Sistema Italiano di Identificazione di ogetti gestito da UNINFO', 'woocommerce-pdf-invoices-packing-slips' ),
			'0077' => __( 'Sistema Italiano di Indirizzamento di Reti OSI Gestito da UNINFO', 'woocommerce-pdf-invoices-packing-slips' ),
			'0078' => __( 'Mitel terminal or switching equipment', 'woocommerce-pdf-invoices-packing-slips' ),
			'0079' => __( 'ATM Forum', 'woocommerce-pdf-invoices-packing-slips' ),
			'0080' => __( 'UK National Health Service Scheme, (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0081' => __( 'International NSAP', 'woocommerce-pdf-invoices-packing-slips' ),
			'0082' => __( "Norwegian Telecommunications Authority's, NTA'S, EDI, identifier scheme (EDIRA compliant)", 'woocommerce-pdf-invoices-packing-slips' ),
			'0083' => __( 'Advanced Telecommunications Modules Limited, Corporate Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0084' => __( 'Athens Chamber of Commerce & Industry Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0085' => __( 'Swiss Chambers of Commerce Scheme (EDIRA) compliant', 'woocommerce-pdf-invoices-packing-slips' ),
			'0086' => __( 'United States Council for International Business (USCIB) Scheme, (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0087' => __( 'National Federation of Chambers of Commerce & Industry of Belgium, Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0088' => __( 'EAN Location Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0089' => __( 'The Association of British Chambers of Commerce Ltd. Scheme, (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0090' => __( 'Internet IP addressing - ISO 6523 ICD encoding', 'woocommerce-pdf-invoices-packing-slips' ),
			'0091' => __( 'Cisco Sysytems / OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0093' => __( 'Revenue Canada Business Number Registration (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0094' => __( 'DEUTSCHER INDUSTRIE- UND HANDELSTAG (DIHT) Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0095' => __( 'Hewlett - Packard Company Internal AM Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0096' => __( 'The Danish Business Authority - P-number (DK:P)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0097' => __( 'FTI - Ediforum Italia, (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0098' => __( 'CHAMBER OF COMMERCE TEL AVIV-JAFFA Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0099' => __( 'Siemens Supervisory Systems Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0100' => __( 'PNG_ICD Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0101' => __( 'South African Code Allocation', 'woocommerce-pdf-invoices-packing-slips' ),
			'0102' => __( 'HEAG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0104' => __( 'BT - ICD Coding System', 'woocommerce-pdf-invoices-packing-slips' ),
			'0105' => __( 'Portuguese Chamber of Commerce and Industry Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0106' => __( 'Vereniging van Kamers van Koophandel en Fabrieken in Nederland (Association of Chambers of Commerce and Industry in the Netherlands), Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0107' => __( 'Association of Swedish Chambers of Commerce and Industry Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0108' => __( 'Australian Chambers of Commerce and Industry Scheme (EDIRA compliant)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0109' => __( 'BellSouth ICD AESA (ATM End System Address)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0110' => __( 'Bell Atlantic', 'woocommerce-pdf-invoices-packing-slips' ),
			'0111' => __( 'Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0112' => __( 'ISO register for Standards producing Organizations', 'woocommerce-pdf-invoices-packing-slips' ),
			'0113' => __( 'OriginNet', 'woocommerce-pdf-invoices-packing-slips' ),
			'0114' => __( 'Check Point Software Technologies', 'woocommerce-pdf-invoices-packing-slips' ),
			'0115' => __( 'Pacific Bell Data Communications Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0116' => __( 'PSS Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0117' => __( 'STENTOR-ICD CODING SYSTEM', 'woocommerce-pdf-invoices-packing-slips' ),
			'0118' => __( "ATM-Network ZN'96", 'woocommerce-pdf-invoices-packing-slips' ),
			'0119' => __( 'MCI / OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0120' => __( 'Advantis', 'woocommerce-pdf-invoices-packing-slips' ),
			'0121' => __( 'Affable Software Data Interchange Codes', 'woocommerce-pdf-invoices-packing-slips' ),
			'0122' => __( 'BB-DATA GmbH', 'woocommerce-pdf-invoices-packing-slips' ),
			'0123' => __( 'BASF Company ATM-Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0124' => __( 'IOTA Identifiers for Organizations for Telecommunications Addressing using the ICD system format defined in ISO/IEC 8348', 'woocommerce-pdf-invoices-packing-slips' ),
			'0125' => __( 'Henkel Corporate Network (H-Net)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0126' => __( 'GTE/OSI Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0127' => __( 'Dresdner Bank Corporate Network', 'woocommerce-pdf-invoices-packing-slips' ),
			'0128' => __( 'BCNR (Swiss Clearing Bank Number)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0129' => __( 'BPI (Swiss Business Partner Identification) code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0130' => __( 'Directorates of the European Commission', 'woocommerce-pdf-invoices-packing-slips' ),
			'0131' => __( 'Code for the Identification of National Organizations', 'woocommerce-pdf-invoices-packing-slips' ),
			'0132' => __( 'Certicom Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0133' => __( 'TC68 OID', 'woocommerce-pdf-invoices-packing-slips' ),
			'0134' => __( 'Infonet Services Corporation', 'woocommerce-pdf-invoices-packing-slips' ),
			'0135' => __( 'SIA Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0136' => __( 'Cable & Wireless Global ATM End-System Address Plan', 'woocommerce-pdf-invoices-packing-slips' ),
			'0137' => __( 'Global AESA scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0138' => __( 'France Telecom ATM End System Address Plan', 'woocommerce-pdf-invoices-packing-slips' ),
			'0139' => __( 'Savvis Communications AESA:.', 'woocommerce-pdf-invoices-packing-slips' ),
			'0140' => __( "Toshiba Organizations, Partners, And Suppliers' (TOPAS) Code", 'woocommerce-pdf-invoices-packing-slips' ),
			'0141' => __( 'NATO Commercial and Government Entity system', 'woocommerce-pdf-invoices-packing-slips' ),
			'0142' => __( 'SECETI Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0143' => __( 'EINESTEINet AG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0144' => __( 'DoDAAC (Department of Defense Activity Address Code)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0145' => __( 'DGCP (Direction Générale de la Comptabilité Publique)administrative accounting identification scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0146' => __( 'DGI (Direction Générale des Impots) code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0147' => __( 'Standard Company Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0148' => __( 'ITU (International Telecommunications Union)Data Network Identification Codes (DNIC)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0149' => __( 'Global Business Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0150' => __( 'Madge Networks Ltd- ICD ATM Addressing Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0151' => __( 'Australian Business Number (ABN) Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0152' => __( 'Edira Scheme Identifier Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0153' => __( 'Concert Global Network Services ICD AESA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0154' => __( 'Identification number of economic subjects: (ICO)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0155' => __( 'Global Crossing AESA (ATM End System Address)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0156' => __( 'AUNA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0157' => __( 'ATM interconnection with the Dutch KPN Telecom', 'woocommerce-pdf-invoices-packing-slips' ),
			'0158' => __( "Identification number of economic subject (ICO) Act on State Statistics of 29 November 2'001, § 27", 'woocommerce-pdf-invoices-packing-slips' ),
			'0159' => __( 'ACTALIS Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0160' => __( 'GTIN - Global Trade Item Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'0161' => __( 'ECCMA Open Technical Directory', 'woocommerce-pdf-invoices-packing-slips' ),
			'0162' => __( 'CEN/ISSS Object Identifier Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0163' => __( 'US-EPA Facility Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0164' => __( 'TELUS Corporation', 'woocommerce-pdf-invoices-packing-slips' ),
			'0165' => __( 'FIEIE Object identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0166' => __( 'Swissguide Identifier Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0167' => __( 'Priority Telecom ATM End System Address Plan', 'woocommerce-pdf-invoices-packing-slips' ),
			'0168' => __( 'Vodafone Ireland OSI Addressing', 'woocommerce-pdf-invoices-packing-slips' ),
			'0169' => __( 'Swiss Federal Business Identification Number. Central Business names Index (zefix) Identification Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'0170' => __( 'Teikoku Company Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0171' => __( 'Luxembourg CP & CPS (Certification Policy and Certification Practice Statement) Index', 'woocommerce-pdf-invoices-packing-slips' ),
			'0172' => __( 'Project Group “Lists of Properties” (PROLIST®)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0173' => __( 'eCI@ss', 'woocommerce-pdf-invoices-packing-slips' ),
			'0174' => __( 'StepNexus', 'woocommerce-pdf-invoices-packing-slips' ),
			'0175' => __( 'Siemens AG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0176' => __( 'Paradine GmbH', 'woocommerce-pdf-invoices-packing-slips' ),
			'0177' => __( 'Odette International Limited', 'woocommerce-pdf-invoices-packing-slips' ),
			'0178' => __( 'Route1 MobiNET', 'woocommerce-pdf-invoices-packing-slips' ),
			'0179' => __( 'Penango Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0180' => __( 'Lithuanian military PKI', 'woocommerce-pdf-invoices-packing-slips' ),
			'0183' => __( "Numéro d'identification suisse des enterprises (IDE), Swiss Unique Business Identification Number (UIDB)", 'woocommerce-pdf-invoices-packing-slips' ),
			'0184' => __( 'DIGSTORG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0185' => __( 'Perceval Object Code', 'woocommerce-pdf-invoices-packing-slips' ),
			'0186' => __( 'TrustPoint Object Identifiers', 'woocommerce-pdf-invoices-packing-slips' ),
			'0187' => __( 'Amazon Unique Identification Scheme', 'woocommerce-pdf-invoices-packing-slips' ),
			'0188' => __( 'Corporate Number of The Social Security and Tax Number System', 'woocommerce-pdf-invoices-packing-slips' ),
			'0189' => __( 'European Business Identifier (EBID)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0190' => __( 'Organisatie Indentificatie Nummer (OIN)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0191' => __( 'Company Code (Estonia)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0192' => __( 'Organisasjonsnummer', 'woocommerce-pdf-invoices-packing-slips' ),
			'0193' => __( 'UBL.BE Party Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0194' => __( 'KOIOS Open Technical Dictionary', 'woocommerce-pdf-invoices-packing-slips' ),
			'0195' => __( 'Singapore Nationwide E-lnvoice Framework', 'woocommerce-pdf-invoices-packing-slips' ),
			'0196' => __( 'Icelandic identifier - Íslensk kennitala', 'woocommerce-pdf-invoices-packing-slips' ),
			'0197' => __( 'APPLiA Pl Standard', 'woocommerce-pdf-invoices-packing-slips' ),
			'0198' => __( 'ERSTORG', 'woocommerce-pdf-invoices-packing-slips' ),
			'0199' => __( 'Legal Entity Identifier (LEI)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0200' => __( 'Legal entity code (Lithuania)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0201' => __( 'Codice Univoco Unità Organizzativa iPA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0202' => __( 'Indirizzo di Posta Elettronica Certificata', 'woocommerce-pdf-invoices-packing-slips' ),
			'0203' => __( 'eDelivery Network Participant identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0204' => __( 'Leitweg-ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'0205' => __( 'CODDEST', 'woocommerce-pdf-invoices-packing-slips' ),
			'0206' => __( 'Registre du Commerce et de l’Industrie : RCI', 'woocommerce-pdf-invoices-packing-slips' ),
			'0207' => __( 'PiLog Ontology Codification Identifier (POCI)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0208' => __( "Numero d'entreprise / ondernemingsnummer / Unternehmensnummer", 'woocommerce-pdf-invoices-packing-slips' ),
			'0209' => __( 'GS1 identification keys', 'woocommerce-pdf-invoices-packing-slips' ),
			'0210' => __( 'CODICE FISCALE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0211' => __( 'PARTITA IVA', 'woocommerce-pdf-invoices-packing-slips' ),
			'0212' => __( 'Finnish Organization Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0213' => __( 'Finnish Organization Value Add Tax Identifier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0214' => __( 'Tradeplace TradePI Standard', 'woocommerce-pdf-invoices-packing-slips' ),
			'0215' => __( 'Net service ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'0216' => __( 'OVTcode', 'woocommerce-pdf-invoices-packing-slips' ),
			'0217' => __( 'The Netherlands Chamber of Commerce and Industry establishment number', 'woocommerce-pdf-invoices-packing-slips' ),
			'0218' => __( 'Unified registration number (Latvia)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0219' => __( 'Taxpayer registration code (Latvia)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0220' => __( 'The Register of Natural Persons (Latvia)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0221' => __( 'The registered number of the qualified invoice issuer', 'woocommerce-pdf-invoices-packing-slips' ),
			'0222' => __( 'Metadata Registry Support', 'woocommerce-pdf-invoices-packing-slips' ),
			'0223' => __( 'EU based company', 'woocommerce-pdf-invoices-packing-slips' ),
			'0224' => __( 'FTCTC CODE ROUTAGE', 'woocommerce-pdf-invoices-packing-slips' ),
			'0225' => __( 'FRCTC ELECTRONIC ADDRESS', 'woocommerce-pdf-invoices-packing-slips' ),
			'0226' => __( 'FRCTC Particulier', 'woocommerce-pdf-invoices-packing-slips' ),
			'0227' => __( 'NON - EU based company', 'woocommerce-pdf-invoices-packing-slips' ),
			'0228' => __( 'Répertoire des Entreprises et des Etablissements (RIDET)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0229' => __( 'T.A.H.I.T.I (traitement automatique hiérarchisé des institutions de Tahiti et des îles)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0230' => __( 'National e-Invoicing Framework', 'woocommerce-pdf-invoices-packing-slips' ),
			'0231' => __( 'Single taxable company (France)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0232' => __( 'NOBB product number', 'woocommerce-pdf-invoices-packing-slips' ),
			'0233' => __( 'Elnummer', 'woocommerce-pdf-invoices-packing-slips' ),
			'0234' => __( 'Toimitusosoite ID', 'woocommerce-pdf-invoices-packing-slips' ),
			'0235' => __( 'UAE Tax Identification Number (TIN)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0236' => __( 'ToimipaikkalD', 'woocommerce-pdf-invoices-packing-slips' ),
			'0237' => __( 'CPR (Danish person civil registration number)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0238' => __( 'Plateforme.s agréée.s à la facturation électronique (PPF/PDP)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0239' => __( 'EAEU', 'woocommerce-pdf-invoices-packing-slips' ),
			'0240' => __( 'Register of legal persons (in French : Répertoire des personnes morales)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0241' => __( 'Name unknown', 'woocommerce-pdf-invoices-packing-slips' ),
			'0242' => __( 'OpenPeppol Service Provider Identification Scheme (SPIS)', 'woocommerce-pdf-invoices-packing-slips' ),
			'0243' => __( 'Name unknown', 'woocommerce-pdf-invoices-packing-slips' ),
			'0244' => __( 'Tax Identification (Tax ID), Nigeria', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_icd', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get the Payment Meanbs code according to code list.
	 * 
	 * - Column [Payment]
	 *
	 * @return array
	 */
	public static function get_payment(): array {
		$defaults = array(
			'1'   => __( 'Instrument not defined', 'woocommerce-pdf-invoices-packing-slips' ),
			'2'   => __( 'Automated clearing house credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'3'   => __( 'Automated clearing house debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'4'   => __( 'ACH demand debit reversal', 'woocommerce-pdf-invoices-packing-slips' ),
			'5'   => __( 'ACH demand credit reversal', 'woocommerce-pdf-invoices-packing-slips' ),
			'6'   => __( 'ACH demand credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'7'   => __( 'ACH demand debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'8'   => __( 'Hold', 'woocommerce-pdf-invoices-packing-slips' ),
			'9'   => __( 'National or regional clearing', 'woocommerce-pdf-invoices-packing-slips' ),
			'10'  => __( 'In cash', 'woocommerce-pdf-invoices-packing-slips' ),
			'11'  => __( 'ACH savings credit reversal', 'woocommerce-pdf-invoices-packing-slips' ),
			'12'  => __( 'ACH savings debit reversal', 'woocommerce-pdf-invoices-packing-slips' ),
			'13'  => __( 'ACH savings credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'14'  => __( 'ACH savings debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'15'  => __( 'Bookentry credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'16'  => __( 'Bookentry debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'17'  => __( 'ACH demand cash concentration/disbursement (CCD) credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'18'  => __( 'ACH demand cash concentration/disbursement (CCD) debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'19'  => __( 'ACH demand corporate trade payment (CTP) credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'20'  => __( 'Cheque', 'woocommerce-pdf-invoices-packing-slips' ),
			'21'  => __( "Banker's draft", 'woocommerce-pdf-invoices-packing-slips' ),
			'22'  => __( "Certified banker's draft", 'woocommerce-pdf-invoices-packing-slips' ),
			'23'  => __( 'Bank cheque (issued by a banking or similar establishment)', 'woocommerce-pdf-invoices-packing-slips' ),
			'24'  => __( 'Bill of exchange awaiting acceptance', 'woocommerce-pdf-invoices-packing-slips' ),
			'25'  => __( 'Certified cheque', 'woocommerce-pdf-invoices-packing-slips' ),
			'26'  => __( 'Local cheque', 'woocommerce-pdf-invoices-packing-slips' ),
			'27'  => __( 'ACH demand corporate trade payment (CTP) debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'28'  => __( 'ACH demand corporate trade exchange (CTX) credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'29'  => __( 'ACH demand corporate trade exchange (CTX) debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'30'  => __( 'Credit transfer', 'woocommerce-pdf-invoices-packing-slips' ),
			'31'  => __( 'Debit transfer', 'woocommerce-pdf-invoices-packing-slips' ),
			'32'  => __( 'ACH demand cash concentration/disbursement plus (CCD+)', 'woocommerce-pdf-invoices-packing-slips' ),
			'33'  => __( 'ACH demand cash concentration/disbursement plus (CCD+)', 'woocommerce-pdf-invoices-packing-slips' ),
			'34'  => __( 'ACH prearranged payment and deposit (PPD)', 'woocommerce-pdf-invoices-packing-slips' ),
			'35'  => __( 'ACH savings cash concentration/disbursement (CCD) credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'36'  => __( 'ACH savings cash concentration/disbursement (CCD) debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'37'  => __( 'ACH savings corporate trade payment (CTP) credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'38'  => __( 'ACH savings corporate trade payment (CTP) debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'39'  => __( 'ACH savings corporate trade exchange (CTX) credit', 'woocommerce-pdf-invoices-packing-slips' ),
			'40'  => __( 'ACH savings corporate trade exchange (CTX) debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'41'  => __( 'ACH savings cash concentration/disbursement plus (CCD+)', 'woocommerce-pdf-invoices-packing-slips' ),
			'42'  => __( 'Payment to bank account', 'woocommerce-pdf-invoices-packing-slips' ),
			'43'  => __( 'ACH savings cash concentration/disbursement plus (CCD+)', 'woocommerce-pdf-invoices-packing-slips' ),
			'44'  => __( 'Accepted bill of exchange', 'woocommerce-pdf-invoices-packing-slips' ),
			'45'  => __( 'Referenced home-banking credit transfer', 'woocommerce-pdf-invoices-packing-slips' ),
			'46'  => __( 'Interbank debit transfer', 'woocommerce-pdf-invoices-packing-slips' ),
			'47'  => __( 'Home-banking debit transfer', 'woocommerce-pdf-invoices-packing-slips' ),
			'48'  => __( 'Bank card', 'woocommerce-pdf-invoices-packing-slips' ),
			'49'  => __( 'Direct debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'50'  => __( 'Payment by postgiro', 'woocommerce-pdf-invoices-packing-slips' ),
			'51'  => __( 'FR, norme 6 97-Telereglement CFONB (French Organisation for', 'woocommerce-pdf-invoices-packing-slips' ),
			'52'  => __( 'Urgent commercial payment', 'woocommerce-pdf-invoices-packing-slips' ),
			'53'  => __( 'Urgent Treasury Payment', 'woocommerce-pdf-invoices-packing-slips' ),
			'54'  => __( 'Credit card', 'woocommerce-pdf-invoices-packing-slips' ),
			'55'  => __( 'Debit card', 'woocommerce-pdf-invoices-packing-slips' ),
			'56'  => __( 'Bankgiro', 'woocommerce-pdf-invoices-packing-slips' ),
			'57'  => __( 'Standing agreement', 'woocommerce-pdf-invoices-packing-slips' ),
			'58'  => __( 'SEPA credit transfer', 'woocommerce-pdf-invoices-packing-slips' ),
			'59'  => __( 'SEPA direct debit', 'woocommerce-pdf-invoices-packing-slips' ),
			'60'  => __( 'Promissory note', 'woocommerce-pdf-invoices-packing-slips' ),
			'61'  => __( 'Promissory note signed by the debtor', 'woocommerce-pdf-invoices-packing-slips' ),
			'62'  => __( 'Promissory note signed by the debtor and endorsed by a bank', 'woocommerce-pdf-invoices-packing-slips' ),
			'63'  => __( 'Promissory note signed by the debtor and endorsed by a', 'woocommerce-pdf-invoices-packing-slips' ),
			'64'  => __( 'Promissory note signed by a bank', 'woocommerce-pdf-invoices-packing-slips' ),
			'65'  => __( 'Promissory note signed by a bank and endorsed by another', 'woocommerce-pdf-invoices-packing-slips' ),
			'66'  => __( 'Promissory note signed by a third party', 'woocommerce-pdf-invoices-packing-slips' ),
			'67'  => __( 'Promissory note signed by a third party and endorsed by a', 'woocommerce-pdf-invoices-packing-slips' ),
			'68'  => __( 'Online payment service', 'woocommerce-pdf-invoices-packing-slips' ),
			'69'  => __( 'Transfer Advice', 'woocommerce-pdf-invoices-packing-slips' ),
			'70'  => __( 'Bill drawn by the creditor on the debtor', 'woocommerce-pdf-invoices-packing-slips' ),
			'74'  => __( 'Bill drawn by the creditor on a bank', 'woocommerce-pdf-invoices-packing-slips' ),
			'75'  => __( 'Bill drawn by the creditor, endorsed by another bank', 'woocommerce-pdf-invoices-packing-slips' ),
			'76'  => __( 'Bill drawn by the creditor on a bank and endorsed by a', 'woocommerce-pdf-invoices-packing-slips' ),
			'77'  => __( 'Bill drawn by the creditor on a third party', 'woocommerce-pdf-invoices-packing-slips' ),
			'78'  => __( 'Bill drawn by creditor on third party, accepted and', 'woocommerce-pdf-invoices-packing-slips' ),
			'91'  => __( "Not transferable banker's draft", 'woocommerce-pdf-invoices-packing-slips' ),
			'92'  => __( 'Not transferable local cheque', 'woocommerce-pdf-invoices-packing-slips' ),
			'93'  => __( 'Reference giro', 'woocommerce-pdf-invoices-packing-slips' ),
			'94'  => __( 'Urgent giro', 'woocommerce-pdf-invoices-packing-slips' ),
			'95'  => __( 'Free format giro', 'woocommerce-pdf-invoices-packing-slips' ),
			'96'  => __( 'Requested method for payment was not used', 'woocommerce-pdf-invoices-packing-slips' ),
			'97'  => __( 'Clearing between partners', 'woocommerce-pdf-invoices-packing-slips' ),
			'98'  => __( 'JP, Electronically Recorded Monetary Claims', 'woocommerce-pdf-invoices-packing-slips' ),
			'ZZZ' => __( 'Mutually defined', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		$extra = (array) apply_filters( 'wpo_ips_edi_en16931_payment', array() );

		return $extra + $defaults;
	}

}
