<?php

namespace WPO\IPS\EDI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EN16931 {

	/**
	 * The standard.
	 *
	 * @var string
	 */
	public static string $standard = 'EN16931';
	
	/**
	 * The version of the standard.
	 *
	 * @var string
	 */
	public static string $standard_version = '15.0';

	/**
	 * Get available tax schemes according to standard.
	 * 
	 * @return array
	 */
	public static function get_available_schemes(): array {
		$defaults = array(
			'VAT' => __( 'Value added tax (VAT)', 'woocommerce-pdf-invoices-packing-slips' ),
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_tax_schemes', array() );

		return $extra + $defaults;
	}

	/**
	 * Get available VAT tax categories according to standard 5305 code list.
	 *
	 * @return array
	 */
	public static function get_available_categories(): array {
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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_tax_categories', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get available VAT exemption reasons according to standard VATEX code list.
	 *
	 * @return array
	 */
	public static function get_available_reasons(): array {
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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_tax_reasons', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get available VAT exemption remarks according to standard VATEX codes.
	 *
	 * @return array
	 */
	public static function get_available_remarks(): array {
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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_tax_remarks', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get available electronic address schemes according to standard.
	 *
	 * @return array
	 */
	public static function get_electronic_address_schemes(): array {
		$defaults = array(
			'0002' => 'System Information et Repertoire des Entreprise et des Etablissements: SIRENE',
			'0007' => 'Organisationsnummer',
			'0009' => 'SIRET-CODE',
			'0037' => 'LY-tunnus',
			'0060' => 'Data Universal Numbering System (D-U-N-S Number)',
			'0088' => 'EAN Location Code',
			'0096' => 'The Danish Business Authority - P-number (DK:P)',
			'0097' => 'FTI - Ediforum Italia, (EDIRA compliant)',
			'0106' => 'Vereniging van Kamers van Koophandel en Fabrieken in Nederland (Association of Chambers of Commerce and Industry in the Netherlands), Scheme (EDIRA compliant)',
			'0130' => 'Directorates of the European Commission',
			'0135' => 'SIA Object Identifiers',
			'0142' => 'SECETI Object Identifiers',
			'0147' => 'Standard Company Code',
			'0151' => 'Australian Business Number (ABN) Scheme',
			'0154' => 'Identification number of economic subjects: (ICO)',
			'0158' => 'Identification number of economic subject (ICO) Act on State Statistics of 29 November 2001, § 27',
			'0170' => 'Teikoku Company Code',
			'0177' => 'Odette International Limited',
			'0183' => "Numéro d'identification suisse des enterprises (IDE), Swiss Unique Business Identification Number (UIDB)",
			'0184' => 'DIGSTORG',
			'0188' => 'Corporate Number of The Social Security and Tax Number System',
			'0190' => "Dutch Originator's Identification Number",
			'0191' => 'Centre of Registers and Information Systems of the Ministry of Justice',
			'0192' => 'Enhetsregisteret ved Bronnoysundregisterne',
			'0193' => 'UBL.BE party identifier',
			'0194' => 'KOIOS Open Technical Dictionary',
			'0195' => 'Singapore UEN identifier',
			'0196' => 'Kennitala - Iceland legal id for individuals and legal entities',
			'0198' => 'ERSTORG',
			'0199' => 'Global legal entity identifier (GLEIF)',
			'0200' => 'Legal entity code (Lithuania)',
			'0201' => 'Codice Univoco Unità Organizzativa iPA',
			'0202' => 'Indirizzo di Posta Elettronica Certificata',
			'0203' => 'eDelivery Network Participant identifier',
			'0204' => 'Leitweg-ID',
			'0205' => 'CODDEST',
			'0208' => "Numero d'entreprise / ondernemingsnummer / Unternehmensnummer",
			'0209' => 'GS1 identification keys',
			'0210' => 'CODICE FISCALE',
			'0211' => 'PARTITA IVA',
			'0212' => 'Finnish Organization Identifier',
			'0213' => 'Finnish Organization Value Add Tax Identifier',
			'0215' => 'Net service ID',
			'0216' => 'OVTcode',
			'0217' => 'The Netherlands Chamber of Commerce and Industry establishment number',
			'0218' => 'Unified registration number (Latvia)',
			'0221' => 'The registered number of the qualified invoice issuer',
			'0225' => 'FRCTC ELECTRONIC ADDRESS',
			'0230' => 'National e-Invoicing Framework',
			'0235' => 'UAE Tax Identification Number (TIN)',
			'0240' => 'Register of legal persons (in French : Répertoire des personnes morales)',
			'9910' => 'Hungary VAT number',
			'9913' => 'Business Registers Network',
			'9914' => 'Österreichische Umsatzsteuer-Identifikationsnummer',
			'9915' => 'Österreichisches Verwaltungs bzw. Organisationskennzeichen',
			'9918' => 'SOCIETY FOR WORLDWIDE INTERBANK FINANCIAL, TELECOMMUNICATION S.W.I.F.T',
			'9919' => 'Kennziffer des Unternehmensregisters',
			'9920' => 'Agencia Española de Administración Tributaria',
			'9922' => 'Andorra VAT number',
			'9923' => 'Albania VAT number',
			'9924' => 'Bosnia and Herzegovina VAT number',
			'9925' => 'Belgium VAT number',
			'9926' => 'Bulgaria VAT number',
			'9927' => 'Switzerland VAT number',
			'9928' => 'Cyprus VAT number',
			'9929' => 'Czech Republic VAT number',
			'9930' => 'Germany VAT number',
			'9931' => 'Estonia VAT number',
			'9932' => 'United Kingdom VAT number',
			'9933' => 'Greece VAT number',
			'9934' => 'Croatia VAT number',
			'9935' => 'Ireland VAT number',
			'9936' => 'Liechtenstein VAT number',
			'9937' => 'Lithuania VAT number',
			'9938' => 'Luxemburg VAT number',
			'9939' => 'Latvia VAT number',
			'9940' => 'Monaco VAT number',
			'9941' => 'Montenegro VAT number',
			'9942' => 'Macedonia, the former Yugoslav Republic of VAT number',
			'9943' => 'Malta VAT number',
			'9944' => 'Netherlands VAT number',
			'9945' => 'Poland VAT number',
			'9946' => 'Portugal VAT number',
			'9947' => 'Romania VAT number',
			'9948' => 'Serbia VAT number',
			'9949' => 'Slovenia VAT number',
			'9950' => 'Slovakia VAT number',
			'9951' => 'San Marino VAT number',
			'9952' => 'Turkey VAT number',
			'9953' => 'Holy See (Vatican City State) VAT number',
			'9957' => 'French VAT number',
			'9959' => 'Employer Identification Number (EIN, USA)',
			'AN'   => 'O.F.T.P. (ODETTE File Transfer Protocol)',
			'AQ'   => 'X.400 address for mail text',
			'AS'   => 'AS2 exchange',
			'AU'   => 'File Transfer Protocol',
			'EM'   => 'Electronic mail (SMPT)',
		);
		
		foreach ( $defaults as $code => $description ) {
			$defaults[ $code ] = sprintf( '[%s] %s', $code, $description );
		}
		
		$extra = (array) apply_filters( 'wpo_ips_edi_electronic_address_schemes', array() );

		return $extra + $defaults;
	}
	
	/**
	 * Get changes from EN16931 version 15.0.
	 *
	 * @return array
	 */	
	public static function get_changes_from_EN16931_15_0(): array {
		return array(
			'Deprecated all tax schemes except VAT, which is the only one allowed by EN16931 v15.',
			'Deprecated tax category codes: A, AA, AB, AC, AD, B, C, D, F, H, I, J.',
			'Added VAT exemption reason codes: VATEX-EU-144, VATEX-EU-146-1E, VATEX-EU-151, VATEX-EU-153, VATEX-EU-159, VATEX-FR-CGI261-1, VATEX-FR-CGI261-2, VATEX-FR-CGI261-3, VATEX-FR-CGI261-4, VATEX-FR-CGI261-5, VATEX-FR-CGI261-7, VATEX-FR-CGI261-8, VATEX-FR-CGI261A, VATEX-FR-CGI261B, VATEX-FR-CGI261C-1, VATEX-FR-CGI261C-2, VATEX-FR-CGI261C-3, VATEX-FR-CGI261D-1, VATEX-FR-CGI261D-1BIS, VATEX-FR-CGI261D-2, VATEX-FR-CGI261D-3, VATEX-FR-CGI261D-4, VATEX-FR-CGI261E-1, VATEX-FR-CGI261E-2, VATEX-FR-CGI277A, VATEX-FR-CGI275, VATEX-FR-298SEXDECIESA, VATEX-FR-CGI295, VATEX-FR-AE.',
		);
	}

}
