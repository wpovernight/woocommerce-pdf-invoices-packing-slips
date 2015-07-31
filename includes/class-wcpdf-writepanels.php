<?php

/**
 * Writepanel class
 */
if ( !class_exists( 'WooCommerce_PDF_Invoices_Writepanels' ) ) {

	class WooCommerce_PDF_Invoices_Writepanels {
		public $bulk_actions;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->general_settings = get_option('wpo_wcpdf_general_settings');
			$this->template_settings = get_option('wpo_wcpdf_template_settings');

			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_number_column' ), 999 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_number_column_data' ), 2 );
			add_action( 'add_meta_boxes_shop_order', array( $this, 'add_meta_boxes' ) );
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_pdf_link' ), 10, 2 );
			add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'add_styles' ) );
			add_action( 'admin_footer', array( $this, 'bulk_actions' ) );

			add_action( 'save_post', array( $this,'save_invoice_number_date' ) );

			add_filter( 'woocommerce_shop_order_search_fields', array( $this, 'search_fields' ) );

			$this->bulk_actions = array(
				'invoice'		=> __( 'PDF Invoices', 'wpo_wcpdf' ),
				'packing-slip'	=> __( 'PDF Packing Slips', 'wpo_wcpdf' ),
			);
		}

		/**
		 * Add the styles
		 */
		public function add_styles() {
			if( $this->is_order_edit_page() ) {
				wp_enqueue_style( 'thickbox' );

				wp_enqueue_style(
					'wpo-wcpdf',
					WooCommerce_PDF_Invoices::$plugin_url . 'css/style.css',
					array(),
					WooCommerce_PDF_Invoices::$version
				);

				if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
					// WC 2.1 or newer (MP6) is used: bigger buttons
					wp_enqueue_style(
						'wpo-wcpdf-buttons',
						WooCommerce_PDF_Invoices::$plugin_url . 'css/style-buttons.css',
						array(),
						WooCommerce_PDF_Invoices::$version
					);
				} else {
					// legacy WC 2.0 styles
					wp_enqueue_style(
						'wpo-wcpdf-buttons',
						WooCommerce_PDF_Invoices::$plugin_url . 'css/style-buttons-wc20.css',
						array(),
						WooCommerce_PDF_Invoices::$version
					);
				}
			}
		}
		
		/**
		 * Add the scripts
		 */
		public function add_scripts() {
			if( $this->is_order_edit_page() ) {
				wp_enqueue_script(
					'wpo-wcpdf',
					WooCommerce_PDF_Invoices::$plugin_url . 'js/script.js',
					array( 'jquery' ),
					WooCommerce_PDF_Invoices::$version
				);
				wp_localize_script(  
					'wpo-wcpdf',  
					'wpo_wcpdf_ajax',  
					array(  
						'ajaxurl'		=> admin_url( 'admin-ajax.php' ), // URL to WordPress ajax handling page  
						'nonce'			=> wp_create_nonce('generate_wpo_wcpdf'),
						'bulk_actions'	=> array_keys( apply_filters( 'wpo_wcpdf_bulk_actions', $this->bulk_actions ) ),
					)  
				);  
			}
		}	
			
		/**
		 * Is order page
		 */
		public function is_order_edit_page() {
			global $post_type;
			if( $post_type == 'shop_order' ) {
				return true;	
			} else {
				return false;
			}
		}	
			
		/**
		 * Add PDF actions to the orders listing
		 */
		public function add_listing_actions( $order ) {
			$listing_actions = array(
				'invoice'		=> array (
					'url'		=> wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_ids=' . $order->id ), 'generate_wpo_wcpdf' ),
					'img'		=> WooCommerce_PDF_Invoices::$plugin_url . 'images/invoice.png',
					'alt'		=> __( 'PDF Invoice', 'wpo_wcpdf' ),
				),
				'packing-slip'	=> array (
					'url'		=> wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=packing-slip&order_ids=' . $order->id ), 'generate_wpo_wcpdf' ),
					'img'		=> WooCommerce_PDF_Invoices::$plugin_url . 'images/packing-slip.png',
					'alt'		=> __( 'PDF Packing Slip', 'wpo_wcpdf' ),
				),
			);

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
			// Check user setting
			if ( !isset($this->general_settings['invoice_number_column'] ) ) {
				return $columns;
			}

			// put the column after the Status column
			$new_columns = array_slice($columns, 0, 2, true) +
				array( 'pdf_invoice_number' => __( 'Invoice Number', 'wpo_wcpdf' ) ) +
				array_slice($columns, 2, count($columns) - 1, true) ;
			return $new_columns;
		}

		/**
		 * Display Invoice Number in Shop Order column (if available)
		 * @param  string $column column slug
		 */
		public function invoice_number_column_data( $column ) {
			global $post, $the_order, $wpo_wcpdf;

			if ( $column == 'pdf_invoice_number' ) {
				if ( empty( $the_order ) || $the_order->id != $post->ID ) {
					$the_order = new WC_Order( $post->ID );
				}

				echo $wpo_wcpdf->export->get_invoice_number( $the_order->id );
			}
		}

		/**
		 * Display download link on My Account page
		 */
		public function my_account_pdf_link( $actions, $order ) {
			$pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_ids=' . $order->id . '&my-account'), 'generate_wpo_wcpdf' );

			// check my account button settings
			if (isset($this->general_settings['my_account_buttons'])) {
				switch ($this->general_settings['my_account_buttons']) {
					case 'available':
						$invoice_allowed = get_post_meta($order->id,'_wcpdf_invoice_exists',true);
						break;
					case 'always':
						$invoice_allowed = true;
						break;
					case 'custom':
						if ( in_array( $order->status, array_keys( $this->general_settings['my_account_restrict'] ) ) ) {
							$invoice_allowed = true;
						} else {
							$invoice_allowed = false;							
						}
						break;
				}
			} else {
				// backwards compatibility
				$invoice_allowed = get_post_meta($order->id,'_wcpdf_invoice_exists',true);
			}

			// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
			if ( $invoice_allowed || in_array($order->status, apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) {
				$actions['invoice'] = array(
					'url'  => $pdf_url,
					'name' => apply_filters( 'wpo_wcpdf_myaccount_button_text', __( 'Download invoice (PDF)', 'wpo_wcpdf' ) )
				);				
			}

			return apply_filters( 'wpo_wcpdf_myaccount_actions', $actions, $order );
		}

		/**
		 * Add the meta box on the single order page
		 */
		public function add_meta_boxes() {
			// create PDF buttons
			add_meta_box(
				'wpo_wcpdf-box',
				__( 'Create PDF', 'wpo_wcpdf' ),
				array( $this, 'sidebar_box_content' ),
				'shop_order',
				'side',
				'default'
			);

			// Invoice number & date
			add_meta_box(
				'wpo_wcpdf-data-input-box',
				__( 'PDF Invoice data', 'wpo_wcpdf' ),
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

			$meta_actions = array(
				'invoice'		=> array (
					'url'		=> wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_ids=' . $post_id ), 'generate_wpo_wcpdf' ),
					'alt'		=> esc_attr__( 'PDF Invoice', 'wpo_wcpdf' ),
					'title'		=> __( 'PDF Invoice', 'wpo_wcpdf' ),
				),
				'packing-slip'	=> array (
					'url'		=> wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=packing-slip&order_ids=' . $post_id ), 'generate_wpo_wcpdf' ),
					'alt'		=> esc_attr__( 'PDF Packing Slip', 'wpo_wcpdf' ),
					'title'		=> __( 'PDF Packing Slip', 'wpo_wcpdf' ),
				),
			);

			$meta_actions = apply_filters( 'wpo_wcpdf_meta_box_actions', $meta_actions, $post_id );

			?>
			<ul class="wpo_wcpdf-actions">
				<?php
				foreach ($meta_actions as $action => $data) {
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
			$invoice_exists = get_post_meta( $post->ID, '_wcpdf_invoice_exists', true );
			$invoice_number = get_post_meta($post->ID,'_wcpdf_invoice_number',true);
			$invoice_date = get_post_meta($post->ID,'_wcpdf_invoice_date',true);
			
			do_action( 'wpo_wcpdf_meta_box_start', $post->ID );

			?>
			<h4><?php _e( 'Invoice', 'wpo_wcpdf' ) ?></h4>
			<p class="form-field _wcpdf_invoice_number_field ">
				<label for="_wcpdf_invoice_number"><?php _e( 'Invoice Number (unformatted!)', 'wpo_wcpdf' ); ?>:</label>
				<?php if (!empty($invoice_exists)) : ?>
				<input type="text" class="short" style="" name="_wcpdf_invoice_number" id="_wcpdf_invoice_number" value="<?php echo $invoice_number ?>">
				<?php else : ?>
				<input type="text" class="short" style="" name="" id="_wcpdf_invoice_number" value="" disabled="disabled" >
				<?php endif; ?>
			</p>
			<p class="form-field form-field-wide">
				<label for="wcpdf_invoice_date"><?php _e( 'Invoice Date:', 'wpo_wcpdf' ); ?></label>
				<?php if (!empty($invoice_exists)) : ?>
				<input type="text" class="date-picker-field" name="wcpdf_invoice_date" id="wcpdf_invoice_date" maxlength="10" value="<?php echo date_i18n( 'Y-m-d', strtotime( $invoice_date ) ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />@<input type="text" class="hour" placeholder="<?php _e( 'h', 'woocommerce' ) ?>" name="wcpdf_invoice_date_hour" id="wcpdf_invoice_date_hour" maxlength="2" size="2" value="<?php echo date_i18n( 'H', strtotime( $invoice_date ) ); ?>" pattern="\-?\d+(\.\d{0,})?" />:<input type="text" class="minute" placeholder="<?php _e( 'm', 'woocommerce' ) ?>" name="wcpdf_invoice_date_minute" id="wcpdf_invoice_date_minute" maxlength="2" size="2" value="<?php echo date_i18n( 'i', strtotime( $invoice_date ) ); ?>" pattern="\-?\d+(\.\d{0,})?" />
				<?php else : ?>
				<input type="text" class="date-picker-field" id="wcpdf_invoice_date" maxlength="10" disabled="disabled" >@<input type="text" class="hour" id="wcpdf_invoice_date_hour" maxlength="2" size="2" disabled="disabled" />:<input type="text" class="minute" id="wcpdf_invoice_date_minute" maxlength="2" size="2" disabled="disabled" />
				<?php endif; ?>
			</p>
			<?php

			do_action( 'wpo_wcpdf_meta_box_end', $post->ID );
		}

		/**
		 * Add actions to menu
		 */
		public function bulk_actions() {
			global $post_type;
			$bulk_actions = apply_filters( 'wpo_wcpdf_bulk_actions', $this->bulk_actions );

			if ( 'shop_order' == $post_type ) {
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
			global $post_type;
			if( $post_type == 'shop_order' ) {
				if ( isset($_POST['_wcpdf_invoice_number']) ) {
					update_post_meta( $post_id, '_wcpdf_invoice_number', stripslashes( $_POST['_wcpdf_invoice_number'] ));
				}

				if ( isset($_POST['wcpdf_invoice_date']) ) {
					if ( empty($_POST['wcpdf_invoice_date']) ) {
						delete_post_meta( $post_id, '_wcpdf_invoice_date' );
					} else {
						$invoice_date = strtotime( $_POST['wcpdf_invoice_date'] . ' ' . (int) $_POST['wcpdf_invoice_date_hour'] . ':' . (int) $_POST['wcpdf_invoice_date_minute'] . ':00' );
						$invoice_date = date_i18n( 'Y-m-d H:i:s', $invoice_date );
						update_post_meta( $post_id, '_wcpdf_invoice_date', $invoice_date );
					}
				}

				if ( empty($_POST['wcpdf_invoice_date']) && empty($_POST['wcpdf_invoice_number'])) {
					delete_post_meta( $post_id, '_wcpdf_invoice_exists' );
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
	}
}