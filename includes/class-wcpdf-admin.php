<?php

namespace WPO\WC\PDF_Invoices;

use \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( '\\WPO\\WC\\PDF_Invoices\\Admin' ) ) :

class Admin {

	function __construct()	{
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );

		if ( $this->invoice_columns_enabled() ) { // prevents the expensive hooks below to be attached. Improves Order List page loading speed
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_invoice_columns' ), 999 ); // WC 7.1+
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'invoice_columns_data' ), 10, 2 ); // WC 7.1+
			add_filter( 'manage_woocommerce_page_wc-orders_sortable_columns', array( $this, 'invoice_columns_sortable' ) ); // WC 7.1+
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_columns' ), 999 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_columns_data' ), 10, 2 );
			add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'invoice_columns_sortable' ) );
		}

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );

		add_filter( 'request', array( $this, 'request_query_sort_by_column' ) );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '>=' ) ) {
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'bulk_actions' ), 20 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'bulk_actions' ), 20 ); // WC 7.1+
		} else {
			add_action( 'admin_footer', array( $this, 'bulk_actions_js' ) );
		}
		
		if ( $this->invoice_number_search_enabled() ) { // prevents slowing down the orders list search
			add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'search_fields' ) );
		}

		add_action( 'woocommerce_process_shop_order_meta', array( $this,'save_invoice_number_date' ), 35, 2 );

		// manually send emails
		// WooCommerce core processes order actions at priority 50
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'send_emails' ), 60, 2 );

		add_action( 'admin_notices', array( $this, 'review_plugin_notice' ) );
		add_action( 'admin_notices', array( $this, 'install_wizard_notice' ) );

		add_action( 'init', array( $this, 'setup_wizard') );
		// add_action( 'wpo_wcpdf_after_pdf', array( $this,'update_pdf_counter' ), 10, 2 );

		add_action( 'admin_bar_menu', array( $this, 'debug_enabled_warning' ), 999 );

		// AJAX actions for deleting, regenerating and saving document data
		add_action( 'wp_ajax_wpo_wcpdf_delete_document', array( $this, 'ajax_crud_document' ) );
		add_action( 'wp_ajax_wpo_wcpdf_regenerate_document', array( $this, 'ajax_crud_document' ) );
		add_action( 'wp_ajax_wpo_wcpdf_save_document', array( $this, 'ajax_crud_document' ) );

		// document actions
		add_action( 'wpo_wcpdf_document_actions', array( $this, 'add_regenerate_document_button' ) );
	}

	// display review admin notice after 100 pdf downloads
	public function review_plugin_notice() {
		if ( $this->is_order_page() === false && !( isset( $_GET['page'] ) && $_GET['page'] == 'wpo_wcpdf_options_page' ) ) {
			return;
		}
		
		if ( get_option( 'wpo_wcpdf_review_notice_dismissed' ) !== false ) {
			return;
		} else {
			if ( isset( $_REQUEST['wpo_wcpdf_dismiss_review'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
				// validate nonce
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'dismiss_review_nonce' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_dismiss_review' );
					return;
				} else {
					update_option( 'wpo_wcpdf_review_notice_dismissed', true );
					return;
				}
			}

			// get invoice count to determine whether notice should be shown
			$invoice_count = $this->get_invoice_count();
			if ( $invoice_count > 100 ) {
				// keep track of how many days this notice is show so we can remove it after 7 days
				$notice_shown_on = get_option( 'wpo_wcpdf_review_notice_shown', array() );
				$today = date('Y-m-d');
				if ( !in_array($today, $notice_shown_on) ) {
					$notice_shown_on[] = $today;
					update_option( 'wpo_wcpdf_review_notice_shown', $notice_shown_on );
				}
				// count number of days review is shown, dismiss forever if shown more than 7
				if (count($notice_shown_on) > 7) {
					update_option( 'wpo_wcpdf_review_notice_dismissed', true );
					return;
				}

				$rounded_count = (int) substr( (string) $invoice_count, 0, 1 ) * pow( 10, strlen( (string) $invoice_count ) - 1);
				?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-review-notice">
					<?php /* translators: rounded count */ ?>
					<h3><?php printf( esc_html__( 'Wow, you have created more than %d invoices with our plugin!', 'woocommerce-pdf-invoices-packing-slips' ), $rounded_count ); ?></h3>
					<p><?php esc_html_e( 'It would mean a lot to us if you would quickly give our plugin a 5-star rating. Help us spread the word and boost our motivation!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<ul>
						<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/?rate=5#new-post" class="button"><?php esc_html_e( 'Yes you deserve it!', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></a></li>
						<li><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_review', true ), 'dismiss_review_nonce' ) ); ?>" class="wpo-wcpdf-dismiss"><?php esc_html_e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?> / <?php esc_html_e( 'Already did!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
						<li><a href="mailto:support@wpovernight.com?Subject=Here%20is%20how%20I%20think%20you%20can%20do%20better"><?php esc_html_e( 'Actually, I have a complaint...', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
					</ul>
				</div>
				<script type="text/javascript">
				jQuery( function( $ ) {
					$( '.wpo-wcpdf-review-notice' ).on( 'click', '.notice-dismiss', function( event ) {
						event.preventDefault();
				  		window.location.href = $( '.wpo-wcpdf-dismiss' ).attr('href');
					});
				});
				</script>
				<!-- Hide extensions ad if this is shown -->
				<style>.wcpdf-extensions-ad { display: none; }</style>
				<?php
			}
		}
	}

	public function install_wizard_notice() {
		// automatically remove notice after 1 week, set transient the first time
		if ( $this->is_order_page() === false && !( isset( $_GET['page'] ) && $_GET['page'] == 'wpo_wcpdf_options_page' ) ) {
			return;
		}
		
		if ( get_option( 'wpo_wcpdf_install_notice_dismissed' ) !== false ) {
			return;
		} else {
			if ( isset( $_REQUEST['wpo_wcpdf_dismiss_install'] ) && isset( $_REQUEST['_wpnonce'] ) ) {
				// validate nonce
				if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'dismiss_install_nonce' ) ) {
					wcpdf_log_error( 'You do not have sufficient permissions to perform this action: wpo_wcpdf_dismiss_install' );
					return;
				} else {
					update_option( 'wpo_wcpdf_install_notice_dismissed', true );
					return;
				}
			}

			if ( get_transient( 'wpo_wcpdf_new_install' ) !== false ) {
				?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-install-notice">
					<p><strong><?php esc_html_e( 'New to PDF Invoices & Packing Slips for WooCommerce?', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong> &#8211; <?php esc_html_e( 'Jumpstart the plugin by following our wizard!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo-wcpdf-setup' ) ); ?>" class="button-primary"><?php esc_html_e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a> <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_dismiss_install', true ), 'dismiss_install_nonce' ) ); ?>" class="wpo-wcpdf-dismiss-wizard"><?php esc_html_e( 'I am the wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
				</div>
				<script type="text/javascript">
				jQuery( function( $ ) {
					$( '.wpo-wcpdf-install-notice' ).on( 'click', '.notice-dismiss', function( event ) {
						event.preventDefault();
				  		window.location.href = $( '.wpo-wcpdf-dismiss-wizard' ).attr('href');
					});
				});
				</script>
				<?php
			}
		}

	}

	public function setup_wizard() {
		// Setup/welcome
		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'wpo-wcpdf-setup' ) {
			delete_transient( 'wpo_wcpdf_new_install' );
			include_once( WPO_WCPDF()->plugin_path() . '/includes/class-wcpdf-setup-wizard.php' );
		}
	}

	public function get_invoice_count() {
		global $wpdb;
		$invoice_count = $wpdb->get_var( $wpdb->prepare( "SELECT count(*)  FROM {$wpdb->postmeta} WHERE meta_key = %s", '_wcpdf_invoice_number' ) );
		return (int) $invoice_count;
	}

	public function update_pdf_counter( $document_type, $document ) {
		if ( in_array( $document_type, array('invoice','packing-slip') ) ) {
			$pdf_count = (int) get_option( 'wpo_wcpdf_count_'.$document_type, 0 );
			update_option( 'wpo_wcpdf_count_'.$document_type, $pdf_count + 1 );
		}
	}

	/**
	 * Add PDF actions to the orders listing
	 */
	public function add_listing_actions( $order ) {
		// do not show buttons for trashed orders
		if ( $order->get_status() == 'trash' ) {
			return;
		}
		$this->disable_storing_document_settings();

		$listing_actions = array();
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ( $documents as $document ) {
			$document_title = $document->get_title();
			$icon = ! empty( $document->icon ) ? $document->icon : WPO_WCPDF()->plugin_url() . "/assets/images/generic_document.png";
			if ( $document = wcpdf_get_document( $document->get_type(), $order ) ) {
				$pdf_url          = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type() );
				$document_title   = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
				$document_exists  = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
				$document_printed = $document_exists && is_callable( array( $document, 'printed' ) ) ? $document->printed() : false;
				$class            = [ $document->get_type() ];
				
				if ( $document_exists ) {
					$class[] = 'exists';
				}
				if ( $document_printed ) {
					$class[] = 'printed';
				}

				$listing_actions[$document->get_type()] = array(
					'url'     => esc_url( $pdf_url ),
					'img'     => $icon,
					'alt'     => "PDF " . $document_title,
					'exists'  => $document_exists,
					'printed' => $document_printed,
					'class'   => apply_filters( 'wpo_wcpdf_action_button_class', implode( ' ', $class ), $document ),
				);
			}
		}

		$listing_actions = apply_filters( 'wpo_wcpdf_listing_actions', $listing_actions, $order );

		foreach ( $listing_actions as $action => $data ) {
			if ( ! isset( $data['class'] ) ) {
				$data['class'] = $data['exists'] ? "exists {$action}" : $action;
			}

			$exists  = $data['exists'] ? '<svg class="icon-exists" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path></svg>' : '';
			$printed = $data['printed'] ? '<svg class="icon-printed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 4H16V6H8V4ZM18 6H22V18H18V22H6V18H2V6H6V2H18V6ZM20 16H18V14H6V16H4V8H20V16ZM8 16H16V20H8V16ZM8 10H6V12H8V10Z"></path></svg>' : '';

			printf(
				'<a href="%1$s" class="button tips wpo_wcpdf %2$s" target="_blank" alt="%3$s" data-tip="%3$s" style="background-image:url(%4$s);">%5$s%6$s</a>',
				esc_attr( $data['url'] ),
				esc_attr( $data['class'] ),
				esc_attr( $data['alt'] ),
				esc_attr( $data['img'] ),
				$exists,
				$printed
			);
		}
	}
	
	/**
	 * Create additional Shop Order column for Invoice Number/Date
	 * @param array $columns shop order columns
	 */
	public function add_invoice_columns( $columns ) {
		if ( WPO_WCPDF()->order_util->custom_orders_table_usage_is_enabled() && isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'wc-orders' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
			return $columns;
		}
		
		// get invoice settings
		$invoice          = wcpdf_get_invoice( null );
		$invoice_settings = $invoice->get_settings();
		$invoice_columns  = array(
			'invoice_number_column' => __( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ),
			'invoice_date_column'   => __( 'Invoice Date', 'woocommerce-pdf-invoices-packing-slips' ),
		);

		$offset = 2; // after order number column
		foreach ( $invoice_columns as $slug => $name ) {
			if ( ! isset( $invoice_settings[$slug] ) ) {
				continue;
			}

			$columns = array_slice( $columns, 0, $offset, true ) +
				array( $slug => $name ) +
				array_slice( $columns, 2, count( $columns ) - 1, true ) ;

			$offset++;
		}

		return $columns;
	}

	/**
	 * Display Invoice Number/Date in Shop Order column (if available)
	 * @param  string $column                 column slug
	 * @param  string $post_or_order_object   object
	 */
	public function invoice_columns_data( $column, $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		if ( ! is_object( $order ) && is_numeric( $order ) ) {
			$order = wc_get_order( absint( $order ) );
		}

		$this->disable_storing_document_settings();
		
		$invoice = wcpdf_get_invoice( $order );

		switch ( $column ) {
			case 'invoice_number_column':
				$invoice_number = ! empty( $invoice ) && ! empty( $invoice->get_number() ) ? $invoice->get_number() : '';
				echo $invoice_number;
				do_action( 'wcpdf_invoice_number_column_end', $order );
				break;
			case 'invoice_date_column':
				$invoice_date = ! empty( $invoice ) && ! empty( $invoice->get_date() ) ? $invoice->get_date()->date_i18n( wcpdf_date_format( $invoice, 'invoice_date_column' ) ) : '';
				echo $invoice_date;
				do_action( 'wcpdf_invoice_date_column_end', $order );
				break;
			default:
				return;
		}
	}
	
	/**
	 * Check if at least 1 of the invoice columns is enabled.
	 */
	public function invoice_columns_enabled() {
		$is_enabled       = false;
		$invoice          = wcpdf_get_invoice( null );
		$invoice_settings = $invoice->get_settings();
		$invoice_columns  = [
			'invoice_number_column',
			'invoice_date_column',
		];
		
		foreach ( $invoice_columns as $column ) {
			if ( isset( $invoice_settings[$column] ) ) {
				$is_enabled = true;
				break;
			}
		}
		
		return $is_enabled;
	}
	
	/**
	 * Check if the invoice number search is enabled.
	 */
	public function invoice_number_search_enabled() {
		$is_enabled       = false;
		$invoice          = wcpdf_get_invoice( null );
		$invoice_settings = $invoice->get_settings();
		
		if ( isset( $invoice_settings['invoice_number_search'] ) ) {
			$is_enabled = true;
		}
		
		return $is_enabled;
	}
	

	/**
	 * Makes invoice columns sortable
	 */
	public function invoice_columns_sortable( $columns ) {
		$columns['invoice_number_column'] = 'invoice_number_column';
		$columns['invoice_date_column']   = 'invoice_date_column';
		return $columns;
	}

	/**
	 * WC3.X+ sorting
	 */
	public function request_query_sort_by_column( $query_vars ) {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) && ! empty( $query_vars['orderby'] ) ) {
			switch ( $query_vars['orderby'] ) {
				case 'invoice_number_column':
					$query_vars = array_merge( $query_vars, array(
						'meta_key' => '_wcpdf_invoice_number',
						'orderby'  => apply_filters( 'wpo_wcpdf_invoice_number_column_orderby', 'meta_value' ),
					) );
					break;
				case 'invoice_date_column':
					$query_vars = array_merge( $query_vars, array(
						'meta_key' => '_wcpdf_invoice_date',
						'orderby'  => apply_filters( 'wpo_wcpdf_invoice_date_column_orderby', 'meta_value' ),
					) );
					break;
				default:
					return $query_vars;
			}
		}

		return $query_vars;
	}

	/**
	 * Add the meta boxes on the single order page
	 *
	 * @param string $wc_screen_id  Can be also $post_type
	 * @param object $wc_order      Can be also $post
	 * @return void
	 */
	public function add_meta_boxes( $wc_screen_id, $wc_order ) {
		if ( class_exists( CustomOrdersTableController::class ) && function_exists( 'wc_get_container' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			$screen_id = wc_get_page_screen_id( 'shop-order' );
		} else {
			$screen_id = 'shop_order';
		}
		
		if ( $wc_screen_id != $screen_id ) {
			return;
		}

		// resend order emails
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.2', '>=' ) ) {
			add_meta_box(
				'wpo_wcpdf_send_emails',
				__( 'Send order email', 'woocommerce-pdf-invoices-packing-slips' ),
				array( $this, 'send_order_email_meta_box' ),
				$screen_id,
				'side',
				'high'
			);
		}

		// create PDF buttons
		add_meta_box(
			'wpo_wcpdf-box',
			__( 'Create PDF', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'pdf_actions_meta_box' ),
			$screen_id,
			'side',
			'default'
		);

		// Invoice number & date
		add_meta_box(
			'wpo_wcpdf-data-input-box',
			__( 'PDF document data', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'data_input_box_content' ),
			$screen_id,
			'normal',
			'default'
		);
	}

	/**
	 * Resend order emails
	 */
	public function send_order_email_meta_box( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		?>
		<ul class="wpo_wcpdf_send_emails order_actions submitbox">
			<li class="wide" id="actions" style="padding-left:0; padding-right:0; border:0;">
				<select name="wpo_wcpdf_send_emails">
					<option value=""><?php esc_html_e( 'Choose an email to send&hellip;', 'woocommerce-pdf-invoices-packing-slips' ); ?></option>
					<?php
					$mailer           = WC()->mailer();
					$available_emails = apply_filters( 'woocommerce_resend_order_emails_available', array( 'new_order', 'cancelled_order', 'customer_processing_order', 'customer_completed_order', 'customer_invoice' ) );
					$mails            = $mailer->get_emails();
					if ( ! empty( $mails ) && ! empty( $available_emails ) ) { ?>
						<?php
						foreach ( $mails as $mail ) {
							if ( in_array( $mail->id, $available_emails ) && 'no' !== $mail->enabled ) {
								echo '<option value="send_email_' . esc_attr( $mail->id ) . '">' . esc_html( $mail->title ) . '</option>';
							}
						} ?>
						<?php
					}
					?>
				</select>
			</li>
			<li class="wide" style="border:0; padding-left:0; padding-right:0; padding-bottom:0; float:left;">
				<input type="submit" class="button save_order button-primary" name="save" value="<?php esc_attr_e( 'Save order & send email', 'woocommerce-pdf-invoices-packing-slips' ); ?>" />
				<?php
				$url = esc_url( wp_nonce_url( add_query_arg( 'wpo_wcpdf_action', 'resend_email' ), 'generate_wpo_wcpdf' ) );
				?>
			</li>
		</ul>
		<?php
	}

	/**
	 * Create the meta box content on the single order page
	 */
	public function pdf_actions_meta_box( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		
		$this->disable_storing_document_settings();

		$meta_box_actions = array();
		$documents        = WPO_WCPDF()->documents->get_documents();
		foreach ( $documents as $document ) {
			$document_title = $document->get_title();
			if ( $document = wcpdf_get_document( $document->get_type(), $order ) ) {
				$pdf_url               = WPO_WCPDF()->endpoint->get_document_link( $order, $document->get_type() );
				$document_title        = is_callable( array( $document, 'get_title' ) ) ? $document->get_title() : $document_title;
				$document_exists       = is_callable( array( $document, 'exists' ) ) ? $document->exists() : false;
				$document_printed      = $document_exists && is_callable( array( $document, 'printed' ) ) ? $document->printed() : false;
				$document_printed_data = $document_exists && $document_printed && is_callable( array( $document, 'get_printed_data' ) ) ? $document->get_printed_data() : [];
				$document_settings     = get_option( 'wpo_wcpdf_documents_settings_'.$document->get_type() ); // $document-settings might be not updated with the last settings
				$unmark_printed_url    = ! empty( $document_printed_data ) && isset( $document_settings['unmark_printed'] ) ? WPO_WCPDF()->endpoint->get_document_printed_link( 'unmark', $order, $document->get_type() ) : false;
				$manually_mark_printed = WPO_WCPDF()->main->document_can_be_manually_marked_printed( $document );
				$mark_printed_url      = $manually_mark_printed ? WPO_WCPDF()->endpoint->get_document_printed_link( 'mark', $order, $document->get_type() ) : false;
				$class                 = [ $document->get_type() ];
				
				if ( $document_exists ) {
					$class[] = 'exists';
				}
				if ( $document_printed ) {
					$class[] = 'printed';
				}
				
				$meta_box_actions[$document->get_type()] = array(
					'url'                   => esc_url( $pdf_url ),
					'alt'                   => "PDF " . $document_title,
					'title'                 => "PDF " . $document_title,
					'exists'                => $document_exists,
					'printed'               => $document_printed,
					'printed_data'          => $document_printed_data,
					'unmark_printed_url'    => $unmark_printed_url,
					'manually_mark_printed' => $manually_mark_printed,
					'mark_printed_url'      => $mark_printed_url,
					'class'                 => apply_filters( 'wpo_wcpdf_action_button_class', implode( ' ', $class ), $document ),
				);
			}
		}

		$meta_box_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_box_actions, $order->get_id() );

		?>
		<ul class="wpo_wcpdf-actions">
			<?php
			foreach ( $meta_box_actions as $document_type => $data ) {
				$url                   = isset( $data['url'] ) ? esc_attr( $data['url'] ) : '';
				$class                 = isset( $data['class'] ) ? esc_attr( $data['class'] ) : '';
				$alt                   = isset( $data['alt'] ) ? esc_attr( $data['alt'] ) : '';
				$title                 = isset( $data['title'] ) ? esc_attr( $data['title'] ) : '';
				$exists                = isset( $data['exists'] ) && $data['exists'] ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path></svg>' : '';
				$manually_mark_printed = isset( $data['manually_mark_printed'] ) && $data['manually_mark_printed'] && ! empty( $data['mark_printed_url'] ) ? '<p class="printed-data">&#x21b3; <a href="'.$data['mark_printed_url'].'">'.__( 'Mark printed', 'woocommerce-pdf-invoices-packing-slips' ).'</a></p>' : '';
				$printed               = isset( $data['printed'] ) && $data['printed'] ? '<svg class="icon-printed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 4H16V6H8V4ZM18 6H22V18H18V22H6V18H2V6H6V2H18V6ZM20 16H18V14H6V16H4V8H20V16ZM8 16H16V20H8V16ZM8 10H6V12H8V10Z"></path></svg>' : '';
				$unmark_printed        = isset( $data['unmark_printed_url'] ) && $data['unmark_printed_url'] ? '<a class="unmark_printed" href="'.$data['unmark_printed_url'].'">'.__( 'Unmark', 'woocommerce-pdf-invoices-packing-slips' ).'</a>' : '';
				$printed_data          = isset( $data['printed'] ) && $data['printed'] && ! empty( $data['printed_data']['date'] ) ? '<p class="printed-data">&#x21b3; '.$printed.''.date_i18n( 'Y/m/d g:i:s a', strtotime( $data['printed_data']['date'] ) ).''.$unmark_printed.'</p>' : '';
				
				
				printf(
					'<li><a href="%1$s" class="button %2$s" target="_blank" alt="%3$s">%4$s%5$s</a>%6$s%7$s</li>',
					$url,
					$class,
					$alt,
					$title,
					$exists,
					$manually_mark_printed,
					$printed_data
				);
			}
			?>
		</ul>
		<?php
	}

	public function data_input_box_content( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$this->disable_storing_document_settings();

		$invoice = wcpdf_get_document( 'invoice', $order );

		do_action( 'wpo_wcpdf_meta_box_start', $order, $this );

		if ( $invoice ) {
			// data
			$data = array(
				'number' => array(
					'label' => __( 'Invoice number:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'date' => array(
					'label' => __( 'Invoice date:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'display_date' =>  array(
					'label' => __( 'Invoice display date:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'creation_trigger' =>  array(
					'label' => __( 'Invoice created via:', 'woocommerce-pdf-invoices-packing-slips' ),
				),
				'notes' => array(
					'label' => __( 'Notes (printed in the invoice):', 'woocommerce-pdf-invoices-packing-slips' ),
				),

			);
			// output
			$this->output_number_date_edit_fields( $invoice, $data );

		}

		do_action( 'wpo_wcpdf_meta_box_end', $order, $this );
	}

	public function get_current_values_for_document( $document, $data ) {
		$current = array(
			'number' => array(
				'plain'     => $document->exists() && ! empty( $document->get_number() ) ? $document->get_number()->get_plain() : '',
				'formatted' => $document->exists() && ! empty( $document->get_number() ) ? $document->get_number()->get_formatted() : '',
				'name'      => "_wcpdf_{$document->slug}_number",
			),
			'date' => array(
				'formatted' => $document->exists() && ! empty( $document->get_date() ) ? $document->get_date()->date_i18n( wc_date_format().' @ '.wc_time_format() ) : '',
				'date'      => $document->exists() && ! empty( $document->get_date() ) ? $document->get_date()->date_i18n( 'Y-m-d' ) : date_i18n( 'Y-m-d' ),
				'hour'      => $document->exists() && ! empty( $document->get_date() ) ? $document->get_date()->date_i18n( 'H' ) : date_i18n( 'H' ),
				'minute'    => $document->exists() && ! empty( $document->get_date() ) ? $document->get_date()->date_i18n( 'i' ) : date_i18n( 'i' ),
				'name'      => "_wcpdf_{$document->slug}_date",
			),
		);

		if ( ! empty( $data['notes'] ) ) {
			$current['notes'] = array(
				'value' => $document->get_document_notes(),
				'name'  => "_wcpdf_{$document->slug}_notes",
			);
		}

		if ( ! empty( $data['display_date'] ) ) {
			$current['display_date'] = array(
				'value' => $document->document_display_date(),
				'name'  => "_wcpdf_{$document->slug}_display_date",
			);
		}

		if ( ! empty( $data['creation_trigger'] ) ) {
			$document_triggers = WPO_WCPDF()->main->get_document_triggers();
			$creation_trigger  = $document->get_creation_trigger();
			$current['creation_trigger'] = array(
				'value' => isset( $document_triggers[$creation_trigger] ) ? $document_triggers[$creation_trigger] : '',
				'name'  => "_wcpdf_{$document->slug}_creation_trigger",
			);
		}
		
		foreach ( $data as $key => $value ) {
			if ( isset( $current[$key] ) ) {
				$data[$key] = array_merge( $current[$key], $value );
			}
		}

		return apply_filters( 'wpo_wcpdf_current_values_for_document', $data, $document );
	}

	public function output_number_date_edit_fields( $document, $data ) {
		if( empty( $document ) || empty( $data ) ) return;
		$data = $this->get_current_values_for_document( $document, $data );
		
		?>
		<div class="wcpdf-data-fields" data-document="<?= esc_attr( $document->get_type() ); ?>" data-order_id="<?php echo esc_attr( $document->order->get_id() ); ?>">
			<section class="wcpdf-data-fields-section number-date">
				<!-- Title -->
				<h4>
					<?php echo wp_kses_post( $document->get_title() ); ?>
					<?php if( $document->exists() && ( isset( $data['number'] ) || isset( $data['date'] ) ) && $this->user_can_manage_document( $document->get_type() ) ) : ?>
						<span class="wpo-wcpdf-edit-date-number dashicons dashicons-edit"></span>
						<span class="wpo-wcpdf-delete-document dashicons dashicons-trash" data-action="delete" data-nonce="<?php echo wp_create_nonce( "wpo_wcpdf_delete_document" ); ?>"></span>
						<?php do_action( 'wpo_wcpdf_document_actions', $document ); ?>
					<?php endif; ?>
				</h4>

				<!-- Read only -->
				<div class="read-only">
					<?php if( $document->exists() ) : ?>
						<?php if( isset( $data['number'] ) ) : ?>
						<div class="<?= esc_attr( $document->get_type() ); ?>-number">
							<p class="form-field <?= esc_attr( $data['number']['name'] ); ?>_field">	
								<p>
									<span><strong><?= wp_kses_post( $data['number']['label'] ); ?></strong></span>
									<span><?= esc_attr( $data['number']['formatted'] ); ?></span>
								</p>
							</p>
						</div>
						<?php endif; ?>
						<?php if( isset( $data['date'] ) ) : ?>
						<div class="<?= esc_attr( $document->get_type() ); ?>-date">
							<p class="form-field form-field-wide">
								<p>
									<span><strong><?= wp_kses_post( $data['date']['label'] ); ?></strong></span>
									<span><?= esc_attr( $data['date']['formatted'] ); ?></span>
								</p>
							</p>
						</div>
						<?php endif; ?>
						<?php if( isset( $data['display_date'] ) ) : ?>
						<div class="<?= esc_attr( $document->get_type() ); ?>-display-date">
							<p class="form-field form-field-wide">
								<p>
									<span><strong><?= wp_kses_post( $data['display_date']['label'] ); ?></strong></span>
									<span><?= esc_attr( $data['display_date']['value'] ); ?></span>
								</p>
							</p>
						</div>
						<?php endif; ?>
						<?php if ( isset( $data['creation_trigger'] ) && ! empty( $data['creation_trigger']['value'] ) ) : ?>
						<div class="<?= esc_attr( $document->get_type() ); ?>-creation-status">
							<p class="form-field form-field-wide">
								<p>
									<span><strong><?= wp_kses_post( $data['creation_trigger']['label'] ); ?></strong></span>
									<span><?= esc_attr( $data['creation_trigger']['value'] ); ?></span>
								</p>
							</p>
						</div>
						<?php endif; ?>	
											
						<?php do_action( 'wpo_wcpdf_meta_box_after_document_data', $document, $document->order ); ?>
					<?php else : ?>
						<?php /* translators: document title */ ?>
						<?php
						if ( $this->user_can_manage_document( $document->get_type() ) ) {
							printf(
								'<span class="wpo-wcpdf-set-date-number button">%s</span>',
								sprintf(
									/* translators: document title */
									esc_html__( 'Set %s number & date', 'woocommerce-pdf-invoices-packing-slips' ),
									wp_kses_post( $document->get_title() )
								)
							); 
						} else {
							printf( '<p>%s</p>', esc_html__( 'You do not have sufficient permissions to edit this document.', 'woocommerce-pdf-invoices-packing-slips' ) );
						}
						?>
						
					<?php endif; ?>
				</div>

				<!-- Editable -->
				<div class="editable">
					<?php if( isset( $data['number'] ) ) : ?>
					<p class="form-field <?= esc_attr( $data['number']['name'] ); ?>_field">	
						<label for="<?= esc_attr( $data['number']['name'] ); ?>"><?= wp_kses_post( $data['number']['label'] ); ?></label>
						<input type="text" class="short" style="" name="<?= esc_attr( $data['number']['name'] ); ?>" id="<?= esc_attr( $data['number']['name'] ); ?>" value="<?= esc_attr( $data['number']['plain'] ); ?>" disabled="disabled" > (<?= esc_html__( 'unformatted!', 'woocommerce-pdf-invoices-packing-slips' ); ?>)
					</p>
					<?php endif; ?>
					<?php if( isset( $data['date'] ) ) : ?>
					<p class="form-field form-field-wide">
						<label for="<?= esc_attr( $data['date']['name'] ); ?>[date]"><?= wp_kses_post( $data['date']['label'] ); ?></label>
						<input type="text" class="date-picker-field" name="<?= esc_attr( $data['date']['name'] ); ?>[date]" id="<?= esc_attr( $data['date']['name'] ); ?>[date]" maxlength="10" value="<?= esc_attr( $data['date']['date'] ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" disabled="disabled"/>@<input type="number" class="hour" disabled="disabled" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ); ?>" name="<?= esc_attr( $data['date']['name'] ); ?>[hour]" id="<?= esc_attr( $data['date']['name'] ); ?>[hour]" min="0" max="23" size="2" value="<?= esc_attr( $data['date']['hour'] ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ); ?>" name="<?= esc_attr( $data['date']['name'] ); ?>[minute]" id="<?= esc_attr( $data['date']['name'] ); ?>[minute]" min="0" max="59" size="2" value="<?= esc_attr( $data['date']['minute'] ); ?>" pattern="[0-5]{1}[0-9]{1}"  disabled="disabled" />
					</p>
					<?php endif; ?>
				</div>

				<!-- Document Notes -->
				<?php if( array_key_exists( 'notes', $data ) ) : ?>

				<?php do_action( 'wpo_wcpdf_meta_box_before_document_notes', $document, $document->order ); ?>

				<!-- Read only -->
				<div class="read-only">
					<span><strong><?= wp_kses_post( $data['notes']['label'] ); ?></strong></span>
					<?php if ( $this->user_can_manage_document( $document->get_type() ) ) : ?>
						<span class="wpo-wcpdf-edit-document-notes dashicons dashicons-edit" data-edit="notes"></span>
					<?php endif; ?>
					<p><?= ( $data['notes']['value'] == strip_tags( $data['notes']['value'] ) ) ? wp_kses_post( nl2br( $data['notes']['value'] ) ) : wp_kses_post( $data['notes']['value'] ); ?></p>
				</div>
				<!-- Editable -->
				<div class="editable-notes">
					<p class="form-field form-field-wide">
						<label for="<?= esc_attr( $data['notes']['name'] ); ?>"><?= wp_kses_post( $data['notes']['label'] ); ?></label>
						<p><textarea name="<?= esc_attr( $data['notes']['name'] ); ?>" class="<?= esc_attr( $data['notes']['name'] ); ?>" cols="60" rows="5" disabled="disabled"><?= wp_kses_post( $data['notes']['value'] ); ?></textarea></p>
					</p>
				</div>

				<?php do_action( 'wpo_wcpdf_meta_box_after_document_notes', $document, $document->order ); ?>

				<?php endif; ?>
				<!-- / Document Notes -->

			</section>

			<!-- Save/Cancel buttons -->
			<section class="wcpdf-data-fields-section wpo-wcpdf-document-buttons">
				<div>
					<a class="button button-primary wpo-wcpdf-save-document" data-nonce="<?php echo wp_create_nonce( "wpo_wcpdf_save_document" ); ?>" data-action="save"><?php esc_html_e( 'Save changes', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					<a class="button wpo-wcpdf-cancel"><?php esc_html_e( 'Cancel', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
				</div>
			</section>
			<!-- / Save/Cancel buttons -->
		</div>
		<?php
	}

	public function add_regenerate_document_button( $document ) {
		$document_settings = $document->get_settings( true );
		if ( $document->use_historical_settings() == true || isset( $document_settings['archive_pdf'] ) ) {
			printf( '<span class="wpo-wcpdf-regenerate-document dashicons dashicons-update-alt" data-nonce="%s" data-action="regenerate"></span>', wp_create_nonce( "wpo_wcpdf_regenerate_document" ) );
		}
	}

	/**
	 * Add actions to menu, WP3.5+
	 */
	public function bulk_actions( $actions ) {
		foreach ($this->get_bulk_actions() as $action => $title) {
			$actions[$action] = $title;
		}
		return $actions;
	}

	/**
	 * Add actions to menu, legacy method
	 */
	public function bulk_actions_js() {
		if ( $this->is_order_page() ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				<?php foreach ($this->get_bulk_actions() as $action => $title) { ?>
				jQuery('<option>').val('<?php echo esc_attr( $action ); ?>').html('<?php echo esc_attr( $title ); ?>').appendTo("select[name='action'], select[name='action2']");
				<?php }	?>
			});
			</script>
			<?php
		}
	}

	public function get_bulk_actions() {
		$actions = array();
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ($documents as $document) {
			$actions[$document->get_type()] = "PDF " . $document->get_title();
		}

		return apply_filters( 'wpo_wcpdf_bulk_actions', $actions );
	}

	/**
	 * Save invoice number date
	 */
	public function save_invoice_number_date( $order_id, $order ) {
		if ( ( empty( $order ) || ! ( $order instanceof \WC_Order || is_subclass_of( $order, '\WC_Abstract_Order') ) ) && ! empty( $order_id ) ) {
			$order = wc_get_order( $order_id );
		} else {
			return;
		}

		$order_type = $order->get_type();

		if ( $order_type == 'shop_order' ) {
			// bail if this is not an actual 'Save order' action
			if ( ! isset( $_POST['action'] ) || $_POST['action'] != 'editpost' ) {
				return;
			}
			
			// Check if user is allowed to change invoice data
			if ( ! $this->user_can_manage_document( 'invoice' ) ) {
				return;
			}

			$form_data = [];
			
			if ( $invoice = wcpdf_get_invoice( $order ) ) {
				$is_new        = false === $invoice->exists();
				$form_data     = stripslashes_deep( $_POST );
				$document_data = $this->process_order_document_form_data( $form_data, $invoice->slug );
				if ( empty( $document_data ) ) {
					return;
				}
				
				
				$invoice->set_data( $document_data, $order );

				// check if we have number, and if not generate one
				if  ( $invoice->get_date() && ! $invoice->get_number() && is_callable( array( $invoice, 'init_number' ) ) ) {
					$invoice->init_number();
				}

				$invoice->save();

				if ( $is_new ) {
					WPO_WCPDF()->main->log_document_creation_to_order_notes( $invoice, 'document_data' );
					WPO_WCPDF()->main->mark_document_printed( $invoice, 'document_data' );
				}
			}

			// allow other documents to hook here and save their form data
			do_action( 'wpo_wcpdf_on_save_invoice_order_data', $form_data, $order, $this );
		}
	}

	/**
	 * Document objects are created in order to check for existence and retrieve data,
	 * but we don't want to store the settings for uninitialized documents.
	 * Only use in frontend/backed (page requests), otherwise settings will never be stored!
	 */
	public function disable_storing_document_settings() {
		add_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function restore_storing_document_settings() {
		remove_filter( 'wpo_wcpdf_document_store_settings', array( $this, 'return_false' ), 9999 );
	}

	public function return_false() {
		return false;
	}

	/**
	 * Send emails manually
	 */
	public function send_emails( $post_or_order_object_id, $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof \WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		if ( ! empty( $_POST['wpo_wcpdf_send_emails'] ) ) {
			$action = wc_clean( $_POST['wpo_wcpdf_send_emails'] );
			if ( strstr( $action, 'send_email_' ) ) {
				$email_to_send = str_replace( 'send_email_', '', $action );
				// Switch back to the site locale.
				wc_switch_to_site_locale();
				do_action( 'woocommerce_before_resend_order_emails', $order, $email_to_send );
				// Ensure gateways are loaded in case they need to insert data into the emails.
				WC()->payment_gateways();
				WC()->shipping();
				// Load mailer.
				$mailer = WC()->mailer();
				$mails  = $mailer->get_emails();
				if ( ! empty( $mails ) ) {
					foreach ( $mails as $mail ) {
						if ( $mail->id == $email_to_send ) {
							add_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
							$mail->trigger( $order->get_id(), $order );
							remove_filter( 'woocommerce_new_order_email_allows_resend', '__return_true' );
							/* translators: %s: email title */
							$order->add_order_note( sprintf( esc_html__( '%s email notification manually sent.', 'woocommerce-pdf-invoices-packing-slips' ), $mail->title ), false, true );
						}
					}
				}
				do_action( 'woocommerce_after_resend_order_email', $order, $email_to_send );
				// Restore user locale.
				wc_restore_locale();
				// Change the post saved message.
				add_filter( 'redirect_post_location', function( $location ) {
					// messages in includes/admin/class-wc-admin-post-types.php
					// 11 => 'Order updated and sent.'
					return esc_url_raw( add_query_arg( 'message', 11, $location ) );
				} );
			}
		}
	}

	/**
	 * Add invoice number to order search scope
	 */
	public function search_fields ( $custom_fields ) {
		$custom_fields[] = '_wcpdf_invoice_number';
		$custom_fields[] = '_wcpdf_formatted_invoice_number';
		return $custom_fields;
	}

	/**
	 * Check if this is a shop_order page (edit or list)
	 */
	public function is_order_page() {
		$screen = get_current_screen();
		if ( ! is_null( $screen ) && in_array( $screen->id, array( 'shop_order', 'edit-shop_order', 'woocommerce_page_wc-orders' ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	public function user_can_manage_document( $document_type ) {
		return apply_filters( 'wpo_wcpdf_current_user_is_allowed', ( current_user_can( 'manage_woocommerce_orders' ) || current_user_can( 'edit_shop_orders' ) ), $document_type );
	}

	/**
	 * Save, regenerate or delete a document from AJAX request
	 */
	public function ajax_crud_document() {
		if ( check_ajax_referer( 'wpo_wcpdf_regenerate_document', 'security', false ) === false && check_ajax_referer( 'wpo_wcpdf_save_document', 'security', false ) === false && check_ajax_referer( 'wpo_wcpdf_delete_document', 'security', false ) === false ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Nonce expired!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		if ( ! isset($_POST['action']) ||  ! in_array( $_POST['action'], array( 'wpo_wcpdf_regenerate_document', 'wpo_wcpdf_save_document', 'wpo_wcpdf_delete_document' ) ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Bad action!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		if( empty($_POST['order_id']) || empty($_POST['document_type']) || empty($_POST['action_type']) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Incomplete request!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		if ( ! $this->user_can_manage_document( sanitize_text_field( $_POST['document_type'] ) ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'No permissions!', 'woocommerce-pdf-invoices-packing-slips' ),
			) );
		}

		$order_id        = absint( $_POST['order_id'] );
		$order           = wc_get_order( $order_id );
		$document_type   = sanitize_text_field( $_POST['document_type'] );
		$action_type     = sanitize_text_field( $_POST['action_type'] );
		$notice          = sanitize_text_field( $_POST['wpcdf_document_data_notice'] );

		// parse form data
		parse_str( $_POST['form_data'], $form_data );
		if ( is_array( $form_data ) ) {
			foreach ( $form_data as $key => &$value ) {
				if ( is_array( $value ) && !empty( $value[$order_id] ) ) {
					$value = $value[$order_id];
				}
			}
		}
		$form_data = stripslashes_deep( $form_data );

		// notice messages
		$notice_messages = array(
			'saved'       => array(
				'success' => __( 'Document data saved!', 'woocommerce-pdf-invoices-packing-slips' ),
				'error'   => __( 'An error occurred while saving the document data!', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'regenerated' => array(
				'success' => __( 'Document regenerated!', 'woocommerce-pdf-invoices-packing-slips' ),
				'error'   => __( 'An error occurred while regenerating the document!', 'woocommerce-pdf-invoices-packing-slips' ),
			),
			'deleted' => array(
				'success' => __( 'Document deleted!', 'woocommerce-pdf-invoices-packing-slips' ),
				'error'   => __( 'An error occurred while deleting the document!', 'woocommerce-pdf-invoices-packing-slips' ),
			),
		);

		try {
			$document = wcpdf_get_document( $document_type, wc_get_order( $order_id ) );

			if( ! empty( $document ) ) {

				// perform legacy date fields replacements check
				if( isset( $form_data["_wcpdf_{$document->slug}_date"] ) && ! is_array( $form_data["_wcpdf_{$document->slug}_date"] ) ) {
					$form_data = $this->legacy_date_fields_replacements( $form_data, $document->slug );
				}

				// save document data
				$document_data = $this->process_order_document_form_data( $form_data, $document->slug );

				// on regenerate
				if( $action_type == 'regenerate' && $document->exists() ) {
					$document->regenerate( $order, $document_data );
					WPO_WCPDF()->main->log_document_creation_trigger_to_order_meta( $document, 'document_data', true );
					$response = array(
						'message' => $notice_messages[$notice]['success'],
					);

				// on delete
				} elseif( $action_type == 'delete' && $document->exists() ) {
					$document->delete();

					$response = array(
						'message' => $notice_messages[$notice]['success'],
					);

				// on save
				} elseif( $action_type == 'save' ) {
					$is_new = false === $document->exists();
					$document->set_data( $document_data, $order );

					// check if we have number, and if not generate one
					if( $document->get_date() && ! $document->get_number() && is_callable( array( $document, 'init_number' ) ) ) {
						$document->init_number();
					}

					$document->save();

					if ( $is_new ) {
						WPO_WCPDF()->main->log_document_creation_to_order_notes( $document, 'document_data' );
						WPO_WCPDF()->main->log_document_creation_trigger_to_order_meta( $document, 'document_data' );
						WPO_WCPDF()->main->mark_document_printed( $document, 'document_data' );
					}
					$response      = array(
						'message' => $notice_messages[$notice]['success'],
					);

				// document not exist
				} else {
					$message_complement = __( 'Document does not exist.', 'woocommerce-pdf-invoices-packing-slips' );
					wp_send_json_error( array(
						'message' => wp_kses_post( $notice_messages[$notice]['error'] . ' ' . $message_complement ),
					) );
				}

				// clean/escape response message
				if ( ! empty( $response['message'] ) ) {
					$response['message'] = wp_kses_post( $response['message'] );
				}

				wp_send_json_success( $response );

			} else {
				$message_complement = __( 'Document is empty.', 'woocommerce-pdf-invoices-packing-slips' );
				wp_send_json_error( array(
					'message' => wp_kses_post( $notice_messages[$notice]['error'] . ' ' . $message_complement ),
				) );
			}
		} catch ( \Throwable $e ) {
			wp_send_json_error( array(
				'message' => wp_kses_post( $notice_messages[$notice]['error'] . ' ' . $e->getMessage() ),
			) );			
		}
	}

	public function legacy_date_fields_replacements( $form_data, $document_slug ) {
		$legacy_date   = sanitize_text_field( $form_data["_wcpdf_{$document_slug}_date"] );
		$legacy_hour   = sanitize_text_field( $form_data["_wcpdf_{$document_slug}_date_hour"] );
		$legacy_minute = sanitize_text_field( $form_data["_wcpdf_{$document_slug}_date_minute"] );
		unset( $form_data["_wcpdf_{$document_slug}_date_hour"] );
		unset( $form_data["_wcpdf_{$document_slug}_date_minute"] );

		$form_data["_wcpdf_{$document_slug}_date"] = array(
			'date'   => $legacy_date,
			'hour'   => $legacy_hour,
			'minute' => $legacy_minute,
		);

		return $form_data;
	}

	public function debug_enabled_warning( $wp_admin_bar ) {
		if ( isset(WPO_WCPDF()->settings->debug_settings['enable_debug']) && current_user_can( 'administrator' ) ) {
			$status_settings_url = 'admin.php?page=wpo_wcpdf_options_page&tab=debug';
			$title = __( 'DEBUG output enabled', 'woocommerce-pdf-invoices-packing-slips' );
			$args = array(
				'id'    => 'admin_bar_wpo_debug_mode',
				'title' => sprintf( '<a href="%s" style="background-color: red; color: white;">%s</a>', esc_attr( $status_settings_url ), esc_html( $title ) ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	public function process_order_document_form_data( $form_data, $document_slug )
	{
		$data = array();

		if( isset( $form_data['_wcpdf_'.$document_slug.'_number'] ) ) {
			$data['number'] = sanitize_text_field( $form_data['_wcpdf_'.$document_slug.'_number'] );
		}

		$date_entered = ! empty( $form_data['_wcpdf_'.$document_slug.'_date'] ) && ! empty( $form_data['_wcpdf_'.$document_slug.'_date']['date'] );
		if( $date_entered ) {
			$date         = $form_data['_wcpdf_'.$document_slug.'_date']['date'];
			$hour         = ! empty( $form_data['_wcpdf_'.$document_slug.'_date']['hour'] ) ? $form_data['_wcpdf_'.$document_slug.'_date']['hour'] : '00';
			$minute       = ! empty( $form_data['_wcpdf_'.$document_slug.'_date']['minute'] ) ? $form_data['_wcpdf_'.$document_slug.'_date']['minute'] : '00';

			// clean & sanitize input
			$date         = date( 'Y-m-d', strtotime( $date ) );
			$hour         = sprintf('%02d', intval( $hour ));
			$minute       = sprintf('%02d', intval( $minute ) );
			$data['date'] = "{$date} {$hour}:{$minute}:00";

		} elseif ( ! $date_entered && !empty( $_POST['_wcpdf_'.$document_slug.'_number'] ) ) {
			$data['date'] = current_time( 'timestamp', true );
		}

		if ( isset( $form_data['_wcpdf_'.$document_slug.'_notes'] ) ) {
			// allowed HTML
			$allowed_html = array(
				'a'		=> array(
					'href' 	=> array(),
					'title' => array(),
					'id' 	=> array(),
					'class'	=> array(),
					'style'	=> array(),
				),
				'br'	=> array(),
				'em'	=> array(),
				'strong'=> array(),
				'div'	=> array(
					'id'	=> array(),
					'class' => array(),
					'style'	=> array(),
				),
				'span'	=> array(
					'id' 	=> array(),
					'class'	=> array(),
					'style'	=> array(),
				),
				'p'		=> array(
					'id' 	=> array(),
					'class' => array(),
					'style' => array(),
				),
				'b'		=> array(),
			);
			
			$data['notes'] = wp_kses( $form_data['_wcpdf_'.$document_slug.'_notes'], $allowed_html );
		}

		return $data;
	}
}

endif; // class_exists

return new Admin();