<?php
namespace WPO\WC\PDF_Invoices\Makers\UBL;

use WPO\WC\PDF_Invoices\Makers\UBL\Documents\UblDocument;
use WPO\WC\PDF_Invoices\Makers\UBL\Builders\SabreBuilder;
use WPO\WC\PDF_Invoices\Makers\UBL\Exceptions\FileWriteException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Makers\\UBL\\UBL_Maker' ) ) :

class UBL_Maker {
	
	public $context = [ 'source' => 'wpo-wcpdf-ubl' ];
	
	public function __construct() {
		// add_action( 'admin_init', [ $this, 'generateUbl' ], 20 );
		// add_filter( 'wpo_wcpdf_meta_box_actions', [ $this, 'metaBoxActions'], 10, 2 );

		add_action( 'wpo_wcpdf_export_bulk_template_type_options', [ $this, 'addUblOptions' ], 10 );
		add_filter( 'wpo_wcpdf_export_bulk_create_file', [ $this, 'ublBulkHandler' ], 10, 3 );
		add_action( 'wpo_wcpdf_export_bulk_get_orders_args', [ $this, 'ublBulkArgs' ], 10, 1 );
		add_action( 'wpo_wcpdf_cloud_storage_upload_by_status', [ $this, 'uploadByStatus' ], 10, 4 );
		
		add_action( 'woocommerce_admin_order_actions_end', [ $this, 'addListingAction' ] );
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '>=' ) ) {
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'addBulkAction' ), 30 );
		}

		add_action( 'woocommerce_checkout_order_processed', [ $this, 'saveTaxRateDetailsRecalculateFrontend' ], 10, 2 );
		add_action( 'woocommerce_order_after_calculate_totals', [ $this, 'saveTaxRateDetailsRecalculate' ], 10, 2 );
		
		add_action( 'woocommerce_email_attachments', [ $this, 'attachToEmail' ], 10, 3 );

		add_filter( 'wpo_wcpdf_settings_tabs', [ $this, 'addTaxesSettingTab' ], 10, 1 );
		add_action( 'wpo_wcpdf_settings_output_ubl', [ $this, 'taxesSettingTabOutput' ], 10, 1 );

		add_action( 'admin_init', [ $this, 'init_general_settings' ] );
		add_action( 'admin_init', [ $this, 'initTaxSettings' ] );
		
		add_action( 'admin_enqueue_scripts', [ $this, 'loadAdminAssets' ] );
	}

	public function init_general_settings() {
		// Register settings.
		$page = $option_group = $option_name = 'ubl_wc_general';

		$settings_fields = array(
			array(
				'type'      => 'section',
				'id'        => 'general',
				'title'     => '',
				'callback'  => 'section',
			),
			array(
				'type'          => 'setting',
				'id'            => 'company_name',
				'title'         => __( 'Company Name', 'ubl-woocommerce-pdf-invoices' ),
				'callback'      => 'text_input',
				'section'       => 'general',
				'args'          => array(
					'option_name'   => $option_name,
					'id'            => 'company_name',
					'size'         => '42',
				)
			),
			array(
				'type'          => 'setting',
				'id'            => 'vat_number',
				'title'         => __( 'VAT Number', 'ubl-woocommerce-pdf-invoices' ),
				'callback'      => 'text_input',
				'section'       => 'general',
				'args'          => array(
					'option_name'   => $option_name,
					'id'            => 'vat_number',
					'size'         => '42',
				)
			),
			array(
				'type'          => 'setting',
				'id'            => 'coc_number',
				'title'         => __( 'Chamber of Commerce Number', 'ubl-woocommerce-pdf-invoices' ),
				'callback'      => 'text_input',
				'section'       => 'general',
				'args'          => array(
					'option_name'   => $option_name,
					'id'            => 'coc_number',
					'size'         => '42',
				)
			),
		);

		// load invoice to reuse method to get wc emails
		$invoice = wcpdf_get_invoice( null );

		$settings_fields[] = array(
			'type'          => 'setting',
			'id'            => 'attach_to_email_ids',
			'title'         => __( 'Attach UBL to:', 'ubl-woocommerce-pdf-invoices' ),
			'callback'      => 'multiple_checkboxes',
			'section'       => 'general',
			'args'          => array(
				'option_name'   => $option_name,
				'id'            => 'attach_to_email_ids',
				'fields'        => $invoice->get_wc_emails(),
			)
		);
		
		$settings_fields[] = array(
			'type'          => 'setting',
			'id'            => 'include_encrypted_pdf',
			'title'         => __( 'Include encrypted PDF:', 'ubl-woocommerce-pdf-invoices' ),
			'callback'      => 'checkbox',
			'section'       => 'general',
			'args'          => array(
				'option_name'   => $option_name,
				'id'            => 'include_encrypted_pdf',
				'description'   => __( 'Include the PDF Invoice file encrypted in the UBL file.', 'ubl-woocommerce-pdf-invoices' ),
			)
		);

		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

	public function initTaxSettings() {
		$page = $option_group = $option_name = 'ubl_wc_taxes';

		$settings_fields = array(
			array(
				'type'          => 'section',
				'id'            => 'ubl_wc_taxes_settings',
				'title'         => '',
				'callback'      => 'section',
			),
			array(
				'type'          => 'setting',
				'id'            => 'ubl_wc_taxes',
				'title'         => 'Taxes settings for UBL',
				'callback'      => 'string',
				'section'       => 'ubl_wc_taxes_settings',
				'args'          => [],
			),
		);

		WPO_WCPDF()->settings->add_settings_fields( $settings_fields, $page, $option_group, $option_name );
	}

	public function attachToEmail( $attachments, $email_id, $order ) {
		// check if all variables properly set
		if ( !is_object( $order ) || !isset( $email_id ) ) {
			return $attachments;
		}

		// Skip User emails
		if ( get_class( $order ) == 'WP_User' ) {
			return $attachments;
		}

		$order_id = $order->get_id();

		if ( get_class( $order ) !== 'WC_Order' && $order_id == false ) {
			return $attachments;
		}

		// WooCommerce Booking compatibility
		if ( get_post_type( $order_id ) == 'wc_booking' && isset($order->order) ) {
			// $order is actually a WC_Booking object!
			$order = $order->order;
		}

		// do not process low stock notifications, user emails etc!
		if ( in_array( $email_id, array( 'no_stock', 'low_stock', 'backorder', 'customer_new_account', 'customer_reset_password' ) ) || get_post_type( $order_id ) != 'shop_order' ) {
			return $attachments;
		}

		$settings = get_option( 'ubl_wc_general', array() );
		if (empty($settings['attach_to_email_ids'])) {
			return $attachments;
		}

		if ( in_array( $email_id, array_keys( $settings['attach_to_email_ids'] ) ) ) {
			$writer = wcpdf_get_ubl_maker();
			$writer->setFilePath( WPO_WCPDF()->main->get_tmp_path('attachments') );

			$document = new UblDocument();
			$document->setOrder($order);
			if ( $invoice = wcpdf_get_invoice( $document->order, true ) ) {
				$document->setInvoice( $invoice );
			} else {
				$message = 'Error generating invoice';
				wcpdf_log_error( $message, 'error', null, $this->context );
				return $attachments;
			}

			$builder  = new SabreBuilder();
			$contents = $builder->build($document);
			$filename = str_ireplace( '.pdf', '.xml', $document->order_document->get_filename() );

			try {
				$fullFileName = $writer->write($filename, $contents);

				// custom attachment condition
				if ( true === apply_filters( 'wpo_wcpdf_custom_ubl_attachment_condition', true, $order, $email_id, $document ) ) {
					// $success[$order_id] = $fullFileName;
					$attachments[] = $fullFileName;
				}
			} catch( FileWriteException $e ) {
				wcpdf_log_error( $e, 'error', $e, $this->context );
			}

			try {
				// hook used to upload UBL do cloud service
				do_action( 'wpo_wc_ubl_attachment_file', $fullFileName, $order );
			} catch( \Throwable $e ) {
				wcpdf_log_error( $e, 'error', $e, $this->context );
			}
		}

		return $attachments;
	}

	// public function generateUbl() {
	// 	if ( ! isset($_GET['ubl'] ) || $_GET['ubl'] !== 'yes' ) {
	// 		return;
	// 	}

	// 	if( ! isset( $_GET['post'] ) ) {
	// 		return;
	// 	}

	// 	if ( ! current_user_can('manage_options') && ! wc_current_user_has_role( 'shop_manager' ) ) {
	// 		return;
	// 	}

	// 	$success = [];
	// 	$errors = [];

	// 	$order_id = intval($_GET['post']);
	// 	$order = wc_get_order( $order_id );
	// 	if ( $order == false ) {
	// 		return;
	// 	}

	// 	$writer = wcpdf_get_ubl_maker();

	// 	$document = new UblDocument();
	// 	$document->setOrder($order);
	// 	if ( $invoice = wcpdf_get_invoice( $document->order, true ) ) {
	// 		$document->setInvoice( $invoice );
	// 	} else {
	// 		$errors[$order_id] = 'Error generating invoice';
	// 		return;
	// 	}

	// 	$builder = new SabreBuilder();
	// 	$contents = $builder->build($document);

	// 	$filename = str_ireplace( '.pdf', '.xml', $document->order_document->get_filename() );

	// 	try {
	// 		$fullFileName = $writer->write($filename, $contents);
	// 		$success[$order_id] = $fullFileName;
	// 	} catch( FileWriteException $e ) {
	// 		$errors[$order_id] = 'Error writing file';
	// 		exit();
	// 	}

	// 	$quoted = sprintf('"%s"', addcslashes(basename($fullFileName), '"\\'));
	// 	$size   = filesize($fullFileName);

	// 	wcpdf_ubl_headers( $quoted, $size );

	// 	ob_clean();
	// 	flush();
	// 	readfile($fullFileName);
	// 	unlink($fullFileName);

	// 	exit();
	// }

	// public function metaBoxActions($meta_box_actions, $post_id) {
	// 	$data['url'] = admin_url('post.php?post='. $post_id.'&action=edit&ubl=yes');
	// 	$data['alt'] = 'Generate UBL invoice';
	// 	$data['title'] = 'UBL Invoice';
	// 	$meta_box_actions['ubl'] = $data;
	// 	return $meta_box_actions;
	// }

	public function addUblOptions() {
		echo '<option value="ubl_invoice">' . __('UBL Invoice') . '</option>';
	}

	public function ublBulkHandler( $file, $order, $args ) {
		// This handler only acts on the ubl_invoice template type
		if ( ! isset( $args['document_type'] ) || $args['document_type'] !== 'ubl_invoice' ) {
			return $file;
		}

		return $this->createUblFile( $order, $args );
	}

	public function uploadByStatus( $template_type, $order, $new_status, $old_status ) {
		if( $template_type == 'invoice' && ! empty( $file = $this->createUblFile( $order ) ) ) {
			do_action( 'wpo_wcpdf_cloud_storage_upload_file', $file );
		}
	}
	
	public function addListingAction( $order ) {
		// do not show button for trashed orders
		if ( $order->get_status() == 'trash' ) {
			return;
		}
		
		printf(
			'<a href="%1$s" class="button tips wpo_wcpdf %2$s" target="_blank" alt="%3$s" data-tip="%3$s" style="background-image:url(%4$s);"></a>',
			esc_attr( admin_url( 'post.php?post='.$order->get_id().'&action=edit&ubl=yes' ) ),
			esc_attr( 'ubl-invoice' ),
			esc_attr(__( 'UBL Invoice' ) ),
			esc_attr( WPO_WCPDF()->plugin_url() . '/assets/images/ubl.svg' )
		);
	}
	
	public function addBulkAction( $actions ) {
		$actions['ubl_invoice'] = __( 'UBL Invoice' );
		return $actions;
	}

	public function createUblFile( $order, $args = [] ) {
		$file = '';

		if( empty( $order ) ) {
			return $file;
		}

		$writer   = wcpdf_get_ubl_maker();
		$order_id = $order->get_id();
		$document = new UblDocument();
		$document->setOrder( $order );

		$init_invoice = $args['only_existing'] ? false : true;
		$invoice      = wcpdf_get_invoice( $document->order, $init_invoice );

		if ( ! $invoice ) {
			$message = sprintf( 'Error generating Invoice for order ID %s', $order_id );
			wcpdf_log_error( $message, 'error', null, $this->context );
			return $file;
		}

		if ( $args['only_existing'] && false === $invoice->exists() ) {
			return $file;
		}

		$document->setInvoice( $invoice );

		$builder  = new SabreBuilder();
		$contents = $builder->build( $document );
		$filename = str_ireplace( '.pdf', '.xml', $document->order_document->get_filename() );

		try {
			$file = $writer->write( $filename, $contents );
		} catch( FileWriteException $e ) {
			$message = sprintf( 'Error writing UBL file for order ID %s', $order_id );
			wcpdf_log_error( $message, 'error', null, $this->context );
			wcpdf_log_error( $e, 'error', $e, $this->context );
			return $file;
		}

		return $file;
	}

	public function ublBulkArgs($args) {
		// Pro automatically converts 'document_type' to date query arg, but we need the invoice date
		if (isset($args['wcpdf_ubl_invoice_date'])) {
			$args['wcpdf_invoice_date'] = $args['wcpdf_ubl_invoice_date'];
			unset($args['wcpdf_ubl_invoice_date']);
		}
		return $args;
	}

	/**
	 * Save specific tax rate details in tax meta every time totals are calculated
	 * @param  bool $and_taxes Calc taxes if true.
	 * @param  WC_Order $order Order object.
	 * @return void
	 */
	public function saveTaxRateDetailsRecalculate( $and_taxes, $order ) {
		// it seems $and taxes is mostly false, meaning taxes are calculated separately,
		// but we still update just in case anything changed
		$this->saveTaxRateDetails( $order );
	}

	public function saveTaxRateDetailsRecalculateFrontend( $order_id, $posted ) {
		if ( $order = wc_get_order( $order_id ) ) {
			$this->saveTaxRateDetails( $order );
		}
	}

	public function saveTaxRateDetails( $order ) {
		foreach ( $order->get_taxes() as $item_id => $tax_item ) {
			if ( is_a( $tax_item, '\WC_Order_Item_Tax' ) && is_callable( array( $tax_item, 'get_rate_id' ) ) ) {
				// get tax rate id from item
				$tax_rate_id = $tax_item->get_rate_id();
				
				// read tax rate data from db
				if ( class_exists('\WC_TAX') && is_callable( array( '\WC_TAX', '_get_tax_rate' ) ) ) {
					$tax_rate = \WC_Tax::_get_tax_rate( $tax_rate_id, OBJECT );
					if ( ! empty( $tax_rate ) && is_numeric( $tax_rate->tax_rate ) ) {
						// store percentage in tax item meta
						wc_update_order_item_meta( $item_id, '_wcpdf_rate_percentage', $tax_rate->tax_rate );

						$ubl_tax_settings = get_option('ubl_wc_taxes');

						$category = isset($ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['category']) ? $ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['category'] : '';
						$scheme = isset($ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['scheme']) ? $ubl_tax_settings['rate'][$tax_rate->tax_rate_id]['scheme'] : '';

						$tax_rate_class = $tax_rate->tax_rate_class;
						if ( empty($tax_rate_class) ) {
							$tax_rate_class = 'standard';
						}

						if ( empty( $category ) ) {
							$category = isset($ubl_tax_settings['class'][$tax_rate_class]['category']) ? $ubl_tax_settings['class'][$tax_rate_class]['category'] : '';
						}

						if ( empty( $scheme ) ) {
							$scheme = isset($ubl_tax_settings['class'][$tax_rate_class]['scheme']) ? $ubl_tax_settings['class'][$tax_rate_class]['scheme'] : '';
						}

						if ( ! empty( $category ) ) {
							wc_update_order_item_meta( $item_id, '_wcpdf_rate_category', $category );
						}

						if ( ! empty( $scheme ) ) {
							wc_update_order_item_meta( $item_id, '_wcpdf_rate_scheme', $scheme );
						}
					}
				}
			}
		}
	}

	public function addTaxesSettingTab($tabs) {
		$tabs['ubl'] = __('UBL', 'ubl-woocommerce-pdf-invoices');
		return $tabs;
	}

	public function taxesSettingTabOutput( $active_section = '' ) {
		if ( empty($active_section) ) {
			$active_section = 'general';
		}
		$sections = [
			'general' => __('General', 'ubl-woocommerce-pdf-invoices'),
			'taxes'   => __('Taxes', 'ubl-woocommerce-pdf-invoices'),
		];
		?>
		<div class="wcpdf-settings-sections">
			<ul>
				<?php
				foreach ($sections as $section => $title) {
					printf('<li><a href="%s" class="%s">%s</a></li>', esc_url( add_query_arg( 'section', $section ) ), $section == $active_section ? 'active' : '', $title );
				}
				?>
			</ul>
		</div>

		<?php
		switch ( $active_section ) {
			case 'general':
			default:
				settings_fields( "ubl_wc_general" );
				do_settings_sections( "ubl_wc_general" );

				submit_button();
				break;
			case 'taxes':
				$setting = new \WPO\WC\PDF_Invoices\Makers\UBL\Settings\TaxesSettings();
				$setting->output();
				break;
		}
	}
	
	public function isOrderPage() {
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ) ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function loadAdminAssets() {
		if ( $this->isOrderPage() ) {
			wp_enqueue_script(
				'wpo-wcpdf-ubl',
				WPO_WCPDF()->plugin_url() . '/assets/js/ubl-scripts.js',
				[ 'jquery' ],
				$this->version,
				true
			);
			wp_localize_script(
				'wpo-wcpdf-ubl',
				'wpo_wcpdf_ubl',
				[
					'adminUrl'         => admin_url( 'post.php' ),
					'noSelectedOrders' => __( 'You have to select order(s) first!' )
				]
			);
		}
	}

}

endif; // class_exists
