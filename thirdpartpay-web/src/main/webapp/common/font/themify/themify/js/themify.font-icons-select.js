/* Routines to manage font icons in theme settings and custom panel. */

;var Themify_Icons = {};

(function($){

	'use strict';

	Themify_Icons = {

		target: '',

		init: function() {

			var $body = $('body');

			$( '#themify_lightbox_overlay, #themify_lightbox_fa' ).appendTo( 'body' );

			$body.on('click', '.themify_fa_toggle', function(e){
				e.preventDefault();
				var $self = $( this );
				if( $self.attr('data-target') ) {
					Themify_Icons.target = $( $self.attr('data-target') );
				} else {
					Themify_Icons.target = $self.prev();
				}
				Themify_Icons.showLightbox( Themify_Icons.target.val() );
			});

			$body.on('click', '#themify_lightbox_fa .lightbox_container a', function(e){
				e.preventDefault();
				Themify_Icons.setIcon( $(this).text().replace( '(alias)', '' ).trim() );
			});

			$body.on('click', '#themify_lightbox_overlay, #themify_lightbox_fa .close_lightbox', function(e){
				e.preventDefault();
				Themify_Icons.closeLightbox();
			});

		},

		getDocHeight: function() {
			var D = document;
			return Math.max(
				Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
				Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
				Math.max(D.body.clientHeight, D.documentElement.clientHeight)
			);
		},

		showLightbox: function( selected ) {
			Themify_Icons.loadIconsList( function(){
				var top = $(document).scrollTop() + 80,
					$lightbox = $("#themify_lightbox_fa"),
					$lightboxOverlay = $('#themify_lightbox_overlay'),
					$body = $('body');

				$lightboxOverlay.show();
				$lightbox
				.show()
				.css('top', Themify_Icons.getDocHeight())
				.animate({
					'top': top
				}, 800 );
				if( selected ) {
					$('a', $lightbox)
					.removeClass('selected')
					.find('.' + selected)
					.closest('a')
						.addClass('selected');
				}

				// Position lightbox correctly in Builder
				if ( $body.hasClass('themify_builder_active') && $body.hasClass('frontend') ) {
					var $tbOverlay = $('#themify_builder_overlay');
					if ( $tbOverlay.length > 0 ) {
						$lightboxOverlay.insertAfter($tbOverlay);
						$lightbox.insertAfter($tbOverlay);
					}
				}
			});
		},

		loadIconsList : function( callback ){
			if( $( '#themify_lightbox_fa' ).hasClass( 'has-loaded' ) ) {
				callback();
			} else {
				$.ajax({
					url : themifyIconPicker.icons_list,
					type : 'GET',
					success : function( data ){
						$( '#themify_lightbox_fa' ).addClass( 'has-loaded' ).find( '.lightbox_container' ).append( data );
						callback();
					}
				});
			}
		},

		setIcon: function(iconName) {
			var $target = $(Themify_Icons.target);
			$target.val( 'fa-' + iconName );
			if ( $('.fa:not(.icon-close)', $target.parent().parent()).length > 0 ) {
				$('.fa:not(.icon-close)', $target.parent().parent()).removeClass().addClass( 'fa fa-' + iconName );
			}
			Themify_Icons.closeLightbox();
		},

		closeLightbox: function() {
			$('#themify_lightbox_fa').animate({
				'top': Themify_Icons.getDocHeight()
			}, 800, function() {
				$('#themify_lightbox_overlay').hide();
				$('#themify_lightbox_fa').hide();
			});
		}

	};

	$(document).ready(function(){
		Themify_Icons.init();
	});

	$('body').on('builderscriptsloaded.themify', function(e){
		Themify_Icons.init();
	});

})(jQuery);