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
		add_action( 'admin_footer', array( $this, 'bulk_actions' ) );
		add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'search_fields' ) );

		add_action( 'save_post', array( $this,'save_invoice_number_date' ) );

		add_action( 'admin_notices', array( $this, 'review_plugin_notice' ) );
		// add_action( 'wpo_wcpdf_after_pdf', array( $this,'update_pdf_counter' ), 10, 2 );
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

			$invoice_count = $this->get_invoice_count();
			if ( $invoice_count > 100 ) {
				$rounded_count = (int) substr( (string) $invoice_count, 0, 1 ) * pow( 10, strlen( (string) $invoice_count ) - 1);
				?>
				<div class="notice notice-info is-dismissible wpo-wcpdf-review-notice">
					<h3><?php printf( __( 'Wow, you have created more than %d invoices with our plugin!', 'woocommerce-pdf-invoices-packing-slips' ), $rounded_count ); ?></h3>
					<p><?php _e( 'It would mean a lot to us if you would quickly give our plugin a 5-star rating. Help us spread the word and boost our motivation!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					<ul>
						<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices-packing-slips/reviews/?rate=5#new-post" class="button"><?php _e( 'Yes you deserve it!', 'woocommerce-pdf-invoices-packing-slips' ); ?></span></a></li>
						<li><a href="<?php echo esc_url( add_query_arg( 'wpo_wcpdf_dismis_review', true ) ); ?>" class="wpo-wcpdf-dismiss"><?php _e( 'Already did!', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
						<li><a href="mailto:support@wpovernight.com?Subject=Here%20is%20how%20I%20think%20you%20can%20do%20better"><?php _e( 'Actually, I have a complaint...', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></li>
					</ul>
				</div>
				<!-- Hide extensions ad if this is shown -->
				<style>.wcpdf-extensions-ad { display: none; }</style>
				<?php
			}
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

		$listing_actions = array();
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ($documents as $document) {
			$listing_actions[$document->get_type()] = array(
				'url'		=> wp_nonce_url( admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type={$document->get_type()}&order_ids=" . WCX_Order::get_id( $order ) ), 'generate_wpo_wcpdf' ),
				'img'		=> !empty($document->icon) ? $document->icon : WPO_WCPDF()->plugin_url() . "/assets/images/generic_document.png",
				'alt'		=> "PDF " . $document->get_title(),
			);
		}

		$listing_actions = apply_filters( 'wpo_wcpdf_listing_actions', $listing_actions, $order );			

		foreach ($listing_actions as $action => $data) {
			?>
			<a href="<?php echo $data['url']; ?>" class="button tips wpo_wcpdf <?php echo $action; ?>" target="_blank" alt="<?php echo $data['alt']; ?>" data-tip="<?php echo $data['alt']; ?>">
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
		// create PDF buttons
		add_meta_box(
			'wpo_wcpdf-box',
			__( 'Create PDF', 'woocommerce-pdf-invoices-packing-slips' ),
			array( $this, 'sidebar_box_content' ),
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
	 * Create the meta box content on the single order page
	 */
	public function sidebar_box_content( $post ) {
		global $post_id;

		$meta_box_actions = array();
		$documents = WPO_WCPDF()->documents->get_documents();
		foreach ($documents as $document) {
			$meta_box_actions[$document->get_type()] = array(
				'url'		=> wp_nonce_url( admin_url( "admin-ajax.php?action=generate_wpo_wcpdf&document_type={$document->get_type()}&order_ids=" . $post_id ), 'generate_wpo_wcpdf' ),
				'alt'		=> esc_attr( "PDF " . $document->get_title() ),
				'title'		=> "PDF " . $document->get_title(),
			);
		}

		$meta_box_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_box_actions, $post_id );

		?>
		<ul class="wpo_wcpdf-actions">
			<?php
			foreach ($meta_box_actions as $document_type => $data) {
				printf('<li><a href="%1$s" class="button" target="_blank" alt="%2$s">%3$s</a></li>', $data['url'], $data['alt'],$data['title']);
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

		do_action( 'wpo_wcpdf_meta_box_start', $post->ID );
		
		if ( $invoice = wcpdf_get_invoice( $order ) ) {
			$invoice_number = $invoice->get_number();
			$invoice_date = $invoice->get_date();
			?>
			<div class="wcpdf-data-fields">
				<h4><?php _e( 'Invoice', 'woocommerce-pdf-invoices-packing-slips' ) ?><?php if ($invoice->exists()) : ?><span id="" class="wpo-wcpdf-edit-date-number dashicons dashicons-edit"></span><?php endif; ?></h4>

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
					<?php else : ?>
					<span id="set-invoice-date-number" class="button"><?php _e( 'Set invoice number & date', 'woocommerce-pdf-invoices-packing-slips' ) ?></span>
					<?php endif; ?>
				</div>

				<!-- Editable -->
				<div class="editable">
					<p class="form-field _wcpdf_invoice_number_field ">
						<label for="_wcpdf_invoice_number"><?php _e( 'Invoice Number (unformatted!)', 'woocommerce-pdf-invoices-packing-slips' ); ?>:</label>
						<?php if ( $invoice->exists() && !empty($invoice_number) ) : ?>
						<input type="text" class="short" style="" name="_wcpdf_invoice_number" id="_wcpdf_invoice_number" value="<?php echo $invoice_number->get_plain(); ?>">
						<?php else : ?>
						<input type="text" class="short" style="" name="_wcpdf_invoice_number" id="_wcpdf_invoice_number" value="" disabled="disabled" >
						<?php endif; ?>
					</p>
					<p class="form-field form-field-wide">
						<label for="wcpdf_invoice_date"><?php _e( 'Invoice Date:', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
						<?php if ( $invoice->exists() && !empty($invoice_date) ) : ?>
						<input type="text" class="date-picker-field" name="wcpdf_invoice_date" id="wcpdf_invoice_date" maxlength="10" value="<?php echo $invoice_date->date_i18n( 'Y-m-d' ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />@<input type="number" class="hour" placeholder="<?php _e( 'h', 'woocommerce' ) ?>" name="wcpdf_invoice_date_hour" id="wcpdf_invoice_date_hour" min="0" max="23" size="2" value="<?php echo $invoice_date->date_i18n( 'H' ) ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:<input type="number" class="minute" placeholder="<?php _e( 'm', 'woocommerce' ) ?>" name="wcpdf_invoice_date_minute" id="wcpdf_invoice_date_minute" min="0" max="59" size="2" value="<?php echo $invoice_date->date_i18n( 'i' ); ?>" pattern="[0-5]{1}[0-9]{1}" />
						<?php else : ?>
						<input type="text" class="date-picker-field" name="wcpdf_invoice_date" id="wcpdf_invoice_date" maxlength="10" disabled="disabled" value="" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />@<input type="number" class="hour" disabled="disabled" placeholder="<?php _e( 'h', 'woocommerce' ) ?>" name="wcpdf_invoice_date_hour" id="wcpdf_invoice_date_hour" min="0" max="23" size="2" value="" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:<input type="number" class="minute" placeholder="<?php _e( 'm', 'woocommerce' ) ?>" name="wcpdf_invoice_date_minute" id="wcpdf_invoice_date_minute" min="0" max="59" size="2" value="" pattern="[0-5]{1}[0-9]{1}" disabled="disabled" />
						<?php endif; ?>
					</p>
				</div>
			</div>
			<?php
		}

		do_action( 'wpo_wcpdf_meta_box_end', $post->ID );
	}

	/**
	 * Add actions to menu
	 */
	public function bulk_actions() {
		if ( $this->is_order_page() ) {
			$bulk_actions = array();
			$documents = WPO_WCPDF()->documents->get_documents();
			foreach ($documents as $document) {
				$bulk_actions[$document->get_type()] = "PDF " . $document->get_title();
			}

			$bulk_actions = apply_filters( 'wpo_wcpdf_bulk_actions', $bulk_actions );
			
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				<?php foreach ($bulk_actions as $action => $title) { ?>
				jQuery('<option>').val('<?php echo $action; ?>').html('<?php echo esc_attr( $title ); ?>').appendTo("select[name='action'], select[name='action2']");
				<?php }	?>
			});
			</script>
			<?php
		}
	}

	/**
	 * Save invoice number
	 */
	public function save_invoice_number_date($post_id) {
		$post_type = get_post_type( $post_id );
		if( $post_type == 'shop_order' ) {
			// bail if this is not an actual 'Save order' action
			if (!isset($_POST['action']) || $_POST['action'] != 'editpost') {
				return;
			}
			
			$order = WCX::get_order( $post_id );
			if ( $invoice = wcpdf_get_invoice( $order ) ) {
				if ( isset( $_POST['wcpdf_invoice_date'] ) ) {
					$date = $_POST['wcpdf_invoice_date'];
					$hour = !empty( $_POST['wcpdf_invoice_date_hour'] ) ? $_POST['wcpdf_invoice_date_hour'] : '00';
					$minute = !empty( $_POST['wcpdf_invoice_date_minute'] ) ? $_POST['wcpdf_invoice_date_minute'] : '00';
					$invoice_date = "{$date} {$hour}:{$minute}:00";
					$invoice->set_date( $invoice_date );
				} elseif ( empty( $_POST['wcpdf_invoice_date'] ) && !empty( $_POST['_wcpdf_invoice_number'] ) ) {
					$invoice->set_date( current_time( 'timestamp', true ) );
				}

				if ( isset( $_POST['_wcpdf_invoice_number'] ) ) {
					$invoice->set_number( $_POST['_wcpdf_invoice_number'] );
				}

				$invoice->save();
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
}

endif; // class_exists

return new Admin();