jQuery( function( $ ) {
	
	$( '.wpo_wcpdf_debug_tools_form a.submit' ).on( 'click', function( e ) {
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
				},
				error ( xhr, error, status ) {
					//console.log( error, status );
				}
			} );
		}
		
		$form.closest( '.tool' ).unblock();
	} );
	
	function process_form_response( tool, response, $form ) {
		let $notice = $form.find( '.notice' );
		$notice.hide();
		$notice.removeClass( 'notice-error' );
		$notice.removeClass( 'notice-success' );
		
		switch ( tool ) {
			case 'export-settings':
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
			case 'import-settings':
			case 'reset-settings':
				if ( response.success && response.data.message ) {
					$notice.addClass( 'notice-success' );
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
	
} );