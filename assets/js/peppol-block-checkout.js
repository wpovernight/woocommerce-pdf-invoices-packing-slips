( function () {

	const CONFIG = window.wpoIpsPeppol || {};

	if ( ! CONFIG.endpoint_derivation ) return;

	const COUNTRY_SELECTOR          = CONFIG.billing_country_selector;
	const VAT_SELECTOR              = CONFIG.vat_field_selector;
	const ENDPOINT_WRAPPER_SELECTOR = CONFIG.peppol_input_wrapper_selector;
	const ENDPOINT_SELECTOR         = ENDPOINT_WRAPPER_SELECTOR ? ( ENDPOINT_WRAPPER_SELECTOR + ' input' ) : '';

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

					engine.log(
						'billing country resolved from store',
						country
					);

					return String( country ).trim();
				}
			}

		} catch ( e ) {

			engine.log(
				'error reading billing country from store',
				e
			);
		}

		const fallback = getValue( COUNTRY_SELECTOR );

		engine.log(
			'billing country resolved from DOM',
			fallback
		);

		return fallback;
	}

	const engine = window.WPO_IPS_PeppolEndpointDerivation.init( CONFIG, {

		getBillingCountry,

		getEndpointNodes() {

			const wrapper  = ENDPOINT_WRAPPER_SELECTOR
				? document.querySelector( ENDPOINT_WRAPPER_SELECTOR )
				: null;

			const endpoint = ENDPOINT_SELECTOR
				? document.querySelector( ENDPOINT_SELECTOR )
				: null;

			engine.log(
				'getEndpointNodes',
				{
					wrapperSelector: ENDPOINT_WRAPPER_SELECTOR,
					endpointSelector: ENDPOINT_SELECTOR,
					wrapperFound: !! wrapper,
					endpointFound: !! endpoint,
					currentValue: endpoint ? endpoint.value : ''
				}
			);

			return {
				wrapper: wrapper,
				endpoint: endpoint,
			};
		},

		onSetFieldValue( el, newVal ) {

			engine.log(
				'setting endpoint value',
				newVal
			);

			// Blocks floating label fix.
			const wrapper = el.closest( '.wc-block-components-text-input' );

			if ( wrapper && newVal ) {
				wrapper.classList.add( 'is-active' );
			} else if ( wrapper && ! newVal ) {
				wrapper.classList.remove( 'is-active' );
			}
		},
	} );

	engine.log(
		'engine initialized',
		{
			countrySelector: COUNTRY_SELECTOR,
			vatSelector: VAT_SELECTOR,
			endpointWrapperSelector: ENDPOINT_WRAPPER_SELECTOR,
			endpointSelector: ENDPOINT_SELECTOR,
			debug: CONFIG.debug
		}
	);

	const root =
		document.querySelector( '#order-fields' )
		|| document.querySelector( '.wc-block-checkout' )
		|| document.documentElement;

	engine.log(
		'mutation observer attached',
		root
	);

	new MutationObserver( () => {

		engine.log(
			'mutation observed -> scheduling apply'
		);

		engine.schedule( 'mutation' );

	} ).observe( root, { childList: true, subtree: true } );

	document.addEventListener( 'input', ( e ) => {

		if ( COUNTRY_SELECTOR && e.target && e.target.matches( COUNTRY_SELECTOR ) ) {

			engine.log(
				'country input detected',
				e.target.value
			);

			engine.schedule( 'country-input' );
		}

		if ( VAT_SELECTOR && e.target && e.target.matches( VAT_SELECTOR ) ) {

			engine.log(
				'vat input detected',
				e.target.value
			);

			engine.schedule( 'vat-input' );
		}

	} );

	if ( window.wp?.data?.subscribe ) {

		engine.log(
			'subscribed to wp.data store'
		);

		window.wp.data.subscribe( () => {

			engine.log(
				'store change detected'
			);

			engine.schedule( 'store' );

		} );
	}

	engine.log(
		'initial schedule'
	);

	engine.schedule( 'init' );

} )();
