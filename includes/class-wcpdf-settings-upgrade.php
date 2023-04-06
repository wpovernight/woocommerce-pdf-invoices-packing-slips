<?php
namespace WPO\WC\PDF_Invoices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Settings_Upgrade' ) ) :

class Settings_Upgrade {

	function __construct()	{
		add_action( 'wpo_wcpdf_after_settings_page', array( $this, 'extension_overview' ), 10, 2 );
	}

	public function extension_overview( $tab, $section ) {
		if ( $tab === 'upgrade' ) {
			
			$features = array(
				array( 
					'label' => __( 'Proforma invoice & Credit note', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Update your workflow and handle refunds.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Dropbox / FTP upload', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Automatically upload your documents.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Bulk export', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Easily export documents for a specific date range.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Multilingual support', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Handle document translations via either WPML or Polylang.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Attach static files', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Add up to three static files to your emails.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Custom document titles and filenames', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Customize document titles and filenames right in the plugin settings.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Order notification email', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>', 
						__( 'Send a notification email to user specified addresses.', 'woocommerce-pdf-invoices-packing-slips' ), 
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/configuring-the-order-notification-email/', 
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' ) 
					),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'PDF Customizer', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>', 
						__( 'Fully customize the product table and totals table on your documents.', 'woocommerce-pdf-invoices-packing-slips' ), 
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/using-the-customizer/', 
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' ) 
					),
					'extensions' => ['templates', 'bundle'],
				),
				array( 
					'label' => __( 'Add custom data to your documents', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => sprintf(
						'%s <a href="%s" target="_blank">%s</a>', 
						__( 'Display all sorts of data and apply conditional logic using Custom Blocks.', 'woocommerce-pdf-invoices-packing-slips' ), 
						'https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/using-custom-blocks/', 
						__( 'Learn more', 'woocommerce-pdf-invoices-packing-slips' ) 
					),
					'extensions' => ['templates', 'bundle'],
				),
				array( 
					'label' => __( 'Additional PDF templates', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Make use of our Business or Modern template designs.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['templates', 'bundle'],
				),
			);

			include( WPO_WCPDF()->plugin_path() . '/includes/views/upgrade-table.php' );
		}
	}
	
}

endif; // class_exists

return new Settings_Upgrade();