jQuery( function( $ ) {
	
	$( document.body ).on( 'click', '.wpo_wcpdf_debug_tools_form a.submit', function( e ) {
		e.preventDefault();
		let $form    = $( this ).closest( 'form' );
		let tool     = $form.find( 'input[name="debug_tool"]' ).val();
		let formData = new FormData( $form[0] );
		formData.append( 'action', 'wpo_wcpdf_debug_tools' );
		formData.append( 'nonce', wpo_wcpdf_debug.nonce );
		
		// block ui
		$form.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
		
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
		
		$form.unblock();
	} );
	
	function process_form_response( tool, response, $form ) {
		let $notice = $form.find( '.notice' );
		$notice.hide();
		$notice.removeClass( 'notice-error' );
		$notice.removeClass( 'notice-success' );
		
		switch ( tool ) {
			case 'export-settings':
				if ( response.success && response.data.filename && response.data.settings ) {
					$form.find( 'a.export-settings-download-file' ).remove();
					let data = {
						'type':     $form.find( 'select[name="type"' ).val(),
						'settings': response.data.settings,
					}
					data = 'data:text/plain;charset=utf-8,' + encodeURIComponent( JSON.stringify( data ) );
					$form.append( $('<label>'+wpo_wcpdf_debug.download_label+':</label> <a href="data:' + data + '" download="'+response.data.filename+'" class="export-settings-download-file">'+response.data.filename+'</a>') );
				} else if ( ! response.success && response.data.message ) {
					$notice.addClass( 'notice-error' );
					$notice.find( 'p' ).text( response.data.message );
					$notice.show();
				}
				break;
			case 'import-settings':
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
	
} );