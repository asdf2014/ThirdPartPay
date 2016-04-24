(function($){
	'use strict';

	$(document).ready(function($){
		var file_frame;

		$('#upload-org-logo').on('click', function( event ){

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				file_frame.open();
			return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: 'Upload Logo',
				button: {
					text: 'Set as Logo',
				},
				multiple: false
			});

			// When an image is selected, run a callback.
			file_frame.on('select', function() {
			// We set multiple to false so only get one image from the uploader
			var attachment = file_frame.state().get('selection').first().toJSON();

			// Do something with attachment.id and/or attachment.url here
			$('#user_meta_org_logo').val(attachment.url);
			$('#user_meta_org_logo_width').val(attachment.width);
			$('#user_meta_org_logo_height').val(attachment.height);
			$('#user_meta_org_placeholder').slideDown().attr('src', attachment.url);
			$('#remove-org-logo').fadeIn();
		});

		// Finally, open the modal
		file_frame.open();
		event.preventDefault();
		});
		$('#remove-org-logo').on('click', function(){
			$('#user_meta_org_logo, #user_meta_org_logo_width, #user_meta_org_logo_height').val('');
			$('#user_meta_org_placeholder').slideUp();
			$(this).fadeOut();
		});
	})

})(jQuery);
