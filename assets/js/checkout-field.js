jQuery( function( $ ) {

	function updateVisibility() {
		const config  = wpoIpsCheckoutField;
		const country = $( '#billing_country' ).val() || '';
		const allowed = ! config.countries.length || config.countries.indexOf( country ) !== -1;
		const type    = config.alternativeType && country && country !== config.shopCountry
			? config.alternativeType
			: config.primaryType;

		$.each( config.fields, function( key, fieldType ) {
			const $field  = $( '#' + key );
			const visible = allowed && fieldType === type;

			$field.closest( '.form-row' ).toggle( visible );
			$field.prop( 'disabled', ! visible );
		} );
	}

	$( document ).on( 'change', '#billing_country', updateVisibility );
	$( document.body ).on( 'updated_checkout', updateVisibility );
	updateVisibility();

} );
