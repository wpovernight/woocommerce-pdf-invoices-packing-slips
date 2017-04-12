<?php
use WPO\WC\PDF_Invoices\Compatibility\WC_Core as WCX;
use WPO\WC\PDF_Invoices\Compatibility\Order as WCX_Order;
use WPO\WC\PDF_Invoices\Compatibility\Product as WCX_Product;

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WooCommerce_PDF_Invoices_Functions' ) ) :

class WooCommerce_PDF_Invoices_Functions {

	/**
	 * Get template name from slug
	 */
	public function get_template_name ( $template_type ) {
		switch ( $template_type ) {
			case 'invoice':
				$template_name = apply_filters( 'wpo_wcpdf_invoice_title', __( 'Invoice', 'wpo_wcpdf' ) );
				break;
			case 'packing-slip':
				$template_name = apply_filters( 'wpo_wcpdf_packing_slip_title', __( 'Packing Slip', 'wpo_wcpdf' ) );
				break;
			default:
				// try to 'unslug' the name
				$template_name = ucwords( str_replace( array( '_', '-' ), ' ', $template_type ) );
				break;
		}

		return apply_filters( 'wpo_wcpdf_template_name', $template_name, $template_type );
	}

	/**
	 * Output template styles
	 */
	public function template_styles() {
		$css = apply_filters( 'wpo_wcpdf_template_styles_file', WPO_WCPDF()->export->template_path. '/' .'style.css' );

		ob_start();
		if (file_exists($css)) {
			include($css);
		}
		$html = ob_get_clean();
		$html = apply_filters( 'wpo_wcpdf_template_styles', $html );
		
		echo $html;
	}

	/**
	 * Return logo id
	 */
	public function get_header_logo_id() {
		if (isset(WPO_WCPDF()->settings->template_settings['header_logo'])) {
			return apply_filters( 'wpo_wcpdf_header_logo_id', WPO_WCPDF()->settings->template_settings['header_logo'] );
		}
	}

	/**
	 * Show logo html
	 */
	public function header_logo() {
		if ($this->get_header_logo_id()) {
			$attachment_id = $this->get_header_logo_id();
			$company = isset(WPO_WCPDF()->settings->template_settings['shop_name'])? WPO_WCPDF()->settings->template_settings['shop_name'] : '';
			if( $attachment_id ) {
				$attachment = wp_get_attachment_image_src( $attachment_id, 'full', false );
				
				$attachment_src = $attachment[0];
				$attachment_width = $attachment[1];
				$attachment_height = $attachment[2];

				$attachment_path = get_attached_file( $attachment_id );

				if ( apply_filters('wpo_wcpdf_use_path', true) && file_exists($attachment_path) ) {
					$src = $attachment_path;
				} else {
					$src = $attachment_src;
				}
				
				printf('<img src="%1$s" width="%2$d" height="%3$d" alt="%4$s" />', $src, $attachment_width, $attachment_height, esc_attr( $company ) );
			}
		}
	}

	/**
	 * Return/Show custom company name or default to blog name
	 */
	public function get_shop_name() {
		if (!empty(WPO_WCPDF()->settings->template_settings['shop_name'])) {
			$name = trim( WPO_WCPDF()->settings->template_settings['shop_name'] );
			return apply_filters( 'wpo_wcpdf_shop_name', wptexturize( $name ) );
		} else {
			return apply_filters( 'wpo_wcpdf_shop_name', get_bloginfo( 'name' ) );
		}
	}
	public function shop_name() {
		echo $this->get_shop_name();
	}
	
	/**
	 * Return/Show shop/company address if provided
	 */
	public function get_shop_address() {
		$shop_address = apply_filters( 'wpo_wcpdf_shop_address', wpautop( wptexturize( WPO_WCPDF()->settings->template_settings['shop_address'] ) ) );
		if (!empty($shop_address)) {
			return $shop_address;
		} else {
			return false;
		}
	}
	public function shop_address() {
		echo $this->get_shop_address();
	}

	/**
	 * Check if billing address and shipping address are equal
	 */
	public function ships_to_different_address() {
		$order = &WPO_WCPDF()->export->order;
		$order_id = WCX_Order::get_id( $order );
		// always prefer parent address for refunds
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$current_order = $order;
			$order = WCX::get_order( $parent_order_id );
		}

		$address_comparison_fields = apply_filters( 'wpo_wcpdf_address_comparison_fields', array(
			'first_name',
			'last_name',
			'company',
			'address_1',
			'address_2',
			'city',
			'state',
			'postcode',
			'country'
		) );
		
		foreach ($address_comparison_fields as $address_field) {
			$billing_field = WCX_Order::get_prop( $order, "billing_{$address_field}", 'view');
			$shipping_field = WCX_Order::get_prop( $order, "shipping_{$address_field}", 'view');
			if ( $shipping_field != $billing_field ) {
				// this address field is different -> ships to different address!
				$order = isset($current_order) ? $current_order : $order; // reset back to refund if necessery
				return true;
			}
		}

		//if we got here, it means the addresses are equal -> doesn't ship to different address!
		$order = isset($current_order) ? $current_order : $order; // reset back to refund if necessery
		return apply_filters( 'wpo_wcpdf_ships_to_different_address', false, $order );
	}
	
	/**
	 * Return/Show billing address
	 */
	public function get_billing_address() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		// always prefer parent billing address for refunds
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			// temporarily switch order to make all filters / order calls work correctly
			$current_order = WPO_WCPDF()->export->order;
			WPO_WCPDF()->export->order = WCX::get_order( $parent_order_id );
			$address = apply_filters( 'wpo_wcpdf_billing_address', WPO_WCPDF()->export->order->get_formatted_billing_address() );
			// switch back & unset
			WPO_WCPDF()->export->order = $current_order;
			unset($current_order);
		} elseif ( $address = WPO_WCPDF()->export->order->get_formatted_billing_address() ) {
			// regular shop_order
			$address = apply_filters( 'wpo_wcpdf_billing_address', $address );
		} else {
			// no address
			$address = apply_filters( 'wpo_wcpdf_billing_address', __('N/A', 'wpo_wcpdf') );
		}

		return $address;
	}
	public function billing_address() {
		echo $this->get_billing_address();
	}

	/**
	 * Return/Show billing email
	 */
	public function get_billing_email() {
		$billing_email = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'billing_email', 'view' );

		if ( !$billing_email && $parent_order_id = wp_get_post_parent_id( WCX_Order::get_id( WPO_WCPDF()->export->order ) ) ) {
			// try parent
			$parent_order = WCX::get_order( $parent_order_id );
			$billing_email = WCX_Order::get_prop( $parent_order, 'billing_email', 'view' );
		}

		return apply_filters( 'wpo_wcpdf_billing_email', $billing_email );
	}
	public function billing_email() {
		echo $this->get_billing_email();
	}
	
	/**
	 * Return/Show billing phone
	 */
	public function get_billing_phone() {
		$billing_phone = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'billing_phone', 'view' );

		if ( !$billing_phone && $parent_order_id = wp_get_post_parent_id( WCX_Order::get_id( WPO_WCPDF()->export->order ) ) ) {
			// try parent
			$parent_order = WCX::get_order( $parent_order_id );
			$billing_phone = WCX_Order::get_prop( $parent_order, 'billing_phone', 'view' );
		}

		return apply_filters( 'wpo_wcpdf_billing_phone', $billing_phone );
	}
	public function billing_phone() {
		echo $this->get_billing_phone();
	}
	
	/**
	 * Return/Show shipping address
	 */
	public function get_shipping_address() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );

		// always prefer parent shipping address for refunds
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			// temporarily switch order to make all filters / order calls work correctly
			$current_order = WPO_WCPDF()->export->order;
			WPO_WCPDF()->export->order = WCX::get_order( $parent_order_id );
			$address = apply_filters( 'wpo_wcpdf_shipping_address', WPO_WCPDF()->export->order->get_formatted_shipping_address() );
			// switch back & unset
			WPO_WCPDF()->export->order = $current_order;
			unset($current_order);
		} elseif ( $address = WPO_WCPDF()->export->order->get_formatted_shipping_address() ) {
			// regular shop_order
			$address = apply_filters( 'wpo_wcpdf_shipping_address', $address );
		} else {
			// no address
			$address = apply_filters( 'wpo_wcpdf_shipping_address', __('N/A', 'wpo_wcpdf') );
		}

		return $address;
	}
	public function shipping_address() {
		echo $this->get_shipping_address();
	}

	/**
	 * Return/Show a custom field
	 */		
	public function get_custom_field( $field_name ) {
		$custom_field = WCX_Order::get_meta( WPO_WCPDF()->export->order, $field_name, true );

		if ( !$custom_field && $parent_order_id = wp_get_post_parent_id( WCX_Order::get_id( WPO_WCPDF()->export->order ) ) ) {
			// try parent
			$parent_order = WCX::get_order( $parent_order_id );
			$custom_field = WCX_Order::get_meta( $parent_order, $field_name, true );
		}

		return apply_filters( 'wpo_wcpdf_billing_custom_field', $custom_field );
	}
	public function custom_field( $field_name, $field_label = '', $display_empty = false ) {
		$custom_field = $this->get_custom_field( $field_name );
		if (!empty($field_label)){
			// add a a trailing space to the label
			$field_label .= ' ';
		}

		if (!empty($custom_field) || $display_empty) {
			echo $field_label . nl2br ($custom_field);
		}
	}

	/**
	 * Return/Show order notes
	 */		
	public function get_order_notes( $filter = 'customer' ) {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$post_id = $parent_order_id;
		} else {
			$post_id = $order_id;
		}

		$args = array(
			'post_id' 	=> $post_id,
			'approve' 	=> 'approve',
			'type' 		=> 'order_note'
		);

		remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		$notes = get_comments( $args );

		add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

		if ( $notes ) {
			foreach( $notes as $key => $note ) {
				if ( $filter == 'customer' && !get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ) {
					unset($notes[$key]);
				}
				if ( $filter == 'private' && get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ) {
					unset($notes[$key]);
				}					
			}
			return $notes;
		}
	}
	public function order_notes( $filter = 'customer' ) {
		$notes = $this->get_order_notes( $filter );
		if ( $notes ) {
			foreach( $notes as $note ) {
				?>
				<div class="note_content">
					<?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Return/Show the current date
	 */
	public function get_current_date() {
		return apply_filters( 'wpo_wcpdf_date', date_i18n( get_option( 'date_format' ) ) );
	}
	public function current_date() {
		echo $this->get_current_date();
	}

	/**
	 * Return/Show payment method  
	 */
	public function get_payment_method() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		$payment_method_label = __( 'Payment method', 'wpo_wcpdf' );

		// use parent for credit notes
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$parent_order = WCX::get_order( $parent_order_id );
			$payment_method_title = WCX_Order::get_prop( $parent_order, 'payment_method_title', 'view' );
			$payment_method = __( $payment_method_title, 'woocommerce' );
		} else {
			$payment_method_title = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'payment_method_title', 'view' );
		}

		$payment_method = __( $payment_method_title, 'woocommerce' );

		return apply_filters( 'wpo_wcpdf_payment_method', $payment_method );
	}
	public function payment_method() {
		echo $this->get_payment_method();
	}

	/**
	 * Return/Show shipping method  
	 */
	public function get_shipping_method() {
		$shipping_method_label = __( 'Shipping method', 'wpo_wcpdf' );
		return apply_filters( 'wpo_wcpdf_shipping_method', __( WPO_WCPDF()->export->order->get_shipping_method(), 'woocommerce' ) );
	}
	public function shipping_method() {
		echo $this->get_shipping_method();
	}

	/**
	 * Return/Show order number
	 */
	public function get_order_number() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		// try parent first
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$parent_order = WCX::get_order( $parent_order_id );
			$order_number = $parent_order->get_order_number();
		} else {
			$order_number = WPO_WCPDF()->export->order->get_order_number();
		}

		// Trim the hash to have a clean number but still 
		// support any filters that were applied before.
		$order_number = ltrim($order_number, '#');
		return apply_filters( 'wpo_wcpdf_order_number', $order_number);
	}
	public function order_number() {
		echo $this->get_order_number();
	}

	/**
	 * Return/Show invoice number 
	 */
	public function get_invoice_number() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		// try parent first
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$invoice_number = WPO_WCPDF()->export->get_invoice_number( $parent_order_id );
		} else {
			$invoice_number = WPO_WCPDF()->export->get_invoice_number( $order_id );
		}

		return $invoice_number;
	}
	public function invoice_number() {
		echo $this->get_invoice_number();
	}

	/**
	 * Return/Show the order date
	 */
	public function get_order_date() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		// try parent first
		if ( get_post_type( $order_id ) == 'shop_order_refund' && $parent_order_id = wp_get_post_parent_id( $order_id ) ) {
			$parent_order = WCX::get_order( $parent_order_id );
			$order_date = WCX_Order::get_prop( $parent_order, 'date_created' );
		} else {
			$order_date = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'date_created' );
		}

		$date = $order_date->date_i18n( get_option( 'date_format' ) );
		$mysql_date = $order_date->date( "Y-m-d H:i:s" );
		return apply_filters( 'wpo_wcpdf_order_date', $date, $mysql_date );
	}
	public function order_date() {
		echo $this->get_order_date();
	}

	/**
	 * Return/Show the invoice date
	 */
	public function get_invoice_date() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		$invoice_date = WPO_WCPDF()->export->get_invoice_date( $order_id );
		return $invoice_date;
	}
	public function invoice_date() {
		echo $this->get_invoice_date();
	}

	/**
	 * Return the order items
	 */
	public function get_order_items() {
		return apply_filters( 'wpo_wcpdf_order_items', WPO_WCPDF()->export->get_order_items() );
	}

	/**
	 * Return/show product attribute
	 */
	public function get_product_attribute( $attribute_name, $product ) {
		// first, check the text attributes
		$attributes = $product->get_attributes();
		$attribute_key = @wc_attribute_taxonomy_name( $attribute_name );
		if (array_key_exists( sanitize_title( $attribute_name ), $attributes) ) {
			$attribute = $product->get_attribute ( $attribute_name );
			return $attribute;
		} elseif (array_key_exists( sanitize_title( $attribute_key ), $attributes) ) {
			$attribute = $product->get_attribute ( $attribute_key );
			return $attribute;
		}

		// not a text attribute, try attribute taxonomy
		$attribute_key = @wc_attribute_taxonomy_name( $attribute_name );
		$product_id = WCX_Product::get_prop($product, 'id');
		$product_terms = @wc_get_product_terms( $product_id, $attribute_key, array( 'fields' => 'names' ) );
		// check if not empty, then display
		if ( !empty($product_terms) ) {
			$attribute = array_shift( $product_terms );
			return $attribute;
		} else {
			// no attribute under this name
			return false;
		}
	}
	public function product_attribute( $attribute_name, $product ) {
		echo $this->get_product_attribute( $attribute_name, $product );
	}


	/**
	 * Return the order totals listing
	 */
	public function get_woocommerce_totals() {
		// get totals and remove the semicolon
		$totals = apply_filters( 'wpo_wcpdf_raw_order_totals', WPO_WCPDF()->export->order->get_order_item_totals(), WPO_WCPDF()->export->order );
		
		// remove the colon for every label
		foreach ( $totals as $key => $total ) {
			$label = $total['label'];
			$colon = strrpos( $label, ':' );
			if( $colon !== false ) {
				$label = substr_replace( $label, '', $colon, 1 );
			}		
			$totals[$key]['label'] = $label;
		}

		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		if ( get_post_type( $order_id ) != 'shop_order_refund' ) {
			// WC2.4 fix order_total for refunded orders
			if ( version_compare( WOOCOMMERCE_VERSION, '2.4', '>=' ) && isset($totals['order_total']) ) {
				if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
					$tax_display = get_option( 'woocommerce_tax_display_cart' );
				} else {
					$tax_display = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'tax_display_cart' );
				}

				$totals['order_total']['value'] = wc_price( WPO_WCPDF()->export->order->get_total(), array( 'currency' => WCX_Order::get_prop( WPO_WCPDF()->export->order, 'currency' ) ) );
				$order_total    = WPO_WCPDF()->export->order->get_total();
				$tax_string     = '';

				// Tax for inclusive prices
				if ( wc_tax_enabled() && 'incl' == $tax_display ) {
					$tax_string_array = array();
					if ( 'itemized' == get_option( 'woocommerce_tax_total_display' ) ) {
						foreach ( WPO_WCPDF()->export->order->get_tax_totals() as $code => $tax ) {
							$tax_amount         = $tax->formatted_amount;
							$tax_string_array[] = sprintf( '%s %s', $tax_amount, $tax->label );
						}
					} else {
						$tax_string_array[] = sprintf( '%s %s', wc_price( WPO_WCPDF()->export->order->get_total_tax() - WPO_WCPDF()->export->order->get_total_tax_refunded(), array( 'currency' => WPO_WCPDF()->export->order->get_order_currency() ) ), WC()->countries->tax_or_vat() );
					}
					if ( ! empty( $tax_string_array ) ) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.6', '>=' ) ) {
							$tax_string = ' ' . sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) );
						} else {
							// use old capitalized string
							$tax_string = ' ' . sprintf( __( '(Includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) );
						}
					}
				}

				$totals['order_total']['value'] .= $tax_string;
			}

			// remove refund lines (shouldn't be in invoice)
			foreach ( $totals as $key => $total ) {
				if ( strpos($key, 'refund_') !== false ) {
					unset( $totals[$key] );
				}
			}

		}

		return apply_filters( 'wpo_wcpdf_woocommerce_totals', $totals, WPO_WCPDF()->export->order );
	}
	
	/**
	 * Return/show the order subtotal
	 */
	public function get_order_subtotal( $tax = 'excl', $discount = 'incl' ) { // set $tax to 'incl' to include tax, same for $discount
		//$compound = ($discount == 'incl')?true:false;
		$subtotal = WPO_WCPDF()->export->order->get_subtotal_to_display( false, $tax );

		$subtotal = ($pos = strpos($subtotal, ' <small')) ? substr($subtotal, 0, $pos) : $subtotal; //removing the 'excluding tax' text			
		
		$subtotal = array (
			'label'	=> __('Subtotal', 'wpo_wcpdf'),
			'value'	=> $subtotal, 
		);
		
		return apply_filters( 'wpo_wcpdf_order_subtotal', $subtotal, $tax, $discount );
	}
	public function order_subtotal( $tax = 'excl', $discount = 'incl' ) {
		$subtotal = $this->get_order_subtotal( $tax, $discount );
		echo $subtotal['value'];
	}

	/**
	 * Return/show the order shipping costs
	 */
	public function get_order_shipping( $tax = 'excl' ) { // set $tax to 'incl' to include tax
		$shipping_cost = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'shipping_total', 'view' );
		$shipping_tax = WCX_Order::get_prop( WPO_WCPDF()->export->order, 'shipping_tax', 'view' );

		if ($tax == 'excl' ) {
			$formatted_shipping_cost = WPO_WCPDF()->export->wc_price( $shipping_cost );
		} else {
			$formatted_shipping_cost = WPO_WCPDF()->export->wc_price( $shipping_cost + $shipping_tax );
		}

		$shipping = array (
			'label'	=> __('Shipping', 'wpo_wcpdf'),
			'value'	=> $formatted_shipping_cost,
			'tax'	=> WPO_WCPDF()->export->wc_price( $shipping_tax ),
		);
		return apply_filters( 'wpo_wcpdf_order_shipping', $shipping, $tax );
	}
	public function order_shipping( $tax = 'excl' ) {
		$shipping = $this->get_order_shipping( $tax );
		echo $shipping['value'];
	}

	/**
	 * Return/show the total discount
	 */
	public function get_order_discount( $type = 'total', $tax = 'incl' ) {
		if ( $tax == 'incl' ) {
			switch ($type) {
				case 'cart':
					// Cart Discount - pre-tax discounts. (deprecated in WC2.3)
					$discount_value = WPO_WCPDF()->export->order->get_cart_discount();
					break;
				case 'order':
					// Order Discount - post-tax discounts. (deprecated in WC2.3)
					$discount_value = WPO_WCPDF()->export->order->get_order_discount();
					break;
				case 'total':
					// Total Discount
					if ( version_compare( WOOCOMMERCE_VERSION, '2.3' ) >= 0 ) {
						$discount_value = WPO_WCPDF()->export->order->get_total_discount( false ); // $ex_tax = false
					} else {
						// WC2.2 and older: recalculate to include tax
						$discount_value = 0;
						$items = WPO_WCPDF()->export->order->get_items();;
						if( sizeof( $items ) > 0 ) {
							foreach( $items as $item ) {
								$discount_value += ($item['line_subtotal'] + $item['line_subtotal_tax']) - ($item['line_total'] + $item['line_tax']);
							}
						}
					}

					break;
				default:
					// Total Discount - Cart & Order Discounts combined
					$discount_value = WPO_WCPDF()->export->order->get_total_discount();
					break;
			}
		} else { // calculate discount excluding tax
			if ( version_compare( WOOCOMMERCE_VERSION, '2.3' ) >= 0 ) {
				$discount_value = WPO_WCPDF()->export->order->get_total_discount( true ); // $ex_tax = true
			} else {
				// WC2.2 and older: recalculate to exclude tax
				$discount_value = 0;

				$items = WPO_WCPDF()->export->order->get_items();;
				if( sizeof( $items ) > 0 ) {
					foreach( $items as $item ) {
						$discount_value += ($item['line_subtotal'] - $item['line_total']);
					}
				}
			}
		}

		$discount = array (
			'label'		=> __('Discount', 'wpo_wcpdf'),
			'value'		=> WPO_WCPDF()->export->wc_price($discount_value),
			'raw_value'	=> $discount_value,
		);

		if ( round( $discount_value, 3 ) != 0 ) {
			return apply_filters( 'wpo_wcpdf_order_discount', $discount, $type, $tax );
		}
	}
	public function order_discount( $type = 'total', $tax = 'incl' ) {
		$discount = $this->get_order_discount( $type, $tax );
		echo $discount['value'];
	}

	/**
	 * Return the order fees
	 */
	public function get_order_fees( $tax = 'excl' ) {
		if ( $wcfees = WPO_WCPDF()->export->order->get_fees() ) {
			foreach( $wcfees as $id => $fee ) {
				if ($tax == 'excl' ) {
					$fee_price = WPO_WCPDF()->export->wc_price( $fee['line_total'] );
				} else {
					$fee_price = WPO_WCPDF()->export->wc_price( $fee['line_total'] + $fee['line_tax'] );
				}

				$fees[ $id ] = array(
					'label' 		=> $fee['name'],
					'value'			=> $fee_price,
					'line_total'	=> WPO_WCPDF()->export->wc_price($fee['line_total']),
					'line_tax'		=> WPO_WCPDF()->export->wc_price($fee['line_tax'])
				);
			}
			return $fees;
		}
	}
	
	/**
	 * Return the order taxes
	 */
	public function get_order_taxes() {
		$tax_label = __( 'VAT', 'wpo_wcpdf' ); // register alternate label translation
		$tax_label = __( 'Tax rate', 'wpo_wcpdf' );
		$tax_rate_ids = WPO_WCPDF()->export->get_tax_rate_ids();
		if (WPO_WCPDF()->export->order->get_taxes()) {
			foreach ( WPO_WCPDF()->export->order->get_taxes() as $key => $tax ) {
				if ( WCX::is_wc_version_gte_3_0() ) {
					$taxes[ $key ] = array(
						'label'					=> $tax->get_label(),
						'value'					=> WPO_WCPDF()->export->wc_price( $tax->get_tax_total() + $tax->get_shipping_tax_total() ),
						'rate_id'				=> $tax->get_rate_id(),
						'tax_amount'			=> $tax->get_tax_total(),
						'shipping_tax_amount'	=> $tax->get_shipping_tax_total(),
						'rate'					=> isset( $tax_rate_ids[ $tax->get_rate_id() ] ) ? ( (float) $tax_rate_ids[$tax->get_rate_id()]['tax_rate'] ) . ' %': '',
					);
				} else {
					$taxes[ $key ] = array(
						'label'					=> isset( $tax[ 'label' ] ) ? $tax[ 'label' ] : $tax[ 'name' ],
						'value'					=> WPO_WCPDF()->export->wc_price( ( $tax[ 'tax_amount' ] + $tax[ 'shipping_tax_amount' ] ) ),
						'rate_id'				=> $tax['rate_id'],
						'tax_amount'			=> $tax['tax_amount'],
						'shipping_tax_amount'	=> $tax['shipping_tax_amount'],
						'rate'					=> isset( $tax_rate_ids[ $tax['rate_id'] ] ) ? ( (float) $tax_rate_ids[$tax['rate_id']]['tax_rate'] ) . ' %': '',
					);
				}

			}
			
			return apply_filters( 'wpo_wcpdf_order_taxes', $taxes );
		}
	}

	/**
	 * Return/show the order grand total
	 */
	public function get_order_grand_total( $tax = 'incl' ) {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1' ) >= 0 ) {
			// WC 2.1 or newer is used
			$total_unformatted = WPO_WCPDF()->export->order->get_total();
		} else {
			// Backwards compatibility
			$total_unformatted = WPO_WCPDF()->export->order->get_order_total();
		}

		if ($tax == 'excl' ) {
			$total = WPO_WCPDF()->export->wc_price( $total_unformatted - WPO_WCPDF()->export->order->get_total_tax() );
			$label = __( 'Total ex. VAT', 'wpo_wcpdf' );
		} else {
			$total = WPO_WCPDF()->export->wc_price( ( $total_unformatted ) );
			$label = __( 'Total', 'wpo_wcpdf' );
		}
		
		$grand_total = array(
			'label' => $label,
			'value'	=> $total,
		);			

		return apply_filters( 'wpo_wcpdf_order_grand_total', $grand_total, $tax );
	}
	public function order_grand_total( $tax = 'incl' ) {
		$grand_total = $this->get_order_grand_total( $tax );
		echo $grand_total['value'];
	}


	/**
	 * Return/Show shipping notes
	 */
	public function get_shipping_notes() {
		$order_id = WCX_Order::get_id( WPO_WCPDF()->export->order );
		if ( get_post_type( $order_id ) == 'shop_order_refund' ) {
			// return reason for refund if order is a refund
			if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
				$shipping_notes = WPO_WCPDF()->export->order->get_reason();
			} elseif ( method_exists(WPO_WCPDF()->export->order, 'get_refund_reason') ) {
				$shipping_notes = WPO_WCPDF()->export->order->get_refund_reason();
			} else {
				$shipping_notes = wpautop( wptexturize( WCX_Order::get_prop( WPO_WCPDF()->export->order, 'customer_note', 'view' ) ) );
			}
		} else {
			$shipping_notes = wpautop( wptexturize( WCX_Order::get_prop( WPO_WCPDF()->export->order, 'customer_note', 'view' ) ) );
		}
		return apply_filters( 'wpo_wcpdf_shipping_notes', $shipping_notes );
	}
	public function shipping_notes() {
		echo $this->get_shipping_notes();
	}
	

	/**
	 * Return/Show shop/company footer imprint, copyright etc.
	 */
	public function get_footer() {
		if (isset(WPO_WCPDF()->settings->template_settings['footer'])) {
			$footer = wpautop( wptexturize( WPO_WCPDF()->settings->template_settings[ 'footer' ] ) );
			return apply_filters( 'wpo_wcpdf_footer', $footer );
		}
	}
	public function footer() {
		echo $this->get_footer();
	}

	/**
	 * Return/Show Extra field 1
	 */
	public function get_extra_1() {
		if (isset(WPO_WCPDF()->settings->template_settings['extra_1'])) {
			$extra_1 = nl2br( wptexturize( WPO_WCPDF()->settings->template_settings[ 'extra_1' ] ) );
			return apply_filters( 'wpo_wcpdf_extra_1', $extra_1 );
		}
	}
	public function extra_1() {
		echo $this->get_extra_1();
	}

	/**
	 * Return/Show Extra field 2
	 */
	public function get_extra_2() {
		if (isset(WPO_WCPDF()->settings->template_settings['extra_2'])) {
			$extra_2 = nl2br( wptexturize( WPO_WCPDF()->settings->template_settings[ 'extra_2' ] ) );
			return apply_filters( 'wpo_wcpdf_extra_2', $extra_2 );
		}
	}
	public function extra_2() {
		echo $this->get_extra_2();
	}

			/**
	 * Return/Show Extra field 3
	 */
	public function get_extra_3() {
		if (isset(WPO_WCPDF()->settings->template_settings['extra_3'])) {
			$extra_3 = nl2br( wptexturize( WPO_WCPDF()->settings->template_settings[ 'extra_3' ] ) );
			return apply_filters( 'wpo_wcpdf_extra_3', $extra_3 );
		}
	}
	public function extra_3() {
		echo $this->get_extra_3();
	}
}

endif; // class_exists