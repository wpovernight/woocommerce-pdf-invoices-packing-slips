jQuery( function( $ ) {

	$( '#doaction, #doaction2' ).on( 'click', function( e ) {
		let actionselected = $( this ).attr( "id" ).substr( 2 );
		let action         = $( 'select[name="' + actionselected + '"]' ).val();

		if ( $.inArray( action, wpo_wcpdf_ajax.bulk_actions ) !== -1 ) {
			e.preventDefault();
			let template   = action;
			let checked    = [];
			let ubl_output = false;

			// is UBL action
			if ( action.indexOf( 'ubl' ) != -1 ) {
				template   = template.replace( '_ubl', '' );
				ubl_output = true;
			}

			$( 'tbody th.check-column input[type="checkbox"]:checked' ).each(
				function() {
					checked.push( $( this ).val() );
				}
			);

			if ( ! checked.length ) {
				alert( wpo_wcpdf_ajax.select_orders );
				return;
			}

			let partial_url = '';
			let full_url    = '';

			if ( wpo_wcpdf_ajax.ajaxurl.indexOf ("?" ) != -1 ) {
				partial_url = wpo_wcpdf_ajax.ajaxurl+'&action=generate_wpo_wcpdf&document_type='+template+'&bulk&_wpnonce='+wpo_wcpdf_ajax.nonce;
			} else {
				partial_url = wpo_wcpdf_ajax.ajaxurl+'?action=generate_wpo_wcpdf&document_type='+template+'&bulk&_wpnonce='+wpo_wcpdf_ajax.nonce;
			}

			// ubl
			if ( ubl_output ) {
				$.each( checked, function( i, order_id ) {
					full_url = partial_url + '&order_ids='+order_id+'&output=ubl';
					window.open( full_url, '_blank' );
				} );

			// pdf
			} else {
				let order_ids = checked.join( 'x' );
				full_url      = partial_url + '&order_ids='+order_ids;
				window.open( full_url, '_blank' );
			}

		}
	} );

	if ( wpo_wcpdf_ajax.sticky_document_data_metabox ) {
		$( '#wpo_wcpdf-data-input-box' ).insertAfter('#woocommerce-order-data');
	}

	// enable invoice number edit if user initiated
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wpo-wcpdf-set-date-number, .wpo-wcpdf-edit-date-number, .wpo-wcpdf-edit-document-notes', function() {
		let $form = $(this).closest('.wcpdf-data-fields-section');
		if ( $form.length == 0 ) { // no section, take overall wrapper
			$form = $(this).closest('.wcpdf-data-fields');
		}

		let edit = $(this).data( 'edit' );

		// check visibility
		toggle_edit_mode( $form, edit );
	} );

	// cancel edit
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wpo-wcpdf-cancel', function() {
		let $form = $(this).closest('.wcpdf-data-fields');
		toggle_edit_mode( $form );
	} );

	// save, regenerate and delete document
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wpo-wcpdf-save-document, .wpo-wcpdf-regenerate-document, .wpo-wcpdf-delete-document', function( e ) {
		e.preventDefault();

		let $form      = $(this).closest('.wcpdf-data-fields');
		let action     = $(this).data('action');
		let nonce      = $(this).data('nonce');
		let data       = $form.data();
		let serialized = $form.find(":input:visible:not(:disabled)").serialize();

		// Manually append any hidden field that ends with '_formatted_number_current'
		$form.find( 'input[type="hidden"]' ).each( function() {
			if ( $( this ).attr( 'name' ).endsWith( '_formatted_number_current' ) ) {
				const name  = $( this ).attr( 'name' );
				const value = $( this ).val();
				serialized += '&' + encodeURIComponent( name ) + '=' + encodeURIComponent( value );
			}
		} );

		// regenerate specific
		if( action == 'regenerate' ) {
			if ( window.confirm( wpo_wcpdf_ajax.confirm_regenerate ) === false ) {
				return; // having second thoughts
			}

			$form.find('.wpo-wcpdf-regenerate-document').addClass('wcpdf-regenerate-spin');

		// delete specific
		} else if( action == 'delete' ) {
			if ( window.confirm( wpo_wcpdf_ajax.confirm_delete ) === false ) {
				return; // having second thoughts
			}

			// hide regenerate button
			$form.find('.wpo-wcpdf-regenerate-document').hide();
		}

		// Remove previous notice if exists.
		const $previous_notice = $( this ).closest( '#wpo_wcpdf-data-input-box' ).find( '.notice' );
		if ( $previous_notice.length ) {
			$previous_notice.remove();
		}

		// block ui
		$form.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		// request
		$.ajax( {
			url:                            wpo_wcpdf_ajax.ajaxurl,
			data: {
				action:                     'wpo_wcpdf_'+action+'_document',
				security:                   nonce,
				form_data:                  serialized,
				order_id:                   data.order_id,
				document_type:              data.document,
				action_type:                action,
				wpcdf_document_data_notice: action+'d',
			},
			type:               'POST',
			context:            $form,
			success: function( response ) {
				// update document DOM data
				$form.closest('#wpo_wcpdf-data-input-box').load(
					document.URL + ' #wpo_wcpdf-data-input-box .postbox-header, #wpo_wcpdf-data-input-box .inside',
					function() {
						toggle_edit_mode( $form );

						const notice_type   = response.success ? 'success' : 'error';
						const $target_field = $( this ).find( '.wcpdf-data-fields[data-document="' + data.document + '"][data-order_id="' + data.order_id + '"]' );

						if ( $target_field.length ) {
							$target_field.before(
								'<div class="notice notice-' + notice_type + ' inline" style="margin:0 10px 10px 10px;">' +
								'<p>' + response.data.message + '</p>' +
								'</div>'
							);
						}

						if( action === 'regenerate' ) {
							$form.find('.wpo-wcpdf-regenerate-document').removeClass('wcpdf-regenerate-spin');
							toggle_edit_mode( $form );
						}

						// unblock ui
						$form.unblock();
				} );
			}
		} );

	} );

	function toggle_edit_mode( $form, mode = null ) {
		// check visibility
		if( $form.find(".read-only").is(":visible") ) {
			if( mode == 'notes' ) {
				$form.find('.editable-notes :input').attr('disabled', false);
			} else {
				$form.find(".editable").show();
				$form.find(':input').attr('disabled', false);
			}

			$form.find(".read-only").hide();
			$form.find(".editable-notes").show();
			$form.closest('.wcpdf-data-fields').find('.wpo-wcpdf-document-buttons').show();
		} else {
			$form.find(".read-only").show();
			$form.find(".editable").hide();
			$form.find(".editable-notes").hide();
			$form.find(':input').attr('disabled', true);
			$form.closest('.wcpdf-data-fields').find('.wpo-wcpdf-document-buttons').hide();
		}
	}

	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.view-more, .hide-details', function( e ) {
		e.preventDefault();

		$( this ).hide();
		$( '.pdf-more-details' ).slideToggle( 'slow' );

		if ( $( this ).hasClass( 'view-more' ) ) {
			$( '.hide-details' ).show();
		} else {
			$( '.view-more' ).show();
		}
	} );

} );
