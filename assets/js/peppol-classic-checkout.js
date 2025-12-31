jQuery( function ( $ ) {
	
	function togglePeppolFields() {
		const checkbox = $( '#peppol_invoice' );
		const rows     = $( '.wpo-ips-peppol-conditional' ).closest( '.form-row' );

		if ( ! checkbox.length ) {
			return;
		}

		if ( checkbox.is( ':checked' ) ) {
			rows.show();
		} else {
			rows.hide();
		}
	}

	togglePeppolFields();
	$( document ).on( 'change', '#peppol_invoice', togglePeppolFields );
	
} );
