jQuery( function ( $ ) {

	// Handle changes to tax settings dropdowns
	$( `select[name^="wpo_ips_edi_tax_settings"][name$="[scheme]"],
		select[name^="wpo_ips_edi_tax_settings"][name$="[category]"],
		select[name^="wpo_ips_edi_tax_settings"][name$="[reason]"]`
	).on( 'change', function () {
		let currentValue = $( this ).data( 'current' );
		let newValue     = $( this ).find( 'option:selected' ).val();
		let $current     = $( this ).closest( 'td, th, div' ).find( '.current' );
		let newHtml      = `${wpo_ips_edi.new}: <code>${newValue}</code> <strong>(${wpo_ips_edi.unsaved})</strong>`;
		let oldHtml      = `${wpo_ips_edi.code}: <code>${currentValue}</code>`;

		// Only update the '.current' element if the value has changed
		if ( newValue !== currentValue ) {
			$current.html( newHtml );
		} else {
			$current.html( oldHtml );
		}

		// Toggle visibility based on whether the value is empty
		if ( newValue === '' || newValue === null ) {
			$current.addClass( 'hidden' );
		} else {
			$current.removeClass( 'hidden' );
		}

		// Display the remark if available
		if ( $( this ).attr( 'name' ).endsWith( '[reason]' ) ) {
			let remark = wpo_ips_edi.remarks[ 'reason' ][ newValue ];

			if ( remark ) {
				$( this ).closest( 'tr' ).find( '.remark' ).html( remark );
			} else {
				$( this ).closest( 'tr' ).find( '.remark' ).html( '' );
			}
		}
	} );

	// Handles switching between tax class tables when clicking the toggle links
	const $group  = $( '.edi-tax-class-group' );
	const $links  = $group.find( '.doc-output-toggle' );
	const $tables = $( '.edi-tax-class-table' );

	function showTable( slug ) {
		if ( ! slug ) {
			return;
		}

		// toggle tables
		$tables.hide().filter( `[data-tax-class="${slug}"]` ).show();

		// toggle active state on links
		$links.removeClass( 'active' ).attr( 'aria-pressed', 'false' )
			  .filter( `[data-tax-class="${slug}"]` )
			  .addClass( 'active' ).attr( 'aria-pressed', 'true' );
	}

	// Click handler
	$group.on( 'click', '.doc-output-toggle', function ( e ) {
		e.preventDefault();
		showTable( $( this ).data( 'tax-class' ) );
	} );

	// Initialize
	const initial = $links.filter( '.active' ).data( 'tax-class' ) ||
		$links.first().data( 'tax-class' ) ||
		$tables.first().data( 'tax-class' );

	showTable( initial );

	// Handle the save taxes
	$( 'button.button-edi-save-taxes' ).on( 'click', function ( e ) {
		e.preventDefault();

		const $button = $( this );
		const nonce   = $button.data( 'nonce' );
		const action  = $button.data( 'action' );
		const $form   = $button.closest( 'form#wpo-wcpdf-settings' );
		const data    = $form.serialize();
		const payload = data + '&action=' + encodeURIComponent( action ) + '&nonce=' + encodeURIComponent( nonce );
		const $notice = $( '#edi-tax-save-notice' );
		
		// block ui
		$form.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		$.post( wpo_ips_edi.ajaxurl, payload, function ( response ) {
			const message   = response.data || 'Unknown response.';
			let noticeClass = 'notice';

			if ( response.success ) {
				noticeClass += ' notice-success';
			} else {
				noticeClass += ' notice-error';
			}

			$notice
				.removeClass()
				.addClass( noticeClass )
				.html( `<p><strong>${message}</strong></p>` )
				.slideDown();

			setTimeout( function () {
				$notice.slideUp();
			}, 8000 );

			// Reload the tax table
			if ( response.success ) {
				reloadTaxTable();
			}
			
			// Unblock UI
			$form.unblock();
		} );
	} );

	function reloadTaxTable() {
		const selectedClass =
			$( '.edi-tax-class-group .doc-output-toggle.active' ).data( 'tax-class' ) ||
			$( '.edi-tax-class-group .doc-output-toggle' ).first().data( 'tax-class' ) ||
			$( '.edi-tax-class-table:visible' ).data( 'tax-class' ) ||
			$( '.edi-tax-class-table' ).first().data( 'tax-class' );

		$.get( wpo_ips_edi.ajaxurl, {
			action:    'wpo_ips_edi_reload_tax_table',
			nonce:     wpo_ips_edi.nonce,
			tax_class: selectedClass
		}, function ( html ) {
			const $container = $( `.edi-tax-class-table[data-tax-class="${selectedClass}"]` );
			$container.html( html );
		} );
	}

	// Shared function to load customer order identifiers
	function loadCustomerOrderIdentifiers() {
		const $input  = $( '#edi-customer-order-id' );
		const orderId = $input.val();
		const $table  = $input.closest( 'table' );
		const $tbody  = $table.find( 'tbody' );

		if ( ! orderId ) {
			$tbody.empty().append(
				`<tr><td colspan="2">${wpo_ips_edi.enter_order_id}</td></tr>`
			);
			return false;
		}

		// Check if orderId is a valid number
		const trimmedOrderId = $.trim( orderId );
		if ( isNaN( trimmedOrderId ) || trimmedOrderId === '' ) {
			$tbody.empty().append(
				`<tr><td colspan="2">${wpo_ips_edi.valid_number}</td></tr>`
			);
			return false;
		}

		$tbody.empty().append(
			`<tr><td colspan="2">${wpo_ips_edi.loading}</td></tr>`
		);

		$.get( wpo_ips_edi.ajaxurl, {
			action:   'wpo_ips_edi_load_customer_order_identifiers',
			nonce:    wpo_ips_edi.nonce,
			order_id: orderId
		}, function( response ) {
			$tbody.empty();
			if (
				response.success &&
				response.data &&
				response.data.data &&
				Object.keys( response.data.data ).length > 0
			) {
				const data = response.data.data;
				$.each( data, function( key, identifier ) {
					let label = identifier.label || key;
					let value = identifier.value;
					let color = '';
					let note  = '';

					if ( typeof value === 'undefined' || value === null || value === '' ) {
						color = identifier.required ? '#d63638' : '#996800';
						value = `<span style="color:${color};">${identifier.required ? wpo_ips_edi.missing : wpo_ips_edi.optional}</span>`;
					}

					if ( key === 'vat_number' && identifier.value && ! wpo_ips_edi_has_country_prefix( identifier.value ) ) {
						note = `<br><small style="color:#996800;">${wpo_ips_edi.vat_warning}</small>`;
					}

					$tbody.append(`
						<tr>
							<td>${label}</td>
							<td>${value}${note}</td>
						</tr>
					`);
				} );
			} else {
				const message = response.data || wpo_ips_edi.no_identifiers_found;
				$tbody.append(
					`<tr><td colspan="2">${message}</td></tr>`
				);
			}
		} );

		return false;
	}

	// Keydown event handler
	$( '#edi-customer-order-id' ).on( 'keydown', function ( e ) {
		if ( e.key === 'Enter' || e.keyCode === 13 ) {
			e.preventDefault();
			loadCustomerOrderIdentifiers();
		}
	} );

	// Button click event handler (replace 'your-button-selector' with your actual button selector)
	$( '#edi-customer-order-id-search-button' ).on( 'click', function( e ) {
		e.preventDefault();
		loadCustomerOrderIdentifiers();
	} );

	function wpo_ips_edi_has_country_prefix( vat ) {
		return /^[A-Z]{2}/.test( vat );
	}

} );
