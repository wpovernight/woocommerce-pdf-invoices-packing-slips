jQuery( function ( $ ) {
	
	$( 'select[name^="wpo_ips_edi_tax_settings"][name$="[scheme]"],   \
		select[name^="wpo_ips_edi_tax_settings"][name$="[category]"], \
		select[name^="wpo_ips_edi_tax_settings"][name$="[reason]"]'
	).on( 'change', function () {
		let currentValue = $( this ).data( 'current' );
		let newValue     = $( this ).find( 'option:selected' ).val();
		let $current     = $( this ).closest( 'td, th' ).find( '.current' );
		let newHtml      = `${wpo_wcpdf_edi.new}: <code>${newValue}</code> <strong>(${wpo_wcpdf_edi.unsaved})</strong>`;
		let oldHtml      = `${wpo_wcpdf_edi.code}: <code>${currentValue}</code>`;

		// Only update the '.current' element if the value has changed
		if ( newValue !== currentValue ) {
			$current.html( newHtml );
		} else {
			$current.html( oldHtml );
		}
		
		// Display the remark if available
		if ( $( this ).attr( 'name' ).endsWith( '[reason]' ) ) {
			let remark = wpo_wcpdf_edi.remarks[ 'reason' ][ newValue ];
			
			if ( remark ) {
				$( this ).closest( 'tr' ).find( '.remark' ).html( remark );
			} else {
				$( this ).closest( 'tr' ).find( '.remark' ).html( '' );
			}
		}
	} );
	
	const $tables = $( '.edi-tax-class-table' );
	const $select = $( '.edi-tax-class-select' );

	function updateTableView() {
		const selected = $select.val();
		$tables.hide();
		$tables.filter( `[data-tax-class=\'${selected}\']` ).show();
	}

	$select.on( 'change', updateTableView );
	updateTableView(); // Initialize on page load
	
	$( '#ubl-show-changelog' ).on( 'click', function( e ) {
		e.preventDefault();
		$( '#ubl-standard-changelog' ).slideToggle();
	} );
	
} );
