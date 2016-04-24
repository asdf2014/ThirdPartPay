/**
 * Tabify
 */
;(function ($) {

	'use strict';

	$.fn.tabify = function () {
		return this.each(function () {
			var tabs = $(this);
			if ( ! tabs.data( 'tabify' ) ) {
				tabs.data( 'tabify', true );
				$('ul.tab-nav:first li:first', tabs).addClass('current');
				$('div:first', tabs).show();
				var tabLinks = $('ul.tab-nav:first li', tabs);
				$(tabLinks).click(function () {
					$(this).addClass('current').attr( 'aria-expanded', 'true' ).siblings().removeClass('current').attr( 'aria-expanded', 'false' );
					$('ul.tab-nav:first', tabs).siblings('.tab-content').hide().attr( 'aria-hidden', 'true' );
					var activeTab = $(this).find('a').attr( 'href' );
					$(activeTab).show().attr( 'aria-hidden', 'false' ).trigger( 'resize' );
					$( 'body' ).trigger( 'tf_tabs_switch', [ activeTab, tabs ] );
					if ( $(activeTab).find('.shortcode.map').length > 0 ) {
						$(activeTab).find('.shortcode.map').each(function(){
							var mapInit = $(this).find('.map-container').data('map'),
								center = mapInit.getCenter();
							google.maps.event.trigger(mapInit, 'resize');
							mapInit.setCenter(center);
						});
					}
					return false;
				});
				$('ul.tab-nav:first', tabs).siblings('.tab-content').find('a[href^="#tab-"]').on('click', function(event){
					event.preventDefault();
					var dest = $(this).prop('hash').replace('#tab-', ''),
						contentID = $('ul.tab-nav:first', tabs).siblings('.tab-content').eq( dest - 1 ).prop('id');
					if ( $('a[href^="#'+ contentID +'"]').length > 0 ) {
						$('a[href^="#'+ contentID +'"]').trigger('click');
					}
				});
			}
		});
	};

	// $('img.photo',this).themifyBuilderImagesLoaded(myFunction)
	// execute a callback when all images have loaded.
	// needed because .load() doesn't work on cached images
	$.fn.themifyBuilderImagesLoaded = function(callback){
		var elems = this.filter('img'),
			len   = elems.length,
			blank = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";

		elems.bind('load.imgloaded',function(){
			if (--len <= 0 && this.src !== blank){
				elems.unbind('load.imgloaded');
				callback.call(elems,this);
			}
		}).each(function(){
			// cached images don't fire load sometimes, so we reset src.
			if (this.complete || this.complete === undefined){
				var src = this.src;
				// webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
				// data uri bypasses webkit log warning (thx doug jones)
				this.src = blank;
				this.src = src;
			}
		});

		return this;
	};
})(jQuery);

/*
 * Parallax Scrolling Builder
 */
(function( $ ){

	'use strict';

	var $window = $(window);
	var windowHeight = $window.height();

	$window.resize(function () {
		windowHeight = $window.height();
	});

	$.fn.builderParallax = function(xpos, speedFactor, outerHeight) {
		var $this = $(this);
		var getHeight;
		var firstTop;
		var paddingTop = 0, resizeId;

		//get the starting position of each element to have parallax applied to it
		$this.each(function(){
			firstTop = $this.offset().top;
		});
		$window.resize(function(){
			clearTimeout(resizeId);
			resizeId = setTimeout(function(){
				$this.each(function(){
					firstTop = $this.offset().top;
				});
			}, 500);
		});

		if (outerHeight) {
			getHeight = function(jqo) {
				return jqo.outerHeight(true);
			};
		} else {
			getHeight = function(jqo) {
				return jqo.height();
			};
		}

		// setup defaults if arguments aren't specified
		if (arguments.length < 1 || xpos === null) xpos = "50%";
		if (arguments.length < 2 || speedFactor === null) speedFactor = 0.1;
		if (arguments.length < 3 || outerHeight === null) outerHeight = true;

		// function to be called whenever the window is scrolled or resized
		function update(){
			var pos = $window.scrollTop();

			$this.each(function(){
				var $element = $(this);
				var top = $element.offset().top;
				var height = getHeight($element);

				// Check if totally above or totally below viewport
				if (top + height < pos || top > pos + windowHeight) {
					return;
				}

				if (isMobile()) {
					/* #3699 = for mobile devices increase background-size-y in 30% (minimum 400px) and decrease background-position-y in 15% (minimum 200px) */
					var outerHeight = $element.outerHeight(true);
					var outerWidth = $element.outerWidth(true);
					var dynamicDifference = outerHeight > outerWidth ? outerHeight : outerWidth;
					dynamicDifference = Math.round(dynamicDifference * 0.15);
					if (dynamicDifference < 200) dynamicDifference = 200;
					$this.css('backgroundSize', "auto " + Math.round(outerHeight + (dynamicDifference * 2)) + "px");
					$this.css('backgroundPosition', xpos + " " + Math.round(((firstTop - pos) * speedFactor) - dynamicDifference) + "px");
				}
				else {
					$this.css('backgroundPosition', xpos + " " + Math.round((firstTop - pos) * speedFactor) + "px");
				}
			});
		}

		function isMobile() {
			var isTouchDevice = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/);
			return isTouchDevice;
		}

		$window.bind('scroll', update).resize(update);
		update();
	};
})(jQuery);

var ThemifyBuilderModuleJs;
(function ($, window, document, undefined) {

	'use strict';

	ThemifyBuilderModuleJs = {
		fwvideos: [], // make it accessible to public
		init: function () {
			this.setupBodyClasses();
			this.bindEvents();
			this.makeColumnsEqualHeight();
		},
		bindEvents: function () {
			if ('complete' !== document.readyState) {
				$(document).ready(this.document_ready);
			} else {
				this.document_ready();
			}
			if (window.loaded) {
				this.window_load();
			} else {
				$(window).load(this.window_load);
			}
			$(window).bind('hashchange', this.tabsDeepLink);
		},
		/**
		 * Executed on jQuery's document.ready() event.
		 */
		document_ready: function () {
			var self = ThemifyBuilderModuleJs;
			if (tbLocalScript.fullwidth_support == '') {
				self.setupFullwidthRows();
				$(window).resize(function(e) {
					if (e.target === window) {
						self.setupFullwidthRows()
					}
				});
			}

			self.InitCSS();
			Themify.bindEvents();
			self.touchdropdown();
			self.accordion();
			self.tabs();
			self.rowCover();
			self.fallbackRowId();
			self.onInfScr();
			self.InitScrollHighlight();

		},
		/**
		 * Executed on JavaScript 'load' window event.
		 */
		window_load: function () {
			var self = ThemifyBuilderModuleJs;
			window.loaded = true;
			self.carousel();
			self.tabsDeepLink();
			self.charts();
			self.backgroundSlider();
			if (tbLocalScript.isParallaxActive) self.backgroundScrolling();
			if (self._isTouch()) {
				self.fullheight();
				return;
			}
			self.fullwidthVideo();
		},
		setupFullwidthRows: function () {
			var container = $(tbLocalScript.fullwidth_container);
			$('div.themify_builder_row.fullwidth').each(function () {
				var row = $(this).closest('.themify_builder_content');

				var left = row.offset().left - container.offset().left;
				var right = container.outerWidth() - left - row.outerWidth();
				$(this).css({
					'margin-left': -left,
					'margin-right': -right,
					'padding-left': left,
					'padding-right': right,
					'width': container.outerWidth() + 'px'
				});
			});
		},
		makeColumnsEqualHeight: function () {

			function computeEqualColHeight($columns) {
				var maxHeight = 0;
				$columns.each(function () {
					var $column = $(this);
					$column.css('min-height', '');
					if ($column.height() > maxHeight) {
						maxHeight = $column.height();
					}
				});

				return maxHeight;
			}

			var $rowsWithEqualColumnHeight = $('.themify_builder_row.equal-column-height');
			var $subrowsWithEqualColumnHeight = $('.themify_builder_sub_row.equal-column-height');

			$rowsWithEqualColumnHeight.each(function () {
				var $cols = $(this).find('.row_inner').first().children('.module_column');
				var maxColHeight = computeEqualColHeight($cols);

				$cols.each(function () {
					$(this).css('min-height', maxColHeight);
				});
			});

			$subrowsWithEqualColumnHeight.each(function () {
				var $cols = $(this).children('.sub_column');
				var maxColHeight = computeEqualColHeight($cols);

				$cols.each(function () {
					$(this).css('min-height', maxColHeight);
				});
			});
		},
		fallbackRowId: function () {
			$('.themify_builder_content').each(function () {
				var index = 0;
				$(this).find('.themify_builder_row').each(function () {
					if (!$(this).attr('class').match(/module_row_\d+/)) {
						$(this).addClass('module_row_' + index);
					}
					index++;
				});
			});
		},
		addQueryArg: function (e, n, l) {
			l = l || window.location.href;
			var r, f = new RegExp("([?&])" + e + "=.*?(&|#|$)(.*)", "gi");
			if (f.test(l))
				return"undefined" != typeof n && null !== n ? l.replace(f, "$1" + e + "=" + n + "$2$3") : (r = l.split("#"), l = r[0].replace(f, "$1$3").replace(/(&|\?)$/, ""), "undefined" != typeof r[1] && null !== r[1] && (l += "#" + r[1]), l);
			if ("undefined" != typeof n && null !== n) {
				var i = -1 !== l.indexOf("?") ? "&" : "?";
				return r = l.split("#"), l = r[0] + i + e + "=" + n, "undefined" != typeof r[1] && null !== r[1] && (l += "#" + r[1]), l
			}
			return l
		},
		onInfScr: function () {
			var self = ThemifyBuilderModuleJs;
			$(document).ajaxSend(function (e, request, settings) {
				var page = settings.url.replace(/^(.*?)(\/page\/\d+\/)/i, '$2'),
						regex = /^\/page\/\d+\//i,
						match;

				if ((match = regex.exec(page)) !== null) {
					if (match.index === regex.lastIndex) {
						regex.lastIndex++;
					}
				}

				if (null !== match) {
					settings.url = self.addQueryArg('themify_builder_infinite_scroll', 'yes', settings.url);
				}
			});
		},
		InitCSS: function () {
			// Enqueue builder style and assets before theme style.css
			var refs = (window.document.getElementsByTagName("head")[ 0 ]).childNodes,
					ref = refs[ refs.length - 1];

			for (var i = 0; i < refs.length; i++) {
				if ('LINK' == refs[i].nodeName && 'stylesheet' == refs[i].rel && refs[i].href.indexOf('style.css') > -1) {
					ref = refs[i];
					break;
				}
			}
			Themify.LoadCss(tbLocalScript.builder_url + '/css/animate.min.css', null, ref);
			if ($('.module-image').length > 0 || $('.module-slider').length > 0 || $('.module-feature .module-feature-chart-html5').length > 0) {
				Themify.LoadCss(themify_vars.url + '/fontawesome/css/font-awesome.min.css', tbLocalScript.version);
			}

		},
		InitScrollHighlight: function () {
			if (tbLocalScript.loadScrollHighlight == true && $('div[class*=tb_section-]').length > 0) {
				Themify.LoadAsync(tbLocalScript.builder_url + '/js/themify.scroll-highlight.js', this.ScrollHighlightCallBack, null, null, function () {
					return('undefined' !== typeof $.fn.themifyScrollHighlight);
				});
			}
		},
		ScrollHighlightCallBack: function () {
			$('body').themifyScrollHighlight(themifyScript.scrollHighlight ? themifyScript.scrollHighlight : {});
		},
		// Row, col, sub-col: Background Slider
		backgroundSlider: function ($bgSlider) {
			$bgSlider = $bgSlider || $('.row-slider, .col-slider, .sub-col-slider');

			if ($bgSlider.length) {
				Themify.LoadAsync(
					themify_vars.url+'/js/backstretch.js',
					function() { this.backgroundSliderCallBack($bgSlider); }.bind(this),
					null,
					null,
					function() { return ('undefined' !== typeof $.fn.backstretch); }
				);
			}
		},
		// Row, col, sub-col: Background Slider
		backgroundSliderCallBack: function ($bgSlider) {
			var themifySectionVars = {
				autoplay: tbLocalScript.backgroundSlider.autoplay,
				speed: tbLocalScript.backgroundSlider.speed
			};

			// Parse injected vars
			themifySectionVars.autoplay = parseInt(themifySectionVars.autoplay, 10);
			if (themifySectionVars.autoplay <= 10) {
				themifySectionVars.autoplay *= 1000;
			}
			themifySectionVars.speed = parseInt(themifySectionVars.speed, 10);

			if ($bgSlider.length > 0) {

				// Initialize slider
				$bgSlider.each(function () {
					var $thisRowSlider = $(this),
							$backel = $thisRowSlider.parent(),
							rsImages = [],
							bgMode = $thisRowSlider.data('bgmode');

					// Initialize images array with URLs
					$thisRowSlider.find('li').each(function () {
						rsImages.push($(this).attr('data-bg'));
					});

					// Call backstretch for the first time
					$backel.backstretch(rsImages, {
						fade: themifySectionVars.speed,
						duration: themifySectionVars.autoplay,
						mode: bgMode
					});

					// Needed for col styling icon and row grid menu to be above row and sub-row top bars.
					if (ThemifyBuilderModuleJs.isBuilderActive()) {
						$backel.css('z-index', 'auto');
					}

					// Fix for navigation dots.
					if ($backel.hasClass('module_column')) {
						var $closestRowSliderNavigation = $backel.closest('.themify_builder_row')
								.find('.row-slider .row-slider-slides');

						$backel
								.on('mouseover', function () {
									$closestRowSliderNavigation.css('z-index', 0);
								})
								.on('mouseout', function () {
									$closestRowSliderNavigation.css('z-index', 1);
								});
					}

					// Cache Backstretch object
					var thisBGS = $backel.data('backstretch');

					// Previous and Next arrows
					$thisRowSlider.find('.row-slider-prev').on('click', function (e) {
						e.preventDefault();
						thisBGS.prev();
					});
					$thisRowSlider.find('.row-slider-next').on('click', function (e) {
						e.preventDefault();
						thisBGS.next();
					});

					// Dots
					$thisRowSlider.find('.row-slider-dot').each(function () {
						var $dot = $(this);
						$dot.on('click', function () {
							thisBGS.show($dot.data('index'));
						});
					});
				});
			}
		},
		// Row: Fullwidth video background
		fullwidthVideo: function ($videoElmt) {
			$videoElmt = $videoElmt || $('.themify_builder_row[data-fullwidthvideo], .module_column[data-fullwidthvideo], .sub_column[data-fullwidthvideo]');

			if ($videoElmt.length > 0 && !this._checkBrowser('opera')) {
				var self = this;
				Themify.LoadAsync(themify_vars.url + '/js/video.js', function () {
					Themify.LoadAsync(
						themify_vars.url + '/js/bigvideo.js',
						function() { self.fullwidthVideoCallBack($videoElmt); },
						null,
						null,
						function () { return ('undefined' !== typeof $.BigVideo); }
					);
				});
			}

		},
		// Row: Fullwidth video background
		fullwidthVideoCallBack: function ($videos) {
			var self = ThemifyBuilderModuleJs;
			$.each($videos, function (i, elm) {
				var $video = $(elm), loop = true, mute = false;
				// If data-unloopvideo or unmutevideo exist is because they have a certain value.
				if ('undefined' !== typeof $video.data('unloopvideo')) {
					loop = 'loop' === $video.data('unloopvideo');
				} else {
					// Backwards compatibility
					loop = 'yes' === tbLocalScript.backgroundVideoLoop;
				}
				if ('undefined' !== typeof $video.data('mutevideo')) {
					mute = 'mute' === $video.data('mutevideo');
				}
				if ($video.find('.big-video-wrap').length > 0) {
					$video.find('.big-video-wrap:first-child').remove();
				}
				var videoURL = $video.data('fullwidthvideo');

				// Video was removed.
				if (!videoURL.length && typeof self.fwvideos[i] !== 'undefined') {
					self.fwvideos[i].dispose();

					return;
				}

				self.fwvideos[i] = new $.BigVideo({
					doLoop: loop,
					ambient: mute,
					container: $video,
					id: i,
					poster: tbLocalScript.videoPoster
				});
				self.fwvideos[i].init();
				self.fwvideos[i].show(videoURL);
			});

		},
		charts: function () {
			if ($('.module-feature .module-feature-chart-html5').length > 0) {
				var $self = this;
				Themify.LoadAsync(themify_vars.url + '/js/waypoints.min.js', $self.chartsCallBack, null, null, function () {
					return ('undefined' !== typeof $.fn.waypoint);
				});
			}
		},
		chartsCallBack: function () {
			var $self = ThemifyBuilderModuleJs;
			$self.chartsCSS();

			$('.module-feature .module-feature-chart-html5').each(function() {
				$(this).waypoint(function() {
					$(this).attr('data-progress', $(this).attr('data-progress-end'));
				},
				{
					offset: '100%',
					triggerOnce: true
				});
			});
			// re-calculate column heights after chart initialization
			ThemifyBuilderModuleJs.makeColumnsEqualHeight();
		},
		chartsCSS: function () {
			var ang = 180,
				percent = 100,
				deg = parseFloat(ang/percent).toFixed(2),
				degInc,
				i = 0,
				styleId = 'chart-html5-styles',
				styleHTML = '<style id="'+styleId+'">';

			while (i <= percent) {
				degInc = parseFloat(deg*i).toFixed(2);
				degInc = degInc - 0.1;
				styleHTML +=	'.module-feature-chart-html5[data-progress="'+i+'"] .chart-html5-circle .chart-html5-mask.chart-html5-full,'+
								'.module-feature-chart-html5[data-progress="'+i+'"] .chart-html5-circle .chart-html5-fill {'+
									'-webkit-transform: rotate('+degInc+'deg);'+
									'-moz-transform: rotate('+degInc+'deg);'+
									'-ms-transform: rotate('+degInc+'deg);'+
									'-o-transform: rotate('+degInc+'deg);'+
									'transform: rotate('+degInc+'deg);'+
								'}';
				i++;
			}

			styleHTML += '</style>';

			if ($('#'+styleId).length == 1) {
				$('#'+styleId).replaceWith(styleHTML);
			}
			else {
				$('head').append(styleHTML);
			}
		},
		carousel: function (checkImageLoaded) {
			if ($('.themify_builder_slider').length > 0) {
				var $self = this;
				Themify.LoadAsync(themify_vars.url + '/js/carousel.js', function () {
					$self.carouselCalback(checkImageLoaded);
				}, null, null, function () {
					return ('undefined' !== typeof $.fn.carouFredSel);
				});
			}

		},
		videoSliderAutoHeight: function ($this) {
			// Get all the possible height values from the slides
			var heights = $this.children().map(function () {
				return $(this).height();
			});
			// Find the max height and set it
			$this.parent().height(Math.max.apply(null, heights));
		},
		carouselCalback: function (checkImageLoaded) {
			$('.themify_builder_slider').each(function () {
				var $this = $(this),
						img_length = $this.find('img').length,
						$height = (typeof $this.data('height') === 'undefined') ? 'variable' : $this.data('height'),
						$args = {
							responsive: true,
							circular: true,
							infinite: true,
							height: $height,
							items: {
								visible: {min: 1, max: $this.data('visible')},
								width: 150,
								height: 'variable'
							},
							onCreate: function (items) {
								$('.themify_builder_slider_wrap').css({'visibility': 'visible', 'height': 'auto'});
								$this.trigger('updateSizes');
								$('.themify_builder_slider_loader').remove();

								// Fix bug video height with auto height settings.
								if ('auto' == $height && 'video' == $this.data('type')) {
									ThemifyBuilderModuleJs.videoSliderAutoHeight($this);
								}
							}
						};

				if ($this.closest('.themify_builder_slider_wrap').find('.caroufredsel_wrapper').length > 0) {
					return;
				}

				// fix the one slide problem
				if ($this.children().length < 2) {
					$('.themify_builder_slider_wrap').css({'visibility': 'visible', 'height': 'auto'});
					$('.themify_builder_slider_loader').remove();
					$(window).resize();
					return;
				}

				// Auto
				if (parseInt($this.data('auto-scroll')) > 0) {
					$args.auto = {
						play: true,
						timeoutDuration: parseInt($this.data('auto-scroll') * 1000)
					};
				}
				else if ($this.data('effect') !== 'continuously' && (typeof $this.data('auto-scroll') !== 'undefined' || parseInt($this.data('auto-scroll')) == 0)) {
					$args.auto = false;
				}

				// Touch
				$args.swipe = true;

				// Scroll
				if ($this.data('effect') == 'continuously') {
					var speed = $this.data('speed'), duration;
					if (speed == .5) {
						duration = 0.10;
					} else if (speed == 4) {
						duration = 0.04;
					} else {
						duration = 0.07;
					}
					$args.auto = {timeoutDuration: 0};
					$args.align = false;
					$args.scroll = {
						delay: 1000,
						easing: 'linear',
						items: $this.data('scroll'),
						duration: duration,
						pauseOnHover: $this.data('pause-on-hover')
					};
				} else {
					$args.scroll = {
						items: $this.data('scroll'),
						pauseOnHover: $this.data('pause-on-hover'),
						duration: parseInt($this.data('speed') * 1000),
						fx: $this.data('effect')
					}
				}

				if ($this.data('arrow') == 'yes') {
					$args.prev = '#' + $this.data('id') + ' .carousel-prev';
					$args.next = '#' + $this.data('id') + ' .carousel-next';
				}

				if ($this.data('pagination') == 'yes') {
					$args.pagination = {
						container: '#' + $this.data('id') + ' .carousel-pager',
						items: $this.data('visible')
					};
				}

				if ($this.data('wrap') == 'no') {
					$args.circular = false;
					$args.infinite = false;
				}


				if (checkImageLoaded && img_length > 0) {
					$(this).find('img').themifyBuilderImagesLoaded(function () {
						$this.carouFredSel($args);
					});
				} else {
					$this.carouFredSel($args);
				}

				$('.mejs-video').on('resize', function (e) {
					e.stopPropagation();
				});

				var didResize = false, afterResize;
				$(window).resize(function () {
					didResize = true;
				});
				setInterval(function () {
					if (didResize) {
						didResize = false;
						clearTimeout(afterResize);
						afterResize = setTimeout(function () {
							$('.mejs-video').resize();
							$this.trigger('updateSizes');

							// Fix bug video height with auto height settings.
							if ('auto' == $height && 'video' == $this.data('type')) {
								ThemifyBuilderModuleJs.videoSliderAutoHeight($this);
							}
						}, 100);
					}
				}, 250);

			});
			ThemifyBuilderModuleJs.makeColumnsEqualHeight();
		},
		loadOnAjax: function () {
			var $self = ThemifyBuilderModuleJs;
			if (tbLocalScript.fullwidth_support == '') {
				$self.setupFullwidthRows();
			}
			$self.touchdropdown();
			$self.tabs();
			$self.carousel(true);
			$self.charts();
			$self.fullwidthVideo();
			$self.backgroundSlider();
		},
		rowCover: function () {
			$('body').on('mouseenter mouseleave', '.themify_builder_row, .module_column, .sub_column', function (e) {
				var cover = $(this).find('> .builder_row_cover');
				if (cover.length == 0) {
					// for split theme
					cover = $(this).children('.tb-column-inner, .ms-tableCell').first().find('> .builder_row_cover');
					if (cover.length == 0) {
						return;
					}
				}
				var new_color = e.type === 'mouseenter' ? cover.data('hover-color') : cover.data('color');
				if (new_color !== undefined) {
					cover.css({'opacity': 1, 'background-color': new_color});
				}
				else if (e.type == 'mouseleave') {
					cover.css('opacity', 0);
				}
			});
		},
		fullheight: function () {
			// Set full-height rows to viewport height
			if (navigator.userAgent.match(/(iPad)/g)) {
				var didResize = false,
						selector = '.themify_builder_row.fullheight';
				$(window).resize(function () {
					didResize = true;
				});
				setInterval(function () {
					if (didResize) {
						didResize = false;
						$(selector).each(function () {
							$(this).css({
								'height': $(window).height()
							});
						});
					}
				}, 250);
			}
		},
		touchdropdown: function () {
			if( tbLocalScript.isTouch && typeof jQuery.fn.themifyDropdown != 'function' ) {
				Themify.LoadAsync(themify_vars.url + '/js/themify.dropdown.js', function(){
					$('.module-menu .nav').themifyDropdown();
				} );
			}
		},
		accordion: function () {
			$('body').off('click.themify', '.accordion-title').on('click.themify', '.accordion-title', function (e) {
				var $this = $(this),
                                    $panel = $this.next(),
                                    $item = $this.closest('li'),
                                    type = $this.closest('.module.module-accordion').data('behavior'),
                                    def = $item.toggleClass('current').siblings().removeClass('current'); /* keep "current" classname for backward compatibility */
                                
				if ('accordion' === type) {
					def.find('.accordion-content').slideUp().attr('aria-expanded', 'false').closest('li').removeClass('builder-accordion-active');
				}
				if ($item.hasClass('builder-accordion-active')) {
					$panel.slideUp();
					$item.removeClass('builder-accordion-active');
					$panel.attr('aria-expanded', 'false');
				} else {
					$item.addClass('builder-accordion-active');
					$panel.slideDown(function(){
                                            if(type=='accordion'){
                                                var $scroll = $('html,body');
                                                $scroll.animate({
                                                        scrollTop: $this.offset().top
                                                    },
                                                    {   duration: tbScrollHighlight.speed,
                                                        complete: function(){
                                                                if( tbScrollHighlight.fixedHeaderSelector != '' && $( tbScrollHighlight.fixedHeaderSelector ).length > 0) {
                                                                    var to = Math.ceil($this.offset().top - $( tbScrollHighlight.fixedHeaderSelector ).outerHeight(true));
                                                                    $scroll.stop().animate({scrollTop:to}, 300);
                                                                }
                                                        }
                                                    }
                                                );
                                            }
                                        });
					$panel.attr('aria-expanded', 'true');
				}

				$('body').trigger('tf_accordion_switch', [$panel]);
				e.preventDefault();
			});
		},
		tabs: function () {
			$(".module.module-tab").each(function () {
				var $height = $(".tab-nav:first", this).outerHeight();
				if ($height > 200) {
					$(".tab-nav:first", this).siblings(".tab-content").css('min-height', $height);
				}
			}).tabify();
		},
		tabsDeepLink: function () {
			var hash = window.location.hash;
			if ('' != hash && '#' != hash && $(hash + '.tab-content').length > 0) {
				var cons = 100,
						$moduleTab = $(hash).closest('.module-tab');
				if ($moduleTab.length > 0) {
					$('a[href="' + hash + '"]').click();
					$('html, body').animate({scrollTop: $moduleTab.offset().top - cons}, 1000);
				}
			}
		},
		backgroundScrolling: function () {
			$('.builder-parallax-scrolling').each(function () {
				$(this).builderParallax('50%', 0.1);
			});
		},
		animationOnScroll: function () {
			var self = ThemifyBuilderModuleJs;
			if (!self.supportTransition())
				return;

			$('body').addClass('animation-on')
					.on('builder_toggle_frontend', function (event, is_edit) {
						self.doAnimation();
					});
			self.doAnimation();
		},
		doAnimation: function (resync) {
			resync = resync || false;
			// On scrolling animation
			var self = ThemifyBuilderModuleJs, selectors = tbLocalScript.animationInviewSelectors,
					$body = $('body'), $overflow = $('body');

			if (!ThemifyBuilderModuleJs.supportTransition())
				return;

			if ($body.find(selectors).length > 0) {
				if (!$overflow.hasClass('animation-running')) {
					$overflow.addClass('animation-running');
				}
			} else {
				if ($overflow.hasClass('animation-running')) {
					$overflow.removeClass('animation-running');
				}
			}

			// Global Animation
			if (tbLocalScript.createAnimationSelectors.selectors) {
				$.each(tbLocalScript.createAnimationSelectors.selectors, function (key, val) {
					$(val).addClass(tbLocalScript.createAnimationSelectors.effect);
				});
			}

			// Specific Animation
			if (tbLocalScript.createAnimationSelectors.specificSelectors) {
				$.each(tbLocalScript.createAnimationSelectors.specificSelectors, function (selector, effect) {
					$(selector).addClass(effect);
				});
			}

			// Core Builder Animation
			$.each(selectors, function (i, selector) {
				$(selector).addClass('wow');
			});

			if (resync)
				Themify.wow.doSync();
		},
		supportTransition: function () {
			var b = document.body || document.documentElement,
					s = b.style,
					p = 'transition';

			if (typeof s[p] == 'string') {
				return true;
			}

			// Tests for vendor specific prop
			var v = ['Moz', 'webkit', 'Webkit', 'Khtml', 'O', 'ms'];
			p = p.charAt(0).toUpperCase() + p.substr(1);

			for (var i = 0; i < v.length; i++) {
				if (typeof s[v[i] + p] == 'string') {
					return true;
				}
			}
			return false;
		},
		setupBodyClasses: function () {
			var classes = [];
			if (ThemifyBuilderModuleJs._isTouch()) {
				classes.push('builder-is-touch');
			}
			if (ThemifyBuilderModuleJs._isMobile()) {
				classes.push('builder-is-mobile');
			}
			if (tbLocalScript.isParallaxActive) classes.push('builder-parallax-scrolling-active');
			$('.themify_builder_content').each(function () {
				if ($(this).children(':not(.js-turn-on-builder)').length > 0) {
					classes.push('has-builder');
					return false;
				}
			});

			$('body').addClass(classes.join(' '));
		},
		_checkBrowser: function (browser) {
			var isOpera = !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
			return  'opera' == browser ? isOpera : false;

		},
		isBuilderActive: function () {
			return $('body').hasClass('themify_builder_active');
		},
		_isTouch: function() {
			var isTouchDevice = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/),
				isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0) || (navigator.maxTouchPoints));
			return isTouchDevice || isTouch;
		},
		_isMobile: function() {
			var isTouchDevice = navigator.userAgent.match(/(iPhone|iPod|iPad|Android|playbook|silk|BlackBerry|BB10|Windows Phone|Tizen|Bada|webOS|IEMobile|Opera Mini)/);
			return isTouchDevice;
		}
	};

	// Initialize
	ThemifyBuilderModuleJs.init();

}(jQuery, window, document));
