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

	//Match wrapper to settings height
	settingsHeight = $('#wpo-wcpdf-preview-wrapper .sidebar').height();
    $('#wpo-wcpdf-preview-wrapper').height(settingsHeight);

    //Preview
    let previewStates = $('#wpo-wcpdf-preview-wrapper').attr('data-preview-states');
    
    $('.gutter .slide-left').on( 'click', function() {
		let $wrapper = $(this).closest('#wpo-wcpdf-preview-wrapper');
		let previewState = $wrapper.attr('data-preview-state');
		if ( previewStates == 3 ) {
			previewState == 'closed' ? $wrapper.attr('data-preview-state', 'sidebar') : $wrapper.attr('data-preview-state', 'full');
		} else {
			$wrapper.attr('data-preview-state', 'full');
		}
	});

	$('.gutter .slide-right').on( 'click', function() {
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
			$previewData.find('p.order-number').show();
			$previewData.find('input').addClass('active');
		} else {
			$previewData.find('p.last-order').show();
			$previewData.find('p.order-number').hide();
			$previewData.find('input').removeClass('active');
		}
	});

});