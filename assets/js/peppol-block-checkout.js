( function () {

	const CONFIG                    = window.wpoIpsPeppol || {};
	const LOCK_COUNTRIES            = Array.isArray( CONFIG.countries )
		? CONFIG.countries.map( ( c ) => String( c ).toUpperCase() )
		: [];

	const DEBUG                     = !!CONFIG.debug;

	const COUNTRY_SELECTOR          = CONFIG.billing_country_selector;
	const VAT_SELECTOR              = CONFIG.vat_field_selector;
	const ENDPOINT_WRAPPER_SELECTOR = CONFIG.peppol_input_wrapper_selector;
	const ENDPOINT_SELECTOR         = ENDPOINT_WRAPPER_SELECTOR + ' input';

	const AUTOFILL_ENDPOINT_ROUTE   = CONFIG.peppol_autofill_endpoint_route;

	function log( ...args ) {
		if ( DEBUG ) console.log( '[WPO IPS Peppol]', ...args );
	}

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
		} catch ( e ) {
			// ignore
		}

		// Fallback to DOM.
		return getValue( COUNTRY_SELECTOR );
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

		// Notify Blocks input wrapper.
		el.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		el.dispatchEvent( new Event( 'change', { bubbles: true } ) );

		// If Blocks didn't update floating label state, fix it.
		const wrapper = el.closest( '.wc-block-components-text-input' );
		if ( wrapper && newVal ) {
			wrapper.classList.add( 'is-active' );
		} else if ( wrapper && ! newVal ) {
			wrapper.classList.remove( 'is-active' );
		}

		return true;
	}

	function reapplyValueAfterUnlock( input, value ) {
		if ( ! input ) return;

		// React may wipe it on the same tick; reapply on next frames.
		requestAnimationFrame( () => {
			setFieldValue( input, value );
			requestAnimationFrame( () => setFieldValue( input, value ) );
		} );
	}

	function fetchPeppolEndpointValue( billingCountry, vatValue ) {
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

		// Only show link when locked.
		if ( ! locked ) {
			if ( link ) link.remove();
			return;
		}

		if ( link ) return link;

		link             = document.createElement( 'a' );
		link.href        = '#';
		link.className   = 'wpo-ips-override';
		link.textContent = CONFIG.override_link_text || 'Unlock';

		link.addEventListener( 'click', ( e ) => {
			e.preventDefault();

			// Preserve current value (the one we autofilled).
			const preservedValue = endpointInput ? String( endpointInput.value || '' ) : '';

			// One-way override: unlock until VAT/country changes again.
			manualOverride      = true;
			manualOverrideValue = preservedValue;

			applyLockState( 'override-click' );

			if ( endpointInput ) {
				// Ensure value doesn't disappear when Blocks re-renders.
				if ( preservedValue ) {
					reapplyValueAfterUnlock( endpointInput, preservedValue );
				}

				endpointInput.focus();
			}
		} );

		wrapper.appendChild( link );
		return link;
	}

	function setLocked( endpoint, wrapper, locked ) {
		// Prefer readOnly so value is still submitted.
		endpoint.readOnly = locked;

		// Hard block clicks when locked.
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
			// Keep the most recent user value, even if Blocks clears the input later.
			const current = String( endpoint.value || '' );
			if ( current ) {
				manualOverrideValue = current;
			}
		}

		// Keep our local value updated when user edits.
		endpoint.addEventListener( 'input', () => {
			if ( manualOverride ) {
				manualOverrideValue = String( endpoint.value || '' );
			}
		} );

		// Capture value BEFORE focus (Blocks/React may clear on focus).
		endpoint.addEventListener( 'pointerdown', () => {
			if ( manualOverride ) {
				captureCurrentValue();
			}
		} );

		// Fallback for browsers without pointer events.
		endpoint.addEventListener( 'mousedown', () => {
			if ( manualOverride ) {
				captureCurrentValue();
			}
		} );

		// React can wipe value on focus; restore on next frames.
		endpoint.addEventListener( 'focus', () => {
			if ( manualOverride && manualOverrideValue ) {
				reapplyValueAfterUnlock( endpoint, manualOverrideValue );
			}
		} );

		// React/store sync can wipe value after blur; restore on next frames.
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

		const wrapper  = document.querySelector( ENDPOINT_WRAPPER_SELECTOR );
		const endpoint = document.querySelector( ENDPOINT_SELECTOR );

		if ( ! endpoint ) return;

		// Make manual override value persist across React re-renders.
		bindManualOverridePersistence( endpoint );

		const country  = getBillingCountry().toUpperCase();
		const vatValue = getValue( VAT_SELECTOR );

		// Reset manual override when VAT/country changes.
		if ( country !== lastCountry || vatValue !== lastVat ) {
			manualOverride      = false;
			manualOverrideValue = '';
			lastCountry         = country;
			lastVat             = vatValue;

			// Reset lookup cache when inputs change.
			endpointLookupLast = null;
		}

		const shouldLockByRule = isLockCountry( country ) && vatValue !== '';
		const locked           = shouldLockByRule && ! manualOverride;

		// Autofill: request endpoint from backend based on VAT.
		if ( shouldLockByRule && ! manualOverride && vatValue !== '' ) {
			const vat = normalizeVat( vatValue );
			const key = country + '|' + vat;

			// If we already resolved this exact key, apply immediately.
			if ( endpointLookupLast && endpointLookupLast.key === key ) {
				if ( endpointLookupLast.value ) {
					setFieldValue( endpoint, endpointLookupLast.value );
				}
			} else {
				// Debounce to avoid calling on every keystroke.
				if ( endpointLookupTimer ) clearTimeout( endpointLookupTimer );

				endpointLookupTimer = setTimeout( () => {
					fetchPeppolEndpointValue( country, vat )
						.then( ( res ) => {
							const value = String( res?.value || '' ).trim();

							endpointLookupLast = {
								key,
								value,
							};

							if ( value ) {
								setFieldValue( endpoint, value );
							}
						} )
						.catch( () => {} );
				}, 250 );
			}
		}

		// Only skip if state and element are unchanged.
		if ( locked === lastLocked && endpoint === lastEndpointEl ) return;

		// Ensure link exists only when locked.
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

	const root =
		document.querySelector( '#order-fields' )
		|| document.querySelector( '.wc-block-checkout' )
		|| document.documentElement;

	new MutationObserver( () => scheduleApply( 'mutation' ) )
		.observe( root, { childList: true, subtree: true } );

	document.addEventListener( 'input', ( e ) => {
		if ( e.target?.matches( COUNTRY_SELECTOR ) || e.target?.matches( VAT_SELECTOR ) ) {
			scheduleApply( 'input' );
		}
	} );

	// Subscribe to Blocks store changes.
	if ( window.wp?.data?.subscribe ) {
		window.wp.data.subscribe( () => scheduleApply( 'store' ) );
	}

	scheduleApply( 'init' );

} )();
