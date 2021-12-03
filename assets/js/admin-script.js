jQuery( function( $ ) {
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
			security:      $input.data('nonce'),
			action:        "wpo_wcpdf_set_next_number",
			store:         $input.data('store'),
			number:        $input.val(), 
		};

		xhr = $.ajax({
			type:		'POST',
			url:		wpo_wcpdf_admin.ajaxurl,
			data:		data,
			success:	function( response ) {
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

    //Preview
    let previewStates = $('#wpo-wcpdf-preview-wrapper').attr('data-preview-states');
    
    $('.slide-left').on( 'click', function() {
		let $wrapper = $(this).closest('#wpo-wcpdf-preview-wrapper');
		let previewState = $wrapper.attr('data-preview-state');
		if ( previewStates == 3 ) {
			previewState == 'closed' ? $wrapper.attr('data-preview-state', 'sidebar') : $wrapper.attr('data-preview-state', 'full');
		} else {
			$wrapper.attr('data-preview-state', 'full');
		}
	});

	$('.slide-right').on( 'click', function() {
		let $wrapper = $(this).closest('#wpo-wcpdf-preview-wrapper');
		let previewState = $wrapper.attr('data-preview-state');
		if ( previewStates == 3 ) {
			previewState == 'full' ? $wrapper.attr('data-preview-state', 'sidebar') : $wrapper.attr('data-preview-state', 'closed');
		} else {
			$wrapper.attr('data-preview-state', 'closed');
		}	
	});

	$('.preview-document .preview-data p').on( 'click', function() {
		let $previewData = $(this).closest('.preview-data');
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
		} else if ( $(this).hasClass('order-search') ) {
			$previewData.find('p.last-order').hide();
			$previewData.find('p.order-number').hide();
			$previewData.find('p.order-search').show();
			$previewData.find('input[name="preview-order-search"]').addClass('active');
			$previewData.find('input[name="preview-order-number"]').removeClass('active');
		} else {
			$previewData.find('p.last-order').show();
			$previewData.find('p.order-number').hide();
			$previewData.find('p.order-search').hide();
			$previewData.find('input[name="preview-order-number"]').removeClass('active');
			$previewData.find('input[name="preview-order-search"]').removeClass('active');
		}
	});

	let wcpdf_preview;

	// Preview on page load
	$( document ).ready( ajax_load_preview( $( '#wpo-wcpdf-settings' ).serialize(), $( '#wpo-wcpdf-preview-wrapper .preview' ) ) );

	// Preview on user input
	$( '#wpo-wcpdf-settings input, #wpo-wcpdf-settings textarea, #wpo-wcpdf-settings select, #wpo-wcpdf-settings checkbox, #preview-order-number' ).on( 'keyup paste', function( event ) {
		let elem      = $(this);
		let form_data = elem.closest( '#wpo-wcpdf-settings' ).serialize();
		let duration  = event.type == 'keyup' ? 1000 : 0;
		clearTimeout( wcpdf_preview );
		wcpdf_preview = setTimeout( function() { ajax_load_preview( form_data, elem ) }, duration );
	} );

	// Preview on user select option (using 'change' event breaks the PDF render)
	$( '#wpo-wcpdf-settings select option' ).on( 'click', function( event ) {
		event.preventDefault();
		let elem      = $(this);
		let form_data = elem.closest( '#wpo-wcpdf-settings' ).serialize();
		let duration  = event.type == 'click' ? 1000 : 0;
		clearTimeout( wcpdf_preview );
		wcpdf_preview = setTimeout( function() { ajax_load_preview( form_data, elem ) }, duration );
	} );

	function ajax_load_preview( form_data, elem ) {
		let preview   = $( '#wpo-wcpdf-preview-wrapper .preview' );
		let order_id  = preview.data( 'order_id' );
		if( elem[0].id == 'preview-order-number' ) {
			order_id = elem.val();
		}
		let nonce     = preview.data('nonce');
		let worker    = wpo_wcpdf_admin.pdfjs_worker;
		let canvas_id = 'preview-canvas';
		let data      = {
			security:  nonce,
			action:    'wpo_wcpdf_preview',
			order_id:  order_id,
			data:      form_data,
		};

		// block ui
		preview.block( {
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		} );

		$.ajax({
			type:     'POST',
			url:      wpo_wcpdf_admin.ajaxurl,
			data:     data,
			success: function( response ) {
				if( response.data.error ) {
					$( '#'+canvas_id ).remove();
					preview.append( '<div class="notice notice-error inline" style="margin:0;"><p>'+response.data.error+'</p></div>' );
				} else if( response.data.pdf_data ) {
					$( '#'+canvas_id ).remove();
					preview.append( '<canvas id="'+canvas_id+'" style="width:100%;"></canvas>' );
					pdf_js( worker, canvas_id, response.data.pdf_data );
				}
				preview.unblock();
			},
		});
	}

	function pdf_js( worker, canvas_id, pdf_data ) {
		// atob() is used to convert base64 encoded PDF to binary-like data.
		// (See also https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/
		// Base64_encoding_and_decoding.)
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
				
				var scale = 1.5;
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

});