jQuery( function ( $ ) {

	const CONFIG = window.wpoIpsPeppol || {};

	// Visibility toggles
	function togglePeppolFields() {
		const mode = ( CONFIG && CONFIG.visibilityMode ) ? CONFIG.visibilityMode : 'always';

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


	// Endpoint derivation
	if ( ! CONFIG.endpoint_derivation ) {
		return;
	}

	const COUNTRY_SELECTOR          = CONFIG.billing_country_selector || '#billing_country';
	const VAT_SELECTOR              = CONFIG.vat_field_selector || '';
	const ENDPOINT_WRAPPER_SELECTOR = CONFIG.peppol_input_wrapper_selector || '';
	const ENDPOINT_SELECTOR         = ENDPOINT_WRAPPER_SELECTOR ? ( ENDPOINT_WRAPPER_SELECTOR + ' input' ) : '';

	function getValue( selector ) {
		const el = document.querySelector( selector );
		return el ? String( el.value || '' ).trim() : '';
	}

	const engine = window.WPO_IPS_PeppolEndpointDerivation.init( CONFIG, {
		getBillingCountry() {
			return getValue( COUNTRY_SELECTOR );
		},

		getEndpointNodes() {
			const nodes = {
				wrapper: ENDPOINT_WRAPPER_SELECTOR ? document.querySelector( ENDPOINT_WRAPPER_SELECTOR ) : null,
				endpoint: ENDPOINT_SELECTOR ? document.querySelector( ENDPOINT_SELECTOR ) : null,
			};

			engine.log( 'getEndpointNodes', {
				wrapperSelector: ENDPOINT_WRAPPER_SELECTOR,
				endpointSelector: ENDPOINT_SELECTOR,
				wrapperFound: !! nodes.wrapper,
				endpointFound: !! nodes.endpoint,
				currentValue: nodes.endpoint ? String( nodes.endpoint.value || '' ) : '',
			} );

			return nodes;
		},

		appendOverrideLink( wrapper, endpoint, link ) {
			if ( endpoint && endpoint.parentNode ) {
				endpoint.insertAdjacentElement( 'afterend', link );
			} else {
				wrapper.appendChild( link );
			}
		},

		// Fetch fallback if wp.apiFetch not present.
		fetchEndpoint( billingCountry, vatValue ) {
			engine.log( 'fetchEndpoint', {
				billingCountry: billingCountry,
				vatValue: vatValue,
				hasApiFetch: !! window.wp?.apiFetch,
				hasWpApiSettingsRoot: !! window.wpApiSettings?.root,
				route: CONFIG.peppol_autofill_endpoint_route,
			} );

			const apiFetch = window.wp?.apiFetch;
			if ( apiFetch ) {
				return apiFetch( {
					path: CONFIG.peppol_autofill_endpoint_route,
					method: 'POST',
					data: {
						billing_country: String( billingCountry || '' ).toUpperCase(),
						vat: String( vatValue || '' ),
					},
				} );
			}

			const root  = window.wpApiSettings?.root ? String( window.wpApiSettings.root ) : '';
			const nonce = window.wpApiSettings?.nonce ? String( window.wpApiSettings.nonce ) : '';

			if ( root ) {
				const path = String( CONFIG.peppol_autofill_endpoint_route || '' ).replace( /^\//, '' );
				const url  = root.replace( /\/$/, '' ) + '/' + path;

				return $.ajax( {
					url,
					method: 'POST',
					dataType: 'json',
					data: {
						billing_country: String( billingCountry || '' ).toUpperCase(),
						vat: String( vatValue || '' ),
					},
					beforeSend: function ( xhr ) {
						if ( nonce ) xhr.setRequestHeader( 'X-WP-Nonce', nonce );
					},
				} );
			}

			return $.Deferred().resolve( null ).promise();
		},
	} );

	$( document ).on( 'input change', COUNTRY_SELECTOR + ', ' + VAT_SELECTOR, function () {
		engine.log( 'input/change event', {
			target: this && this.name ? this.name : ( this && this.id ? this.id : '' ),
			country: getValue( COUNTRY_SELECTOR ),
			vat: VAT_SELECTOR ? getValue( VAT_SELECTOR ) : '',
		} );

		engine.schedule( 'input' );
	} );

	$( document.body ).on( 'updated_checkout updated_shipping_method', function ( event ) {
		engine.log( 'wc event', {
			type: event && event.type ? event.type : '',
		} );

		engine.schedule( 'wc-updated' );
	} );

	engine.log( 'init schedule' );

	engine.schedule( 'init' );

} );
