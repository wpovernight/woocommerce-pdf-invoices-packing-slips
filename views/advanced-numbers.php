<?php defined( 'ABSPATH' ) or exit; ?>

<div class="wcpdf_document_settings_sections wcpdf_advanced_numbers_choose_table">
	<?php
		$choose_table_title = isset( $number_store_tables[ $selected_table_name ] ) ? esc_attr( $number_store_tables[ $selected_table_name ] ) : __( 'Choose a number store', 'woocommerce-pdf-invoices-packing-slips' );
		echo '<h2>' . esc_html( $choose_table_title ) . '<span class="arrow-down">&#9660;</span></h2>';
	?>
	<ul>
		<?php
			foreach ( $number_store_tables as $table_name => $title ) {
				if ( isset( $_GET['table_name'] ) && $table_name !== $_GET['table_name'] ) {
					if ( empty( trim( $title ) ) ) {
						$title = '[' . __( 'untitled', 'woocommerce-pdf-invoices-packing-slips' ) . ']';
					}
					printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( add_query_arg( 'table_name', esc_attr( $table_name ) ) ), esc_html( $title ) );
				}
			}
		?>
	</ul>
	<?php if ( ! empty( $document_type ) && 'invoice' !== $document_type && in_array( $document_type, $invoice_number_store_doc_types ) ) : ?>
		<div class="notice notice-warning inline">
			<p><?php _e( 'This document is currently using the main invoice number sequence.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		</div>
	<?php elseif ( ! empty( $selected_table_name ) && ! empty( $number_store_tables[ $selected_table_name ] ) ) : ?>
		<p>
			<?php
				printf(
					/* translators: chose table title */
					__( 'Below is a list of all the document numbers generated since the last reset (which happens when you set the <strong>next %s number</strong> value in the settings).', 'woocommerce-pdf-invoices-packing-slips' ),
					$choose_table_title
				);
			?>
		</p>
		<p><?php _e( 'Numbers may have been assigned to orders before this.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<div class="number-search" style="text-align:right;">
			<input type="number" id="number_search_input" name="number_search_input" min="1" max="4294967295" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>">
			<a href="#" class="button button-primary number-search-button"><?php _e( 'Search number', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
			<?php $disabled = ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) ? '' : 'disabled'; ?>
			<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>" class="button button-secondary" <?php echo $disabled; ?>><?php _e( 'Reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
		</div>
		<?php $list_table->display(); ?>
	<?php else : ?>
		<div class="notice notice-info inline">
			<p><?php _e( 'Please select a number store!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		</div>
	<?php endif; ?>
</div>
