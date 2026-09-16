jQuery( function( $ ) {

	const config = window.wpoIpsCheckoutFieldLabel;
	if ( ! config ) {
		return;
	}

	const fieldSelector = '#wpo_ips_checkout_field, .wc-block-components-address-form__wpo-ips-checkout-field input';

	function getBillingCountry() {
		const $country = $( '#billing_country' );
		if ( $country.length ) {
			return $country.val();
		}

		// The store also handles billing matching shipping, when no billing country input is shown.
		const cart     = window.wp?.data?.select( 'wc/store/cart' );
		const customer = cart?.getCustomerData?.();
		return customer?.billingAddress?.country || '';
	}

	function updateLabel() {
		const country = String( getBillingCountry() || '' ).toUpperCase();
		const text    = config.labels[ country ] || config.labels[ '' ];

		$( fieldSelector ).each( function() {
			$( this.labels ).each( function() {
				const $label   = $( this );
				const previous = $label.data( 'wpo-ips-label' ) || config.initialLabel;

				// Replace only the label text, preserving optional/required markers and their markup.
				$label.contents().each( function() {
					if ( this.nodeType !== Node.TEXT_NODE ) {
						return;
					}

					const oldText = this.textContent.includes( previous ) ? previous : config.initialLabel;
					if ( ! oldText || ! this.textContent.includes( oldText ) ) {
						return;
					}

					const newText = this.textContent.replace( oldText, text );
					if ( this.textContent !== newText ) {
						this.textContent = newText;
					}
				} );

				$label.data( 'wpo-ips-label', text );
			} );
		} );
	}

	$( document ).on( 'change', '#billing_country', updateLabel );
	$( document.body ).on( 'updated_checkout', updateLabel );

	const $blockCheckout = $( '.wc-block-checkout' );
	if ( $blockCheckout.length ) {
		window.wp?.data?.subscribe( updateLabel, 'wc/store/cart' );
		// Blocks may mount or render the field again after the country changes.
		new MutationObserver( updateLabel ).observe( $blockCheckout[0], {
			childList: true,
			characterData: true,
			subtree: true,
		} );
	}

	updateLabel();

} );
