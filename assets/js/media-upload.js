// Thanks to Mike Jolley!
// http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/

jQuery(document).ready(function($) {
		
	// Uploading files
	var file_frame;
	$('#wpo-wcpdf-settings, .wpo-wcpdf-setup').on('click', '.wpo_upload_image_button', function( event ){
		// get corresponding input fields
		$row = $(this).parent();
		$id = $row.find('input#header_logo');
		$logo = $row.find('img#img-header_logo');

		// get remove button text
		remove_button_text = $( this ).data( 'remove_button_text' );
	 
		event.preventDefault();
	 
		// If the media frame already exists, reopen it.
		if ( file_frame ) {
			file_frame.open();
			return;
		}

		 
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'uploader_title' ),
			button: {
				text: $( this ).data( 'uploader_button_text' ),
			},
			multiple: false	// Set to true to allow multiple files to be selected
		});
	 
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
			// We set multiple to false so only get one image from the uploader
			attachment = file_frame.state().get('selection').first().toJSON();
			
			// set the value of the input field to the attachment id
			$id.val(attachment.id);
			
			if ( $logo.length == 0 ) {
				// show image & remove button
				attachment_img = '<img src="'+attachment.url+'" style="display:block" id="img-header_logo"/>';
				remove_button = '<span class="button wpo_remove_image_button" data-input_id="header_logo">'+remove_button_text+'</span>';
				$id.before(attachment_img+remove_button);
			} else {
				$logo.attr("src", attachment.url );
			}
		});
	 
		// Finally, open the modal
		file_frame.open();
	});
 
	$('#wpo-wcpdf-settings').on('click', '.wpo_remove_image_button', function( event ){
		// get corresponding input fields
		$row = $(this).parent();
		$id = $row.find('input#header_logo');
		$logo = $row.find('img#img-header_logo');
	 	
		$id.val('');
		$logo.remove();
		$( this ).remove();
		$( '.attachment-resolution' ).remove();
	});		
});