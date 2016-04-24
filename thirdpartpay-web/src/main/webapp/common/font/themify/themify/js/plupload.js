function themify_create_pluploader(obj) {

	'use strict';

	var $this = obj,
		id1 = $this.attr("id"),
		imgId = id1.replace("plupload-upload-ui", ""),
		haspreset = false,
		haspreview = false,
		tomedia = false,
		topost = false,
		$j = jQuery;
	
	var pconfig = JSON.parse(JSON.stringify(global_plupload_init));
	pconfig["browse_button"] = imgId + pconfig["browse_button"];
	pconfig["container"] = imgId + pconfig["container"];
	pconfig["drop_element"] = imgId + pconfig["drop_element"];
	pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
	pconfig["multipart_params"]["imgid"] = imgId;
	pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");
	
	if($this.hasClass('add-preset')) {
		haspreset = true;
		pconfig["multipart_params"]['haspreset'] = 'haspreset'; 
	}
	if($this.hasClass('add-preview')) {
		haspreview = true;
		pconfig["multipart_params"]['haspreview'] = 'haspreview'; 
	}
	if($this.hasClass('add-to-media')){
		tomedia = true;
		pconfig["multipart_params"]['tomedia'] = 'tomedia';
	}
	if($this.data('postid')) {
		topost = true;
		pconfig["multipart_params"]['topost'] = $this.data('postid');
	}
	if($this.data('fields')) {
		pconfig["multipart_params"]['fields'] = $this.data('fields');
	}
	if($this.data('featured')) {
		pconfig["multipart_params"]['featured'] = $this.data('featured');
	}
	if($this.data('formats')) {
		pconfig['filters'][0]['extensions'] = $this.data('formats');
	}
	
	var uploader = new plupload.Uploader(pconfig);
	
	uploader.bind('Init', function(up) { });
	uploader.init();
	
	uploader.bind('FilesAdded', function(up, files) {
		if($this.data('confirm')) {
			var reply = confirm($this.data('confirm'));
			if(!reply) return;
		}
		up.refresh();
		up.start();
		$j(".alert").addClass("busy").fadeIn(800);
	});
	
	uploader.bind('Error', function(up, error){
		$j('.prompt-box .show-login').hide();
		$j('.prompt-box .show-error').show();
		
		if( -600 == error.code ){
			var errorMessage = themify_lang.filesize_error,
				errorMessageFix = themify_lang.filesize_error_fix;
		}
		
		if($j('.prompt-box .show-error').length > 0){
			$j('.prompt-box .show-error').html('<p class="prompt-error">' + errorMessage + '</p>');
			if(errorMessageFix)
				$j('.prompt-box .show-error').append('<p>' + errorMessageFix + '</p>');
		}
		$j(".overlay, .prompt-box").fadeIn(500);
		
		return;
	});
	
	uploader.bind('FileUploaded', function(up, file, response) {
		var json = JSON.parse(response['response']),
			status = '';
		
		if('200' == response['status'] && !json.error) {
			status = 'done';
		} else {
			status = 'error';
		}
		
		$j(".alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function() {
			$j(this).removeClass(status);
		});
		
		if(json.error){
			$j('.prompt-box .show-login').hide();
			$j('.prompt-box .show-error').show();
			
			if($j('.prompt-box .show-error').length > 0){
				$j('.prompt-box .show-error').html('<p class="prompt-error">' + json.error + '</p>');
				$j('.prompt-box .show-error').append('<p>' + themify_lang.enable_zip_upload + '</p>');
			}
			$j(".overlay, .prompt-box").fadeIn(500);
			return;
		}
		
		$j('#' + file.id).fadeOut();
		
		var response_file = json.file,
		response_url = json.url,
		response_type = json.type;
		
		if('zip' == response_type || 'rar' == response_type || 'plain' == response_type)
			window.location = location.href.replace(location.hash, '');
		else
			$j('#' + imgId).val(response_url);
		
		if(topost){
			var thumb_url = json.thumb;
			var post_image_preview = $j('<a href="' + response_url + '" target="_blank"><img src="' + thumb_url + '" width="40" /></a>')
			.fadeIn(1000)
			.css('display', 'inline-block');
			
			if($j('#' + imgId + 'plupload-upload-ui').closest('.themify_field').children('.themify_upload_preview').find('a').length > 0){
				$j('#' + imgId + 'plupload-upload-ui').closest('.themify_field').children('.themify_upload_preview').find('a').remove();
			}
			$j('#' + imgId + 'plupload-upload-ui').closest('.themify_field').children('.themify_upload_preview').fadeIn().append(post_image_preview);
			$this.closest('.themify_field').children('.themify_featimg_remove').removeClass('hide');
		}
		
		if(haspreset){
			$j('#' + imgId).closest('fieldset').children('.preset').find('img').removeClass('selected');
			
			var title = response_url.replace(/^.*[\\\/]/, '');
			//<span title="' + title + '"></span>
			var new_preset = $j('<a href="#" title="' + title + '"><span title="' + response_file + '"></span><img src="' + response_url + '" alt="' + response_url + '" class="backgroundThumb selected" /></a>')
			.css('display', 'inline-block');
			$j('#' + imgId).closest('fieldset').children('.preset').append(new_preset);
		}
		
		if(haspreview){
			$j('#' + imgId + '-preview').attr('src', response_url);
		}
	});
}

jQuery(document).ready(function($) {

	'use strict';

	var $pluploadUIC = $('.plupload-upload-uic');

	if ( $pluploadUIC.length > 0 ) {
		$pluploadUIC.each(function() {
			themify_create_pluploader($(this));
		});
	}
});