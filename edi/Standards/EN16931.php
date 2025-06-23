<?php

namespace WPO\IPS\EDI\Standards;

use WPO\IPS\EDI\Abstracts\AbstractStandard;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class EN16931 extends AbstractStandard {

	public static string $slug    = 'en16931';
	public static string $name    = 'EN16931';
	public static string $version = '15.0';

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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_' . self::$slug . '_vat_cat', array() );

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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_' . self::$slug . '_5305', array() );

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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_' . self::$slug . '_vatex', array() );

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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_' . self::$slug . '_vatex_remarks', array() );

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
		
		$extra = (array) apply_filters( 'wpo_ips_edi_' . self::$slug . '_eas', array() );

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
			'0002' => 'System Information et Repertoire des Entreprise et des Etablissements: SIRENE',
			'0003' => 'Codification Numerique des Etablissments Financiers En Belgique',
			'0004' => 'NBS/OSI NETWORK',
			'0005' => 'USA FED GOV OSI NETWORK',
			'0006' => 'USA DOD OSI NETWORK',
			'0007' => 'Organisationsnummer',
			'0008' => 'LE NUMERO NATIONAL',
			'0009' => 'SIRET-CODE',
			'0010' => 'Organizational Identifiers for Structured Names under ISO 9541 Part 2',
			'0011' => 'International Code Designator for the Identification of OSI-based, Amateur Radio Organizations, Network Objects and Application Services.',
			'0012' => 'European Computer Manufacturers Association: ECMA',
			'0013' => 'VSA FTP CODE (FTP = File Transfer Protocol)',
			'0014' => 'NIST/OSI Implememts\' Workshop',
			'0015' => 'Electronic Data Interchange: EDI',
			'0016' => 'EWOS Object Identifiers',
			'0017' => 'COMMON LANGUAGE',
			'0018' => 'SNA/OSI Network',
			'0019' => 'Air Transport Industry Services Communications Network',
			'0020' => 'European Laboratory for Particle Physics: CERN',
			'0021' => 'SOCIETY FOR WORLDWIDE INTERBANK FINANCIAL, TELECOMMUNICATION S.W.I.F.T.',
			'0022' => 'OSF Distributed Computing Object Identification',
			'0023' => 'Nordic University and Research Network: NORDUnet',
			'0024' => 'Digital Equipment Corporation: DEC',
			'0025' => 'OSI ASIA-OCEANIA WORKSHOP',
			'0026' => 'NATO ISO 6523 ICDE coding scheme',
			'0027' => 'Aeronautical Telecommunications Network (ATN)',
			'0028' => 'International Standard ISO 6523',
			'0029' => 'The All-Union Classifier of Enterprises and Organisations',
			'0030' => 'AT&T/OSI Network',
			'0031' => 'EDI Partner Identification Code',
			'0032' => 'Telecom Australia',
			'0033' => 'S G W OSI Internetwork',
			'0034' => 'Reuter Open Address Standard',
			'0035' => 'ISO 6523 - ICD',
			'0036' => 'TeleTrust Object Identifiers',
			'0037' => 'LY-tunnus',
			'0038' => 'The Australian GOSIP Network',
			'0039' => 'The OZ DOD OSI Network',
			'0040' => 'Unilever Group Companies',
			'0041' => 'Citicorp Global Information Network',
			'0042' => 'DBP Telekom Object Identifiers',
			'0043' => 'HydroNETT',
			'0044' => 'Thai Industrial Standards Institute (TISI)',
			'0045' => 'ICI Company Identification System',
			'0046' => 'FUNLOC',
			'0047' => 'BULL ODI/DSA/UNIX Network',
			'0048' => 'OSINZ',
			'0049' => 'Auckland Area Health',
			'0050' => 'Firmenich',
			'0051' => 'AGFA-DIS',
			'0052' => 'Society of Motion Picture and Television Engineers (SMPTE)',
			'0053' => 'Migros_Network M_NETOPZ',
			'0054' => 'ISO6523 - ICDPCR',
			'0055' => 'Energy Net',
			'0056' => 'Nokia Object Identifiers (NOI)',
			'0057' => 'Saint Gobain',
			'0058' => 'Siemens Corporate Network',
			'0059' => 'DANZNET',
			'0060' => 'Data Universal Numbering System (D-U-N-S Number)',
			'0061' => 'SOFFEX OSI',
			'0062' => 'KPN OVN',
			'0063' => 'ascomOSINet',
			'0064' => 'UTC: Uniforme Transport Code',
			'0065' => 'SOLVAY OSI CODING',
			'0066' => 'Roche Corporate Network',
			'0067' => 'ZellwegerOSINet',
			'0068' => 'Intel Corporation OSI',
			'0069' => 'SITA Object Identifier Tree',
			'0070' => 'DaimlerChrysler Corporate Network',
			'0071' => 'LEGO /OSI NETWORK',
			'0072' => 'NAVISTAR/OSI Network',
			'0073' => 'ICD Formatted ATM address',
			'0074' => 'ARINC',
			'0075' => 'Alcanet/Alcatel-Alsthom Corporate Network',
			'0076' => 'Sistema Italiano di Identificazione di ogetti gestito da UNINFO',
			'0077' => 'Sistema Italiano di Indirizzamento di Reti OSI Gestito da UNINFO',
			'0078' => 'Mitel terminal or switching equipment',
			'0079' => 'ATM Forum',
			'0080' => 'UK National Health Service Scheme, (EDIRA compliant)',
			'0081' => 'International NSAP',
			'0082' => "Norwegian Telecommunications Authority's, NTA'S, EDI, identifier scheme (EDIRA compliant)",
			'0083' => 'Advanced Telecommunications Modules Limited, Corporate Network',
			'0084' => 'Athens Chamber of Commerce & Industry Scheme (EDIRA compliant)',
			'0085' => 'Swiss Chambers of Commerce Scheme (EDIRA) compliant',
			'0086' => 'United States Council for International Business (USCIB) Scheme, (EDIRA compliant)',
			'0087' => 'National Federation of Chambers of Commerce & Industry of Belgium, Scheme (EDIRA compliant)',
			'0088' => 'EAN Location Code',
			'0089' => 'The Association of British Chambers of Commerce Ltd. Scheme, (EDIRA compliant)',
			'0090' => 'Internet IP addressing - ISO 6523 ICD encoding',
			'0091' => 'Cisco Sysytems / OSI Network',
			'0093' => 'Revenue Canada Business Number Registration (EDIRA compliant)',
			'0094' => 'DEUTSCHER INDUSTRIE- UND HANDELSTAG (DIHT) Scheme (EDIRA compliant)',
			'0095' => 'Hewlett - Packard Company Internal AM Network',
			'0096' => 'The Danish Business Authority - P-number (DK:P)',
			'0097' => 'FTI - Ediforum Italia, (EDIRA compliant)',
			'0098' => 'CHAMBER OF COMMERCE TEL AVIV-JAFFA Scheme (EDIRA compliant)',
			'0099' => 'Siemens Supervisory Systems Network',
			'0100' => 'PNG_ICD Scheme',
			'0101' => 'South African Code Allocation',
			'0102' => 'HEAG',
			'0104' => 'BT - ICD Coding System',
			'0105' => 'Portuguese Chamber of Commerce and Industry Scheme (EDIRA compliant)',
			'0106' => 'Vereniging van Kamers van Koophandel en Fabrieken in Nederland (Association of Chambers of Commerce and Industry in the Netherlands), Scheme (EDIRA compliant)',
			'0107' => 'Association of Swedish Chambers of Commerce and Industry Scheme (EDIRA compliant)',
			'0108' => 'Australian Chambers of Commerce and Industry Scheme (EDIRA compliant)',
			'0109' => 'BellSouth ICD AESA (ATM End System Address)',
			'0110' => 'Bell Atlantic',
			'0111' => 'Object Identifiers',
			'0112' => 'ISO register for Standards producing Organizations',
			'0113' => 'OriginNet',
			'0114' => 'Check Point Software Technologies',
			'0115' => 'Pacific Bell Data Communications Network',
			'0116' => 'PSS Object Identifiers',
			'0117' => 'STENTOR-ICD CODING SYSTEM',
			'0118' => "ATM-Network ZN'96",
			'0119' => 'MCI / OSI Network',
			'0120' => 'Advantis',
			'0121' => 'Affable Software Data Interchange Codes',
			'0122' => 'BB-DATA GmbH',
			'0123' => 'BASF Company ATM-Network',
			'0124' => 'IOTA Identifiers for Organizations for Telecommunications Addressing using the ICD system format defined in ISO/IEC 8348',
			'0125' => 'Henkel Corporate Network (H-Net)',
			'0126' => 'GTE/OSI Network',
			'0127' => 'Dresdner Bank Corporate Network',
			'0128' => 'BCNR (Swiss Clearing Bank Number)',
			'0129' => 'BPI (Swiss Business Partner Identification) code',
			'0130' => 'Directorates of the European Commission',
			'0131' => 'Code for the Identification of National Organizations',
			'0132' => 'Certicom Object Identifiers',
			'0133' => 'TC68 OID',
			'0134' => 'Infonet Services Corporation',
			'0135' => 'SIA Object Identifiers',
			'0136' => 'Cable & Wireless Global ATM End-System Address Plan',
			'0137' => 'Global AESA scheme',
			'0138' => 'France Telecom ATM End System Address Plan',
			'0139' => 'Savvis Communications AESA:.',
			'0140' => "Toshiba Organizations, Partners, And Suppliers' (TOPAS) Code",
			'0141' => 'NATO Commercial and Government Entity system',
			'0142' => 'SECETI Object Identifiers',
			'0143' => 'EINESTEINet AG',
			'0144' => 'DoDAAC (Department of Defense Activity Address Code)',
			'0145' => 'DGCP (Direction Générale de la Comptabilité Publique)administrative accounting identification scheme',
			'0146' => 'DGI (Direction Générale des Impots) code',
			'0147' => 'Standard Company Code',
			'0148' => 'ITU (International Telecommunications Union)Data Network Identification Codes (DNIC)',
			'0149' => 'Global Business Identifier',
			'0150' => 'Madge Networks Ltd- ICD ATM Addressing Scheme',
			'0151' => 'Australian Business Number (ABN) Scheme',
			'0152' => 'Edira Scheme Identifier Code',
			'0153' => 'Concert Global Network Services ICD AESA',
			'0154' => 'Identification number of economic subjects: (ICO)',
			'0155' => 'Global Crossing AESA (ATM End System Address)',
			'0156' => 'AUNA',
			'0157' => 'ATM interconnection with the Dutch KPN Telecom',
			'0158' => "Identification number of economic subject (ICO) Act on State Statistics of 29 November 2'001, § 27",
			'0159' => 'ACTALIS Object Identifiers',
			'0160' => 'GTIN - Global Trade Item Number',
			'0161' => 'ECCMA Open Technical Directory',
			'0162' => 'CEN/ISSS Object Identifier Scheme',
			'0163' => 'US-EPA Facility Identifier',
			'0164' => 'TELUS Corporation',
			'0165' => 'FIEIE Object identifiers',
			'0166' => 'Swissguide Identifier Scheme',
			'0167' => 'Priority Telecom ATM End System Address Plan',
			'0168' => 'Vodafone Ireland OSI Addressing',
			'0169' => 'Swiss Federal Business Identification Number. Central Business names Index (zefix) Identification Number',
			'0170' => 'Teikoku Company Code',
			'0171' => 'Luxembourg CP & CPS (Certification Policy and Certification Practice Statement) Index',
			'0172' => 'Project Group “Lists of Properties” (PROLIST®)',
			'0173' => 'eCI@ss',
			'0174' => 'StepNexus',
			'0175' => 'Siemens AG',
			'0176' => 'Paradine GmbH',
			'0177' => 'Odette International Limited',
			'0178' => 'Route1 MobiNET',
			'0179' => 'Penango Object Identifiers',
			'0180' => 'Lithuanian military PKI',
			'0183' => "Numéro d'identification suisse des enterprises (IDE), Swiss Unique Business Identification Number (UIDB)",
			'0184' => 'DIGSTORG',
			'0185' => 'Perceval Object Code',
			'0186' => 'TrustPoint Object Identifiers',
			'0187' => 'Amazon Unique Identification Scheme',
			'0188' => 'Corporate Number of The Social Security and Tax Number System',
			'0189' => 'European Business Identifier (EBID)',
			'0190' => 'Organisatie Indentificatie Nummer (OIN)',
			'0191' => 'Company Code (Estonia)',
			'0192' => 'Organisasjonsnummer',
			'0193' => 'UBL.BE Party Identifier',
			'0194' => 'KOIOS Open Technical Dictionary',
			'0195' => 'Singapore Nationwide E-lnvoice Framework',
			'0196' => 'Icelandic identifier - Íslensk kennitala',
			'0197' => 'APPLiA Pl Standard',
			'0198' => 'ERSTORG',
			'0199' => 'Legal Entity Identifier (LEI)',
			'0200' => 'Legal entity code (Lithuania)',
			'0201' => 'Codice Univoco Unità Organizzativa iPA',
			'0202' => 'Indirizzo di Posta Elettronica Certificata',
			'0203' => 'eDelivery Network Participant identifier',
			'0204' => 'Leitweg-ID',
			'0205' => 'CODDEST',
			'0206' => 'Registre du Commerce et de l’Industrie : RCI',
			'0207' => 'PiLog Ontology Codification Identifier (POCI)',
			'0208' => "Numero d'entreprise / ondernemingsnummer / Unternehmensnummer",
			'0209' => 'GS1 identification keys',
			'0210' => 'CODICE FISCALE',
			'0211' => 'PARTITA IVA',
			'0212' => 'Finnish Organization Identifier',
			'0213' => 'Finnish Organization Value Add Tax Identifier',
			'0214' => 'Tradeplace TradePI Standard',
			'0215' => 'Net service ID',
			'0216' => 'OVTcode',
			'0217' => 'The Netherlands Chamber of Commerce and Industry establishment number',
			'0218' => 'Unified registration number (Latvia)',
			'0219' => 'Taxpayer registration code (Latvia)',
			'0220' => 'The Register of Natural Persons (Latvia)',
			'0221' => 'The registered number of the qualified invoice issuer',
			'0222' => 'Metadata Registry Support',
			'0223' => 'EU based company',
			'0224' => 'FTCTC CODE ROUTAGE',
			'0225' => 'FRCTC ELECTRONIC ADDRESS',
			'0226' => 'FRCTC Particulier',
			'0227' => 'NON - EU based company',
			'0228' => 'Répertoire des Entreprises et des Etablissements (RIDET)',
			'0229' => 'T.A.H.I.T.I (traitement automatique hiérarchisé des institutions de Tahiti et des îles)',
			'0230' => 'National e-Invoicing Framework',
			'0231' => 'Single taxable company (France)',
			'0232' => 'NOBB product number',
			'0233' => 'Elnummer',
			'0234' => 'Toimitusosoite ID',
			'0235' => 'UAE Tax Identification Number (TIN)',
			'0236' => 'ToimipaikkalD',
			'0237' => 'CPR (Danish person civil registration number)',
			'0238' => 'Plateforme.s agréée.s à la facturation électronique (PPF/PDP)',
			'0239' => 'EAEU',
			'0240' => 'Register of legal persons (in French : Répertoire des personnes morales)',
		);
		
		$extra = (array) apply_filters( 'wpo_ips_edi_' . self::$slug . '_icd', array() );

		return $extra + $defaults;
	}

}
