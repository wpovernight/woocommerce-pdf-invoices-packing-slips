<?php

/**
 * This file is required to keep compatibility with legacy classes and namespaces
 * before the new PSR-4 autoloading standard was introduced in version 3.9.0.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$wcpdf_legacy_class_alias_mapping = apply_filters( 'wpo_wcpdf_legacy_class_alias_mapping', array(

	// includes/Compatibility
	'\\WPO\\WC\\PDF_Invoices\\Compatibility\\Third_Party_Plugins' => '\\WPO\\IPS\\Compatibility\\ThirdPartyPlugins',
	'\\WPO\\WC\\PDF_Invoices\\Compatibility\\Order_Util'          => '\\WPO\\IPS\\Compatibility\\OrderUtil',

	// includes/Documents
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Order_Document_Methods'  => '\\WPO\\IPS\\Documents\\OrderDocumentMethods',
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Order_Document'          => '\\WPO\\IPS\\Documents\\OrderDocument',
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Bulk_Document'           => '\\WPO\\IPS\\Documents\\BulkDocument',
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Document_Number'         => '\\WPO\\IPS\\Documents\\DocumentNumber',
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Invoice'                 => '\\WPO\\IPS\\Documents\\Invoice',
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Packing_Slip'            => '\\WPO\\IPS\\Documents\\PackingSlip',
	'\\WPO\\WC\\PDF_Invoices\\Documents\\Sequential_Number_Store' => '\\WPO\\IPS\\Documents\\SequentialNumberStore',

	// includes/Makers
	'\\WPO\\WC\\PDF_Invoices\\Makers\\PDF_Maker'                  => '\\WPO\\IPS\\Makers\\PDFMaker',
	'\\WPO\\WC\\PDF_Invoices\\Makers\\UBL_Maker'                  => '\\WPO\\IPS\\Makers\\UBLMaker',

	// includes/Settings
	'\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_Callbacks'       => '\\WPO\\IPS\\Settings\\SettingsCallbacks',
	'\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_Debug'           => '\\WPO\\IPS\\Settings\\SettingsDebug',
	'\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_Documents'       => '\\WPO\\IPS\\Settings\\SettingsDocuments',
	'\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_General'         => '\\WPO\\IPS\\Settings\\SettingsGeneral',
	'\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_UBL'             => '\\WPO\\IPS\\Settings\\SettingsUbl',
	'\\WPO\\WC\\PDF_Invoices\\Settings\\Settings_Upgrade'         => '\\WPO\\IPS\\Settings\\SettingsUpgrade',

	// includes/Tables
	'\\WPO\\WC\\PDF_Invoices\\Tables\\Number_Store_List_Table'    => '\\WPO\\IPS\\Tables\\NumberStoreListTable',

	// includes
	'\\WPO\\WC\\PDF_Invoices\\Admin'                              => '\\WPO\\IPS\\Admin',
	'\\WPO\\WC\\PDF_Invoices\\Assets'                             => '\\WPO\\IPS\\Assets',
	'\\WPO\\WC\\PDF_Invoices\\Documents'                          => '\\WPO\\IPS\\Documents',
	'\\WPO\\WC\\PDF_Invoices\\Endpoint'                           => '\\WPO\\IPS\\Endpoint',
	'\\WPO\\WC\\PDF_Invoices\\Font_Synchronizer'                  => '\\WPO\\IPS\\FontSynchronizer',
	'\\WPO\\WC\\PDF_Invoices\\Frontend'                           => '\\WPO\\IPS\\Frontend',
	'\\WPO\\WC\\PDF_Invoices\\Install'                            => '\\WPO\\IPS\\Install',
	'\\WPO\\WC\\PDF_Invoices\\Main'                               => '\\WPO\\IPS\\Main',
	'\\WPO\\WC\\PDF_Invoices\\Settings'                           => '\\WPO\\IPS\\Settings',
	'\\WPO\\WC\\PDF_Invoices\\Setup_Wizard'                       => '\\WPO\\IPS\\SetupWizard',
	'\\WPO\\WC\\PDF_Invoices\\Updraft_Semaphore_3_0'              => '\\WPO\\IPS\\Semaphore',

	// ubl
	'\\WPO\\WC\\UBL\\Handlers\\UblHandler'                        => '\\WPO\\IPS\\UBL\\Handlers\\UblHandler', // used by `woocommerce-pdf-ips-ubl-extender`

) );

foreach ( $wcpdf_legacy_class_alias_mapping as $old_class => $new_class ) {
	if ( ! class_exists( $old_class ) && class_exists( $new_class ) ) {
		class_alias( $new_class, $old_class );
	}
}
