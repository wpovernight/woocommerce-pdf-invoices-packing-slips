<?php defined( 'ABSPATH' ) or exit; ?>

<div class="wcpdf-data-fields" data-document="<?php echo esc_attr( $document->get_type() ); ?>" data-order_id="<?php echo esc_attr( $document->order->get_id() ); ?>" data-is_pending="<?php echo wc_bool_to_string( $in_process ) ?>">
	<section class="wcpdf-data-fields-section number-date">
		<!-- Title -->
		<h4>
			<?php echo wp_kses_post( $document->get_title() ); ?>
			<?php if ( $document->exists() && ( isset( $data['number'] ) || isset( $data['date'] ) ) && $this->user_can_manage_document( $document->get_type() ) ) : ?>
				<span class="wpo-wcpdf-edit-date-number dashicons dashicons-edit"></span>
				<span class="wpo-wcpdf-delete-document dashicons dashicons-trash" data-action="delete" data-nonce="<?php echo wp_create_nonce( "wpo_wcpdf_delete_document" ); ?>"></span>
				<?php do_action( 'wpo_wcpdf_document_actions', $document ); ?>
			<?php endif; ?>
		</h4>

		<!-- Read only -->
		<div class="read-only">
			<?php if ( ! $document->exists() && $in_process ) : ?>
			<p>
				<?php
				// translators: document title
				printf( esc_html__( 'The %s is being generated in the background. Please reload the page to see the document data.', 'woocommerce-pdf-invoices-packing-slips' ), $document->get_title() );
				?>
			</p>
			<?php elseif ( $document->exists() ) : ?>
				<?php if ( isset( $data['number'] ) ) : ?>
					<div class="<?php echo esc_attr( $document->get_type() ); ?>-number">
						<p class="form-field <?php echo esc_attr( $data['number']['name'] ); ?>_field">
							<p>
								<span><strong><?php echo wp_kses_post( $data['number']['label'] ); ?></strong></span>
								<span><?php echo esc_attr( $data['number']['formatted'] ); ?></span>
							</p>
						</p>
					</div>
				<?php endif; ?>
				<?php if( isset( $data['date'] ) ) : ?>
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
						<a href="#" class="view-more"><?php _e( 'View more details', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
						<a href="#" class="hide-details" style="display:none;"><?php _e( 'Hide details', 'woocommerce-pdf-invoices-packing-slips' ); ?></a>
					</div>
				<?php endif; ?>
				<?php do_action( 'wpo_wcpdf_meta_box_after_document_data', $document, $document->order ); ?>
			<?php elseif ( $this->user_can_manage_document( $document->get_type() ) ) : ?>
				<span class="wpo-wcpdf-set-date-number button">
					<?php
					printf(
						/* translators: document title */
						esc_html__( 'Set %s number & date', 'woocommerce-pdf-invoices-packing-slips' ),
						wp_kses_post( $document->get_title() )
					);
					?>
				</span>
			<?php else : ?>
				<p>
					<?php echo esc_html__( 'You do not have sufficient permissions to edit this document.', 'woocommerce-pdf-invoices-packing-slips' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<!-- Editable -->
		<div class="editable">
			<?php if( isset( $data['number'] ) ) : ?>
				<p class="form-field <?php echo esc_attr( $data['number']['name'] ); ?>_field">
					<label for="<?php echo esc_attr( $data['number']['name'] ); ?>"><?php echo wp_kses_post( $data['number']['label'] ); ?></label>
					<input type="text" class="short" name="<?php echo esc_attr( $data['number']['name'] ); ?>" id="<?php echo esc_attr( $data['number']['name'] ); ?>" value="<?php echo esc_attr( $data['number']['plain'] ); ?>" disabled="disabled" > (<?php echo esc_html__( 'unformatted!', 'woocommerce-pdf-invoices-packing-slips' ); ?>)
				</p>
			<?php endif; ?>
			<?php if( isset( $data['date'] ) ) : ?>
				<p class="form-field form-field-wide">
					<label for="<?php echo esc_attr( $data['date']['name'] ); ?>[date]"><?php echo wp_kses_post( $data['date']['label'] ); ?></label>
					<input type="text" class="date-picker-field" name="<?php echo esc_attr( $data['date']['name'] ); ?>[date]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[date]" maxlength="10" value="<?php echo esc_attr( $data['date']['date'] ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" disabled="disabled"/>@<input type="number" class="hour" disabled="disabled" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ); ?>" name="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[hour]" min="0" max="23" size="2" value="<?php echo esc_attr( $data['date']['hour'] ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ); ?>" name="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]" id="<?php echo esc_attr( $data['date']['name'] ); ?>[minute]" min="0" max="59" size="2" value="<?php echo esc_attr( $data['date']['minute'] ); ?>" pattern="[0-5]{1}[0-9]{1}"  disabled="disabled" />
				</p>
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
				<p><?php echo ( $data['notes']['value'] == strip_tags( $data['notes']['value'] ) ) ? wp_kses_post( nl2br( $data['notes']['value'] ) ) : wp_kses_post( $data['notes']['value'] ); ?></p>
			</div>
			<!-- Editable -->
			<div class="editable-notes">
				<p class="form-field form-field-wide">
					<label for="<?php echo esc_attr( $data['notes']['name'] ); ?>"><?php echo wp_kses_post( $data['notes']['label'] ); ?></label>
				<p><textarea name="<?php echo esc_attr( $data['notes']['name'] ); ?>" class="<?php echo esc_attr( $data['notes']['name'] ); ?>" cols="60" rows="5" disabled="disabled"><?php echo wp_kses_post( $data['notes']['value'] ); ?></textarea></p>
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