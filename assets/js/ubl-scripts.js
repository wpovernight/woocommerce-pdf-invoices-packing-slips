jQuery( function( $ ) {

	$( '#doaction, #doaction2' ).on( 'click', function( e ) {
		let actionselected = $( this ).attr( 'id' ).substr( 2 );
		let action         = $( 'select[name="'+actionselected+'"]' ).val();

		if ( action == 'ubl_invoice' ) {
			e.preventDefault();
			let checked = [];

			$( 'tbody th.check-column input[type="checkbox"]:checked' ).each( function() {
				checked.push( $( this ).val() );
			} );
			
			if ( ! checked.length ) {
				alert( wpo_wcpdf_ubl.noSelectedOrders );
				return;
			}
			
			$.each( checked, function( i, order_id ) {
				if ( wpo_wcpdf_ubl.adminUrl.indexOf( '?' ) != -1 ) {
					url = wpo_wcpdf_ubl.adminUrl+'&post='+order_id+'&action=edit&ubl=yes';
				} else {
					url = wpo_wcpdf_ubl.adminUrl+'?post='+order_id+'&action=edit&ubl=yes';
				}
	
				window.open( url, '_blank' );
			} );
		}
	} );

} );