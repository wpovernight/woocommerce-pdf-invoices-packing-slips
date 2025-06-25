jQuery( function( $ ) {

	$( '.wc-enhanced-select' ).select2();
	
	$( '.tab' ).on( 'click', function() {
		$( this ).closest( '.extra-field' ).find( '.tab' ).removeClass( 'active' );
		$( this ).addClass( 'active' );
		let $language = $( this ).attr( 'id' );
		$( this ).siblings( '.extra-field-input' ).hide();
		$( '.' + $language ).show();
	} );

	// Show Preview of logo
	$( '#file-upload' ).on( 'change',  function( event ) {
		if ( event.target.files[0] ) {
			let tmp_path = URL.createObjectURL( event.target.files[0] );
			$( '#logo-preview' ).find( "img" ).attr( 'src', tmp_path );
		}
	} );
	
	// Handle shop address country change
	$( document ).on( 'change', 'body.wpo-wcpdf-setup select[name*="[shop_address_country]"]', function( event ) {
		if ( 'shop_address_country' === event.target.id || ! event.isTrigger ) { // exclude programmatic triggers that aren't actually changing anything
			shopCountryChanged( event );
		}
	} );
	
	function shopCountryChanged( event ) {
		const $country        = $( event.target );
		const selectedCountry = $country.val();
		const $form           = $country.closest( 'form' );

		// Find the matching state field
		const $state = $form.find( `select[name*="[shop_address_state]"]` );

		// Clear previous states
		$state.empty().prop( 'disabled', true );

		// Temporary loading option
		$state.append(
			$( '<option>', {
				value: '',
				text: wpo_wcpdf_setup.shop_country_changed_messages.loading
			} )
		);

		$.ajax( {
			url: wpo_wcpdf_setup.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wcpdf_get_country_states',
				country: selectedCountry
			},
			success: function( response ) {
				$state.empty();

				const states   = response.data?.states;
				const selected = response.data?.selected;

				if ( response.success && states && Object.keys( states ).length > 0 ) {
					$.each( states, function( code, name ) {
						$state.append(
							$( '<option>', {
								value: code,
								text: name,
								selected: code === selected
							} )
						);
					} );
					$state.prop( 'disabled', false );
				} else {
					$state.append(
						$( '<option>', {
							value: '',
							text: wpo_wcpdf_setup.shop_country_changed_messages.empty
						} )
					);
				}
			},
			error: function() {
				$state.empty().append(
					$( '<option>', {
						value: '',
						text: wpo_wcpdf_setup.shop_country_changed_messages.error
					} )
				);
			}
		} );
	}

} );
