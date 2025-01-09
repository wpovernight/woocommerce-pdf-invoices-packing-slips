jQuery( function( $ ) {

	$( '.wcpdf-extensions .more' ).hide();

	$( '.wcpdf-extensions > li' ).on( 'click', function( event ) {
		$( this ).toggleClass( 'expanded' );
		$( this ).find( '.more' ).slideToggle();
	} );

	$( '.edit-next-number' ).on( 'click', function( event ) {
		// enable input & show save button
		$( this ).hide();
		$( this ).siblings( 'input' ).prop( 'disabled', false );
		$( this ).siblings( '.save-next-number.button' ).show();
	} );

	$( '.save-next-number' ).on( 'click', function( event ) {
		$input = $( this ).siblings( 'input' );
		$input.addClass( 'ajax-waiting' );
		let number = $input.val();

		if ( number.length > 0 && number > 2147483647 ) {
			alert( wpo_wcpdf_admin.mysql_int_size_limit );
			$input.removeClass( 'ajax-waiting' );
			return;
		}


		let data = {
			security: $input.data( 'nonce' ),
			action:   'wpo_wcpdf_set_next_number',
			store:    $input.data( 'store' ),
			number:   number,
		};

		xhr = $.ajax( {
			type: 'POST',
			url:  wpo_wcpdf_admin.ajaxurl,
			data: data,
			success: function( response ) {
				$input.removeClass( 'ajax-waiting' );
				$input.siblings( '.edit-next-number' ).show();
				$input.prop( 'disabled', 'disabled' );
				$input.siblings( '.save-next-number.button' ).hide();
			}
		} );
	} );

	$( "[name='wpo_wcpdf_documents_settings_invoice[display_number]']" ).on( 'change', function( event ) {
		if ( $( this ).val() == 'order_number' ) {
			$( this ).closest( 'td' ).find( '.description' ).slideDown();
			$( this ).closest( 'tr' ).nextAll( 'tr' ).has( 'input#next_invoice_number' ).first().hide();
		} else {
			$( this ).closest( 'td' ).find( '.description' ).hide();
			$( this ).closest( 'tr' ).nextAll( 'tr' ).has( 'input#next_invoice_number' ).first().show();
		}
	} ).trigger( 'change' );

	// disable encrypted pdf option for non UBL 2.1 formats
	$( "[name='wpo_wcpdf_documents_settings_invoice_ubl[ubl_format]']" ).on( 'change', function( event ) {
		let $encryptedPdfCheckbox = $( this ).closest( 'form' ).find( "[name='wpo_wcpdf_documents_settings_invoice_ubl[include_encrypted_pdf]']" );

		if ( $( this ).val() !== 'ubl_2_1' ) {
			$encryptedPdfCheckbox.prop( 'checked', false ).prop( 'disabled', true );
		} else {
			$encryptedPdfCheckbox.prop( 'disabled', false );
		}
	} ).trigger( 'change' );

	// enable settings document switch
	$( '.wcpdf_document_settings_sections > h2' ).on( 'click', function() {
		$( this ).parent().find( 'ul' ).toggleClass( 'active' );
	} );

	// Add admin pointers
	$.each( wpo_wcpdf_admin.pointers, function( key, pointer ) {

		$( pointer.target ).pointer(
			{
				content: pointer.content,

				position:
					{
						edge:  pointer.position.edge,
						align: pointer.position.align
					},

				pointerClass: pointer.pointer_class,

				pointerWidth: pointer.pointer_width,

				close: function() {
					jQuery.post(
						wpo_wcpdf_admin.ajaxurl,
						{
							pointer: key,
							action:  'dismiss-wp-pointer',
						}
					);
				},
			}
		);

		// Check if pointer was dismissed
		if ( $.inArray( key, wpo_wcpdf_admin.dismissed_pointers.split(',') ) === -1 ) {
			$( pointer.target ).pointer('open');
		}

	});

	// enable WooCommerce help tips
	$( '.woocommerce-help-tip' ).tipTip( {
		'attribute': 'data-tip',
		'fadeIn':    50,
		'fadeOut':   50,
		'delay':     200
	} );

	$( '#wpo-wcpdf-preview-wrapper #due_date' ).on( 'change', function() {
		const $due_date_checkbox   = $( '#wpo-wcpdf-preview-wrapper #due_date' );
		const $due_date_days_input = $( '#wpo-wcpdf-preview-wrapper #due_date_days' );

		if ( $due_date_checkbox.is( ':checked' ) ) {
			$due_date_days_input.prop( 'disabled', false );
		} else {
			$due_date_days_input.prop( 'disabled', true );
		}
	} ).trigger( 'change' );

	//----------> Preview <----------//
	// objects
	let $previewWrapper           = $( '#wpo-wcpdf-preview-wrapper' );
	let $preview                  = $( '#wpo-wcpdf-preview-wrapper .preview' );
	let $previewOrderIdInput      = $( '#wpo-wcpdf-preview-wrapper input[name="order_id"]' );
	let $previewDocumentTypeInput = $( '#wpo-wcpdf-preview-wrapper input[name="document_type"]' );
	let $previewOutputFormatInput = $( '#wpo-wcpdf-preview-wrapper input[name="output_format"]' );
	let $previewNonceInput        = $( '#wpo-wcpdf-preview-wrapper input[name="nonce"]' );
	let $previewSettingsForm      = $( '#wpo-wcpdf-settings' );
	let previewXhr                = null;

	// variables
	let previewOrderId, previewDocumentType, previewOutputFormat, previewNonce, previewSettingsFormData, previewTimeout, previewSearchTimeout, previousWindowWidth;

	function loadPreviewData() {
		previewOrderId          = $previewOrderIdInput.val();
		previewDocumentType     = $previewDocumentTypeInput.val();
		previewOutputFormat     = $previewOutputFormatInput.val();
		previewNonce            = $previewNonceInput.val();
		previewSettingsFormData = $previewSettingsForm.serialize();
	}

	function resetDocumentType() {
		$previewDocumentTypeInput.val( $previewDocumentTypeInput.data( 'default' ) ).trigger( 'change' );
	}

	function resetOrderId() {
		$previewOrderIdInput.val( '' ).trigger( 'change' );
	}

	resetDocumentType();      // force document type reset
	resetOrderId();           // force order ID reset
	loadPreviewData();        // load preview data

	previousWindowWidth = $( window ).width();
	determinePreviewStates(); // determine preview states based on screen size

	$( window ).on( 'resize', determinePreviewStates );

	function determinePreviewStates() {
		// Check if preview states are allowed to change based on screen size
		if ( $previewWrapper.attr( 'data-preview-states-lock') == false ) {

			// On small screens: 2 preview states and close preview
			if ( $(this).width() <= 1200 && ( previousWindowWidth > 1200 || $(this).width() == previousWindowWidth ) ) {
				if ( $previewWrapper.attr( 'data-preview-state') == 'full' ) {
					$previewWrapper.find( '.preview-document' ).show();
					$previewWrapper.find( '.sidebar' ).hide();
					$previewWrapper.find( '.slide-left' ).hide();
					$previewWrapper.find( '.slide-right' ).show();
					$previewWrapper.attr( 'data-preview-states', 2 );
					$previewWrapper.attr( 'data-preview-state', 'full' );
					$previewWrapper.attr( 'data-from-preview-state', '' );
				} else {
					$previewWrapper.find( '.preview-document' ).hide();
					$previewWrapper.find( '.sidebar' ).show();
					$previewWrapper.find( '.slide-left' ).show();
					$previewWrapper.find( '.slide-right' ).hide();
					$previewWrapper.attr( 'data-preview-states', 2 );
					$previewWrapper.attr( 'data-preview-state', 'closed' );
					$previewWrapper.attr( 'data-from-preview-state', '' );
				}

			// On larger screens: 3 preview states and show settings as sidebar
			} else if ( $(this).width() > 1200 && ( previousWindowWidth <= 1200 || $(this).width() == previousWindowWidth ) ) {
				if ( $previewWrapper.attr( 'data-preview-state') == 'full' ) {
					$previewWrapper.find( '.preview-document' ).show();
					$previewWrapper.find( '.sidebar' ).hide();
					$previewWrapper.find( '.slide-left' ).hide();
					$previewWrapper.find( '.slide-right' ).show();
					$previewWrapper.attr( 'data-preview-states', 3 );
					$previewWrapper.attr( 'data-preview-state', 'full' );
					$previewWrapper.attr( 'data-from-preview-state', 'sidebar' );
					$previewWrapper.addClass( 'static' );
				} else if ( $previewWrapper.attr( 'data-preview-state') == 'closed' && $(this).width() !== previousWindowWidth ) {
					$previewWrapper.find( '.preview-document' ).hide();
					$previewWrapper.find( '.sidebar' ).show();
					$previewWrapper.find( '.slide-left' ).show();
					$previewWrapper.find( '.slide-right' ).hide();
					$previewWrapper.attr( 'data-preview-states', 3 );
					$previewWrapper.attr( 'data-preview-state', 'closed' );
					$previewWrapper.attr( 'data-from-preview-state', '' );
					$previewWrapper.removeClass( 'static' );
				} else {
					$previewWrapper.find( '.preview-document, .sidebar' ).show();
					$previewWrapper.find( '.slide-left, .slide-right' ).show();
					$previewWrapper.attr( 'data-preview-states', 3 );
					$previewWrapper.attr( 'data-preview-state', 'sidebar' );
					$previewWrapper.attr( 'data-from-preview-state', '' );
					$previewWrapper.removeClass( 'static' );
				}
			}
		}
		previousWindowWidth = $(this).width();
	}

	$( '.slide-left' ).on( 'click', function() {
		let previewStates = $previewWrapper.attr( 'data-preview-states' );
		let previewState  = $previewWrapper.attr( 'data-preview-state' );

		$previewWrapper.find( '.preview-data-wrapper ul' ).removeClass( 'active' );

		if ( previewStates == 3 ) {
			if ( previewState == 'closed' ) {
				$previewWrapper.find( '.preview-document' ).show();
				$previewWrapper.find( '.slide-right' ).show();
				$previewWrapper.attr( 'data-preview-state', 'sidebar' );
				$previewWrapper.attr( 'data-from-preview-state', 'closed' );
			} else {
				$previewWrapper.find( '.slide-left' ).hide();
				$previewWrapper.find( '.sidebar' ).delay(300).hide(0);
				$previewWrapper.attr( 'data-preview-state', 'full' );
				$previewWrapper.attr( 'data-from-preview-state', 'sidebar' );
				makePreviewScrollable( $previewWrapper );
			}
		} else {
			$previewWrapper.find( '.preview-document' ).show();
			$previewWrapper.find( '.slide-left' ).hide();
			$previewWrapper.find( '.slide-right' ).show();
			$previewWrapper.attr( 'data-preview-state', 'full' );
			$previewWrapper.attr( 'data-from-preview-state', 'closed' );
			makePreviewScrollable( $previewWrapper );
		}
	} );

	$( '.slide-right' ).on( 'click', function() {
		let previewStates = $previewWrapper.attr( 'data-preview-states' );
		let previewState  = $previewWrapper.attr( 'data-preview-state' );

		$previewWrapper.find( '.preview-data-wrapper ul' ).removeClass( 'active' );

		if ( previewStates == 3 ) {
			if ( previewState == 'full' ) {
				$previewWrapper.find( '.slide-left' ).delay(400).show(0);
				$previewWrapper.find( '.sidebar' ).show();
				$previewWrapper.attr( 'data-preview-state', 'sidebar' );
				$previewWrapper.attr( 'data-from-preview-state', 'full' );
			} else {
				$previewWrapper.find( '.preview-document' ).hide(300);
				$previewWrapper.find( '.slide-right' ).hide();
				$previewWrapper.attr( 'data-preview-state', 'closed' );
				$previewWrapper.attr( 'data-from-preview-state', 'sidebar' );
			}
		} else {
			$previewWrapper.find( '.preview-document' ).hide(300);
			$previewWrapper.find( '.slide-left' ).show();
			$previewWrapper.find( '.slide-right' ).hide();
			$previewWrapper.attr( 'data-preview-state', 'closed' );
			$previewWrapper.attr( 'data-from-preview-state', 'full' );
		}
		$previewWrapper.removeClass( 'static' );
	} );

	function makePreviewScrollable( wrapper ) {
		window.scrollTo( 0, 0 );
		let $wrapper = wrapper;
		// Make preview scrollable after panel animation is complete
		setTimeout( function() {
			$wrapper.addClass( 'static' );
		}, 300 );
	}

	$( '.preview-document .preview-data p' ).on( 'click', function() {
		let $previewData = $( this ).closest( '.preview-data' );
		$previewData.siblings( '.preview-data' ).find( 'ul' ).removeClass( 'active' );
		$previewData.find( 'ul' ).toggleClass( 'active' );
	} );

	$( '.preview-document .preview-data ul > li' ).on( 'click', function() {
		let $previewData = $( this ).closest( '.preview-data' );
		$previewData.find( 'ul' ).toggleClass( 'active' );
		if ( $( this ).hasClass( 'order-search' ) ) {
			$previewData.find( 'p.last-order' ).hide();
			$previewData.find( 'input[name="preview-order-search"]' ).addClass( 'active' );
			$previewData.find( 'p.order-search' ).show().find( '.order-search-label' ).text( $( this ).text() );
		} else {
			$previewData.find( 'p.last-order' ).show();
			$previewData.find( 'p.order-search' ).hide();
			$previewData.find( 'input[name="preview-order-search"]' ).removeClass( 'active' ).val( '' );
			$previewData.find( '#preview-order-search-results' ).hide();
			$previewData.find( 'img.preview-order-search-clear' ).hide(); // remove the clear button
			resetOrderId()    // force order ID reset
			triggerPreview(); // trigger preview
		}
	} );

	// Preview on page load
	triggerPreview();

	// Custom trigger to signify settings have changed (will show save button and refresh preview)
	$( document ).on( 'wpo-wcpdf-settings-changed', function( event, delay ) {
		showSaveBtn();
		triggerPreview( delay );
	} );

	// Custom trigger to refresh preview
	$( document ).on( 'wpo-wcpdf-refresh-preview wpo_wcpdf_refresh_preview', function( event, delay ) {
		triggerPreview( delay );
	} );

	// Preview on user click in search result
	$( document ).on( 'click', '#preview-order-search-results a', function( event ) {
		event.preventDefault();
		$( '.preview-document .order-search-label').text( '#' + $( this ).data( 'order_id' ) );
		$previewOrderIdInput.val( $( this ).data( 'order_id' ) ).trigger( 'change' );
		$( this ).closest( 'div' ).hide();                   // hide results div
		$( this ).closest( 'div' ).children( 'a' ).remove(); // remove all results
		triggerPreview();
	} );

	// Check for settings change
	$( document ).on( 'keyup paste', '#wpo-wcpdf-settings input, #wpo-wcpdf-settings textarea', settingsChanged );
	$( document ).on( 'change', '#wpo-wcpdf-settings input[type="checkbox"], #wpo-wcpdf-settings input[type="radio"], #wpo-wcpdf-settings select', function( event ) {
		if ( ! event.isTrigger ) { // exclude programmatic triggers that aren't actually changing anything
			settingsChanged( event );
		}
	});
	$( document ).on( 'select2:select select2:unselect', '#wpo-wcpdf-settings select.wc-enhanced-select', settingsChanged );
	$( document.body ).on( 'wpo-wcpdf-media-upload-setting-updated', settingsChanged );
	$( document ).on( 'click', '.wpo_remove_image_button, #wpo-wcpdf-settings .remove-requirement', settingsChanged );

	function settingsChanged( event, previewDelay ) {

		// Show secondary save button
		showSaveBtn();

		// Check if preview needs to reload and with what delay
		let $element = $( event.target );

		if ( ! settingIsExcludedForPreview( $element.attr('name') ) ) {

			if ( $element.hasClass( 'remove-requirement' ) || $element.attr('id') == 'disable_for' ) {
				return;
			}

			if ( jQuery.inArray( event.type, ['keyup', 'paste'] ) !== -1 ) {
				if ( $element.is( 'input[type="checkbox"], select' ) ) {
					return;
				} else {
					previewDelay = event.type == 'keyup' ? 1000 : 0;
				}
			}

			triggerPreview( previewDelay );
		}
	}

	function showSaveBtn( event ) {
		$('.preview-data-wrapper .save-settings p').css('margin-right', '0');
	}

	// Submit settings form when clicking on secondary save button
	$( document.body ).on( 'click', '.preview-data-wrapper .save-settings p input', function( event ) {
		$( '#wpo-wcpdf-settings input#submit' ).trigger( 'click' );
	} );

	// Trigger the Preview
	function triggerPreview( timeoutDuration = 0 ) {
		$previewStates = $( '#wpo-wcpdf-preview-wrapper' ).data( 'preview-states' );
		
		// Check if preview is disabled and return
		if ( 'undefined' === $previewStates || 1 === $previewStates ) {
			return;
		}
		
		timeoutDuration = typeof timeoutDuration == 'number' ? timeoutDuration : 0;

		loadPreviewData();
		clearTimeout( previewTimeout );
		previewTimeout = setTimeout( function() { ajaxLoadPreview() }, timeoutDuration );
	}

	// Settings excluded from trigger the Preview
	function settingIsExcludedForPreview( settingName ) {
		let excluded = false;
		if ( ! settingName ) {
			return excluded;
		}
		let nameKey = settingName.includes( '[' ) ? settingName.match(/\[(.*?)\]/)[1] : settingName;
		if ( $.inArray( nameKey, wpo_wcpdf_admin.preview_excluded_settings ) !== -1 ) {
			excluded = true;
		}
		return excluded;
	}

	// Clear preview order search results/input
	$( document ).on( 'click', 'img.preview-order-search-clear', function( event ) {
		event.preventDefault();
		$( this ).closest( 'div' ).find( 'input#preview-order-search' ).val( '' );
		$( this ).closest( '.preview-data' ).find( '#preview-order-search-results' ).children( 'a' ).remove();      // remove previous results
		$( this ).closest( '.preview-data' ).find( '#preview-order-search-results' ).children( '.error' ).remove(); // remove previous errors
		$( this ).closest( '.preview-data' ).find( '#preview-order-search-results' ).hide();
		$( this ).hide();
	} );

	// Trigger preview on document selection and change the document type input with the new value
	$( '#wpo-wcpdf-preview-wrapper ul.preview-data-option-list li' ).on( 'click', function() {
		let inputName = $( this ).closest( 'ul' ).data( 'input-name' );
		let $input    = $( '#wpo-wcpdf-preview-wrapper :input[name='+inputName+']');
		$input.val( $( this ).data( 'value' ) ).trigger( 'change' );
	} );

	// Detect document type input changes and apply the same document title to the document selector
	$previewDocumentTypeInput.on( 'change', function() {
		let inputValue = $( this ).val();
		if ( inputValue.length ) {
			let inputName  = $( this ).attr( 'name' );
			let $ul        = $( '#wpo-wcpdf-preview-wrapper ul.preview-data-option-list[data-input-name='+inputName+']' );
			let $li        = $ul.find( 'li[data-value='+inputValue+']' );
			$ul.parent().find( '.current-label' ).text( $li.text() );
			triggerPreview();
		}
	} ).trigger( 'change' );

	// Detect order ID input changes
	$previewOrderIdInput.on( 'change', function() {
		triggerPreview();
	} ).trigger( 'change' );

	// Load the Preview with AJAX
	function ajaxLoadPreview() {
		console.log( 'Loading preview...' );
		let worker   = wpo_wcpdf_admin.pdfjs_worker;
		let canvasId = 'preview-canvas';
		let data     = {
			action:        'wpo_wcpdf_preview',
			security:      previewNonce,
			order_id:      previewOrderId,
			document_type: previewDocumentType,
			output_format: previewOutputFormat,
			data:          previewSettingsFormData,
		};

		// remove previous error notices
		$preview.children( '.notice' ).remove();

		// block ui
		$preview.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		previewXhr = $.ajax( {
			type:    'POST',
			url:     wpo_wcpdf_admin.ajaxurl,
			data:    data,
			beforeSend: function( jqXHR, settings ) {
				if ( previewXhr != null ) {
					previewXhr.abort();
				}
			},
			success: function( response, textStatus, jqXHR ) {
				if ( response.data.error ) {
					$( '#'+canvasId ).remove();
					$preview.append( '<div class="notice notice-error inline"><p>'+response.data.error+'</p></div>' );
				} else if ( response.data.preview_data && response.data.output_format ) {
					$( '#'+canvasId ).remove();

					switch ( response.data.output_format ) {
						default:
						case 'pdf':
							$preview.append( '<canvas id="'+canvasId+'" style="width:100%;"></canvas>' );
							renderPdf( worker, canvasId, response.data.preview_data );
							break;
						case 'ubl':
							let xml         = response.data.preview_data;
							let xml_escaped = xml.replace( /&/g,'&amp;' ).replace( /</g,'&lt;' ).replace( />/g,'&gt;' ).replace( / /g, '&nbsp;' ).replace( /\n/g,'<br />' );
							$preview.html( '<div id="preview-ubl">'+xml_escaped+'</div>' );
							break;
					}
				}

				$preview.unblock();
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				if ( textStatus != 'abort' ) {
					let errorMessage = jqXHR.status + ': ' + jqXHR.statusText
					$( '#'+canvasId ).remove();
					$preview.append( '<div class="notice notice-error inline"><p>'+errorMessage+'</p></div>' );
					$preview.unblock();
				}
			},
		} );
	}

	// pdf_js (third party library code)
	function renderPdf( worker, canvasId, pdfData ) {
		// atob() is used to convert base64 encoded PDF to binary-like data.
		// (See also https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding.)
		pdfData = window.atob( pdfData );

		// The workerSrc property shall be specified.
		pdfjsLib.GlobalWorkerOptions.workerSrc = worker;

		// Using DocumentInitParameters object to load binary data.
		let loadingTask = pdfjsLib.getDocument( { data: pdfData } );
		loadingTask.promise.then( function( pdf ) {
			// Fetch the first page
			let pageNumber = 1;
			pdf.getPage( pageNumber ).then( function( page ) {
				let scale     = 2;
				let viewport  = page.getViewport( { scale: scale } );

				// Prepare canvas using PDF page dimensions
				let canvas    = document.getElementById( canvasId );
				let context   = canvas.getContext( '2d' );

				canvas.height = viewport.height;
				canvas.width  = viewport.width;

				// Render PDF page into canvas context
				let renderContext = {
					canvasContext: context,
					viewport:      viewport
				};
				let renderTask = page.render( renderContext );
				renderTask.promise.then( function() {
					// page rendered
				} );
			} );
		}, function( reason ) {
			// PDF loading error
			console.error( reason );
		} );
	}

	// Preview on user input
	$( '#preview-order-search' ).on( 'keyup paste', function( event ) {
		let $elem = $( this );
		$elem.addClass( 'ajax-waiting' );
		let duration = event.type == 'keyup' ? 1000 : 0;
		loadPreviewData();
		clearTimeout( previewSearchTimeout );
		previewSearchTimeout = setTimeout( function() { previewOrderSearch( $elem ) }, duration );
	} );

	// Preview order search
	function previewOrderSearch( $elem ) {
		let $div   = $elem.closest( '.preview-data' ).find( '#preview-order-search-results' );
		let value  = $elem.val();
		let nonce  = $elem.data( 'nonce' );
		let action = 'wpo_wcpdf_preview_order_search';

		let data = {
			security:      nonce,
			action:        action,
			search:        value,
			document_type: previewDocumentType,
		};

		$div.parent().find( 'img.preview-order-search-clear' ).hide(); // hide the clear button
		$div.children( '.error' ).remove();                            // remove previous errors
		$div.children( 'a' ).remove();                                 // remove previous results
		$div.hide();                                                   // hide search results

		$.ajax( {
			type:    'POST',
			url:     wpo_wcpdf_admin.ajaxurl,
			data:    data,
			success: function( response ) {
				if ( response.data ) {
					if ( response.data.error ) {
						$div.append( '<span class="error">'+response.data.error+'</span>' );
						$div.show();
					} else {
						$.each( response.data, function( i, item ) {
							let firstLine = '<a data-order_id="'+i+'"><span class="order-number">#'+item.order_number+'</span> - '+item.billing_first_name+' '+item.billing_last_name;
							if ( item.billing_company.length > 0 ) {
								firstLine = firstLine+', '+item.billing_company;
							}
							let secondLine = '<br><span class="date">'+item.date_created+'</span><span class="total">'+item.total+'</span></a>';
							$div.append( firstLine+secondLine );
							$div.show();
						} );
					}
				}

				$elem.removeClass( 'ajax-waiting' );
				$elem.closest( 'div' ).find( 'img.preview-order-search-clear' ).show();
			}
		} );
	}

	//----------> /Preview <----------//

	function settingsAccordion() {
		// Default to expanded for '#general', collapsed for others.
		$( '.settings_category' ).not( '#general' ).find( '.form-table' ).hide();
		$( '#general > h2' ).addClass( 'active' );

		// Retrieve the state from localStorage
		$( '.settings_category h2' ).each( function( index ) {
			const state = localStorage.getItem( 'wcpdf_accordion_state_' + index );
			if ( 'true' === state ) {
				$( this ).addClass( 'active' ).next( '.form-table' ).show();
			}
		} );

		$('.settings_category h2' ).on( 'click', function() {
			const index = $( '.settings_category h2' ).index( this );

			$( this ).toggleClass( 'active' ).next( '.form-table' ).slideToggle( 'fast', function() {
				// Save the state in localStorage
				const isVisible = $( this ).is( ':visible' );
				localStorage.setItem( 'wcpdf_accordion_state_' + index, isVisible );
			} );
		} );
	}

	settingsAccordion();

} );
