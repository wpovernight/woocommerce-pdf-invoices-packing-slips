<?php defined( 'ABSPATH' ) or exit; ?>

<div
	class="wcpdf-data-fields"
	data-document="<?php echo esc_attr( $document->get_type() ); ?>"
	data-order_id="<?php echo esc_attr( $document->order->get_id() ); ?>"
	data-document_number="<?php echo esc_attr( $document->get_number() ); ?>"
	data-is_pending="<?php echo wc_bool_to_string( $in_process ); ?>">
	<section class="wcpdf-data-fields-section number-date">
		<!-- Title -->
		<h4>
			<?php echo wp_kses_post( $document->get_title() ); ?>
			<?php if ( $document->exists() && ( isset( $data['number'] ) || isset( $data['date'] ) ) && $this->user_can_manage_document( $document->get_type() ) ) : ?>
				<span class="wpo-wcpdf-edit-date-number dashicons dashicons-edit"></span>
				<span class="wpo-wcpdf-delete-document dashicons dashicons-trash" data-action="delete" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpo_wcpdf_delete_document' ) ); ?>"></span>
				<?php do_action( 'wpo_wcpdf_document_actions', $document ); ?>
			<?php endif; ?>
		</h4>

		<!-- Read only -->
		<div class="read-only">
			<?php if ( ! $document->exists() && $in_process ) : ?>
				<p>
					<?php
						printf(
							/* translators: %s: document title */
							esc_html__( 'The %s is being generated in the background. Please reload the page to see the document data.', 'woocommerce-pdf-invoices-packing-slips' ),
							$document->get_title()
						);
					?>
				</p>
			<?php elseif ( $document->exists() ) : ?>
				<?php if ( isset( $data['number'] ) ) : ?>
					<div class="<?php echo esc_attr( $document->get_type() ); ?>-number">
						<p class="form-field <?php echo esc_attr( $data['number']['formatted']['name'] ); ?>_field">
							<p>
								<span><strong><?php echo wp_kses_post( $data['number']['label'] ); ?></strong></span>
								<span><?php echo esc_attr( $data['number']['formatted']['value'] ); ?></span>
							</p>
						</p>
					</div>
				<?php endif; ?>
				<?php if ( isset( $data['date'] ) ) : ?>
					<div class="<?php echo esc_attr( $document->get_type() ); ?>-date">
						<p class="form-field form-field-wide">
							<p>
								<span><strong><?php echo wp_kses_post( $data['date']['label'] ); ?></strong></span>
								<span><?php echo esc_attr( $data['date']['formatted'] ); ?></span>
							</p>
						</p>
					</div>
				<?php endif; ?>
				<div class="pdf-more-details" style="display:none;">
					<?php if ( isset( $data['display_date'] ) ) : ?>
						<div class="<?php echo esc_attr( $document->get_type() ); ?>-display-date">
							<p class="form-field form-field-wide">
								<p>
									<span><strong><?php echo wp_kses_post( $data['display_date']['label'] ); ?></strong></span>
									<span><?php echo esc_attr( $data['display_date']['value'] ); ?></span>
								</p>
							</p>
						</div>
					<?php endif; ?>
					<?php if ( isset( $data['creation_trigger'] ) && ! empty( $data['creation_trigger']['value'] ) ) : ?>
						<div class="<?php echo esc_attr( $document->get_type() ); ?>-creation-status">
							<p class="form-field form-field-wide">
								<p>
									<span><strong><?php echo wp_kses_post( $data['creation_trigger']['label'] ); ?></strong></span>
									<span><?php echo esc_attr( $data['creation_trigger']['value'] ); ?></span>
								</p>
							</p>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( isset( $data['display_date'] ) || isset( $data['creation_trigger'] ) ) : ?>
					<div>
						<a href="#" class="view-more"><?php esc_html_e( 'View more details', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
						<a href="#" class="hide-details" style="display:none;"><?php esc_html_e( 'Hide details', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_meta_box_after_document_data', $document, $document->order ); ?>
			<?php else : ?>
				<?php if ( $this->user_can_manage_document( $document->get_type() ) ) : ?>
					<?php if ( $document_data_editing_enabled ) : ?>
						<span class="wpo-wcpdf-set-date-number button">
									<?php
									printf(
									/* translators: document title */
										esc_html__( 'Set %s number & date', 'woocommerce-pdf-invoices-packing-slips' ),
										esc_html( $document->get_title() )
									);
									?>
								</span>
					<?php else : ?>
						<?php $this->document_data_editing_disabled_notice( $document ); ?>
					<?php endif; ?>
				<?php else : ?>
					<p><?php echo esc_html__( 'You do not have sufficient permissions to edit this document.', 'woocommerce-pdf-invoices-packing-slips' ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<!-- Editable -->
		<div class="editable editable-number-date">
			<?php if ( $document_data_editing_enabled ) : ?>
				<?php if ( ! empty( $data['number'] ) ) : ?>
					<div class="data-fields-grid">
						<div class="data-fields-row">
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['number']['prefix']['name'] ); ?>">
									<?php esc_html_e( 'Number prefix', 'woocommerce-pdf-invoices-packing-slips' ); ?>
									<?php
									$tip_text = sprintf(
										'%s %s',
										__( 'If set, this value will be used as number prefix.' , 'woocommerce-pdf-invoices-packing-slips' ),
										sprintf(
										/* translators: 1. document title, 2-3 placeholders */
											__( 'You can use the %1$s year and/or month with the %2$s or %3$s placeholders respectively.', 'woocommerce-pdf-invoices-packing-slips' ),
											esc_html( $document->get_title() ),
											'<strong>[' . esc_html( $document->slug ) . '_year]</strong>',
											'<strong>[' . esc_html( $document->slug ) . '_month]</strong>'
										)
									);
									echo wc_help_tip( wp_kses_post( $tip_text ), true );
									?>
								</label>
								<input type="text" class="short" name="<?php echo esc_attr( $data['number']['prefix']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['prefix']['name'] ); ?>" value="<?php echo esc_html( $data['number']['prefix']['value'] ); ?>" disabled="disabled">
							</div>
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['number']['suffix']['name'] ); ?>">
									<?php esc_html_e( 'Number suffix', 'woocommerce-pdf-invoices-packing-slips' ); ?>
									<?php
									$tip_text = sprintf(
										'%s %s',
										__( 'If set, this value will be used as number suffix.' , 'woocommerce-pdf-invoices-packing-slips' ),
										sprintf(
										/* translators: 1. document title, 2-3 placeholders */
											__( 'You can use the %1$s year and/or month with the %2$s or %3$s placeholders respectively.', 'woocommerce-pdf-invoices-packing-slips' ),
											esc_html( $document->get_title() ),
											'<strong>[' . esc_html( $document->slug ) . '_year]</strong>',
											'<strong>[' . esc_html( $document->slug ) . '_month]</strong>'
										)
									);
									echo wc_help_tip( wp_kses_post( $tip_text ), true );
									?>
								</label>
								<input type="text" class="short" name="<?php echo esc_attr( $data['number']['suffix']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['suffix']['name'] ); ?>" value="<?php echo esc_html( $data['number']['suffix']['value'] ); ?>" disabled="disabled">
							</div>
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['number']['padding']['name'] ); ?>">
									<?php esc_html_e( 'Number padding', 'woocommerce-pdf-invoices-packing-slips' ); ?>
									<?php
									$tip_text = sprintf(
									/* translators: %1$s: code, %2$s: document title, %3$s: number, %4$s: padded number */
										__( 'Enter the number of digits you want to use as padding. For instance, enter %1$s to display the %2$s number %3$s as %4$s, filling it with zeros until the number set as padding is reached.' , 'woocommerce-pdf-invoices-packing-slips' ),
										'<code>6</code>',
										esc_html( $document->get_title() ),
										'<code>123</code>',
										'<code>000123</code>'
									);
									echo wc_help_tip( wp_kses_post( $tip_text ), true );
									?>
								</label>
								<input type="number" min="1" step="1" class="short" name="<?php echo esc_attr( $data['number']['padding']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['padding']['name'] ); ?>" value="<?php echo absint( $data['number']['padding']['value'] ); ?>" disabled="disabled">
							</div>
							<div class="row-note">
								<?php
								echo wp_kses_post(
									sprintf(
									/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
										__( 'For more information about setting up the number format and see the available placeholders for the prefix and suffix, check this article: %1$sNumber format explained%2$s', 'woocommerce-pdf-invoices-packing-slips' ),
										'<a href="https://docs.wpovernight.com/woocommerce-pdf-invoices-packing-slips/number-format-explained/" target="_blank">',
										'</a>'
									)
								);
								?>
							</div>
						</div>
						<div class="data-fields-row">
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['number']['plain']['name'] ); ?>">
									<?php
									printf(
									/* translators: %s document title */
										esc_html__( '%s number', 'woocommerce-pdf-invoices-packing-slips' ),
										esc_html( $document->get_title() )
									);
									?>
								</label>
								<input type="number" min="1" step="1" class="short" name="<?php echo esc_attr( $data['number']['plain']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['plain']['name'] ); ?>" value="<?php echo absint( $data['number']['plain']['value'] ); ?>" disabled="disabled">
							</div>
							<div class="field-group">
								<label><?php esc_html_e( 'Formatted number', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
								<input type="text" class="formatted-number" data-current="<?php echo esc_html( $data['number']['formatted']['value'] ); ?>" value="<?php echo esc_html( $data['number']['formatted']['value'] ); ?>" readonly>
							</div>
							<div class="field-group placeholder"></div> <!-- Empty cell -->
							<div class="row-note">
								<?php echo wp_kses_post( sprintf(
								/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
									__( 'Manually changing the document\'s plain number also requires updating the next document number in the %1$sdocument settings%2$s.' ),
									'<a href="' . esc_url( admin_url( 'admin.php?page=wpo_wcpdf_options_page&tab=documents&section=' . $document->get_type() ) ) . '#next_' . $document->slug . '_number" target="_blank">',
									'</a>'
								) ); ?>
								<?php esc_html_e( 'Please note that changing the document number may create gaps in the numbering sequence.', 'woocommerce-pdf-invoices-packing-slips' ); ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( isset( $data['date'] ) ) : ?>
					<div class="data-fields-grid">
						<div class="data-fields-row">
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[date]">
									<?php
									printf(
									/* translators: %s document title */
										esc_html__( '%s date', 'woocommerce-pdf-invoices-packing-slips' ),
										esc_html( $document->get_title() )
									);
									?>
								</label>
								<input type="text" class="date-picker-field" name="<?php echo esc_attr( $data['date']['name'] ); ?>[date]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[date]" maxlength="10" value="<?php echo esc_attr( $data['date']['date'] ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" disabled="disabled">
							</div>
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]"><?php esc_html_e( 'Hour', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
								<input type="number" class="hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce-pdf-invoices-packing-slips' ); ?>" name="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]" min="0" max="23" size="2" value="<?php echo esc_attr( $data['date']['hour'] ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" disabled="disabled">
							</div>
							<div class="field-group">
								<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]"><?php esc_html_e( 'Minute', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
								<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce-pdf-invoices-packing-slips' ); ?>" name="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]" min="0" max="59" size="2" value="<?php echo esc_attr( $data['date']['minute'] ); ?>" pattern="[0-5]{1}[0-9]{1}"  disabled="disabled">
							</div>
						</div>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<?php $this->document_data_editing_disabled_notice( $document ); ?>
			<?php endif; ?>
		</div>

		<!-- Document Notes -->
		<?php if ( array_key_exists( 'notes', $data ) && ! $in_process ) : ?>
			<?php do_action( 'wpo_wcpdf_meta_box_before_document_notes', $document, $document->order ); ?>
			<!-- Read only -->
			<div class="read-only">
				<span><strong><?php echo wp_kses_post( $data['notes']['label'] ); ?></strong></span>
				<?php if ( $this->user_can_manage_document( $document->get_type() ) ) : ?>
					<span class="wpo-wcpdf-edit-document-notes dashicons dashicons-edit" data-edit="notes"></span>
				<?php endif; ?>
				<p><?php echo ( $data['notes']['value'] == wp_strip_all_tags( $data['notes']['value'] ) ) ? wp_kses_post( nl2br( $data['notes']['value'] ) ) : wp_kses_post( $data['notes']['value'] ); ?></p>
			</div>
			<!-- Editable -->
			<div class="editable-notes">
				<div class="data-fields-grid">
					<div class="data-fields-row">
						<div class="field-group">
							<label for="<?php echo esc_attr( $data['notes']['name'] ); ?>"><?php esc_html_e( 'Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></label>
							<textarea name="<?php echo esc_attr( $data['notes']['name'] ); ?>" class="<?php echo esc_attr( $data['notes']['name'] ); ?>" cols="60" rows="5" disabled="disabled"><?php echo wp_kses_post( $data['notes']['value'] ); ?></textarea>
						</div>
						<div class="field-group placeholder"></div> <!-- Empty cell -->
						<div class="field-group placeholder"></div> <!-- Empty cell -->
						<div class="row-note"><?php esc_html_e( 'Displayed in the document!', 'woocommerce-pdf-invoices-packing-slips' ); ?></div>
					</div>
				</div>
			</div>
			<?php do_action( 'wpo_wcpdf_meta_box_after_document_notes', $document, $document->order ); ?>
		<?php endif; ?>
	</section>

	<!-- Save/Cancel buttons -->
	<section class="wcpdf-data-fields-section wpo-wcpdf-document-buttons">
		<div>
			<a class="button button-primary wpo-wcpdf-save-document" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpo_wcpdf_save_document' ) ); ?>" data-action="save"><?php esc_html_e( 'Save changes', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
			<a class="button wpo-wcpdf-cancel"><?php esc_html_e( 'Cancel', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
		</div>
	</section>
	<!-- / Save/Cancel buttons -->
</div>
