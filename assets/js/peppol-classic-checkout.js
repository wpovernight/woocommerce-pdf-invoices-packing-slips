jQuery( function ( $ ) {

	function togglePeppolFields() {
		const mode = ( window.wpoIpsPeppol && window.wpoIpsPeppol.visibilityMode ) ? window.wpoIpsPeppol.visibilityMode : 'always';

		let rows = $();
		if ( mode === 'toggle' ) {
			rows = $( '.wpo-ips-peppol-conditional' ).closest( '.form-row' );
			const checkbox = $( '#peppol_invoice' );

			if ( ! checkbox.length ) {
				return;
			}

			rows.toggle( checkbox.is( ':checked' ) );
			return;
		}

		if ( mode === 'company' ) {
			rows = $( '.wpo-ips-peppol-company-conditional' ).closest( '.form-row' );
			const company = $( '#billing_company' );

			if ( ! company.length ) {
				return;
			}

			rows.toggle( $.trim( company.val() ).length > 0 );
			return;
		}
	}

	togglePeppolFields();

	$( document ).on( 'change', '#peppol_invoice', togglePeppolFields );
	$( document ).on( 'input change', '#billing_company', togglePeppolFields );

} );
