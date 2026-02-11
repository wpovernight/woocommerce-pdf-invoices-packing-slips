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

	const LOCK_COUNTRIES = Array.isArray( CONFIG.countries )
		? CONFIG.countries.map( ( c ) => String( c ).toUpperCase() )
		: [];

	const DEBUG = !!CONFIG.debug;

	const COUNTRY_SELECTOR          = CONFIG.billing_country_selector && String( CONFIG.billing_country_selector ).trim()
		? CONFIG.billing_country_selector
		: '#billing_country';

	const VAT_SELECTOR              = CONFIG.vat_field_selector && String( CONFIG.vat_field_selector ).trim()
		? CONFIG.vat_field_selector
		: '#billing_vat, input[name="billing_vat"]';

	const ENDPOINT_WRAPPER_SELECTOR = CONFIG.peppol_input_wrapper_selector && String( CONFIG.peppol_input_wrapper_selector ).trim()
		? CONFIG.peppol_input_wrapper_selector
		: '';

	const AUTOFILL_ENDPOINT_ROUTE   = CONFIG.peppol_autofill_endpoint_route || '/wpo-ips/v1/peppol-endpoint';

	function log( ...args ) {
		if ( DEBUG ) {
			console.log( '[WPO IPS Peppol]', ...args );
		}
	}

	function normalizeVat( vat ) {
		return String( vat || '' ).replace( /\s+/g, '' ).toUpperCase().trim();
	}

	function isLockCountry( country ) {
		const c = String( country || '' ).toUpperCase();
		return LOCK_COUNTRIES.includes( c );
	}

	function getCountry() {
		const val = $( COUNTRY_SELECTOR ).val();
		return String( val || '' ).toUpperCase().trim();
	}

	function getVat() {
		const val = $( VAT_SELECTOR ).val();
		return String( val || '' ).trim();
	}

	function getEndpointInput() {
		// Wrapper selector (preferred, if provided)
		if ( ENDPOINT_WRAPPER_SELECTOR ) {
			const $wrap = $( ENDPOINT_WRAPPER_SELECTOR );
			const $inp  = $wrap.find( 'input' ).first();
			if ( $inp.length ) {
				return { $wrapper: $wrap, $input: $inp };
			}
		}

		// Fallbacks (best effort)
		const $input =
			$( '#peppol_endpoint_id' ).first()
			.add( $( 'input[name="peppol_endpoint_id"]' ).first() )
			.add( $( 'input[name*="peppol"][name*="endpoint"]' ).first() )
			.filter( function () { return $( this ).length; } )
			.first();

		if ( $input.length ) {
			return { $wrapper: $input.closest( '.form-row' ), $input };
		}

		return { $wrapper: $(), $input: $() };
	}

	function setFieldValue( $input, value ) {
		if ( ! $input || ! $input.length ) return false;

		const newVal = String( value ?? '' );
		if ( String( $input.val() ?? '' ) === newVal ) return true;

		$input.val( newVal );

		// Trigger WC/other listeners.
		$input.trigger( 'input' );
		$input.trigger( 'change' );

		return true;
	}

	function setLocked( $input, $wrapper, locked ) {
		// Prefer readonly so it still submits.
		$input.prop( 'readonly', !!locked );

		// Hard block clicks when locked.
		$input.css( 'pointer-events', locked ? 'none' : '' );

		$input.attr( 'aria-disabled', locked ? 'true' : 'false' );
		$input.toggleClass( 'wpo-is-locked', !!locked );

		if ( $wrapper && $wrapper.length ) {
			$wrapper.toggleClass( 'wpo-ips-locked-wrap', !!locked );
		}
	}

	// Debounce + cache
	let endpointLookupTimer = null;
	let endpointLookupLast  = null;

	// Manual override (session only)
	let manualOverride      = false;
	let manualOverrideValue = '';

	// Reset override when inputs change.
	let lastVat     = null;
	let lastCountry = null;

	function ensureOverrideLink( $wrapper, $input, locked ) {
		if ( ! $wrapper || ! $wrapper.length || ! $input || ! $input.length ) return;

		let $link = $wrapper.find( '.wpo-ips-override' ).first();

		if ( ! locked ) {
			if ( $link.length ) $link.remove();
			return;
		}

		if ( $link.length ) return;

		$link = $( '<a />', {
			href: '#',
			class: 'wpo-ips-override',
			text: CONFIG.override_link_text || 'Override (edit manually)',
		} );

		$link.on( 'click', function ( e ) {
			e.preventDefault();

			const preservedValue = String( $input.val() || '' );

			manualOverride      = true;
			manualOverrideValue = preservedValue;

			applyLockState( 'override-click' );

			if ( preservedValue ) {
				setTimeout( function () {
					setFieldValue( $input, preservedValue );
				}, 0 );
			}

			$input.focus();
		} );

		$input.after( $link );
	}

	function fetchPeppolEndpointValue( billingCountry, vatValue ) {
		// Preferred: wp.apiFetch (if available)
		const apiFetch = window.wp && window.wp.apiFetch ? window.wp.apiFetch : null;

		if ( apiFetch ) {
			return apiFetch( {
				path: AUTOFILL_ENDPOINT_ROUTE,
				method: 'POST',
				data: {
					billing_country: String( billingCountry || '' ).toUpperCase(),
					vat: String( vatValue || '' ),
				},
			} );
		}

		// Fallback: jQuery AJAX to wpApiSettings.root (if present)
		const root  = window.wpApiSettings && window.wpApiSettings.root ? String( window.wpApiSettings.root ) : '';
		const nonce = window.wpApiSettings && window.wpApiSettings.nonce ? String( window.wpApiSettings.nonce ) : '';

		if ( root ) {
			const path = AUTOFILL_ENDPOINT_ROUTE.replace( /^\//, '' );
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
					if ( nonce ) {
						xhr.setRequestHeader( 'X-WP-Nonce', nonce );
					}
				},
			} );
		}

		return $.Deferred().resolve( null ).promise();
	}

	function applyLockState( source ) {
		const { $wrapper, $input } = getEndpointInput();
		if ( ! $input.length ) return;

		const country  = getCountry();
		const vatValue = getVat();

		// Reset manual override when VAT/country changes.
		if ( country !== lastCountry || vatValue !== lastVat ) {
			manualOverride      = false;
			manualOverrideValue = '';
			lastCountry         = country;
			lastVat             = vatValue;
			endpointLookupLast  = null;
		}

		const shouldLockByRule = isLockCountry( country ) && vatValue !== '';
		const locked           = shouldLockByRule && ! manualOverride;

		// Autofill when it should lock and no manual override.
		if ( shouldLockByRule && ! manualOverride && vatValue !== '' ) {
			const vat = normalizeVat( vatValue );
			const key = country + '|' + vat;

			if ( endpointLookupLast && endpointLookupLast.key === key ) {
				if ( endpointLookupLast.value ) {
					setFieldValue( $input, endpointLookupLast.value );
				}
			} else {
				if ( endpointLookupTimer ) {
					clearTimeout( endpointLookupTimer );
				}

				endpointLookupTimer = setTimeout( function () {
					fetchPeppolEndpointValue( country, vat )
						.then( function ( res ) {
							const id = String( res && res.id ? res.id : '' ).trim();

							endpointLookupLast = {
								key,
								value: id,
							};

							if ( id ) {
								setFieldValue( $input, id );
							}
						} )
						.catch( function () {} );
				}, 250 );
			}
		}

		ensureOverrideLink( $wrapper, $input, locked );
		setLocked( $input, $wrapper, locked );

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

	// Apply on input changes.
	$( document ).on( 'input change', COUNTRY_SELECTOR + ', ' + VAT_SELECTOR, function () {
		applyLockState( 'input' );
	} );

	// Woo fragments; re-apply after updates.
	$( document.body ).on( 'updated_checkout updated_shipping_method', function () {
		applyLockState( 'wc-updated' );
	} );

	// Initial apply.
	applyLockState( 'init' );

} );
