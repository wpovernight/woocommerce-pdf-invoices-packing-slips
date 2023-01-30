jQuery( function( $ ) {
	
	$( document.body ).on( 'click', '.wpo_wcpdf_debug_tools_form a.submit', function( e ) {
		e.preventDefault();
		let $form = $( this ).closest( 'form' );
		let tool  = $form.find( 'input[name="debug_tool"]' ).val();
		
		// block ui
		$form.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );
		
		$.ajax( {
			url:   wpo_wcpdf_debug.ajaxurl,
			data:  {
				'action': 'wpo_wcpdf_debug_tools',
				'nonce':  wpo_wcpdf_debug.nonce,
				'data':   $form.serialize(),
			},
			type:  'POST',
			cache: false,
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
				let type = $form.find( 'select[name="export_settings_type"' ).val();
				let data = {
					type:     type,
					settings: tool_data.settings
				}
				data = 'data:text/plain;charset=utf-8,' + encodeURIComponent( JSON.stringify( data ) );
				$form.append( $('<a href="data:' + data + '" download="'+tool_data.filename+'" class="export-settings-download-file">'+tool_data.filename+'</a>') );
				break;
		}
	}
	
} );