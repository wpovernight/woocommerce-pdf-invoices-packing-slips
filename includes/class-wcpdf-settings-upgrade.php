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
					'label' => __( 'Proforma Invoice & Credit Note', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Update your workflow and handle refunds. Both documents can either follow the main invoice numbering or have their own separate number sequence.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Attach to email', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Also attach the Packing Slip, Proforma Invoice and Credit Note to any of the outgoing emails.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['pro', 'bundle'],
				),
				array( 
					'label' => __( 'Cloud storage upload', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Automatically upload your documents via FTP/SFTP or to Dropbox.', 'woocommerce-pdf-invoices-packing-slips' ),
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
					'label' => __( 'Custom address format', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Customize the address format of the billing and shipping addresses.', 'woocommerce-pdf-invoices-packing-slips' ),
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
				array( 
					'label' => __( 'Add styling', 'woocommerce-pdf-invoices-packing-slips' ),
					'description' => __( 'Easily change the look and feel of your documents by addding some custom CSS.', 'woocommerce-pdf-invoices-packing-slips' ),
					'extensions' => ['templates', 'bundle'],
				),
			);
			
			$extension_license_infos = $this->get_extension_license_infos();

			include( WPO_WCPDF()->plugin_path() . '/includes/views/upgrade-table.php' );
		}
	}
	
	/**
	 * Check if a PDF extension is enabled
	 *
	 * @param  string  $extension  can be 'pro' or 'templates'
	 * @return boolean
	 */
	public function extension_is_enabled( $extension ) {
		$is_enabled = false;
		
		if ( ! empty( $extension ) || ! in_array( $extension, [ 'pro', 'templates' ] ) ) {
			$extension_main_function = "WPO_WCPDF_".ucfirst( $extension );
			if ( function_exists( $extension_main_function ) ) {
				$is_enabled = true;
			}
		}
		
		return $is_enabled;
	}
	
	/**
	 * Get PDF extensions license info
	 *
	 * @return array
	 */
	public function get_extension_license_infos() {
		$extensions          = [ 'pro', 'templates' ];
		$license_info        = [];
		$bundle_upgrade_link = '';
		$license_status      = 'inactive';
		
		foreach ( $extensions as $extension ) {
			$license_info[$extension] = [];
			
			if ( $this->extension_is_enabled( $extension ) ) {
				$extension_main_function = "WPO_WCPDF_".ucfirst( $extension );
				
				if ( $extension == 'templates' && version_compare( $extension_main_function()->version, '2.20.0', '<=' ) ) { // 'updater' property had 'private' visibility
					continue;
				}
				
				if ( is_callable( [ $extension_main_function()->updater, 'get_license_key' ] ) && is_callable( [ $extension_main_function()->updater, 'remote_license_actions' ] ) ) {
					if ( ! empty( $license_key = $extension_main_function()->updater->get_license_key() ) ) {
						$args = array(
							'edd_action'  => 'check_license',
							'license_key' => trim( $license_key ),
						);
					} else {
						continue;
					}
					
					$request = $extension_main_function()->updater->remote_license_actions( $args );
					if ( is_object( $request ) && isset( $request->license ) ) {
						$license_info[$extension]['status'] = $license_status = $request->license;
						
						if ( empty( $bundle_upgrade_link ) && ! empty( $request->bundle_upgrade ) && is_string( $request->bundle_upgrade ) ) {
							$bundle_upgrade_link = $request->bundle_upgrade; // https://github.com/wpovernight/woocommerce-pdf-invoices-packing-slips/pull/503#issue-1678203436
						}
					}
				}
			}	
		}
		
		$extensions[] = 'bundle';
		foreach ( $extensions as $extension ) {
			if ( ! empty( $bundle_upgrade_link ) && $license_status == 'valid' ) {
				$license_info[$extension]['url'] = $bundle_upgrade_link;
			} else {
				switch ( $extension ) {
					case 'pro':
						$license_info[$extension]['url'] = 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/';
						break;
					case 'templates':
						$license_info[$extension]['url'] = 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-premium-templates/';
						break;
					case 'bundle':
						$license_info[$extension]['url'] = 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-bundle/';
						break;
				}
			}
		}
		
		return $license_info;
	}
	
}

endif; // class_exists

return new Settings_Upgrade();