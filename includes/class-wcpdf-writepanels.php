<?php

/**
 * Writepanel class
 */
if ( !class_exists( 'WooCommerce_PDF_Invoices_Writepanels' ) ) {

	class WooCommerce_PDF_Invoices_Writepanels {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_listing_actions' ) );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_invoice_number_column' ), 999 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'invoice_number_column_data' ), 2 );
			add_action( 'add_meta_boxes_shop_order', array( $this, 'add_box' ) );
			add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_account_pdf_link' ), 10, 2 );
			add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
			add_action( 'admin_print_styles', array( $this, 'add_styles' ) );
			add_action( 'admin_footer-edit.php', array(&$this, 'bulk_actions') );

			add_action( 'woocommerce_admin_order_data_after_order_details', array(&$this, 'edit_invoice_number') );
			add_action( 'save_post', array( &$this,'save_invoice_number' ) );

			$this->general_settings = get_option('wpo_wcpdf_general_settings');
			$this->template_settings = get_option('wpo_wcpdf_template_settings');
		}

		/**
		 * Add the styles
		 */
		public function add_styles() {
			if( $this->is_order_edit_page() ) {
				wp_enqueue_style( 'thickbox' );

				if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
					// WC 2.1 or newer (MP6) is used: bigger buttons
					wp_enqueue_style( 'wpo-wcpdf', WooCommerce_PDF_Invoices::$plugin_url . 'css/style-wc21.css' );
				} else {
					wp_enqueue_style( 'wpo-wcpdf', WooCommerce_PDF_Invoices::$plugin_url . 'css/style.css' );
				}

			}
		}
		
		/**
		 * Add the scripts
		 */
		public function add_scripts() {
			if( $this->is_order_edit_page() ) {
				wp_enqueue_script( 'wpo-wcpdf', WooCommerce_PDF_Invoices::$plugin_url . 'js/script.js', array( 'jquery' ) );
				wp_localize_script(  
					'wpo-wcpdf',  
					'wpo_wcpdf_ajax',  
					array(  
						'ajaxurl' => admin_url( 'admin-ajax.php' ), // URL to WordPress ajax handling page  
						'nonce' => wp_create_nonce('generate_wpo_wcpdf')  
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
			?>
			<a href="<?php echo wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_ids=' . $order->id ), 'generate_wpo_wcpdf' ); ?>" class="button tips wpo_wcpdf" target="_blank" alt="<?php esc_attr_e( 'PDF invoice', 'wpo_wcpdf' ); ?>" data-tip="<?php esc_attr_e( 'PDF invoice', 'wpo_wcpdf' ); ?>">
				<img src="<?php echo WooCommerce_PDF_Invoices::$plugin_url . 'images/invoice.png'; ?>" alt="<?php esc_attr_e( 'PDF invoice', 'wpo_wcpdf' ); ?>" width="16">
			</a>
			<a href="<?php echo wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=packing-slip&order_ids=' . $order->id ), 'generate_wpo_wcpdf' ); ?>" class="button tips wpo_wcpdf" target="_blank" alt="<?php esc_attr_e( 'PDF Packing Slip', 'wpo_wcpdf' ); ?>" data-tip="<?php esc_attr_e( 'PDF Packing Slip', 'wpo_wcpdf' ); ?>">
				<img src="<?php echo WooCommerce_PDF_Invoices::$plugin_url . 'images/packing-slip.png'; ?>" alt="<?php esc_attr_e( 'PDF Packing Slip', 'wpo_wcpdf' ); ?>" width="16">
			</a>
			<?php
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
			global $post, $the_order;

			if ( $column == 'pdf_invoice_number' && get_post_meta($the_order->id,'_wcpdf_invoice_number',true) ) {
				if ( empty( $the_order ) || $the_order->id != $post->ID ) {
					$the_order = new WC_Order( $post->ID );
				}

				// collect data for invoice number filter
				$invoice_number = get_post_meta($the_order->id,'_wcpdf_invoice_number',true);
				$order_number = $the_order->get_order_number();
				$order_id = $the_order->id;
				$order_date = $the_order->order_date;

				echo apply_filters( 'wpo_wcpdf_invoice_number', $invoice_number, $order_number, $order_id, $order_date );
			}
		}

		/**
		 * Add the meta box on the single order page
		 */
		public function add_box() {
			add_meta_box( 'wpo_wcpdf-box', __( 'Create PDF', 'wpo_wcpdf' ), array( $this, 'create_box_content' ), 'shop_order', 'side', 'default' );
		}

		public function my_account_pdf_link( $actions, $order ) {
			$pdf_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_ids=' . $order->id . '&my-account'), 'generate_wpo_wcpdf' );

			// Check if invoice has been created already or if status allows download (filter your own array of allowed statuses)
			if ( get_post_meta($order->id,'_wcpdf_invoice_exists',true) || in_array($order->status, apply_filters( 'wpo_wcpdf_myaccount_allowed_order_statuses', array() ) ) ) {
				$actions['invoice'] = array(
					'url'  => $pdf_url,
					'name' => __( 'Download invoice (PDF)', 'wpo_wcpdf' )
				);				
			}

			return $actions;
		}

		/**
		 * Create the meta box content on the single order page
		 */
		public function create_box_content() {
			global $post_id;
			?>
			<ul class="wpo_wcpdf-actions">
				<li><a href="<?php echo wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_ids=' . $post_id ), 'generate_wpo_wcpdf' ); ?>" class="button" target="_blank" alt="<?php esc_attr_e( 'PDF Invoice', 'wpo_wcpdf' ); ?>"><?php _e( 'PDF invoice', 'wpo_wcpdf' ); ?></a></li>
				<li><a href="<?php echo wp_nonce_url( admin_url( 'admin-ajax.php?action=generate_wpo_wcpdf&template_type=packing-slip&order_ids=' . $post_id ), 'generate_wpo_wcpdf' ); ?>" class="button" target="_blank" alt="<?php esc_attr_e( 'PDF Packing Slip', 'wpo_wcpdf' ); ?>"><?php _e( 'PDF Packing Slip', 'wpo_wcpdf' ); ?></a></li>
			</ul>
			<?php
		}

		/**
		 * Add actions to menu
		 */
		public function bulk_actions() {
			global $post_type;
	
			if ( 'shop_order' == $post_type ) {
				?>
				<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('<option>').val('invoice').text('<?php _e( 'PDF Invoices', 'wpo_wcpdf' )?>').appendTo("select[name='action']");
					jQuery('<option>').val('invoice').text('<?php _e( 'PDF Invoices', 'wpo_wcpdf' )?>').appendTo("select[name='action2']");
					jQuery('<option>').val('packing-slip').text('<?php _e( 'PDF Packing Slips', 'wpo_wcpdf' )?>').appendTo("select[name='action']");
					jQuery('<option>').val('packing-slip').text('<?php _e( 'PDF Packing Slips', 'wpo_wcpdf' )?>').appendTo("select[name='action2']");
				});
				</script>
				<?php
			}
		}

		/**
		 * Add box to edit invoice number to order details page
		 */
		public function edit_invoice_number($order) {
			$invoice_exists = get_post_meta( $order->id, '_wcpdf_invoice_exists', true );
			if (!empty($invoice_exists)) {
				woocommerce_wp_text_input( array( 'id' => '_wcpdf_invoice_number', 'label' => __( 'PDF Invoice Number (unformatted!)', 'wpo_wcpdf' ) ) );
			}
		}

		/**
		 * Save invoice number
		 */
		public function save_invoice_number($post_id) {
			global $post_type;
			if( $post_type == 'shop_order' && isset($_POST['_wcpdf_invoice_number'])) {
				update_post_meta( $post_id, '_wcpdf_invoice_number', stripslashes( $_POST['_wcpdf_invoice_number'] ));
			}
		}
	}
}