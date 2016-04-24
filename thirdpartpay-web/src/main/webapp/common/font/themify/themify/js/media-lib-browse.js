var themifyMediaLib = {};

(function($){

'use strict';

themifyMediaLib = {
	init: function() {
		this.mediaUploader();
	},

	mediaUploader: function() {

		// Uploading files
		var file_frame = '', set_to_post_id = wp.media.model.settings.post.id; // Set this

		jQuery('.themify-media-lib-browse').on('click', function( event ){
			var $el = jQuery(this), $data = $el.data('submit');

			file_frame = wp.media.frames.file_frame = wp.media({
				title: jQuery(this).data('uploader-title'),
				library: {
					type: $el.data('type')
				},
				button: {
					text: jQuery(this).data('uploader-button-text')
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// We set multiple to false so only get one image from the uploader
				var attachment = file_frame.state().get('selection').first().toJSON();
				$data.attach_id = attachment.id;

				// Do something with attachment.id and/or attachment.url here
				jQuery.ajax({
					type: "POST",
					url: ajaxurl,
					data: $data,
					dataType: 'json',
					success: function( data ){
						var post_image_preview = jQuery('<a href="' + data.thumb + '" target="_blank"><img src="' + data.thumb + '" width="40" /></a>')
							.fadeIn(1000)
							.css('display', 'inline-block');
						var data_field = $el.data('fields');
						jQuery('#' + data_field).val(attachment.url);

						if( $el.parents('.themify_field').find('.themify_upload_preview').find('a').length > 0 ) {
							$el.parents('.themify_field').find('.themify_upload_preview').find('a').remove();
						}

						$el.parents('.themify_field').find('.themify_upload_preview').fadeIn().append(post_image_preview);
						$el.parents('.themify_field').find('.themify_featimg_remove').removeClass('hide')
						.find('a').attr('data-attachid', attachment.id);
					}
				});

			});

			// Finally, open the modal
			file_frame.open();
			event.preventDefault();
		});
	}
};

$(document).ready(function(){
	themifyMediaLib.init();
});

})(jQuery);