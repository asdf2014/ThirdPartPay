;(function($, window, document, undefined) {

	'use strict';

	var TBuilderAdmin = {};

	TBuilderAdmin.savePost = function(callback) {
		var $loader = $('.td-spinner, .td_modal-backdrop');
		$loader.show();

		if(typeof wp == 'undefined' || typeof wp.autosave == 'undefined') {
			callback.call(this);
		}
		else {
			var jqxhr = wp.ajax.post( 'tfb_switch_frontend', {
				nonce : TBuilderAdmin_Settings.tfb_load_nonce,
				data: wp.autosave.getPostData()
			});
			jqxhr.done(callback);
			jqxhr.fail(function(){
				$loader.hide();
			});
		}
	};

	TBuilderAdmin.switchToBuilder = function() {
		var $loader = $('.td-spinner, .td_modal-backdrop');
		window.location.href = TBuilderAdmin_Settings.permalink + '#builder_active';
		$loader.hide();
	};

	// DOM Ready
	$(function(){

		var spinner = '<section class="td-spinner"><div class="spin"></div></section>'+
			'<div class="td_modal-backdrop"></div>';

		$(spinner).appendTo($('body'));

		$('.themify_builder_switch_btn').on('click', function(event){
			event.preventDefault();
			TBuilderAdmin.savePost(TBuilderAdmin.switchToBuilder);
		});
	});

}(jQuery, window, document));