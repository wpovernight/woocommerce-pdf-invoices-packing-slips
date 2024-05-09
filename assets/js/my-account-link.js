jQuery( function( $ ) {
	$('a').each(function(e){
		// check if href attribute exists
		if ( $(this).attr('href') ) {
			// for lack of specific classes on the my account action buttons we check the url.
			// 'generate_wpo_wcpdf' can be replaced when using the pretty links setting from the status page.
			if( $(this).attr('href').indexOf('generate_wpo_wcpdf') != -1 ){
				$(this).attr('target', '_blank');
			};
		}
	});
});
