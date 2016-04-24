(function($){

	'use strict';

	var fieldData = '',
		Builder_WPSEO_Content_Analysis = function() {
			YoastSEO.app.registerPlugin( 'Themify_Builder_Content_Analysis', {status: 'loading'} );
			this.appendBuilderData();
		}
	 
	Builder_WPSEO_Content_Analysis.prototype.appendBuilderData = function(  ) {
		var $this = this;
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: {
				action : 'wpseo_get_html_builder',
				nonce : themifyBuilder.tfb_load_nonce,
				post_id : $('input#post_ID').val()
			},
			success: function( text ){
				YoastSEO.app.showLoadingDialog();
				$this.setFieldData(text);
				YoastSEO.app.refresh();
			}
		});	
	};

	Builder_WPSEO_Content_Analysis.prototype.setFieldData = function( data ) {

		YoastSEO.app.pluginReady( 'Themify_Builder_Content_Analysis' );
		fieldData = data;
		this.registerModification();

	};

	Builder_WPSEO_Content_Analysis.prototype.getFieldData = function( data ) {
		var newData = data + ' ' + fieldData;
		YoastSEO.app.callbacks.getData();
		YoastSEO.app.rawData.text = newData;
		return newData;
	};

	Builder_WPSEO_Content_Analysis.prototype.registerModification = function( data ) {
		YoastSEO.app.registerModification( 'content', this.getFieldData, 'Themify_Builder_Content_Analysis', 50 );	
	};

	$(function(){
		if( TBuilderPluginCompat.wpseo_active ) {
			new Builder_WPSEO_Content_Analysis();
		}
	});
	/*$(window).on('YoastSEO:ready', function(){
		new Builder_WPSEO_Content_Analysis();
	});*/
})(jQuery);