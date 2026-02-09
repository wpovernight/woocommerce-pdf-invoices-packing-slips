( function () {

	const CONFIG         = window.wpoIpsPeppol || {};
	const LOCK_COUNTRIES = Array.isArray( CONFIG.countries )
		? CONFIG.countries.map( ( c ) => String( c ).toUpperCase() )
		: [];

	const DEBUG = !!CONFIG.debug;

	const COUNTRY_SELECTOR          = '#billing-country, select[name="billing_country"], .wc-block-components-address-form__country select, .wc-block-components-country-input select';
	const VAT_SELECTOR              = CONFIG.vat_field_selector;
	const VAT_MAPPINGS              = CONFIG.vat_mappings && typeof CONFIG.vat_mappings === 'object'
		? CONFIG.vat_mappings
		: {};
	const ENDPOINT_WRAPPER_SELECTOR = '.wc-block-components-address-form__wpo-ips-edi-peppol-endpoint-id';
	const ENDPOINT_SELECTOR         = ENDPOINT_WRAPPER_SELECTOR + ' input';

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

	// Manual override flag (session only).
	let manualOverride = false;

	// Reset override when VAT/country changes.
	let lastVat     = null;
	let lastCountry = null;

	let lastLocked     = null;
	let lastEndpointEl = null;
	let scheduled      = false;

	function ensureStyles() {
		if ( document.getElementById( 'wpo-ips-peppol-lock-styles' ) ) return;

		const style       = document.createElement( 'style' );
		style.id          = 'wpo-ips-peppol-lock-styles';
		style.textContent = `
			/* Locked look */
			.wpo-is-locked {
				opacity: 0.75;
				cursor: not-allowed;
			}

			/* Make wrapper look disabled (Blocks uses wrapper divs) */
			.wpo-ips-locked-wrap {
				opacity: 0.75;
			}
			.wpo-ips-locked-wrap input {
				background: rgba(0,0,0,0.04);
			}

			/* Override link */
			.wpo-ips-override {
				display: block;
				margin-top: 6px;
				font-size: 12px;
				text-decoration: underline;
				cursor: pointer;
				user-select: none;
			}
			.wpo-ips-override:hover {
				text-decoration: none;
			}
		`;
		document.head.appendChild( style );
	}

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
			manualOverride = true;

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

	function applyLockState( source = 'unknown' ) {
		scheduled = false;

		ensureStyles();

		const wrapper  = document.querySelector( ENDPOINT_WRAPPER_SELECTOR );
		const endpoint = document.querySelector( ENDPOINT_SELECTOR );

		if ( ! endpoint ) return;

		const country  = getBillingCountry().toUpperCase();
		const vatValue = getValue( VAT_SELECTOR );

		// Reset manual override when VAT/country changes.
		if ( country !== lastCountry || vatValue !== lastVat ) {
			manualOverride = false;
			lastCountry    = country;
			lastVat        = vatValue;
		}

		const shouldLockByRule = isLockCountry( country ) && vatValue !== '';
		const locked           = shouldLockByRule && ! manualOverride;

		// Autofill: copy VAT straight into endpoint.
		const computedEndpoint = ( vatValue !== '' )
			? normalizeVat( vatValue )
			: '';

		if ( shouldLockByRule && ! manualOverride && computedEndpoint ) {
			setFieldValue( endpoint, computedEndpoint );
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
			computedEndpoint,
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
