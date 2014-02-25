// Thanks to Mike Jolley!
// http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/

jQuery(document).ready(function($) {
		
	// Uploading files
	var file_frame;
	 
	jQuery('.upload_image_button').live('click', function( event ){

		// get input field id from data-input_id
		input_id = '#'+jQuery( this ).data( 'input_id' );
		input_id_class = '.'+jQuery( this ).data( 'input_id' );
		input_id_clean = jQuery( this ).data( 'input_id' );

		// get remove button text
		remove_button_text = jQuery( this ).data( 'remove_button_text' );
	 
		event.preventDefault();
	 
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		 
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery( this ).data( 'uploader_title' ),
			button: {
				text: jQuery( this ).data( 'uploader_button_text' ),
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});
	 
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			
			// set the value of the input field to the attachment id
			jQuery( input_id ).val(attachment.id);
			
			if (jQuery( '#img-'+input_id_clean ).length){
				jQuery( '#img-'+input_id_clean ).attr("src", attachment.url );
			} else {
				// show image & remove button
				attachment_img = '<img src="'+attachment.url+'" style="display:block" id="img-'+input_id_clean+'"/>';
				remove_button = '<span class="button remove_image_button" data-input_id="'+input_id_clean+'">'+remove_button_text+'</span>';
				jQuery( input_id ).before(attachment_img+remove_button);
				
			}
		});
	 
		// Finally, open the modal
		file_frame.open();
	});
 
	jQuery('.remove_image_button').live('click', function( event ){
		
		
		// get input field from data-input_id
		input_id = '#'+jQuery( this ).data( 'input_id' );
		img_id = '#img-'+jQuery( this ).data( 'input_id' );
	 	
		jQuery( input_id ).val('');
		jQuery( img_id ).remove();
		jQuery( this ).remove();
	});		
});