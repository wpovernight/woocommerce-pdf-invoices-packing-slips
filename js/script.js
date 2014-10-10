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
			
			var order_ids=checked.join('x');
			url = wpo_wcpdf_ajax.ajaxurl+'?action=generate_wpo_wcpdf&template_type='+template+'&order_ids='+order_ids+'&_wpnonce='+wpo_wcpdf_ajax.nonce;
			window.open(url,'_blank');
		}
	});
});

