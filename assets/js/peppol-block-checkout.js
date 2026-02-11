( function () {

	const CONFIG = window.wpoIpsPeppol || {};

	if ( ! CONFIG.endpoint_derivation ) return;

	const COUNTRY_SELECTOR          = CONFIG.billing_country_selector;
	const ENDPOINT_WRAPPER_SELECTOR = CONFIG.peppol_input_wrapper_selector;
	const ENDPOINT_SELECTOR         = ENDPOINT_WRAPPER_SELECTOR + ' input';

	function getValue( selector ) {
		const el = document.querySelector( selector );
		return el ? String( el.value || '' ).trim() : '';
	}

	function getBillingCountry() {
		try {
			const cart = window.wp?.data?.select?.( 'wc/store/cart' );
			if ( cart && typeof cart.getCustomerData === 'function' ) {
				const data = cart.getCustomerData() || {};

				const country =
					data.billingAddress?.country
					|| data.billing_address?.country
					|| data.customer?.billingAddress?.country
					|| data.customer?.billing_address?.country;

				if ( country ) {
					return String( country ).trim();
				}
			}
		} catch ( e ) {}

		return getValue( COUNTRY_SELECTOR );
	}

	const engine = window.WPO_IPS_PeppolEndpointDerivation.init( CONFIG, {
		getBillingCountry,

		getEndpointNodes() {
			return {
				wrapper: document.querySelector( ENDPOINT_WRAPPER_SELECTOR ),
				endpoint: document.querySelector( ENDPOINT_SELECTOR ),
			};
		},

		onSetFieldValue( el, newVal ) {
			// Blocks floating label fix.
			const wrapper = el.closest( '.wc-block-components-text-input' );
			if ( wrapper && newVal ) {
				wrapper.classList.add( 'is-active' );
			} else if ( wrapper && ! newVal ) {
				wrapper.classList.remove( 'is-active' );
			}
		},
	} );

	const root =
		document.querySelector( '#order-fields' )
		|| document.querySelector( '.wc-block-checkout' )
		|| document.documentElement;

	new MutationObserver( () => engine.schedule( 'mutation' ) )
		.observe( root, { childList: true, subtree: true } );

	document.addEventListener( 'input', ( e ) => {
		if ( e.target?.matches( COUNTRY_SELECTOR ) || e.target?.matches( CONFIG.vat_field_selector ) ) {
			engine.schedule( 'input' );
		}
	} );

	if ( window.wp?.data?.subscribe ) {
		window.wp.data.subscribe( () => engine.schedule( 'store' ) );
	}

	engine.schedule( 'init' );

} )();
