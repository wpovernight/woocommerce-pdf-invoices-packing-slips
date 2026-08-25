<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message_with_replacement    = 'The hook "%s" is deprecated since version %s. Use "%s" instead.';
$message_without_replacement = 'The hook "%s" is deprecated since version %s and no longer has a replacement.';

/**
 * Hooks that have a replacement.
 * Format: 'old_hook' => array( 'new_hook', 'since_version' )
 */
$deprecated_hooks = array(
	'wpo_wcpdf_custom_ubl_attachment_condition'         => array( 'wpo_wcpdf_custom_attachment_condition',             '3.6.0' ),
	'wpo_wc_ubl_document_root_element'                  => array( 'wpo_ips_edi_root_element',                          '5.0.0' ),
	'wpo_wc_ubl_document_additional_root_elements'      => array( 'wpo_ips_edi_additional_root_elements',              '5.0.0' ),
	'wpo_wc_ubl_document_namespaces'                    => array( 'wpo_ips_edi_namespaces',                            '5.0.0' ),
	'wpo_wc_ubl_document_format'                        => array( 'wpo_ips_edi_format_structure',                      '5.0.0' ),
	'wpo_wc_ubl_document_data'                          => array( 'wpo_ips_edi_document_data',                         '5.0.0' ),
	'wpo_ips_ubl_get_tax_data_from_fallback_vat_exempt' => array( 'wpo_ips_edi_get_tax_data_from_fallback_vat_exempt', '5.0.0' ),
	'wpo_wcpdf_ubl_maker'                               => array( 'wpo_ips_edi_maker',                                 '5.0.0' ),
	'wpo_wcpdf_ubl_available'                           => array( 'wpo_ips_edi_is_available',                          '5.0.0' ),
	'wpo_ips_ubl_contents'                              => array( 'wpo_ips_edi_contents',                              '5.0.0' ),
	'wpo_ips_ubl_filename'                              => array( 'wpo_ips_edi_filename',                              '5.0.0' ),
	'wcpdf_ubl_headers_charset'                         => array( 'wpo_ips_edi_file_header_content_type_charset',      '5.0.0' ),
	'wpo_after_ubl_headers'                             => array( 'wpo_ips_edi_after_headers',                         '5.0.0' ),
	'wpo_wcpdf_ubl_action_button_class'                 => array( 'wpo_ips_edi_action_button_class',                   '5.0.0' ),
	'wpo_wcpdf_ubl_meta_box_actions'                    => array( 'wpo_ips_edi_meta_box_actions',                      '5.0.0' ),
	'wpo_wcpdf_ubl_tax_schemes'                         => array( 'wpo_ips_edi_en16931_vat_cat',                       '5.0.0' ),
	'wpo_wcpdf_ubl_tax_categories'                      => array( 'wpo_ips_edi_en16931_5305',                          '5.0.0' ),
	'wpo_wcpdf_ubl_tax_reasons'                         => array( 'wpo_ips_edi_en16931_vatex',                         '5.0.0' ),
	'wpo_wcpdf_ubl_tax_remarks'                         => array( 'wpo_ips_edi_en16931_vatex_remarks',                 '5.0.0' ),
	'wpo_wc_ubl_handle_AdditionalDocumentReference'     => array( 'wpo_ips_edi_ubl_additional_document_reference',     '5.0.0' ),
	'wpo_wc_ubl_handle_AccountingSupplierParty'         => array( 'wpo_ips_edi_ubl_accounting_supplier_party',         '5.0.0' ),
	'wpo_wc_ubl_vat_number'                             => array( 'wpo_ips_edi_ubl_vat_number',                        '5.0.0' ),
	'wpo_wc_ubl_handle_AccountingCustomerParty'         => array( 'wpo_ips_edi_ubl_accounting_customer_party',         '5.0.0' ),
	'wpo_wc_ubl_handle_AllowanceCharge'                 => array( 'wpo_ips_edi_ubl_allowance_charge',                  '5.0.0' ),
	'wpo_wc_ubl_handle_BuyerReference'                  => array( 'wpo_ips_edi_ubl_buyer_reference',                   '5.0.0' ),
	'wpo_wc_ubl_handle_Delivery'                        => array( 'wpo_ips_edi_ubl_delivery',                          '5.0.0' ),
	'wpo_wc_ubl_handle_DocumentCurrencyCode'            => array( 'wpo_ips_edi_ubl_document_currency_code',            '5.0.0' ),
	'wpo_wc_ubl_handle_ID'                              => array( 'wpo_ips_edi_ubl_id',                                '5.0.0' ),
	'wpo_wc_ubl_handle_InvoiceLine'                     => array( 'wpo_ips_edi_ubl_line',                              '5.0.0' ),
	'wpo_wc_ubl_handle_InvoiceTypeCode'                 => array( 'wpo_ips_edi_ubl_type_code',                         '5.0.0' ),
	'wpo_wc_ubl_handle_IssueDate'                       => array( 'wpo_ips_edi_ubl_issue_date',                        '5.0.0' ),
	'wpo_wc_ubl_handle_LegalMonetaryTotal'              => array( 'wpo_ips_edi_ubl_legal_monetary_total',              '5.0.0' ),
	'wpo_wc_ubl_handle_OrderReference'                  => array( 'wpo_ips_edi_ubl_order_reference',                   '5.0.0' ),
	'wpo_wc_ubl_handle_PaymentMeans'                    => array( 'wpo_ips_edi_ubl_payment_means',                     '5.0.0' ),
	'wpo_wc_ubl_handle_PaymentTerms'                    => array( 'wpo_ips_edi_ubl_payment_terms',                     '5.0.0' ),
	'wpo_wc_ubl_orderTaxData'                           => array( 'wpo_ips_edi_ubl_order_tax_data',                    '5.0.0' ),
	'wpo_wc_ubl_handle_TaxTotal'                        => array( 'wpo_ips_edi_ubl_tax_total',                         '5.0.0' ),
	'wpo_wc_ubl_handle_UBLVersionID'                    => array( 'wpo_ips_edi_ubl_version_id',                        '5.0.0' ),
);


foreach ( $deprecated_hooks as $old_hook => $meta ) {
	[ $new_hook, $version ] = $meta;

	if ( has_action( $old_hook ) ) {
		_doing_it_wrong(
			esc_html( $old_hook ),
			sprintf(
				esc_html( $message_with_replacement ),
				esc_html( $old_hook ),
				esc_html( $version ),
				esc_html( $new_hook )
			),
			esc_html( $version )
		);
	}
}

/**
 * Hooks that are deprecated without a replacement.
 * Format: 'old_hook' => 'since_version'
 */
$deprecated_hooks_no_replacement = array(
	'wpo_ips_ubl_is_country_format_extension_active' => '5.0.0',
);

foreach ( $deprecated_hooks_no_replacement as $old_hook => $version ) {
	if ( has_action( $old_hook ) ) {
		_doing_it_wrong(
			esc_html( $old_hook ),
			sprintf(
				esc_html( $message_without_replacement ),
				esc_html( $old_hook ),
				esc_html( $version )
			),
			esc_html( $version )
		);
	}
}
