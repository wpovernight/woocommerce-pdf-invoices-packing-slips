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
				if ( response.data ) {
					process_response_data( tool, response.data, $form );
				}
			},
			error ( xhr, error, status ) {
				//console.log( error, status );
			}
		} );
		
		$form.unblock();
	} );
	
	function process_response_data( tool, tool_data, $form ) {
		switch ( tool ) {
			case 'export-settings':
				$form.find( 'a.export-settings-download-file' ).remove();
				let data = {
					'type':     $form.find( 'select[name="type"' ).val(),
					'settings': tool_data.settings,
				}
				data = 'data:text/plain;charset=utf-8,' + encodeURIComponent( JSON.stringify( data ) );
				$form.append( $('<a href="data:' + data + '" download="'+tool_data.filename+'" class="export-settings-download-file">'+tool_data.filename+'</a>') );
				break;
		}
	}
	
} );