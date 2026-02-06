( function () {
	
	const CONFIG         = window.wpoIpsPeppol || {};
	const LOCK_COUNTRIES = Array.isArray( CONFIG.countries )
		? CONFIG.countries.map( ( c ) => String( c ).toUpperCase() )
		: [];

	const DEBUG = !!CONFIG.debug;

	const COUNTRY_SELECTOR          = '#billing-country, select[name="billing_country"]';
	const VAT_SELECTOR              = CONFIG.vat_field_selector;
	const ENDPOINT_WRAPPER_SELECTOR = '.wc-block-components-address-form__wpo-ips-edi-peppol-endpoint-id';
	const ENDPOINT_SELECTOR         = ENDPOINT_WRAPPER_SELECTOR + ' input';

	function log( ...args ) {
		if ( DEBUG ) console.log( '[WPO IPS Peppol]', ...args );
	}

	function getValue( selector ) {
		const el = document.querySelector( selector );
		return el ? String( el.value || '' ).trim() : '';
	}

	function isLockCountry() {
		const country = getValue( COUNTRY_SELECTOR ).toUpperCase();
		return LOCK_COUNTRIES.includes( country );
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

			// One-way override: unlock until VAT/country changes again.
			manualOverride = true;

			applyLockState( 'override-click' );

			if ( endpointInput ) {
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

		const country = getValue( COUNTRY_SELECTOR ).toUpperCase();
		const vatValue = getValue( VAT_SELECTOR );
		
		// Reset manual override when country/VAT changes, so it "follows the flow".
		if ( country !== lastCountry || vatValue !== lastVat ) {
			manualOverride = false;
			lastCountry    = country;
			lastVat        = vatValue;
		}

		const shouldLockByRule = isLockCountry() && vatValue !== '';
		const locked           = shouldLockByRule && ! manualOverride;

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
		} );
	}

	function scheduleApply( source ) {
		if ( scheduled ) return;
		
		scheduled = true;
		requestAnimationFrame( () => applyLockState( source ) );
	}

	const root =
		document.querySelector( '#order-fields')
		|| document.querySelector( '.wc-block-checkout' )
		|| document.documentElement;

	new MutationObserver( () => scheduleApply( 'mutation' ) )
		.observe( root, { childList: true, subtree: true } );

	document.addEventListener( 'input', ( e ) => {
		if ( e.target?.matches( COUNTRY_SELECTOR ) || e.target?.matches( VAT_SELECTOR ) ) {
			scheduleApply( 'input' );
		}
	});

	scheduleApply( 'init' );
	
} )();
