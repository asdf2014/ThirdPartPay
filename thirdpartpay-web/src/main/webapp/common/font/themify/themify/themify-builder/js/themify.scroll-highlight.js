/**
 * Themify Scroll to element based on its class and highlight it when a menu item is clicked.
 * Copyright (c) Themify
 */
;(function( $, window, document ) {

	'use strict';

	if ( ! String.prototype.trim ) {
		String.prototype.trim = function () {
			return this.replace( /^\s+|\s+$/g, '' );
		};
	}

	var pluginName = 'themifyScrollHighlight',
		defaults = {
			speed: parseInt( tbScrollHighlight.speed ),
			prefix: '.tb_section-',
			navigation: tbScrollHighlight.navigation,
			context: 'body',
			element: '.themify_builder_row',
			scrollRate: 250,
			considerHeader: false,
						fixedHeaderHeight:0,
			scroll: 'internal' // can be 'external' so no scroll is done here but by the theme. Example: Fullpane.
		};

	function Plugin( element, options ) {
		this.element = element;
		this.options = $.extend( {}, defaults, options );
		this._defaults = defaults;
				if($('#headerwrap').length>0){
					var  $fixedheader = $('#headerwrap').clone();
					$fixedheader.removeClass('fixed-header')
					.removeAttr('id')
					.css({position: "absolute",left: "-10000px"})
					.appendTo("body");
					this.options.fixedHeaderHeight = $fixedheader.outerHeight(true)+10;//10 is a range only
					$fixedheader.remove();
		}
		this.init();
	}

	Plugin.prototype = {

		getOffset: function(){
			var $wpAdminBar = $('#wpadminbar'),	$headerWrap = $('#headerwrap'),
				scrollOffset = parseInt( tbScrollHighlight.scrollOffset );
			if ( this.options.considerHeader && $headerWrap.length > 0 && $('body').hasClass('has-fixed-header') ) {
				scrollOffset = $headerWrap.outerHeight();
			}
			if ( $wpAdminBar.length > 0 ) {
				scrollOffset += $wpAdminBar.outerHeight();
			}
			return scrollOffset;
		},

		highlightLink: function( hash ){
			this.dehighlightLinks();
			if ( '' != hash ) {
				var $linkHash = $(this.options.navigation).find( 'a[href*="' + hash + '"]' );
				if ( $linkHash.length > 0 ) {
					$linkHash.each(function(){
						var $link = $(this);
						if ( $link.prop('hash') == hash ) {
							$link.parent().addClass( 'current_page_item' );
							/**
							 * Fires event scrollhighlight.themify
							 * Receives anchor with hash
							 */
							$('body').trigger( 'scrollhighlight.themify', [ hash ] );
							return;
						}
					});
				} 
			}
		},

		dehighlightLinks: function() {
			$(this.options.navigation).find( 'a[href*="#"]' ).each(function(){
				$(this).parent().removeClass( 'current_page_item' ).removeClass( 'current-menu-item' );
			});
		},

		isInViewport: function ( obj ) {
			var $t = $(obj);
			if ( 'undefined' === typeof $t.offset() ) {
				return false;
			}
			var $window = $(window),
				windowHeight = $window.height(),
				windowTop = $window.scrollTop(),
				// Divided by X to tell it's visible when the section is half way into viewport
				windowBottom = windowTop + (windowHeight/4),
				eleTop = $t.offset().top,
				eleBottom = eleTop + $t.height();
			return ((eleTop <= windowBottom) && (eleBottom >= windowTop));
		},

		isHash: function( hash ) {
			return hash && '' != hash && '#' != hash;
		},

		removeHash: function() {
			var windowLocation = window.location;
			if ( this.isCorrectHash() && this.isHash( windowLocation.hash ) ) {
				if ( 'replaceState' in history ) {
					history.replaceState( '', document.title, windowLocation.pathname + windowLocation.search );
				}
			}
		},

		changeHash: function ( href ) {
			if ( 'replaceState' in history ) {
				history.replaceState( null, null, href );
			} else {
				var section = href.replace(/^.*#/, '');
				if ( section && '' != section ) {
					var $elem = $('.tb_section-' + section);
					if ( $elem.length > 0 ) {
						var realID = $elem.attr('id');
						$elem.attr('id', realID + 'tmpobjxyz5783a');
						window.location.hash = section;
						$elem.attr('id', realID);
					}
				}
			}
		},

		isCorrectHash: function() {
			var hash = location.hash.slice(1);
			// Compatiblity with Ecwid Plugin
			return ! ! (hash != '' && hash.indexOf('!') === - 1);
		},

		linkScroll: function( obj, href ) {
			var self = this;
			// Set offset from top
			var to = $(obj).offset().top - this.getOffset(),
				hash = obj.replace( self.options.prefix, '#' ),
				speed = this.options.speed,
				$scroll = $('html,body');

			/**
			 * Fires event scrollhighlightstart.themify before the scroll begins.
			 * Receives anchor with hash.
			 */
			$('body').trigger( 'scrollhighlightstart.themify', [ hash ] );

			if ( 'internal' === self.options.scroll ) {
				// Complete callback
				var completeCallback = function(){
					// Highlight link
					self.highlightLink( hash );

					if ( href != window.location.hash ) {
						// Change URL hash
						self.changeHash( href );
					}

					// Set scrolling state
					self.scrolling = false;
				};
				// Animate scroll
				$scroll.stop().animate({
					scrollTop: to
				},{
					duration: speed,
					complete: function(){
						if( tbScrollHighlight.fixedHeaderSelector != '' && $( tbScrollHighlight.fixedHeaderSelector ).length > 0 && $(window).scrollTop() > self.options.fixedHeaderHeight) {
							var height = $( tbScrollHighlight.fixedHeaderSelector ).outerHeight(true),
								to = $( obj ).offset().top - self.getOffset(); // the height of the fixed header can change as we scroll, recalculate the scrollTop
							$scroll.stop().animate({scrollTop: Math.ceil(to - height)}, 300, completeCallback );
						} else {
							completeCallback();
						}
					}
				});
			} else {
				// Highlight link
				self.highlightLink( hash );

				if ( href != window.location.hash ) {
					// Change URL hash
					self.changeHash( href );
				}

				// Set scrolling state
				self.scrolling = false;
			}
		},

		manualScroll: function( elementsToCheck ) {
			if ( elementsToCheck.length > 0 ) {
				if ( $(window).scrollTop() < 50 ) {
					this.dehighlightLinks();
					this.removeHash();
				} else {
					var self = this,
						didHighlight = false,
						href = '';
					$.each(elementsToCheck, function( i, val ){
						if ( self.isInViewport( val ) ) {
							var elemsFirstPass = val.split('.tb_section-');
							if ( elemsFirstPass[1] ) {
								var elemsSndPass = elemsFirstPass[1].split('.' );

								href = '';
								if ( elemsSndPass.length > 1 && elemsSndPass[0] ) {
									href = '#' + elemsSndPass[0];
								} else {
									href = '#' + elemsSndPass;
								}

								// Set highlight state
								didHighlight = true;

								if ( '' != href ) {
									return;
								}
							}
						}
					});
					if ( '#' != href && href != window.location.hash ) {
						// Highlight link
						self.highlightLink( href );

						// Change URL hash
						self.changeHash( href );
					}
					if ( ! didHighlight ) {
						self.dehighlightLinks();
					}
				}
			}
		},

		scrolling: false,

		init: function () {
			var self = this;

			// Smooth Scroll and Link Highlight
			$( this.options.context ).find( 'a[href*="#"]' ).not( 'a[href="#"]').not('a.ab-item').on( 'click', function (e) {
				// Build class to scroll to
				var href = $(this).prop('hash'),
					classToScroll = href.replace(/#/, self.options.prefix);

				// If the section exists in this page
				if ( 1 == $(classToScroll).length ) {
					// Set state
					self.scrolling = true;
					// Perform scroll
					self.linkScroll( classToScroll, href );
					// Avoid link behaviour
					e.preventDefault();
				}
			});

			// Highlight Link when scroll over element
			var elementsToCheck = [];
			// Build list of elements to check visibility
			$('div[class*=' + self.options.prefix.replace('.', '') + ']').not(self.options.exclude).each(function(){
				elementsToCheck.push( '.' + $(this).attr('class').trim().replace(/ /g, '.') );
			});

			// Setup scroll event
			var didScroll = false;
			$(window).scroll(function() {
				didScroll = true;
			});
			setInterval(function() {
				if ( didScroll && ! self.scrolling ) {
					didScroll = false;
					self.manualScroll( elementsToCheck );
				}
			}, self.options.scrollRate);

			// Initial section visibility check and link highlight
			$(window).load(function(){
				if ( self.isHash( window.location.hash ) ) {
					// If there's a hash, scroll to it
					var hash = window.location.hash,
						$linkHash = $( self.options.context ).find( 'a[href*="' + hash + '"]' );
					if ( $linkHash.length > 0 ) {
						$linkHash.each(function(){
							var $link = $(this);
							if ( $link.prop('hash') == hash ) {
								$link.trigger( 'click' );
								return;
							}
						});
					}  else {
						// Build class to scroll to
						var	classToScroll = hash.replace(/#/, self.options.prefix);
						// If the section exists in this page
						if ( 1 == $(classToScroll).length ) {
							// Set state
							self.scrolling = true;
							// Perform scroll
							self.linkScroll( classToScroll, hash );
						}
					}
				} else {
					self.manualScroll( elementsToCheck );
				}
			});
		}
	};

	$.fn[pluginName] = function ( options ) {
		return this.each( function () {
			if ( ! $.data( this, 'plugin_' + pluginName ) ) {
				$.data( this, 'plugin_' + pluginName, new Plugin( this, options ) );
			}
		});
	};

})( jQuery, window, document );