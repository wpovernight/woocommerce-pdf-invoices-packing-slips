jQuery( function ( $ ) {
	
	$( 'select[name^="wpo_wcpdf_settings_ubl_taxes"][name$="[scheme]"],   \
		select[name^="wpo_wcpdf_settings_ubl_taxes"][name$="[category]"], \
		select[name^="wpo_wcpdf_settings_ubl_taxes"][name$="[reason]"]'
	).on( 'change', function () {
		let currentValue = $( this ).data( 'current' );
		let newValue     = $( this ).find( 'option:selected' ).val();
		let $current     = $( this ).closest( 'td, th' ).find( '.current' );
		let newHtml      = `${wpo_wcpdf_ubl.new}: <code>${newValue}</code> <strong>(${wpo_wcpdf_ubl.unsaved})</strong>`;
		let oldHtml      = `${wpo_wcpdf_ubl.code}: <code>${currentValue}</code>`;

		// Only update the '.current' element if the value has changed
		if ( newValue !== currentValue ) {
			$current.html( newHtml );
		} else {
			$current.html( oldHtml );
		}
		
		// Display the remark if available
		if ( $( this ).attr( 'name' ).endsWith( '[reason]' ) ) {
			let remark = wpo_wcpdf_ubl.remarks[ 'reason' ][ newValue ];
			
			if ( remark ) {
				$( this ).closest( 'tr' ).find( '.remark' ).html( remark );
			} else {
				$( this ).closest( 'tr' ).find( '.remark' ).html( '' );
			}
		}
	} );
	
	const $tables = $( '.ubl-tax-class-table' );
	const $select = $( '#ubl-tax-class-select' );

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
