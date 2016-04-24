// Themify Lightbox and Fullscreen /////////////////////////
var ThemifyGallery = {};

(function($){

	'use strict';

ThemifyGallery = {
	
	config: {
		fullscreen: themifyScript.lightbox.fullscreenSelector,
		lightbox: themifyScript.lightbox.lightboxSelector,
		lightboxGallery: themifyScript.lightbox.gallerySelector,
		lightboxContentImages: themifyScript.lightbox.lightboxContentImagesSelector,
		context: document
	},
	
	init: function(config){
		if (config && typeof config == 'object') {
			$.extend(ThemifyGallery.config, config);
		}
		if (config.extraLightboxArgs && typeof config == 'object') {
			for (var attrname in config.extraLightboxArgs) {
				themifyScript.lightbox[attrname] = config.extraLightboxArgs[attrname];
			}
		}
		this.parseArgs();
		this.doLightbox();
	},
	parseArgs: function(){
		$.each(themifyScript.lightbox, function(index, value){
			if( 'false' == value || 'true' == value ){
				themifyScript.lightbox[index] = 'false'!=value;
			} else if( parseInt(value) ){
				themifyScript.lightbox[index] = parseInt(value);
			} else if( parseFloat(value) ){
				themifyScript.lightbox[index] = parseFloat(value);
			}
		});
	},
	
	doLightbox: function(){
		var context = this.config.context;
		
		if(typeof $.fn.magnificPopup !== 'undefined' && typeof themifyScript.lightbox.lightboxOn !== 'undefined') {
			
			// Lightbox Link
			$(context).on('click', ThemifyGallery.config.lightbox, function(event){
				event.preventDefault();
				var $self = $(this),
					$link = ( $self.find( '> a' ).length > 0 ) ? $self.find( '> a' ).attr( 'href' ) : $self.attr('href'),
					$type = ThemifyGallery.getFileType($link),
					$title = (typeof $(this).children('img').attr('alt') !== 'undefined') ? $(this).children('img').attr('alt') : $(this).attr('title'),
					$iframe_width = (ThemifyGallery.isVideo($link)) ? '100%' : (ThemifyGallery.getParam('width', $link)) ? ThemifyGallery.getParam('width', $link) : '94%',
					$iframe_height = (ThemifyGallery.isVideo($link)) ? '100%' : (ThemifyGallery.getParam('height', $link)) ? ThemifyGallery.getParam('height', $link) : '100%';
					if($iframe_width.indexOf("%") == -1) $iframe_width += 'px';
					if($iframe_height.indexOf("%") == -1) $iframe_height += 'px';

				if( ThemifyGallery.isYoutube( $link ) ) {
					// for youtube videos, sanitize the URL properly
					$link = ThemifyGallery.getYoutubePath( $link );
				}
				var $args = {
					items: {
						src: $link,
						title: $title
					},
					type: $type,
					iframe: {
						markup: '<div class="mfp-iframe-scaler" style="max-width: '+$iframe_width+' !important; height: '+$iframe_height+';">'+
						'<div class="mfp-close"></div>'+
						'<div class="mfp-iframe-wrapper">'+
						'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						'</div>'+
						'</div>'
					}
				};
				if($self.find('img').length > 0) {
					$.extend( $args, {
						mainClass: 'mfp-with-zoom',
						zoom: {
							enabled: true,
							duration: 300,
							easing: 'ease-in-out',
							opener: function() {
								return $self.find('img');
							}
						}
					});
				}
				if(ThemifyGallery.isVideo($link)){
					$args['mainClass'] += ' video-frame';
				} else {
					$args['mainClass'] += ' standard-frame';
				}
				if(ThemifyGallery.isInIframe()) {
					window.parent.jQuery.magnificPopup.open($args);
				} else {
					$.magnificPopup.open($args);
				}
			});
			
			// Images in post content
			$(themifyScript.lightbox.contentImagesAreas, context).each(function() {
				var images = [],
					links = [];
				if(themifyScript.lightbox.lightboxContentImages && themifyScript.lightbox.lightboxGalleryOn){
					$(ThemifyGallery.config.lightboxContentImages, $(this)).filter( function(){
						if(!$(this).parent().hasClass('gallery-icon') && !$(this).hasClass('themify_lightbox')){
							links.push($(this));
							var description = $(this).attr('title');
							if($(this).next('.wp-caption-text').length > 0){
								// If there's a caption set for the image, use it
								description = $(this).next('.wp-caption-text').html();
							} else {
								// Otherwise, see if there's an alt attribute set
								description = $(this).children('img').attr('alt');
							}
							images.push({ src: $(this).attr('href'), title: description, type: 'image' });
							return $(this);
						}
					}).each(function(index) {
						if (links.length > 0) {
							$(this).on('click', function(event){
								event.preventDefault();
								var $self = $(this);
								var $args = {
									items: {
										src: images[index].src,
										title: images[index].title
									},
									type: 'image'
								};
								if($self.find('img').length > 0) {
									$.extend( $args, {
										mainClass: 'mfp-with-zoom',
										zoom: {
											enabled: true,
											duration: 300,
											easing: 'ease-in-out',
											opener: function() {
												return $self.find('img');
											}
										}
									});
								}
								if(ThemifyGallery.isInIframe()) {
									window.parent.jQuery.magnificPopup.open($args);
								} else {
									$.magnificPopup.open($args);
								}
							});
						}
					});
				}
			});
			
			// Images in WP Gallery
			if(themifyScript.lightbox.lightboxGalleryOn){
				$(context).on('click', ThemifyGallery.config.lightboxGallery, function(event){
					event.preventDefault();
					var $gallery = $(ThemifyGallery.config.lightboxGallery, $(this).parent().parent().parent()),
						images = [];
					$gallery.each(function() {
						var description = $(this).attr('title');
						if($(this).parent().next('.gallery-caption').length > 0){
							// If there's a caption set for the image, use it
							description = $(this).parent().next('.wp-caption-text').html();
						} else if ( $(this).children('img').length > 0 ) {
							// Otherwise, see if there's an alt attribute set
							description = $(this).children('img').attr('alt');
						} else if ( $(this).find('.gallery-caption').find('.entry-content').length > 0 ) {
							description = $(this).find('.gallery-caption').find('.entry-content').text();
						}
						images.push({ src: $(this).attr('href'), title: description, type: 'image' });
					});
					var $args = {
						gallery: {
							enabled: true
						},
						items: images,
						mainClass: 'mfp-with-zoom',
						zoom: {
							enabled: true,
							duration: 300,
							easing: 'ease-in-out',
							opener: function(openerElement) {
								var imageEl = $($gallery[openerElement.index]);
								return imageEl.is('img') ? imageEl : imageEl.find('img');
							}
						}
					};
					if(ThemifyGallery.isInIframe()){
						window.parent.jQuery.magnificPopup.open($args, $gallery.index(this));
					} else {
						$.magnificPopup.open($args, $gallery.index(this));
					}
				});
			}
		}
	},
	
	countItems : function(type){
		var context = this.config.context;
		if('lightbox' == type) return $(this.config.lightbox, context).length + $(this.config.lightboxGallery, context).length + $(ThemifyGallery.config.lightboxContentImages, context).length;
		else return $(this.config.fullscreen, context).length + $(ThemifyGallery.config.lightboxContentImages, context).length;
	},

	isInIframe: function(){
		if( typeof ThemifyGallery.config.extraLightboxArgs !== 'undefined' ) {
			return typeof ThemifyGallery.config.extraLightboxArgs.displayIframeContentsInParent !== 'undefined' && true == ThemifyGallery.config.extraLightboxArgs.displayIframeContentsInParent;
		} else {
			return false;
		}
	},
	
	getFileType: function( itemSrc ) {
		if ( itemSrc.match( /\.(gif|jpg|jpeg|tiff|png)$/i ) ) {
			return 'image';
		} else if(itemSrc.match(/\bajax=true\b/i)) {
			return 'ajax';
		} else if(itemSrc.substr(0,1) == '#') {
			return 'inline';
		} else {
			return 'iframe';
		}
	},
	
	isVideo: function( itemSrc ) {
		return ThemifyGallery.isYoutube( itemSrc )
			|| itemSrc.match(/vimeo\.com/i) || itemSrc.match(/\b.mov\b/i)
			|| itemSrc.match(/\b.swf\b/i);
	},

	isYoutube : function( itemSrc ) {
		return itemSrc.match( /youtube\.com\/watch/i ) || itemSrc.match( /youtu\.be/i );
	},

	getYoutubePath : function( url ) {
		if( url.match( /youtu\.be/i ) ) {
			// convert youtu.be/ urls to youtube.com
			return '//youtube.com/watch?v=' + url.match( /youtu\.be\/([^\?]*)/i )[1];
		} else {
			return '//youtube.com/watch?v=' + ThemifyGallery.getParam( 'v', url );
		}
	},
	
	getParam: function(name, url){
		name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
		var regexS = "[\\?&]"+name+"=([^&#]*)";
		var regex = new RegExp(regexS);
		var results = regex.exec(url);
		return(results==null) ? "" : results[1];
	}
};

}(jQuery));