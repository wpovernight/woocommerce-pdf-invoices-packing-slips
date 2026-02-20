( function () {

	window.WPO_IPS_PeppolEndpointDerivation = window.WPO_IPS_PeppolEndpointDerivation || {};

	window.WPO_IPS_PeppolEndpointDerivation.init = function init( CONFIG, adapter ) {

		const LOCK_COUNTRIES = Array.isArray( CONFIG.countries )
			? CONFIG.countries.map( ( c ) => String( c ).toUpperCase() )
			: [];

		const DEBUG                   = !!CONFIG.debug;
		const VAT_SELECTOR             = CONFIG.vat_field_selector;
		const AUTOFILL_ENDPOINT_ROUTE  = CONFIG.peppol_autofill_endpoint_route;

		function log( ...args ) {
			if ( DEBUG ) console.log( '[WPO IPS Peppol]', ...args );
		}

		function getValue( selector ) {
			if ( ! selector ) return '';
			const el = document.querySelector( selector );
			return el ? String( el.value || '' ).trim() : '';
		}

		function isLockCountry( country ) {
			const c = String( country || '' ).toUpperCase();
			return LOCK_COUNTRIES.includes( c );
		}

		function normalizeVat( vat ) {
			return String( vat || '' )
				.replace( /\s+/g, '' )
				.toUpperCase()
				.trim();
		}

		function setFieldValue( el, value ) {
			if ( ! el ) return false;

			const newVal = String( value ?? '' );
			if ( String( el.value ?? '' ) === newVal ) return true;

			el.value = newVal;

			el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
			el.dispatchEvent( new Event( 'change', { bubbles: true } ) );

			if ( typeof adapter.onSetFieldValue === 'function' ) {
				adapter.onSetFieldValue( el, newVal );
			}

			return true;
		}

		function reapplyValueAfterUnlock( input, value ) {
			if ( ! input ) return;

			requestAnimationFrame( () => {
				setFieldValue( input, value );
				requestAnimationFrame( () => setFieldValue( input, value ) );
			} );
		}

		function fetchPeppolEndpointValue( billingCountry, vatValue ) {
			if ( typeof adapter.fetchEndpoint === 'function' ) {
				return adapter.fetchEndpoint( billingCountry, vatValue );
			}

			const apiFetch = window.wp?.apiFetch;
			if ( ! apiFetch ) return Promise.resolve( null );

			return apiFetch( {
				path: AUTOFILL_ENDPOINT_ROUTE,
				method: 'POST',
				data: {
					billing_country: String( billingCountry || '' ).toUpperCase(),
					vat: String( vatValue || '' ),
				},
			} );
		}

		// Debounce + cache.
		let endpointLookupTimer = null;
		let endpointLookupLast  = null;

		// Manual override flag (session only).
		let manualOverride      = false;
		let manualOverrideValue = '';

		// Reset override when VAT/country changes.
		let lastVat     = null;
		let lastCountry = null;

		let lastLocked     = null;
		let lastEndpointEl = null;
		let scheduled      = false;

		function ensureOverrideLink( wrapper, endpointInput, locked ) {
			if ( ! wrapper ) return;

			let link = wrapper.querySelector( '.wpo-ips-override' );

			if ( ! locked ) {
				if ( link ) link.remove();
				return;
			}

			if ( link ) return link;

			link             = document.createElement( 'a' );
			link.href        = '#';
			link.className   = 'wpo-ips-override';
			link.textContent = CONFIG.override_link_text || 'Override (edit manually)';

			link.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				const preservedValue = endpointInput ? String( endpointInput.value || '' ) : '';

				manualOverride      = true;
				manualOverrideValue = preservedValue;

				applyLockState( 'override-click' );

				if ( endpointInput ) {
					if ( preservedValue ) {
						reapplyValueAfterUnlock( endpointInput, preservedValue );
					}

					endpointInput.focus();
				}
			} );

			// Let adapter decide where to place link, default to append.
			if ( typeof adapter.appendOverrideLink === 'function' ) {
				adapter.appendOverrideLink( wrapper, endpointInput, link );
			} else {
				wrapper.appendChild( link );
			}

			return link;
		}

		function setLocked( endpoint, wrapper, locked ) {
			endpoint.readOnly = locked;
			endpoint.style.pointerEvents = locked ? 'none' : '';
			endpoint.setAttribute( 'aria-disabled', locked ? 'true' : 'false' );
			endpoint.classList.toggle( 'wpo-is-locked', locked );

			if ( wrapper ) {
				wrapper.classList.toggle( 'wpo-ips-locked-wrap', locked );
			}
		}

		function bindManualOverridePersistence( endpoint ) {
			if ( ! endpoint || endpoint.__wpoPeppolManualBound ) return;

			endpoint.__wpoPeppolManualBound = true;

			function captureCurrentValue() {
				const current = String( endpoint.value || '' );
				if ( current ) {
					manualOverrideValue = current;
				}
			}

			endpoint.addEventListener( 'input', () => {
				if ( manualOverride ) {
					manualOverrideValue = String( endpoint.value || '' );
				}
			} );

			endpoint.addEventListener( 'pointerdown', () => {
				if ( manualOverride ) {
					captureCurrentValue();
				}
			} );

			endpoint.addEventListener( 'mousedown', () => {
				if ( manualOverride ) {
					captureCurrentValue();
				}
			} );

			endpoint.addEventListener( 'focus', () => {
				if ( manualOverride && manualOverrideValue ) {
					reapplyValueAfterUnlock( endpoint, manualOverrideValue );
				}
			} );

			endpoint.addEventListener( 'blur', () => {
				if ( manualOverride ) {
					captureCurrentValue();

					if ( manualOverrideValue ) {
						reapplyValueAfterUnlock( endpoint, manualOverrideValue );
					}
				}
			} );
		}

		function applyLockState( source = 'unknown' ) {
			scheduled = false;

			const nodes = adapter.getEndpointNodes && adapter.getEndpointNodes();
			const wrapper  = nodes ? nodes.wrapper : null;
			const endpoint = nodes ? nodes.endpoint : null;

			if ( ! endpoint ) return;

			bindManualOverridePersistence( endpoint );

			const country  = String( adapter.getBillingCountry ? adapter.getBillingCountry() : '' ).toUpperCase();
			const vatValue = getValue( VAT_SELECTOR );

			if ( country !== lastCountry || vatValue !== lastVat ) {
				manualOverride      = false;
				manualOverrideValue = '';
				lastCountry         = country;
				lastVat             = vatValue;
				endpointLookupLast  = null;
			}

			const shouldLockByRule = isLockCountry( country ) && vatValue !== '';
			const locked           = shouldLockByRule && ! manualOverride;

			if ( shouldLockByRule && ! manualOverride && vatValue !== '' ) {
				const vat = normalizeVat( vatValue );
				const key = country + '|' + vat;

				if ( endpointLookupLast && endpointLookupLast.key === key ) {
					if ( endpointLookupLast.value ) {
						setFieldValue( endpoint, endpointLookupLast.value );
					}
				} else {
					if ( endpointLookupTimer ) clearTimeout( endpointLookupTimer );

					endpointLookupTimer = setTimeout( () => {
						fetchPeppolEndpointValue( country, vat )
							.then( ( res ) => {
								const id = String( res?.id || '' ).trim();

								endpointLookupLast = {
									key,
									value: id,
								};

								if ( id ) {
									setFieldValue( endpoint, id );
								}
							} )
							.catch( () => {} );
					}, 250 );
				}
			}

			if ( locked === lastLocked && endpoint === lastEndpointEl ) return;

			ensureOverrideLink( wrapper, endpoint, locked );
			setLocked( endpoint, wrapper, locked );

			lastLocked     = locked;
			lastEndpointEl = endpoint;

			log( 'lock state applied', {
				source,
				country,
				vatValue,
				locked,
				manualOverride,
				lastValue: endpointLookupLast ? endpointLookupLast.value : '',
				manualOverrideValue,
			} );
		}

		function scheduleApply( source ) {
			if ( scheduled ) return;

			scheduled = true;
			requestAnimationFrame( () => applyLockState( source ) );
		}

		// Expose minimal API for wrapper files.
		return {
			apply: applyLockState,
			schedule: scheduleApply,
			log,
		};
	};

} )();
