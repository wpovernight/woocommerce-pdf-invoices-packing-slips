jQuery( function( $ ) {
	$('.wcpdf-extensions .more').hide();

	$('.wcpdf-extensions > li').on('click',function( event ) {
		$(this).toggleClass('expanded');
		$(this).find('.more').slideToggle();
	});

	$('.edit-next-number').on('click', function( event ) {
		// enable input & show save button
		$( this ).hide();
		$( this ).siblings( 'input' ).prop('disabled', false);
		$( this ).siblings( '.save-next-number.button').show();
	});

	$('.save-next-number').on('click', function( event ) {
		$input = $( this ).siblings( 'input' );
		$input.addClass('ajax-waiting');
		var data = {
			security: $input.data('nonce'),
			action:   "wpo_wcpdf_set_next_number",
			store:    $input.data('store'),
			number:   $input.val(), 
		};

		xhr = $.ajax({
			type: 'POST',
			url:  wpo_wcpdf_admin.ajaxurl,
			data: data,
			success: function( response ) {
				$input.removeClass('ajax-waiting');
				$input.siblings( '.edit-next-number' ).show();
				$input.prop('disabled', 'disabled');
				$input.siblings( '.save-next-number.button').hide();
			}
		});
	});

	$("[name='wpo_wcpdf_documents_settings_invoice[display_number]']").on('change', function (event) {
		if ($(this).val() == 'order_number') {
			$(this).closest('td').find('.description').slideDown();
		} else {
			$(this).closest('td').find('.description').hide();
		}
	}).trigger('change');

	// enable settings document switch
	$('.wcpdf_document_settings_sections > h2').on( 'click', function() {
		$(this).parent().find('ul').toggleClass('active');
	} );


	//----------> Preview <----------//
	let $preview     = $( '#wpo-wcpdf-preview-wrapper .preview' );
	let documentType = $preview.data( 'document_type' );
	let lastOrderId  = $preview.data( 'order_id' );
	
	$( '.slide-left' ).on( 'click', function() {
		let $wrapper      = $( this ).closest( '#wpo-wcpdf-preview-wrapper' );
		let previewStates = $wrapper.attr( 'data-preview-states' );
		let previewState  = $wrapper.attr( 'data-preview-state' );

		if ( previewStates == 3 ) {
			if ( previewState == 'closed' ) {
				$wrapper.attr( 'data-preview-state', 'sidebar' );
				$wrapper.attr( 'data-from-preview-state', 'closed' );
			} else {
				$wrapper.attr( 'data-preview-state', 'full' );
				$wrapper.attr( 'data-from-preview-state', 'sidebar' );
				makePreviewScrollable($wrapper);
			}
		} else {
			$wrapper.attr( 'data-preview-state', 'full' );
			$wrapper.attr( 'data-from-preview-state', 'closed' );
			makePreviewScrollable($wrapper);
		}
	});

	$( '.slide-right' ).on( 'click', function() {
		let $wrapper      = $( this ).closest( '#wpo-wcpdf-preview-wrapper' );
		let previewStates = $wrapper.attr( 'data-preview-states' );
		let previewState  = $wrapper.attr( 'data-preview-state' );

		if ( previewStates == 3 ) {
			if ( previewState == 'full' ) {
				$wrapper.attr( 'data-preview-state', 'sidebar' );
				$wrapper.attr( 'data-from-preview-state', 'full' );
			} else {
				$wrapper.attr( 'data-preview-state', 'closed' );
				$wrapper.attr( 'data-from-preview-state', 'sidebar' );
			}
		} else {
			$wrapper.attr( 'data-preview-state', 'closed' );
			$wrapper.attr( 'data-from-preview-state', 'full' );
		}
		$wrapper.removeClass('static');
	});

	function makePreviewScrollable(wrapper) {
		window.scrollTo(0, 0);
		let $wrapper = wrapper;
		// Make preview scrollable after panel animation is complete
		setTimeout( function() {
			$wrapper.addClass('static');
		}, 300);
	}

	$('.preview-document .preview-data p').on( 'click', function() {
		let $previewData = $(this).closest('.preview-data');
		$previewData.siblings('.preview-data').find('ul').removeClass('active');
		$previewData.find('ul').toggleClass('active');
	});

	$('.preview-document .preview-data li').on( 'click', function() {
		let $previewData = $(this).closest('.preview-data');
		$previewData.find('ul').toggleClass('active');
		if ( $(this).hasClass('order-number') ) {
			$previewData.find('p.last-order').hide();
			$previewData.find('p.order-search').hide();
			$previewData.find('p.order-number').show();
			$previewData.find('input[name="preview-order-number"]').addClass('active');
			$previewData.find('input[name="preview-order-search"]').removeClass('active');
			$previewData.find('#preview-order-search-results').hide();
			$previewData.find( 'img.preview-order-search-clear' ).hide(); // remove the clear button
		} else if ( $(this).hasClass('order-search') ) {
			$previewData.find('p.last-order').hide();
			$previewData.find('p.order-number').hide();
			$previewData.find('p.order-search').show();
			$previewData.find('input[name="preview-order-search"]').addClass('active');
			$previewData.find('input[name="preview-order-number"]').removeClass('active').val('');
		} else {
			$previewData.find('p.last-order').show();
			$previewData.find('p.order-number').hide();
			$previewData.find('p.order-search').hide();
			$previewData.find('input[name="preview-order-number"]').removeClass('active').val('');
			$previewData.find('input[name="preview-order-search"]').removeClass('active');
			$previewData.find('#preview-order-search-results').hide();
			$previewData.find( 'img.preview-order-search-clear' ).hide(); // remove the clear button
			// trigger preview
			trigger_preview();
		}
	});

	let previewTimeout;

	// Preview on page load
	$( document ).ready( trigger_preview() );

	// Preview on user input
	$( document ).on( 'keyup paste', '#wpo-wcpdf-settings input:not([type=checkbox]), #wpo-wcpdf-settings textarea, #wpo-wcpdf-settings select:not(.dropdown-add-field), #preview-order-number', function( event ) {
		if ( ! setting_is_excluded_for_preview( $( this ).attr( 'id' ) ) ) {
			let duration  = event.type == 'keyup' ? 1000 : 0; 
			trigger_preview( duration );
		}
	} );

	// Preview on user selected option (using 'change' event breaks the PDF render)
	$( document ).on( 'click', '#wpo-wcpdf-settings select:not(.dropdown-add-field) option', function( event ) {
		if ( ! setting_is_excluded_for_preview( $( this ).parent().attr( 'id' ) ) ) {
			trigger_preview();
		}
	} );

	// Preview on user checkbox change
	$( document ).on( 'change', '#wpo-wcpdf-settings input[type="checkbox"]', function( event ) {
		if ( ! setting_is_excluded_for_preview( $( this ).attr( 'id' ) ) ) {
			trigger_preview( 1000 );
		}
	} );

	// Preview on header logo change
	$( document.body ).on( 'wpo-wcpdf-media-upload-setting-updated', function( event, $input ) {
		trigger_preview();
	} );
	$( document ).on( 'click', '.wpo_remove_image_button', function( event ) {
		trigger_preview();
	} );

	// Custom trigger
	$( document ).on( 'wpo_wcpdf_refresh_preview', function( event, duration, order_id, document_type ) {
		trigger_preview( duration, order_id, document_type );
	} );

	// Preview on user click in search result
	$( document ).on( 'click', '#preview-order-search-results a', function( event ) {
		event.preventDefault();
		let order_id = $( this ).data( 'order_id' );

		$( this ).closest( 'div' ).hide();                   // hide results div
		$( this ).closest( 'div' ).children( 'a' ).remove(); // remove all results

		trigger_preview( 0, order_id );
	} );

	// Trigger the Preview
	function trigger_preview( timeoutDuration = 0, order_id = 0, document_type = '' ) {
		if ( document_type.length == 0 || document_type == '' ) {
			if ( documentType.length > 0 ) {
				document_type = documentType;
			} else {
				document_type = 'invoice';
			}
		}
		if ( order_id.length == 0 || order_id == '' || order_id == 0 ) {
			order_id = lastOrderId;
		}
		let order_number  = $( '#wpo-wcpdf-preview-wrapper input[name="preview-order-number"]' ).val();
		if( order_number.length > 0 ) {
			order_id = order_number;
		}
		clearTimeout( previewTimeout );
		previewTimeout = setTimeout( function() { ajax_load_preview( order_id, document_type ) }, timeoutDuration );
	}

	// Settings excluded from trigger the Preview
	function setting_is_excluded_for_preview( settingName ) {
		let excluded = false;
		$.each( wpo_wcpdf_admin.preview_excluded_settings, function( i, v ) {
			if ( settingName.indexOf( v ) != -1 ) {
				excluded = true;
			}
		} );
		return excluded;
	}

	// Set the document selector default if needs to be updated
	function set_document_selector_default( document_type ) {
		let $wrapper     = $( document ).find( '.preview-document-type' );
		let $current     = $wrapper.find( '.current' );
		let current_type = $current.data( 'type' );
		let $documents   = $wrapper.find( 'ul > li' );

		if ( document_type != current_type ) {
			$.each( $documents, function( i, v ) {
				let $document = $( v );
				let type      = $document.data( 'type' );
				let title     = $document.data( 'title' );

				if ( document_type == type  ) {
					$current.data( 'type', type );
					$current.text( title ).append( '<span class="arrow-down">&#9660;</span>' );
				}
			} );
		}
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

	// Load the Preview with AJAX
	function ajax_load_preview( order_id, document_type ) {
		let form_data = $( '#wpo-wcpdf-settings' ).serialize();
		let nonce     = $preview.data('nonce');
		let worker    = wpo_wcpdf_admin.pdfjs_worker;
		let canvas_id = 'preview-canvas';
		let data      = {
			security:      nonce,
			action:        'wpo_wcpdf_preview',
			order_id:      order_id,
			document_type: document_type,
			data:          form_data,
		};

		// remove previous error notices
		$preview.children( '.notice' ).remove();

		// if we don't have an order_id, let's finish here
		if( order_id.length === 0 ) {
			let no_order_message = $preview.data( 'no_order' );
			$preview.find( 'canvas' ).remove();
			$preview.append( '<div class="notice notice-error inline"><p>'+no_order_message+'</p></div>' );
			return;
		}

		// block ui
		$preview.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		$.ajax({
			type:    'POST',
			url:     wpo_wcpdf_admin.ajaxurl,
			data:    data,
			success: function( response ) {
				if( response.data.error ) {
					$( '#'+canvas_id ).remove();
					$preview.append( '<div class="notice notice-error inline"><p>'+response.data.error+'</p></div>' );
				} else if( response.data.pdf_data ) {
					$( '#'+canvas_id ).remove();
					$preview.append( '<canvas id="'+canvas_id+'" style="width:100%;"></canvas>' );
					pdf_js( worker, canvas_id, response.data.pdf_data );
				}

				// set document selector default if needed
				set_document_selector_default( document_type );

				$preview.unblock();

				$preview.data( 'order_id', lastOrderId ); // reset preview data to 'lastOrderId'
			},
		});
	}

	// pdf_js (third party library code)
	function pdf_js( worker, canvas_id, pdf_data ) {
		// atob() is used to convert base64 encoded PDF to binary-like data.
		// (See also https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding.)
		var pdfData = atob( pdf_data );

		// Loaded via <script> tag, create shortcut to access PDF.js exports.
		var pdfjsLib = window['pdfjs-dist/build/pdf'];

		// The workerSrc property shall be specified.
		pdfjsLib.GlobalWorkerOptions.workerSrc = worker;

		// Using DocumentInitParameters object to load binary data.
		var loadingTask = pdfjsLib.getDocument({data: pdfData});
		loadingTask.promise.then(function(pdf) {
			console.log('PDF loaded');
			
			// Fetch the first page
			var pageNumber = 1;
			pdf.getPage(pageNumber).then(function(page) {
				console.log('Page loaded');
				
				var scale = 2;
				var viewport = page.getViewport({scale: scale});

				// Prepare canvas using PDF page dimensions
				var canvas = document.getElementById(canvas_id);
				var context = canvas.getContext('2d');

				canvas.height = viewport.height;
				canvas.width = viewport.width;

				// Render PDF page into canvas context
				var renderContext = {
					canvasContext: context,
					viewport: viewport
				};
				var renderTask = page.render(renderContext);
				renderTask.promise.then(function () {
					console.log('Page rendered');
				});
			});
		}, function (reason) {
			// PDF loading error
			console.error(reason);
			}
		);
	}

	let previewSearchTimeout;

	// Preview on user input
	$( '#preview-order-search' ).on( 'keyup paste', function( event ) {
		let elem = $(this);
		elem.addClass( 'ajax-waiting' );
		let div  = elem.closest( '.preview-data' ).find( '#preview-order-search-results' );
		div.children( 'a' ).remove();                                          // remove previous results
		div.children( '.error' ).remove();                                     // remove previous errors
		elem.closest( '.preview-data' ).find( '#preview-order-search-results' ).hide();
		elem.closest( 'div' ).find( 'img.preview-order-search-clear' ).hide(); // remove the clear button

		let duration  = event.type == 'keyup' ? 1000 : 0;
		clearTimeout( previewSearchTimeout );
		previewSearchTimeout = setTimeout( function() { preview_order_search( elem ) }, duration );
	} );

	// Preview order search
	function preview_order_search( elem ) {
		let div  = elem.closest( '.preview-data' ).find( '#preview-order-search-results' );
		let data = {
			security: elem.data('nonce'),
			action:   "wpo_wcpdf_preview_order_search",
			search:   elem.val(), 
		};

		$.ajax({
			type:    'POST',
			url:     wpo_wcpdf_admin.ajaxurl,
			data:    data,
			success: function( response ) {
				if( response.data ) {
					if( response.data.error ) {
						div.append( '<span class="error">'+response.data.error+'</span>' );
						div.show();
					} else {
						$.each( response.data, function ( i, item ) {
							let first_line = '<a data-order_id="'+i+'"><span class="order-number">#'+item.order_number+'</span> - '+item.billing_first_name+' '+item.billing_last_name;
							if( item.billing_company.length > 0 ) {
								first_line = first_line+', '+item.billing_company;
							}
							let second_line = '<br><span class="date">'+item.date_created+'</span><span class="total">'+item.total+'</span></a>';
							div.append( first_line+second_line );
							div.show();
						});
					}
				}

				elem.removeClass( 'ajax-waiting' );
				elem.closest( 'div' ).find( 'img.preview-order-search-clear' ).show();
			}
		});
	}

});