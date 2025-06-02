<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = 'The hook "%s" is deprecated since version %s. Use "%s" instead.';

if ( has_action( 'wpo_wc_ubl_document_root_element' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wc_ubl_document_root_element',
		sprintf(
			$message,
			'wpo_wc_ubl_document_root_element',
			$version,
			'wpo_ips_edi_root_element'
		),
		$version
	);
}

if ( has_action( 'wpo_wc_ubl_document_additional_root_elements' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wc_ubl_document_additional_root_elements',
		sprintf(
			$message,
			'wpo_wc_ubl_document_additional_root_elements',
			$version,
			'wpo_ips_edi_additional_root_elements'
		),
		$version
	);
}

if ( has_action( 'wpo_wc_ubl_document_namespaces' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wc_ubl_document_namespaces',
		sprintf(
			$message,
			'wpo_wc_ubl_document_namespaces',
			$version,
			'wpo_ips_edi_namespaces'
		),
		$version
	);
}

if ( has_action( 'wpo_wc_ubl_document_format' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wc_ubl_document_format',
		sprintf(
			$message,
			'wpo_wc_ubl_document_format',
			$version,
			'wpo_ips_edi_format_structure'
		),
		$version
	);
}

if ( has_action( 'wpo_wc_ubl_document_data' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wc_ubl_document_data',
		sprintf(
			$message,
			'wpo_wc_ubl_document_data',
			$version,
			'wpo_ips_edi_document_data'
		),
		$version
	);
}

if ( has_action( 'wpo_ips_ubl_get_tax_data_from_fallback_vat_exempt' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_ips_ubl_get_tax_data_from_fallback_vat_exempt',
		sprintf(
			$message,
			'wpo_ips_ubl_get_tax_data_from_fallback_vat_exempt',
			$version,
			'wpo_ips_edi_get_tax_data_from_fallback_vat_exempt'
		),
		$version
	);
}

if ( has_action( 'wpo_ips_ubl_is_country_format_extension_active' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_ips_ubl_is_country_format_extension_active',
		sprintf(
			$message,
			'wpo_ips_ubl_is_country_format_extension_active',
			$version,
			'wpo_ips_edi_is_country_format_extension_active'
		),
		$version
	);
}

if ( has_action( 'wpo_wcpdf_ubl_maker' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wcpdf_ubl_maker',
		sprintf(
			$message,
			'wpo_wcpdf_ubl_maker',
			$version,
			'wpo_ips_edi_maker'
		),
		$version
	);
}

if ( has_action( 'wpo_wcpdf_ubl_available' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_wcpdf_ubl_available',
		sprintf(
			$message,
			'wpo_wcpdf_ubl_available',
			$version,
			'wpo_ips_edi_is_available'
		),
		$version
	);
}

if ( has_action( 'wpo_ips_ubl_contents' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_ips_ubl_contents',
		sprintf(
			$message,
			'wpo_ips_ubl_contents',
			$version,
			'wpo_ips_edi_contents'
		),
		$version
	);
}

if ( has_action( 'wpo_ips_ubl_filename' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_ips_ubl_filename',
		sprintf(
			$message,
			'wpo_ips_ubl_filename',
			$version,
			'wpo_ips_edi_filename'
		),
		$version
	);
}

if ( has_action( 'wcpdf_ubl_headers_charset' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wcpdf_ubl_headers_charset',
		sprintf(
			$message,
			'wcpdf_ubl_headers_charset',
			$version,
			'wpo_ips_edi_file_header_content_type_charset'
		),
		$version
	);
}

if ( has_action( 'wpo_after_ubl_headers' ) ) {
	$version = '5.0.0';
	_doing_it_wrong(
		'wpo_after_ubl_headers',
		sprintf(
			$message,
			'wpo_after_ubl_headers',
			$version,
			'wpo_ips_edi_after_headers'
		),
		$version
	);
}