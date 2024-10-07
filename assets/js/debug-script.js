jQuery( function( $ ) {

	$( '#debug-tools .tool' ).on( 'click', 'input[type="submit"]', function( e ) {
		e.preventDefault();
		let $form    = $( this ).closest( 'form' );
		let tool     = $form.find( 'input[name="debug_tool"]' ).val();
		let formData = new FormData( $form[0] );
		formData.append( 'action', 'wpo_wcpdf_debug_tools' );
		formData.append( 'nonce', wpo_wcpdf_debug.nonce );

		// block ui
		$form.closest( '.tool' ).block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		let reset = false;
		if ( 'reset-settings' === tool ) {
			reset = window.confirm( wpo_wcpdf_debug.confirm_reset );
		} else {
			reset = true;
		}

		if ( reset ) {
			$.ajax( {
				url:         wpo_wcpdf_debug.ajaxurl,
				data:        formData,
				type:        'POST',
				cache:       false,
				processData: false,
				contentType: false,
				success ( response ) {
					process_form_response( tool, response, $form );
					$form.closest( '.tool' ).unblock();
				},
				error ( xhr, error, status ) {
					//console.log( error, status );
					$form.closest( '.tool' ).unblock();
				}
			} );
		}
	} );

	function process_form_response( tool, response, $form ) {
		let $notice = $form.find( 'fieldset > .notice' );
		$notice.hide();
		$notice.removeClass( 'notice-error' );
		$notice.removeClass( 'notice-success' );

		switch ( tool ) {
			case 'export_settings':
				if ( response.success && response.data.filename && response.data.settings ) {
					$form.find( '.download_file' ).remove();
					let data = {
						'type':     $form.find( 'select[name="type"' ).val(),
						'settings': response.data.settings,
					}
					data = 'data:text/plain;charset=utf-8,' + encodeURIComponent( JSON.stringify( data ) );
					$form.append( $('<div class="download_file"><label>'+wpo_wcpdf_debug.download_label+':</label> <a href="data:' + data + '" download="'+response.data.filename+'">'+response.data.filename+'</a></div>') );
				} else if ( ! response.success && response.data.message ) {
					$notice.addClass( 'notice-error' );
					$notice.find( 'p' ).text( response.data.message );
					$notice.show();
				}
				break;
			default:
				if ( response.success && response.data.message ) {
					$notice.addClass( 'notice-success' );
					$form.find( '> .notice-warning' ).remove();
				} else if ( ! response.success && response.data.message ) {
					$notice.addClass( 'notice-error' );
				}
				$notice.find( 'p' ).text( response.data.message );
				$notice.show();
				break;
		}
	}

	// toggle custom redirect page
	$( "[name='wpo_wcpdf_settings_debug[document_access_denied_redirect_page]']" ).on( 'change', function( event ) {
		let $custom_page_field = $( this ).closest( 'table' ).find( '#document_custom_redirect_page' );
		let $field_description = $custom_page_field.closest( 'td' ).find( '.description' );

		if ( 'custom_page' === $( this ).val() ) {
			$custom_page_field.show();
			$field_description.show();
		} else {
			$custom_page_field.hide();
			$field_description.hide();
		}
	} ).trigger( 'change' );

	// danger zone enabled notice
	if ( true === wpo_wcpdf_debug.danger_zone['enabled'] ) {
		let notice = '<div class="notice notice-warning inline"><p>' + wpo_wcpdf_debug.danger_zone['message'] + '</p></div>';
		$( "input#enable_danger_zone_tools" ).closest( 'td' ).find( '.description' ).append( notice );
	}

	// number search
	$( document.body ).on( 'click', '#wpo-wcpdf-settings a.number-search-button', function( e ) {
		e.preventDefault();

		let search_val = $( this ).closest( 'div' ).find( ':input[name="number_search_input"]' ).val();
		window.location.href = window.location.href + '&s=' + search_val;
	} );

	// datepicker
	$( '#renumber-date-from, #renumber-date-to, #delete-date-from, #delete-date-to' ).datepicker( { dateFormat: 'yy-mm-dd' } );

	// danger zone tools
	$( document.body ).on( 'click', '#debug-tools .number-tools-btn', function( event ) {
		event.preventDefault();

		let documentType     = '';
		let dateFrom         = '';
		let dateTo           = '';
		let deleteOrRenumber = '';
		let pageCount        = 1;
		let documentCount    = 0;

		if ( 'renumber-documents-btn' === this.id ) {
			documentType     = $( '#renumber-document-type' ).val();
			dateType         = $( '#renumber-date-type' ).val();
			dateFrom         = $( '#renumber-date-from' ).val();
			dateTo           = $( '#renumber-date-to' ).val();
			deleteOrRenumber = 'renumber';

		} else if ( 'delete-documents-btn' === this.id ) {
			documentType     = $( '#delete-document-type' ).val();
			dateType         = $( '#delete-date-type' ).val();
			dateFrom         = $( '#delete-date-from' ).val();
			dateTo           = $( '#delete-date-to' ).val();
			deleteOrRenumber = 'delete';
		}

		if ( '' === documentType || 'undefined' === documentType ) {
			alert( wpo_wcpdf_debug.select_document_type );
			return;
		}

		if ( 'renumber' === deleteOrRenumber ) {
			$( '.renumber-spinner' ).css( 'visibility', 'visible' );
		} else if ( 'delete' === deleteOrRenumber ) {
			$( '.delete-spinner' ).css( 'visibility', 'visible' );
		}

		$( '#renumber-documents-btn, #delete-documents-btn' ).attr( 'disabled', true );
		$( '#renumber-document-type, #renumber-date-from, #renumber-date-to, #delete-document-type, #delete-date-from, #delete-date-to' ).prop( 'disabled', true );

		// first call
		renumberOrDeleteDocuments( documentType, dateType, dateFrom, dateTo, pageCount, documentCount, deleteOrRenumber );
	} );

	// disable `document_date` when selecting `all` documents
	$( '#debug-tools #delete-document-type' ).on( 'change', function( event ) {
		event.preventDefault();

		if ( 'all' === $( this ).val() ) {
			$( this ).closest( 'form' ).find( '#delete-date-type option[value="document_date"]' ).prop( 'disabled', true );
		} else {
			$( this ).closest( 'form' ).find( '#delete-date-type option[value="document_date"]' ).prop( 'disabled', false );
		}
	} ).trigger( 'change' );

	$( '#debug-tools #delete-date-type' ).on( 'change', function( event ) {
		event.preventDefault();

		let $document_type_selector = $( this ).closest( 'form' ).find( '#delete-document-type' );

		if ( '' === $document_type_selector.val() || 'all' === $document_type_selector.val() ) {
			$( this ).find( 'option[value="document_date"]' ).prop( 'disabled', true );
		} else {
			$( this ).find( 'option[value="document_date"]' ).prop( 'disabled', false );

		}
	} ).trigger( 'change' );

	function renumberOrDeleteDocuments( documentType, dateType, dateFrom, dateTo, pageCount, documentCount, deleteOrRenumber ) {
		let data = {
			'action':             'wpo_wcpdf_danger_zone_tools',
			'delete_or_renumber': deleteOrRenumber,
			'document_type':      documentType,
			'date_type':          dateType,
			'date_from':          dateFrom,
			'date_to':            dateTo,
			'page_count':         pageCount,
			'document_count':     documentCount,
			'nonce':              wpo_wcpdf_debug.nonce,
		};

		$.ajax( {
			type:     'POST',
			url:      wpo_wcpdf_debug.ajaxurl,
			data:     data,
			dataType: 'json',
			success: function( response ) {
				if ( false === response.data.finished ) {
					// update page count and document count
					pageCount     = response.data.pageCount;
					documentCount = response.data.documentCount;

					// recall function
					renumberOrDeleteDocuments( documentType, dateType, dateFrom, dateTo, pageCount, documentCount, deleteOrRenumber );

				} else {
					$( '.renumber-spinner, .delete-spinner' ).css( 'visibility', 'hidden' );
					$( '#renumber-documents-btn, #delete-documents-btn' ).removeAttr( 'disabled' );
					$( '#renumber-document-type, #renumber-date-from, #renumber-date-to, #delete-document-type, #delete-date-from, #delete-date-to' ).prop( 'disabled', false );
					let message = response.data.message;
					alert( documentCount + message );
				}
			},
			error: function( xhr, ajaxOptions, thrownError ) {
				alert( xhr.status + ':'+ thrownError );
			}
		} );
	}

} );
