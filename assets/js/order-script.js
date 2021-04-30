jQuery( function( $ ) {

	$( '#doaction, #doaction2' ).on( 'click', function( e ) {
		let actionselected = $(this).attr("id").substr(2);
		let action         = $('select[name="' + actionselected + '"]').val();

		if ( $.inArray(action, wpo_wcpdf_ajax.bulk_actions) !== -1 ) {
			e.preventDefault();
			let template = action;
			let checked  = [];

			$('tbody th.check-column input[type="checkbox"]:checked').each(
				function() {
					checked.push($(this).val());
				}
			);
			
			if (!checked.length) {
				alert('You have to select order(s) first!');
				return;
			}
			
			let order_ids = checked.join('x');

			if (wpo_wcpdf_ajax.ajaxurl.indexOf("?") != -1) {
				url = wpo_wcpdf_ajax.ajaxurl+'&action=generate_wpo_wcpdf&document_type='+template+'&order_ids='+order_ids+'&bulk&_wpnonce='+wpo_wcpdf_ajax.nonce;
			} else {
				url = wpo_wcpdf_ajax.ajaxurl+'?action=generate_wpo_wcpdf&document_type='+template+'&order_ids='+order_ids+'&bulk&_wpnonce='+wpo_wcpdf_ajax.nonce;
			}

			window.open(url,'_blank');
		}
	} );

	$( '#wpo_wcpdf-data-input-box' ).insertAfter('#woocommerce-order-data');
	
	// enable invoice number edit if user initiated
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wpo-wcpdf-set-date-number, .wpo-wcpdf-edit-date-number, .wpo-wcpdf-edit-document-notes', function() {
		let $form = $(this).closest('.wcpdf-data-fields-section');
		if ( $form.length == 0 ) { // no section, take overall wrapper
			$form = $(this).closest('.wcpdf-data-fields');
		}

		let edit = $(this).data( 'edit' );

		// check visibility
		if( $form.find(".read-only").is(":visible") ) {
			if( edit == 'notes' ) {
				$form.find(".read-only").hide();
				$form.find(".editable-notes").show();
				$form.find('.editable-notes :input').attr('disabled', false);
				$form.closest('.wcpdf-data-fields').find('.wpo-wcpdf-document-buttons').show();
			} else {
				$form.find(".read-only").hide();
				$form.find(".editable").show();
				$form.find(".editable-notes").show();
				$form.find(':input').attr('disabled', false);
				$form.closest('.wcpdf-data-fields').find('.wpo-wcpdf-document-buttons').show();
			}
		} else {
			$form.find(".read-only").show();
			$form.find(".editable").hide();
			$form.find(".editable-notes").hide();
			$form.find(':input').attr('disabled', true);
			$form.closest('.wcpdf-data-fields').find('.wpo-wcpdf-document-buttons').hide();
		}
	} );


	// delete document
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wcpdf-data-fields .wpo-wcpdf-delete-document', function() {
		if ( window.confirm( wpo_wcpdf_ajax.confirm_delete ) === false ) {
			return; // having second thoughts
		}

		let $form = $(this).closest('.wcpdf-data-fields');

		// Hide regenerate button
		$form.find('.wpo-wcpdf-regenerate-document').hide();

		$.ajax({
			url:     wpo_wcpdf_ajax.ajaxurl,
			data:    {
				action  : 'wpo_wcpdf_delete_document',
				security: $(this).data('nonce'),
				document: $form.data('document'),
				order_id: $form.data('order_id')
			},
			type:    'POST',
			context: $form,
			success: function( response ) {
				if ( response.success ) {
					$(this).find(':input').val("");
					$(this).find('.read-only').hide();
					$(this).find('.wpo-wcpdf-delete-document').hide();
				}
			}
		});
	} );

	// regenerate document
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wcpdf-data-fields .wpo-wcpdf-regenerate-document', save_document_data );

	// save document
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wcpdf-data-fields .wpo-wcpdf-save-document', save_document_data );

	function save_document_data( e ) {
		e.preventDefault();

		let $form      = $(this).closest('.wcpdf-data-fields');
		let action     = $(this).data('action');
		let nonce      = $(this).data('nonce');
		let data       = $form.data();
		let serialized = $form.find(":input:visible:not(:disabled)").serialize();

		// Make sure all feedback icons are hidden before each call
		$form.find('.document-action-success, .document-action-failed').hide();

		// block UI
		if( action == 'save' ) {
			$form.block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );
		} else if( action == 'regenerate' ) {
			if ( window.confirm( wpo_wcpdf_ajax.confirm_regenerate ) === false ) {
				return; // having second thoughts
			}
	
			$form.find('.wpo-wcpdf-regenerate-document').addClass('wcpdf-regenerate-spin');
		}

		$.ajax( {
			url:                wpo_wcpdf_ajax.ajaxurl,
			data: {
				action:         'wpo_wcpdf_'+action+'_document',
				security:       nonce,
				form_data:      serialized,
				order_id:       data.order_id,
				document_type:  data.document,
				action_type:    action,
			},
			type:               'POST',
			context:            $form,
			success: function( response ) {
				if ( response.success ) {
					if( action == 'save' ) {
						$form.find(".read-only").show();
						$form.find(".editable").hide();
						$form.find(':input').attr('disabled', true);
						$form.find('.wpo-wcpdf-document-buttons').hide();

						// update document DOM data
						$form.closest('#wpo_wcpdf-data-input-box').load( document.URL + ' #wpo_wcpdf-data-input-box .postbox-header, #wpo_wcpdf-data-input-box .inside', function() {
							$('#wpo_wcpdf-data-input-box .notice').show();
						});
					} else if( action == 'regenerate' ) {
						$form.find('.document-action-success').show();
					}
				} else {
					$form.find('.document-action-failed').show();
				}

				if( action == 'save' ) {
					// unblock UI
					$form.unblock();
				} else if( action == 'regenerate' ) {
					$form.find('.wpo-wcpdf-regenerate-document').removeClass('wcpdf-regenerate-spin');
				}
			}
		} );
	}

	// cancel edit
	$( '#wpo_wcpdf-data-input-box' ).on( 'click', '.wpo-wcpdf-cancel', function() {
		let $form = $(this).closest('.wcpdf-data-fields');

		$form.find(".read-only").show();
		$form.find(".editable").hide();
		$form.find(".editable-notes").hide();
		$form.find(':input').attr('disabled', true);
		$form.find('.wpo-wcpdf-document-buttons').hide();
	} );

} );