// Thanks to Mike Jolley!
// http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/

jQuery(document).ready(function($) {

	// Uploading files
	var file_frame;
	let $settings_wrapper;

	// This function returns the translatable media input field in case translation is present.
	// If the translation is not present, the function will return the media input field.
	let get_media_field = function( self, settings_wrapper, element_id ) {
		let $input = $( '#wpo-wcpdf-settings' ).find( element_id ).filter( function() {
			let parent = self.parent( 'div' );
			return parent.length && parent.attr( 'aria-hidden' ) === 'false';
		} );

		return $input.length ? $input : settings_wrapper.find( element_id );
	};

	$( '#wpo-wcpdf-settings, .wpo-wcpdf-setup' ).on( 'click', '.wpo_upload_image_button', function( event ){
		event.preventDefault();

		// get input wrapper
		$settings_wrapper = $( this ).parent();

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
			// get target elements
			let $input   = get_media_field( $( this ), $settings_wrapper, 'input.media-upload-id' );
			let $preview = get_media_field( $( this ), $settings_wrapper, 'img.media-upload-preview' );

			// We set multiple to false so only get one image from the uploader
			let attachment = file_frame.state().get( 'selection' ).first().toJSON();

			// set the value of the input field to the attachment id and set the image until we have an ajax response
			$input.val( attachment.id );
			if ( $preview.length ) {
				$preview.attr( 'src', attachment.url );
			}

			get_media_field( $( this ), $settings_wrapper, '.attachment-resolution, .attachment-resolution-warning' ).remove();

			// Block the media upload UI until we have a response.
			$settings_wrapper.parent().block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			} );

			let data = {
				security:      $input.data( 'ajax_nonce' ),
				action:        'wpo_wcpdf_get_media_upload_setting_html',
				args:          $input.data( 'settings_callback_args' ),
				attachment_id: attachment.id,
			};

			xhr = $.ajax({
				type:    'POST',
				url:     wpo_wcpdf_admin.ajaxurl,
				data:    data,
				success: function( response ) {
					if ( response && typeof response.success != 'undefined' && response.success === true ) {
						$settings_wrapper.html( response.data );
					}
					$settings_wrapper.removeAttr( 'style' );
					$settings_wrapper.parent().unblock();

					// custom trigger
					$input = get_media_field( $( this ), $settings_wrapper, 'input.media-upload-id' );
					$( document.body ).trigger( 'wpo-wcpdf-media-upload-setting-updated', [ $input ] );
				},
				error: function (xhr, ajaxOptions, thrownError) {
					$settings_wrapper.parent().unblock();
				}
			});

		});

		// Finally, open the modal
		file_frame.open();
	});

	$( '#wpo-wcpdf-settings, .wpo-wcpdf-setup' ).on( 'click', '.wpo_remove_image_button', function( event ){
		// get source & target elements
		let $settings_wrapper = $(this).parent();
		let $input            = $settings_wrapper.find( 'input.media-upload-id' );
		let $preview          = $settings_wrapper.find( 'img.media-upload-preview' );

		// clear all inputs & warnings
		$input.val( '' );
		$preview.remove();
		$( this ).remove();
		get_media_field( $( this ), $settings_wrapper, '.attachment-resolution, .attachment-resolution-warning' ).remove();
	});
});
