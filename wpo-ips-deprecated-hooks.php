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
			'wpo_ips_einvoice_root_element'
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
			'wpo_ips_einvoice_additional_root_elements'
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
			'wpo_ips_einvoice_namespaces'
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
			'wpo_ips_einvoice_format_structure'
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
			'wpo_ips_einvoice_document_data'
		),
		$version
	);
}