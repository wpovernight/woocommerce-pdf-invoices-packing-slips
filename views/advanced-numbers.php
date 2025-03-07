<?php defined( 'ABSPATH' ) or exit; ?>

<div class="wcpdf_document_settings_sections wcpdf_advanced_numbers_choose_table">
	<?php
		$choose_table_title = isset( $number_store_tables[ $selected_table_name ] ) ? esc_attr( $number_store_tables[ $selected_table_name ] ) : __( 'Choose a number store', 'woocommerce-pdf-invoices-packing-slips' );
		echo '<h2>' . esc_html( $choose_table_title ) . '<span class="arrow-down">&#9660;</span></h2>';
	?>
	<ul>
		<?php
			foreach ( $number_store_tables as $table_name => $title ) {
				if ( isset( $list_table_name ) && $table_name !== $list_table_name ) {
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
			<p><?php esc_html_e( 'This document is currently using the main invoice number sequence.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		</div>
	<?php elseif ( ! empty( $selected_table_name ) && ! empty( $number_store_tables[ $selected_table_name ] ) ) : ?>
		<p>
			<?php
				printf(
					/* translators: chose table title */
					wp_kses_post( 'Below is a list of all the document numbers generated since the last reset (which happens when you set the <strong>next %s number</strong> value in the settings).', 'woocommerce-pdf-invoices-packing-slips' ),
					esc_html( $choose_table_title )
				);
			?>
		</p>
		<p><?php esc_html_e( 'Numbers may have been assigned to orders before this.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		<div class="number-table-data-info">
			<?php if ( ! empty( $as_actions ) ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php
							printf(
								/* translators: %1$s: link to action scheduler, %2$s: closing tag */
								esc_html__( 'The data fetching process is currently underway. Please consider refreshing the page periodically until it is completed or check current status %1$shere%2$s', 'woocommerce-pdf-invoices-packing-slips' ),
								'<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=action-scheduler&s=wpo_wcpdf_number_table_data_fetch' ) ) . '">',
								'</a>'
							);
						?>
					</p>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Given the potential impact of querying a large volume of orders on site performance, it\'s essential to fetch data each time you need the most current information. This procedure ensures that the site remains efficient and responsive, even when handling substantial order quantities.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<?php if ( ! empty( $last_fetch ) ) : ?>
					<p><strong><?php esc_html_e( 'Last fetch', 'woocommerce-pdf-invoices-packing-slips' ); ?>: </strong><code><?php echo esc_html( date_i18n( 'Y-m-d H:i:s', $last_fetch ) ); ?></code></p>
					<?php if ( $last_fetch > strtotime( 'today 23:59:59' ) ) : ?>
						<div class="notice notice-warning inline"><p><?php esc_html_e( 'The displayed data may not be current. To ensure you have the most recent information, you might want to fetch updated data.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p></div>
					<?php endif; ?>
				<?php endif; ?>
				<table>
					<tbody>
						<tr>
							<td><?php esc_html_e( 'From:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td>
								<input type="text" id="fetch-numbers-data-date-from" name="fetch-numbers-data-date-from" value="<?php echo esc_html( date_i18n( 'Y-m-d' ) ); ?>" size="10">
								<span class="add-info"><?php esc_html_e( '(as: yyyy-mm-dd)', 'woocommerce-pdf-invoices-packing-slips' ); ?></span>
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'To:', 'woocommerce-pdf-invoices-packing-slips' ); ?></td>
							<td>
								<input type="text" id="fetch-numbers-data-date-to" name="fetch-numbers-data-date-to" value="<?php echo esc_html( date_i18n( 'Y-m-d' ) ); ?>" size="10">
								<span class="add-info"><?php esc_html_e( '(as: yyyy-mm-dd)', 'woocommerce-pdf-invoices-packing-slips' ); ?></span>
							</td>
						</tr>
					</tbody>
				</table>
				<p>
					<span><a href="#" id="fetch-numbers-data" class="button button-primary" data-table_name="<?php echo esc_attr( $selected_table_name ); ?>" data-operation="fetch"><?php esc_html_e( 'Fetch data', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></span>
					<?php if ( $last_fetch ) : ?>
						<span style="margin-left:6px;"><a href="#" id="delete-numbers-data" class="button button-secondary" data-table_name="<?php echo esc_attr( $selected_table_name ); ?>" data-operation="delete"><?php esc_html_e( 'Delete cached data', 'woocommerce-pdf-invoices-packing-slips' ); ?></a></span>
					<?php endif; ?>
				</p>
				<?php if ( $last_fetch ) : ?>
					<div class="number-search" style="text-align:right;">
						<input type="number" id="number_search_input" name="number_search_input" min="1" max="4294967295" value="<?php echo esc_attr( $search_value ); ?>">
						<a href="#" class="button button-primary number-search-button"><?php esc_html_e( 'Search number', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
						<?php $disabled = ! empty( $search_value ) ? '' : 'disabled'; ?>
						<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>" class="button button-secondary" <?php echo esc_attr( $disabled ); ?>><?php esc_html_e( 'Reset', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					</div>
				<?php $list_table->prepare_items(); $list_table->display(); ?>
				<?php else : ?>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Please fetch data to view it listed here.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'Please select a number store!', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
		</div>
	<?php endif; ?>
</div>
