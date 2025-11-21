jQuery( function( $ ) {

	$( '#doaction, #doaction2' ).on( 'click', function( e ) {
		let actionselected = $( this ).attr( "id" ).substr( 2 );
		let action         = $( 'select[name="' + actionselected + '"]' ).val();

		if ( $.inArray( action, wpo_wcpdf_ajax.bulk_actions ) !== -1 ) {
			e.preventDefault();

			let document_type = action;
			let checked       = [];
			let xml_output    = false;

			// is XML action
			if ( action.indexOf( 'xml' ) != -1 ) {
				document_type = document_type.replace( '_xml', '' );
				xml_output    = true;
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
				partial_url = wpo_wcpdf_ajax.ajaxurl+'&action=generate_wpo_wcpdf&document_type='+document_type+'&bulk&_wpnonce='+wpo_wcpdf_ajax.nonce;
			} else {
				partial_url = wpo_wcpdf_ajax.ajaxurl+'?action=generate_wpo_wcpdf&document_type='+document_type+'&bulk&_wpnonce='+wpo_wcpdf_ajax.nonce;
			}

			// xml
			if ( xml_output ) {

				// Credit Note: get refund IDs first
				if ( 'credit-note' === document_type ) {
					$.ajax( {
						url:       wpo_wcpdf_ajax.ajaxurl,
						type:     'POST',
						dataType: 'json',
						data: {
							action:    'wpo_ips_get_refund_order_ids',
							order_ids: checked,
							security:  wpo_wcpdf_ajax.nonce
						},
						success: function( response ) {
							if ( response && response.success && response.data && response.data.refund_ids && response.data.refund_ids.length ) {
								$.each( response.data.refund_ids, function( i, refund_id ) {
									full_url = partial_url + '&order_ids='+refund_id+'&output=xml';
									window.open( full_url, '_blank' );
								} );
							} else {
								let msg = ( response && response.data && response.data.message ) ? response.data.message : wpo_wcpdf_ajax.error_no_refunds_found;
								alert( msg );
							}
						},
						error: function() {
							alert( wpo_wcpdf_ajax.error_fetching_refund_ids );
						}
					} );

					// stop normal XML processing
					return;
				}

				// default xml
				$.each( checked, function( i, order_id ) {
					full_url = partial_url + '&order_ids='+order_id+'&output=xml';
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
		let $form = $(this).closest('.wcpdf-data-fields');
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

		let $form      = $( this ).closest( '.wcpdf-data-fields' );
		let action     = $( this ).data( 'action' );
		let nonce      = $( this ).data( 'nonce' );
		let data       = $form.data();
		let serialized = $form.find( ":input:visible:not(:disabled)" ).serialize();

		// regenerate specific
		if ( 'regenerate' === action ) {
			if ( window.confirm( wpo_wcpdf_ajax.confirm_regenerate ) === false ) {
				return; // having second thoughts
			}

			$form.find( '.wpo-wcpdf-regenerate-document' ).addClass( 'wcpdf-regenerate-spin' );

		// delete specific
		} else if ( 'delete' === action ) {
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
		if ( $form.find( '.read-only' ).is( ':visible' ) ) {
			if ( mode === 'notes' ) {
				$form.find( '.editable-notes :input' ).attr( 'disabled', false );
			} else {
				$form.find( '.editable' ).show();
				$form.find( ':input' ).attr( 'disabled', false );
			}

			$form.find( '.read-only' ).hide();
			$form.find( '.editable-notes' ).show();
			$form.closest( '.wcpdf-data-fields' ).find( '.wpo-wcpdf-document-buttons' ).show();

			// re-initialize WooCommerce tooltips
			$( '.wcpdf-data-fields .woocommerce-help-tip' ).tipTip( {
					attribute: 'data-tip',
					fadeIn: 50,
					fadeOut: 50,
					delay: 200,
					keepAlive: true,
				} )
				.css( 'cursor', 'help' );

			// re-initialize datepicker
			$( '.wcpdf-data-fields .date-picker-field, .date-picker' ).datepicker( {
				dateFormat: 'yy-mm-dd',
				numberOfMonths: 1,
				showButtonPanel: true,
			} );
		} else {
			$form.find( '.read-only' ).show();
			$form.find( '.editable' ).hide();
			$form.find( '.editable-notes' ).hide();
			$form.find( ':input' ).attr( 'disabled', true );
			$form.closest( '.wcpdf-data-fields' ).find( '.wpo-wcpdf-document-buttons' ).hide();
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

	function updatePreviewNumber( $table ) {
		let prefix   = $table.find( 'input[name$="_number_prefix"]' ).val();
		let suffix   = $table.find( 'input[name$="_number_suffix"]' ).val();
		let padding  = $table.find( 'input[name$="_number_padding"]' ).val();
		let plain    = $table.find( 'input[name$="_number_plain"]' ).val();
		let document = $table.data( 'document' );
		let orderId  = $table.data( 'order_id' );

		$.ajax( {
			url:    wpo_wcpdf_ajax.ajaxurl,
			method: 'POST',
			data: {
				action:   'wpo_wcpdf_preview_formatted_number',
				security: wpo_wcpdf_ajax.nonce,
				prefix:   prefix,
				suffix:   suffix,
				padding:  padding,
				plain:    plain,
				document: document,
				order_id: orderId,
			},
			success: function( response ) {
				if ( response.success && response.data.formatted ) {
					let $preview = $table.find( '.formatted-number' );
					let current  = $preview.data( 'current' );
					let updated  = response.data.formatted;

					$preview.val( updated );

					if ( current !== updated ) {
						$preview.addClass( 'changed' );
					} else {
						$preview.removeClass( 'changed' );
					}
				}
			},
			error: function( xhr, status, error ) {
				console.error( 'AJAX error:', status, error );
				$table.find( '.formatted-number' ).value( wpo_wcpdf_ajax.error_loading_number_preview );
			}
		} );
	}

	let previewTimer;
	$( document ).on( 'input', '.wcpdf-data-fields input', function () {
		const $table = $( this ).closest( '.wcpdf-data-fields' );

		clearTimeout( previewTimer );
		previewTimer = setTimeout( () => {
			updatePreviewNumber( $table );
		}, 300 );
	} );

	// Edi identifiers
	const root = '#wpo_ips-edi-box .edi-customer-identifiers';

	$( document.body ).on( 'click', root + ' td.collapse > a', function( e ) {
		e.preventDefault();
		let $this  = $( this );
		let $tbody = $this.closest( 'table' ).find( 'tbody' );

		if ( $tbody.is( ':visible' ) ) {
			$tbody.slideUp( 'fast' );
			$this.text( wpo_wcpdf_ajax.edi_metabox.show );
		} else {
			$tbody.slideDown( 'fast' );
			$this.text( wpo_wcpdf_ajax.edi_metabox.hide );
		}
	} );

	// Peppol identifiers
	const peppolRoot = `${root}.peppol`;

	// Edit
	$( document.body ).on( 'click', peppolRoot + ' thead .editable a', function ( e ) {
		e.preventDefault();
		$( this ).closest( 'table' ).addClass( 'is-editing' );
	} );

	// Cancel
	$( document.body ).on( 'click', peppolRoot + ' tfoot .button.cancel', function ( e ) {
		e.preventDefault();
		$( this ).closest( 'table' ).removeClass( 'is-editing' );
	} );

	// Save (AJAX)
	$( document.body ).on( 'click', peppolRoot + ' tfoot .button-primary', function ( e ) {
		e.preventDefault();

		const $btn    = $( this );
		const orderId = $btn.data( 'order_id' );
		const $table  = $btn.closest( 'table' );
		const $box    = $table.closest( peppolRoot );

		const pairs   = $box.find( 'tbody input[type="text"]' ).serializeArray();
		const values  = {};

		$.each( pairs, function ( _, p ) { values[p.name] = p.value; } );

		const data = {
			action:   'wpo_ips_edi_save_order_customer_peppol_identifiers',
			security: wpo_wcpdf_ajax.nonce,
			order_id: orderId,
			values:   values
		};

		$btn.prop( 'disabled', true );

		$.post( wpo_wcpdf_ajax.ajaxurl, data )
			.done( function ( response ) {
				$box.find( 'tbody tr' ).each( function () {
					const row = $( this );
					const val = row.find( 'td.edit input[type="text"]' ).val();
					row.find( 'td.display' ).text( val || '—' );
				} );
				
				$table.removeClass( 'is-editing' );

				const msg = ( response && response.data && response.data.message ) || wpo_wcpdf_ajax.saved;
				if ( window.wp && wp.a11y && wp.a11y.speak ) {
					wp.a11y.speak( msg );
				} else {
					alert( msg );
				}
			} )
			.fail( function ( jqXHR ) {
				const msg = ( jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) || wpo_wcpdf_ajax.fail;
				alert( msg );
			} )
			.always( function () {
				$btn.prop( 'disabled', false );
			} );
	} );

} );
