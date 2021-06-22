jQuery( function( $ ) {
	$('a').each(function(e){
		// for lack of specific classes on the my account action buttons we check the url
		if( $(this).attr('href').indexOf('generate_wpo_wcpdf') != -1 ){
			$(this).attr('target', '_blank');
		};
	});
});