jQuery(document).ready(function($) {
	$("#doaction, #doaction2").click(function (event) {
		var actionselected = $(this).attr("id").substr(2);
		var action = $('select[name="' + actionselected + '"]').val();
		if ( $.inArray(action, wpo_wcpdf_ajax.bulk_actions) !== -1 ) {
			event.preventDefault();
			var template = action;
			var checked = [];
			$('tbody th.check-column input[type="checkbox"]:checked').each(
				function() {
					checked.push($(this).val());
				}
			);
			
			if (!checked.length) {
				alert('You have to select order(s) first!');
				return;
			}
			
			var order_ids=checked.join('x');

			if (wpo_wcpdf_ajax.ajaxurl.indexOf("?") != -1) {
				url = wpo_wcpdf_ajax.ajaxurl+'&action=generate_wpo_wcpdf&document_type='+template+'&order_ids='+order_ids+'&_wpnonce='+wpo_wcpdf_ajax.nonce;
			} else {
				url = wpo_wcpdf_ajax.ajaxurl+'?action=generate_wpo_wcpdf&document_type='+template+'&order_ids='+order_ids+'&_wpnonce='+wpo_wcpdf_ajax.nonce;
			}

			window.open(url,'_blank');
		}
	});

	$('#wpo_wcpdf-data-input-box').insertAfter('#woocommerce-order-data');

	// enable invoice number edit if user initiated
	$( ".wpo-wcpdf-set-date-number, .wpo-wcpdf-edit-date-number" ).click(function() {
		$form = $(this).closest('.wcpdf-data-fields');
		$form.find(".read-only").hide();
		$form.find(".editable").show();
		$form.find(':input').prop('disabled', false);
	});

	$( ".wcpdf-data-fields .wpo-wcpdf-delete-document" ).click(function() {
		if ( window.confirm( wpo_wcpdf_ajax.confirm_delete ) === false ) {
			return; // having second thoughts
		}

		$form = $(this).closest('.wcpdf-data-fields');
		$.ajax({
			url:     wpo_wcpdf_ajax.ajaxurl,
			data:    {
				action  : 'wpo_wcpdf_delete_document',
				security: $(this).data('nonce'),
				document: $form.data('document'),
				order_id: $form.data('order_id')
			},
			type:    'POST',
			context: $form,
			success: function( response ) {
				if ( response.success ) {
					$(this).find(':input').val("");
					$(this).find('.read-only').hide();
					$(this).find('.wpo-wcpdf-delete-document').hide();
				}
			}
		});
	});

});

