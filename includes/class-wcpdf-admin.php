<?php
namespace WPO\WC\PDF_Invoices;

use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Admin' ) ) :

class Admin {
	function __construct()	{
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_number_column' ), 999 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_number_column_data' ), 2 );
		add_action( 'add_meta_boxes_shop_order', array( $this, 'add_meta_boxes' ) );
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.3', '>=' ) ) {
			add_action( 'bulk_actions-edit-shop_order', array( $this, 'bulk_actions' ), 20 );
		} else {
			add_action( 'admin_footer', array( $this, 'bulk_actions_js' ) );
		}
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'search_fields' ) );

		add_action( 'woocommerce_process_shop_order_meta', array( $this,'save_invoice_number_date' ), 35, 2 );

		// manually send emails
		// WooCommerce core processes order actions at priority 50
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'send_emails' ), 60, 2 );

		add_action( 'admin_notices', array( $this, 'review_plugin_notice' ) );
		add_action( 'admin_notices', array( $this, 'install_wizard_notice' ) );

		add_action( 'init', array( $this, 'setup_wizard') );
		// add_action( 'wpo_wcpdf_after_pdf', array( $this,'update_pdf_counter' ), 10, 2 );

		add_action( 'admin_bar_menu', array( $this, 'debug_enabled_warning' ), 999 );


		add_filter( 'manage_edit-shop_order_sortable_columns', array( $this, 'invoice_number_column_sortable' ) );
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '>=' ) ) {
			add_filter( 'request', array( $this, 'request_query_sort_by_invoice_number' ) );
		} else {
			add_filter( 'pre_get_posts', array( $this, 'pre_get_posts_sort_by_invoice_number' ) );
		}

		// AJAX actions for deleting and regenerating document data
		add_action( 'wp_ajax_wpo_wcpdf_delete_document', array( $this, 'delete_document' ) );
		add_action( 'wp_ajax_wpo_wcpdf_regenerate_document', array( $this, 'regenerate_document' ) );

		add_action( 'wpo_wcpdf_document_actions', array( $this, 'add_regenerate_document_button' ) );
		add_action( 'wpo_wcpdf_document_actions', array( $this, 'add_document_action_feedback_icons' ) );
	}

	// display review admin notice after 100 pdf downloads
	public function review_plugin_notice() {
		if ( $this->is_order_page() === false && !( isset( $_GET['page'] ) && $_GET['page'] == 'wpo_wcpdf_options_page' ) ) {
			return;
		}
		
		if ( get_option( 'wpo_wcpdf_review_notice_dismissed' ) !== false ) {
			return;
		} else {
			if ( isset( $_GET['wpo_wcpdf_dismis_review'] ) ) {
				update_option( 'wpo_wcpdf_review_notice_dismissed', true );
				return;
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
					<h3><?php printf( __( 'Wow, you have created more than %d invoices with our plugin!', 'woocommerce-pdf-invoices-packing-slips' ), $rounded_count ); ?></h3>
					<p><?php _e( 'It would mean a lot to us if you would quickly give our plugin a 5-star rating. Help us spread the word and boost our motivation!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<ul>
						<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/?rate=5#new-post" class="button"><?php _e( 'Yes you deserve it!', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></a></li>
						<li><a href="<?php echo esc_url( add_query_arg( 'wpo_wcpdf_dismis_review', true ) ); ?>" class="wpo-wcpdf-dismiss"><?php _e( 'Hide this message', 'woocommerce-pdf-invoices-packing-slips' ); ?> / <?php _e( 'Already did!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
						<li><a href="mailto:support@wpovernight.com?Subject=Here%20is%20how%20I%20think%20you%20can%20do%20better"><?php _e( 'Actually, I have a complaint...', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
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
			if ( isset( $_GET['wpo_wcpdf_dismis_install'] ) ) {
				update_option( 'wpo_wcpdf_install_notice_dismissed', true );
				return;
			}

			if ( get_transient( 'wpo_wcpdf_new_install' ) !== false ) {
				?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-install-notice">
					<p><strong><?php _e( 'New to WooCommerce PDF Invoices & Packing Slips?', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong> &#8211; <?php _e( 'Jumpstart the plugin by following our wizard!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpo-wcpdf-setup' ) ); ?>" class="button-primary"><?php _e( 'Run the Setup Wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a> <a href="<?php echo esc_url( add_query_arg( 'wpo_wcpdf_dismis_install', true ) ); ?>" class="wpo-wcpdf-dismiss-wizard"><?php _e( 'I am the wizard', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></p>
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
		foreach ($documents as $document) {
			$document_title = $document->get_title();
			$icon = !empty($document->icon) ? $document->icon : WPO_WCPDF()->plugin_url() . "/assets/images/generic_document.png";
			if ( $document = wcpdf_get_document( $document->get_type(), $order ) ) {
				$document_title = method_exists($document, 'get_title') ? $document->get_title() : $document_title;
				$listing_actions[$document->get_type()] = array(
					'url'		=> wp_nonce_url( admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type={$document->get_type()}&order_ids=" . WCX_Order::get_id( $order ) ), 'generate_wpo_wcpdf' ),
					'img'		=> $icon,
					'alt'		=> "PDF " . $document_title,
					'exists'	=> method_exists($document, 'exists') ? $document->exists() : false,
				);
			}
		}

		$listing_actions = apply_filters( 'wpo_wcpdf_listing_actions', $listing_actions, $order );			

		foreach ($listing_actions as $action => $data) {
			?>
			<a href="<?php echo $data['url']; ?>" class="button tips wpo_wcpdf <?php echo $data['exists'] == true ? "exists " . $action : $action; ?>" target="_blank" alt="<?php echo $data['alt']; ?>" data-tip="<?php echo $data['alt']; ?>">
				<img src="<?php echo $data['img']; ?>" alt="<?php echo $data['alt']; ?>" width="16">
			</a>
			<?php
		}
	}
	
	/**
	 * Create additional Shop Order column for Invoice Numbers
	 * @param array $columns shop order columns
	 */
	public function add_invoice_number_column( $columns ) {
		// get invoice settings
		$invoice = wcpdf_get_invoice( null );
		$invoice_settings = $invoice->get_settings();
		if ( !isset( $invoice_settings['invoice_number_column'] ) ) {
			return $columns;
		}

		// put the column after the Status column
		$new_columns = array_slice($columns, 0, 2, true) +
			array( 'pdf_invoice_number' => __( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ) ) +
			array_slice($columns, 2, count($columns) - 1, true) ;
		return $new_columns;
	}

	/**
	 * Display Invoice Number in Shop Order column (if available)
	 * @param  string $column column slug
	 */
	public function invoice_number_column_data( $column ) {
		global $post, $the_order;

		if ( $column == 'pdf_invoice_number' ) {
			$this->disable_storing_document_settings();
			if ( empty( $the_order ) || WCX_Order::get_id( $the_order ) != $post->ID ) {
				$order = WCX::get_order( $post->ID );
				if ( $invoice = wcpdf_get_invoice( $order ) ) {
					echo $invoice->get_number();
				}
				do_action( 'wcpdf_invoice_number_column_end', $order );
			} else {
				if ( $invoice = wcpdf_get_invoice( $the_order ) ) {
					echo $invoice->get_number();
				}
				do_action( 'wcpdf_invoice_number_column_end', $the_order );
			}
		}
	}

	/**
	 * Add the meta box on the single order page
	 */
	public function add_meta_boxes() {
		// resend order emails
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.2', '>=' ) ) {
			add_meta_box(
				'wpo_wcpdf_send_emails',
				__( 'Send order email', 'woocommerce-pdf-invoices-packing-slips' ),
				array( $this, 'send_order_email_meta_box' ),
				'shop_order',
				'side',
				'high'
			);
		}

		// create PDF buttons
		add_meta_box(
			'wpo_wcpdf-box',
			__( 'Create PDF', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'pdf_actions_meta_box' ),
			'shop_order',
			'side',
			'default'
		);

		// Invoice number & date
		add_meta_box(
			'wpo_wcpdf-data-input-box',
			__( 'PDF Invoice data', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'data_input_box_content' ),
			'shop_order',
			'normal',
			'default'
		);
	}

	/**
	 * Resend order emails
	 */
	public function send_order_email_meta_box( $post ) {
		global $theorder;
		// This is used by some callbacks attached to hooks such as woocommerce_resend_order_emails_available
		// which rely on the global to determine if emails should be displayed for certain orders.
		if ( ! is_object( $theorder ) ) {
			$theorder = wc_get_order( $post->ID );
		}
		?>
		<ul class="wpo_wcpdf_send_emails submitbox">
			<li class="wide" id="actions">
				<select name="wpo_wcpdf_send_emails">
					<option value=""></option>
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
				<input type="submit" class="button save_order button-primary" name="save" value="<?php esc_attr_e( 'Save order & send email', 'woocommerce-pdf-invoices-packing-slips' ); ?>" />
				<?php
				$title = __( 'Send email', 'woocommerce-pdf-invoices-packing-slips' );
				$url = wp_nonce_url( add_query_arg('wpo_wcpdf_action','resend_email'), 'generate_wpo_wcpdf' );
				// printf('<a href="%s" class="button wpo_wcpdf_send_email"><span>%s</span></a>')
				?>
			</li>
		</ul>
		<?php
	}

	/**
	 * Create the meta box content on the single order page
	 */
	public function pdf_actions_meta_box( $post ) {
		global $post_id;
		$this->disable_storing_document_settings();

		$meta_box_actions = array();
		$documents = WPO_WCPDF()->documents->get_documents();
		$order = WCX::get_order( $post->ID );
		foreach ($documents as $document) {
			$document_title = $document->get_title();
			if ( $document = wcpdf_get_document( $document->get_type(), $order ) ) {
				$document_title = method_exists($document, 'get_title') ? $document->get_title() : $document_title;
				$meta_box_actions[$document->get_type()] = array(
					'url'		=> wp_nonce_url( admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type={$document->get_type()}&order_ids=" . $post_id ), 'generate_wpo_wcpdf' ),
					'alt'		=> esc_attr( "PDF " . $document_title ),
					'title'		=> "PDF " . $document_title,
					'exists'	=> method_exists($document, 'exists') ? $document->exists() : false,
				);
			}
		}

		$meta_box_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_box_actions, $post_id );

		?>
		<ul class="wpo_wcpdf-actions">
			<?php
			foreach ($meta_box_actions as $document_type => $data) {
				$exists = ( isset( $data['exists'] ) && $data['exists'] == true ) ? 'exists' : '';
				printf('<li><a href="%1$s" class="button %4$s" target="_blank" alt="%2$s">%3$s</a></li>', $data['url'], $data['alt'], $data['title'], $exists);
			}
			?>
		</ul>
		<?php
	}

	/**
	 * Add metabox for invoice number & date
	 */
	public function data_input_box_content ( $post ) {
		$order = WCX::get_order( $post->ID );
		$this->disable_storing_document_settings();

		do_action( 'wpo_wcpdf_meta_box_start', $post->ID );
		
		if ( $invoice = wcpdf_get_invoice( $order ) ) {
			$invoice_number = $invoice->get_number();
			$invoice_date = $invoice->get_date();
			?>
			<div class="wcpdf-data-fields" data-document="invoice" data-order_id="<?php echo WCX_Order::get_id( $order ); ?>">
				<h4>
					<?php echo $invoice->get_title(); ?><?php if ($invoice->exists()) : ?>
						<span class="wpo-wcpdf-edit-date-number dashicons dashicons-edit"></span>
						<span class="wpo-wcpdf-delete-document dashicons dashicons-trash" data-nonce="<?php echo wp_create_nonce( "wpo_wcpdf_delete_document" ); ?>"></span>
						<?php do_action( 'wpo_wcpdf_document_actions', $invoice ); ?>
					<?php endif; ?>
				</h4>

				<!-- Read only -->
				<div class="read-only">
					<?php if ($invoice->exists()) : ?>
					<div class="invoice-number">
						<p class="form-field _wcpdf_invoice_number_field ">	
							<p>
								<span><strong><?php _e( 'Invoice Number', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</strong></span>
								<span><?php if (!empty($invoice_number)) echo $invoice_number->get_formatted(); ?></span>
							</p>
						</p>
					</div>

					<div class="invoice-date">
						<p class="form-field form-field-wide">
							<p>
								<span><strong><?php _e( 'Invoice Date:', 'woocommerce-pdf-invoices-packing-slips' ); ?></strong></span>
								<span><?php if (!empty($invoice_date)) echo $invoice_date->date_i18n( wc_date_format().' @ '.wc_time_format() ); ?></span>
							</p>
						</p>
					</div>

					<?php do_action( 'wpo_wcpdf_meta_box_after_document_data', $invoice, $order ); ?>

					<?php else : ?>
					<span class="wpo-wcpdf-set-date-number button"><?php _e( 'Set invoice number & date', 'woocommerce-pdf-invoices-packing-slips' ) ?></span>
					<?php endif; ?>
				</div>

				<!-- Editable -->
				<div class="editable">
					<p class="form-field _wcpdf_invoice_number_field ">
						<label for="_wcpdf_invoice_number"><?php _e( 'Invoice Number (unformatted!)', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</label>
						<?php if ( $invoice->exists() && !empty($invoice_number) ) : ?>
						<input type="text" class="short" style="" name="_wcpdf_invoice_number" id="_wcpdf_invoice_number" value="<?php echo $invoice_number->get_plain(); ?>" disabled="disabled">
						<?php else : ?>
						<input type="text" class="short" style="" name="_wcpdf_invoice_number" id="_wcpdf_invoice_number" value="" disabled="disabled">
						<?php endif; ?>
					</p>
					<p class="form-field form-field-wide">
						<label for="wcpdf_invoice_date"><?php _e( 'Invoice Date:', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
						<?php if ( $invoice->exists() && !empty($invoice_date) ) : ?>
						<input type="text" class="date-picker-field" name="wcpdf_invoice_date" id="wcpdf_invoice_date" maxlength="10" value="<?php echo $invoice_date->date_i18n( 'Y-m-d' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" disabled="disabled"/>@<input type="number" class="hour" placeholder="<?php _e( 'h', 'woocommerce' ) ?>" name="wcpdf_invoice_date_hour" id="wcpdf_invoice_date_hour" min="0" max="23" size="2" value="<?php echo $invoice_date->date_i18n( 'H' ) ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:<input type="number" class="minute" placeholder="<?php _e( 'm', 'woocommerce' ) ?>" name="wcpdf_invoice_date_minute" id="wcpdf_invoice_date_minute" min="0" max="59" size="2" value="<?php echo $invoice_date->date_i18n( 'i' ); ?>" pattern="[0-5]{1}[0-9]{1}" />
						<?php else : ?>
						<input type="text" class="date-picker-field" name="wcpdf_invoice_date" id="wcpdf_invoice_date" maxlength="10" disabled="disabled" value="<?php echo date_i18n( 'Y-m-d' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />@<input type="number" class="hour" disabled="disabled" placeholder="<?php _e( 'h', 'woocommerce' ) ?>" name="wcpdf_invoice_date_hour" id="wcpdf_invoice_date_hour" min="0" max="23" size="2" value="<?php echo date_i18n( 'H' ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:<input type="number" class="minute" placeholder="<?php _e( 'm', 'woocommerce' ) ?>" name="wcpdf_invoice_date_minute" id="wcpdf_invoice_date_minute" min="0" max="59" size="2" value="<?php echo date_i18n( 'i' ); ?>" pattern="[0-5]{1}[0-9]{1}" disabled="disabled" />
						<?php endif; ?>
					</p>
				</div>
			</div>
			<?php
		}

		do_action( 'wpo_wcpdf_meta_box_end', $post->ID );
	}

	public function add_regenerate_document_button( $document ) {
		$document_settings = $document->get_settings( true );
		if ( $document->use_historical_settings() == true || isset( $document_settings['archive_pdf'] ) ) {
			printf( '<span class="wpo-wcpdf-regenerate-document dashicons dashicons-update-alt" data-nonce="%s"></span>', wp_create_nonce( "wpo_wcpdf_regenerate_document" ) );
		}
	}

	public function add_document_action_feedback_icons( $document ) {
		echo '<span class="dashicons dashicons-yes document-action-success"></span>';
		echo '<span class="dashicons dashicons-no document-action-failed"></span>';
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
				jQuery('<option>').val('<?php echo $action; ?>').html('<?php echo esc_attr( $title ); ?>').appendTo("select[name='action'], select[name='action2']");
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
	 * Save invoice number
	 */
	public function save_invoice_number_date($post_id, $post) {
		$post_type = get_post_type( $post_id );
		if( $post_type == 'shop_order' ) {
			// bail if this is not an actual 'Save order' action
			if (!isset($_POST['action']) || $_POST['action'] != 'editpost') {
				return;
			}
			
			$order = WCX::get_order( $post_id );
			if ( $invoice = wcpdf_get_invoice( $order ) ) {
				if ( !empty( $_POST['wcpdf_invoice_date'] ) ) {
					$date = $_POST['wcpdf_invoice_date'];
					$hour = !empty( $_POST['wcpdf_invoice_date_hour'] ) ? $_POST['wcpdf_invoice_date_hour'] : '00';
					$minute = !empty( $_POST['wcpdf_invoice_date_minute'] ) ? $_POST['wcpdf_invoice_date_minute'] : '00';

					// clean & sanitize input
					$date = date( 'Y-m-d', strtotime( $date ) );
					$hour = sprintf('%02d', intval( $hour ));
					$minute = sprintf('%02d', intval( $minute ) );
					$invoice_date = "{$date} {$hour}:{$minute}:00";

					// set date
					$invoice->set_date( $invoice_date );
				} elseif ( empty( $_POST['wcpdf_invoice_date'] ) && !empty( $_POST['_wcpdf_invoice_number'] ) ) {
					$invoice->set_date( current_time( 'timestamp', true ) );
				}

				if ( isset( $_POST['_wcpdf_invoice_number'] ) ) {
					// sanitize
					$invoice_number = sanitize_text_field( $_POST['_wcpdf_invoice_number'] );
					// set number
					$invoice->set_number( $invoice_number );
				}

				$invoice->save();
			}
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

	public function return_false(){
		return false;
	}

	/**
	 * Send emails manually
	 */
	public function send_emails( $post_id, $post ) {
		if ( ! empty( $_POST['wpo_wcpdf_send_emails'] ) ) {
			$order = wc_get_order( $post_id );
			$action = wc_clean( $_POST['wpo_wcpdf_send_emails'] );
			if ( strstr( $action, 'send_email_' ) ) {
				// Switch back to the site locale.
				wc_switch_to_site_locale();
				do_action( 'woocommerce_before_resend_order_emails', $order );
				// Ensure gateways are loaded in case they need to insert data into the emails.
				WC()->payment_gateways();
				WC()->shipping();
				// Load mailer.
				$mailer = WC()->mailer();
				$email_to_send = str_replace( 'send_email_', '', $action );
				$mails = $mailer->get_emails();
				if ( ! empty( $mails ) ) {
					foreach ( $mails as $mail ) {
						if ( $mail->id == $email_to_send ) {
							$mail->trigger( $order->get_id(), $order );
							/* translators: %s: email title */
							$order->add_order_note( sprintf( __( '%s email notification manually sent.', 'woocommerce-pdf-invoices-packing-slips' ), $mail->title ), false, true );
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
					return add_query_arg( 'message', 11, $location );
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
		global $post_type;
		if( $post_type == 'shop_order' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add invoice number to order search scope
	 */
	public function invoice_number_column_sortable( $columns ) {
		$columns['pdf_invoice_number'] = 'pdf_invoice_number';
		return $columns;
	}


	/**
	 * Pre WC3.X sorting
	 */
	public function pre_get_posts_sort_by_invoice_number( $query ) {
		if( ! is_admin() ) {
			return;
		}
		$orderby = $query->get( 'orderby');
		if( 'pdf_invoice_number' == $orderby ) {
			$query->set( 'meta_key', '_wcpdf_invoice_number' );
			$query->set( 'orderby', apply_filters( 'wpo_wcpdf_invoice_number_column_orderby', 'meta_value' ) );
		}
	}

	/**
	 * WC3.X+ sorting
	 */
	public function request_query_sort_by_invoice_number( $query_vars ) {
		global $typenow;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ), true ) ) {
			if ( isset( $query_vars['orderby'] ) ) {
				if ( 'pdf_invoice_number' === $query_vars['orderby'] ) {
					$query_vars = array_merge( $query_vars, array(
						'meta_key'  => '_wcpdf_invoice_number',
						'orderby'   => apply_filters( 'wpo_wcpdf_invoice_number_column_orderby', 'meta_value' ),
					) );
				}
			}
		}

		return $query_vars;
	}

	public function delete_document() {
		if ( check_ajax_referer( "wpo_wcpdf_delete_document", 'security', false ) === false ) {
			wp_send_json_error( array(
				'message' => 'nonce expired',
			) );
		}
		if ( empty($_POST['order_id']) || empty($_POST['document']) ) {
			wp_send_json_error( array(
				'message' => 'incomplete request',
			) );
		}
		if ( !current_user_can('manage_woocommerce') ) {
			wp_send_json_error( array(
				'message' => 'no permissions',
			) );
		}

		$order_id = absint($_POST['order_id']);
		$document = sanitize_text_field($_POST['document']);

		try {
			$document = wcpdf_get_document( $document, wc_get_order( $order_id ) );
			if ( !empty($document) && $document->exists() ) {
				$document->delete();
				$response = array(
					'message' => $document->get_type()." deleted",
				);
				wp_send_json_success($response);
			} else {
				wp_send_json_error( array(
					'message' => 'document does not exist',
				) );
			}
		} catch (\Exception $e) {
			wp_send_json_error( array(
				'message' => 'error: '.$e->getMessage(),
			) );			
		}
	}

	public function regenerate_document() {
		if ( check_ajax_referer( "wpo_wcpdf_regenerate_document", 'security', false ) === false ) {
			wp_send_json_error( array(
				'message' => 'nonce expired',
			) );
		}
		if ( empty($_POST['order_id']) || empty($_POST['document']) ) {
			wp_send_json_error( array(
				'message' => 'incomplete request',
			) );
		}
		if ( !current_user_can('manage_woocommerce') ) {
			wp_send_json_error( array(
				'message' => 'no permissions',
			) );
		}

		$order_id = absint($_POST['order_id']);
		$document = sanitize_text_field($_POST['document']);

		try {
			$document = wcpdf_get_document( $document, wc_get_order( $order_id ) );
			if ( !empty($document) && $document->exists() ) {
				$document->regenerate();
				$response = array(
					'message' => $document->get_type()." regenerated",
				);
				wp_send_json_success($response);
			} else {
				wp_send_json_error( array(
					'message' => 'document does not exist',
				) );
			}
		} catch (\Exception $e) {
			wp_send_json_error( array(
				'message' => 'error: '.$e->getMessage(),
			) );			
		}
	}

	public function debug_enabled_warning( $wp_admin_bar ) {
		if ( isset(WPO_WCPDF()->settings->debug_settings['enable_debug']) && current_user_can( 'administrator' ) ) {
			$status_settings_url = 'admin.php?page=wpo_wcpdf_options_page&tab=debug';
			$title = __( 'DEBUG output enabled', 'woocommerce-pdf-invoices-packing-slips' );
			$args = array(
				'id'    => 'admin_bar_wpo_debug_mode',
				'title' => sprintf( '<a href="%s" style="background-color: red; color: white;">%s</a>', $status_settings_url, $title ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}
}

endif; // class_exists

return new Admin();