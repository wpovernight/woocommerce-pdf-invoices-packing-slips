<?php defined( 'ABSPATH' ) or exit; ?>
<div class="wpo-step-description">
	<h2><?php esc_html_e( 'Display options', 'woocommerce-pdf-invoices-packing-slips' ); ?></h2>
	<p><?php esc_html_e( 'Select some additional display options for your invoice.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
</div>
<div class="wpo-setup-input">
	<table>
	<?php
	$current_settings = get_option( 'wpo_wcpdf_documents_settings_invoice', array() );
	?>
		<tr>
			<th>
				<label for="display-shipping-address" class="checkbox"><?php esc_html_e( 'Shipping address', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_shipping_address]" value="">
				<select  id="display-shipping-address" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_shipping_address]">
					<?php
						$options = array(
							''               => __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
							'when_different' => __( 'Only when different from billing address' , 'woocommerce-pdf-invoices-packing-slips' ),
							'always'         => __( 'Always' , 'woocommerce-pdf-invoices-packing-slips' ),
						);
						foreach ( $options as $slug => $name ) {
							$selected = ( ! empty( $current_settings['display_shipping_address'] ) && $current_settings['display_shipping_address'] == $slug ) ? 'selected' : '';
							echo '<option value="'.esc_attr( $slug ).'" '.esc_attr( $selected ).'>'.esc_html( $name ).'</option>';
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th>
				<label for="display-email" class="checkbox"><?php esc_html_e( 'Email address', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_email]" value="">
				<input id="display-email" type="checkbox" <?php echo ! empty( $current_settings['display_email'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_email]" value="1">
			</td>
		</tr>
		<tr>
			<th>
				<label for="display-phone" class="checkbox"><?php esc_html_e( 'Phone number', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_phone]" value="">
				<input id="display-phone" type="checkbox" <?php echo ! empty( $current_settings['display_phone'] ) ? 'checked' : ''; ?> name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_phone]" value="1">
			</td>
		</tr>
		<tr>
			<th>
				<label for="display-date" class="checkbox"><?php esc_html_e( 'Invoice date', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_date]" value="">
				<select id="display-date" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_date]">
					<?php
						$options = array(
							''             => __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
							'invoice_date' => __( 'Invoice Date' , 'woocommerce-pdf-invoices-packing-slips' ),
							'order_date'   => __( 'Order Date' , 'woocommerce-pdf-invoices-packing-slips' ),
						);
						foreach ( $options as $slug => $name ) {
							$selected = ( ! empty( $current_settings['display_date'] ) && $current_settings['display_date'] == $slug ) ? 'selected' : '';
							echo '<option value="'.esc_attr( $slug ).'" '.esc_attr( $selected ).'>'.esc_html( $name ).'</option>';
						}
					?>
				</select>
			</td>
		<tr>
		</tr>
			<th>
				<label for="display-number" class="checkbox"><?php esc_html_e( 'Invoice number', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
			</th>
			<td>
				<input type="hidden" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_number]" value="">
				<select id="display-number" name="wcpdf_settings[wpo_wcpdf_documents_settings_invoice][display_number]">
					<?php
						$options = array(
							''               => __( 'No' , 'woocommerce-pdf-invoices-packing-slips' ),
							'invoice_number' => __( 'Invoice Number' , 'woocommerce-pdf-invoices-packing-slips' ),
							'order_number'   => __( 'Order Number' , 'woocommerce-pdf-invoices-packing-slips' ),
						);
						foreach ( $options as $slug => $name ) {
							$selected = ( ! empty( $current_settings['display_number'] ) && $current_settings['display_number'] == $slug ) ? 'selected' : '';
							echo '<option value="'.esc_attr( $slug ).'" '.esc_attr( $selected ).'>'.esc_html( $name ).'</option>';
						}
					?>
				</select>
			</td>
		</tr>
	</table>
</div>
