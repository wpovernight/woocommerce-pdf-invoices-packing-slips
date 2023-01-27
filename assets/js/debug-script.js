jQuery( function( $ ) {
	
	$( document.body ).on( 'click', '.wpo_wcpdf_debug_tools_form a.submit', function( e ) {
		e.preventDefault();
		let $form = $( this );
		
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
				//console.log( response );
			},
			error ( xhr, error, status ) {
				//console.log( error, status );
			}
		} );
		
		$form.unblock();
	} );
	
} );