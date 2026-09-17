jQuery( function( $ ) {

	function updateVisibility() {
		const visible = wpoIpsCheckoutField.countries.indexOf( $( '#billing_country' ).val() ) !== -1;
		const $field  = $( '#wpo_ips_checkout_field' );

		$field.closest( '.form-row' ).toggle( visible );
		$field.prop( 'disabled', ! visible );
	}

	$( document ).on( 'change', '#billing_country', updateVisibility );
	$( document.body ).on( 'updated_checkout', updateVisibility );
	updateVisibility();

} );
