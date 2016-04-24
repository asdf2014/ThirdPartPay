;
var Themify, ThemifyGallery;
(function ($, window, document, undefined) {
    'use strict';

    /* window load fires only once in IE */
    window.addEventListener("load", function () {
        window.loaded = true;
    });
    Themify = {
        wow: null,
        triggerEvent: function (a, b) {
            var c;
            document.createEvent ? (c = document.createEvent("HTMLEvents"), c.initEvent(b, !0, !0)) : document.createEventObject && (c = document.createEventObject(), c.eventType = b), c.eventName = b, a.dispatchEvent ? a.dispatchEvent(c) : a.fireEvent && htmlEvents["on" + b] ? a.fireEvent("on" + c.eventType, c) : a[b] ? a[b]() : a["on" + b] && a["on" + b]()
        },
        Init: function () {
            if (typeof tbLocalScript !== 'undefined' && tbLocalScript) {
                var $self = Themify;
                $(document).ready(function () {
                    tbLocalScript.isTouch = $('body').hasClass('touch');
                    $self.LoadAsync(tbLocalScript.builder_url + '/js/themify.builder.script.js'); // this script should be always loaded even there is no builder content because it's also requires for themify_shortcode for exp: animation js
                });
                $('body').on('builderscriptsloaded.themify', function () {
                    $self.LoadAsync(tbLocalScript.builder_url + '/js/themify.builder.script.js');
                });
            }
            else {
                this.bindEvents();
            }
        },
        bindEvents: function () {
            var $self = Themify;
            if (window.loaded) {
                $('.shortcode.slider, .shortcode.post-slider').css({'height': 'auto', 'visibility': 'visible'});
                $self.InitCarousel();
                $self.InitGallery();
                $self.InitMap();
                $self.wowInit();
            }
            else {
                $(window).load(function () {
                    $('.shortcode.slider, .shortcode.post-slider').css({'height': 'auto', 'visibility': 'visible'});
                    $self.InitCarousel();
                    $self.InitGallery();
                });
                $(document).ready(function () {
                    $self.InitMap();
                    $self.wowInit();
                });
            }
            $('body').on('builder_load_module_partial builder_toggle_frontend', this.InitMap); // builder toggle/update module map.
        },
        InitCarousel: function () {
            if ($('.slides[data-slider]').length > 0) {
                this.LoadAsync(themify_vars.url + '/js/carousel.js', this.carouselCalback, null, null, function () {
                    return ('undefined' !== typeof $.fn.carouFredSel);
                });
            }
        },
        carouselCalback: function () {

            $('.slides[data-slider]').each(function () {
                $(this).find("> br, > p").remove();
                var $this = $(this),
                        $data = JSON.parse(window.atob($(this).data('slider'))),
                        height = (typeof $data.height === 'undefined') ? 'auto' : $data.height,
                        $numsldr = $data.numsldr,
                        $slideContainer = 'undefined' !== typeof $data.custom_numsldr ? '#' + $data.custom_numsldr : '#slider-' + $numsldr,
                        $args = {
                            responsive: true,
                            swipe: true,
                            circular: $data.wrapvar,
                            infinite: $data.wrapvar,
                            auto: {
                                play: $data.play,
                                timeoutDuration: $data.auto,
                                duration: $data.speed,
                                pauseOnHover: $data.pause_hover
                            },
                            scroll: {
                                items: parseInt($data.scroll),
                                duration: $data.speed,
                                fx: $data.effect
                            },
                            items: {
                                visible: {
                                    min: 1,
                                    max: parseInt($data.visible)
                                },
                                width: 120,
                                height: height
                            },
                            onCreate: function (items) {
                                $($slideContainer).css({'visibility': 'visible', 'height': 'auto'});
                                $this.trigger('updateSizes');
                            }
                        };
                if ($data.slider_nav) {
                    $args.prev = $slideContainer + ' .carousel-prev';
                    $args.next = $slideContainer + ' .carousel-next';
                }
                if ($data.pager) {
                    $args.pagination = $slideContainer + ' .carousel-pager';
                }
                $(this).carouFredSel($args);
            });



            var tscpsDidResize = false;
            $(window).on("resize", function () {
                tscpsDidResize = true;
            });
            setInterval(function () {
                if (tscpsDidResize) {
                    tscpsDidResize = false;
                    $(".slides[data-slider]").each(function () {
                        var heights = [],
                                newHeight,
                                $self = $(this);
                        $self.find("li").each(function () {
                            heights.push($(this).outerHeight());
                        });
                        newHeight = Math.max.apply(Math, heights);
                        $self.outerHeight(newHeight);
                        $(".caroufredsel_wrapper").outerHeight(newHeight);
                    });
                }
            }, 500);

        },
        InitMap: function () {
            var $self = Themify;
            if ($('.themify_map').length > 0) {
                if (typeof google !== 'object' || typeof google.maps !== 'object') {
                    $self.LoadAsync('//maps.googleapis.com/maps/api/js?v=3.exp&callback=Themify.MapCallback', false, true, true);
                }
                else {
                    $self.MapCallback();
                }
            }
        },
        MapCallback: function () {
            var $maps = $('.themify_map');
            $maps.each(function ($i) {
                var $data = JSON.parse(window.atob($(this).data('map'))),
                        address = $data.address,
                        zoom = parseInt($data.zoom),
                        type = $data.type,
                        scroll = $data.scroll,
                        drag = $data.drag,
                        node = this;
                var delay = $i * 1000;
                setTimeout(function () {
                    var geo = new google.maps.Geocoder(),
                            latlng = new google.maps.LatLng(-34.397, 150.644),
                            mapOptions = {
                                zoom: zoom,
                                center: latlng,
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                scrollwheel: scroll,
                                draggable: drag
                            };
                    switch (type.toUpperCase()) {
                        case 'ROADMAP':
                            mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
                            break;
                        case 'SATELLITE':
                            mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
                            break;
                        case 'HYBRID':
                            mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
                            break;
                        case 'TERRAIN':
                            mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
                            break;
                    }

                    var map = new google.maps.Map(node, mapOptions),
                            revGeocoding = $(node).data('reverse-geocoding') ? true : false;

                    /* store a copy of the map object in the dom node, for future reference */
                    $(node).data('gmap_object', map);

                    if (revGeocoding) {
                        var latlngStr = address.split(',', 2),
                                lat = parseFloat(latlngStr[0]),
                                lng = parseFloat(latlngStr[1]),
                                geolatlng = new google.maps.LatLng(lat, lng),
                                geoParams = {'latLng': geolatlng};
                    } else {
                        var geoParams = {'address': address};
                    }

                    geo.geocode(geoParams, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var position = revGeocoding ? geolatlng : results[0].geometry.location;
                            map.setCenter(position);
                            var marker = new google.maps.Marker({
                                map: map,
                                position: position
                            }),
                                    info = $(node).data('info-window');
                            if (undefined !== info) {
                                var contentString = '<div class="themify_builder_map_info_window">' + info + '</div>',
                                        infowindow = new google.maps.InfoWindow({
                                            content: contentString
                                        });

                                google.maps.event.addListener(marker, 'click', function () {
                                    infowindow.open(map, marker);
                                });
                            }
                        }
                    });
                }, delay);
            });
        },
        wowInit: function () {
            if ((typeof tbLocalScript === 'undefined' || !tbLocalScript) || tbLocalScript.animationInviewSelectors && tbLocalScript.animationInviewSelectors.length > 0) {
                if (!Themify.wow) {
                    Themify.LoadAsync(themify_vars.url + '/js/wow.js', Themify.wowCallback, null, null, function () {
                        return (Themify.wow);
                    });
                }
                else {
                    Themify.wowCallback();
                    return (Themify.wow);
                }
            }
        },
        wowCallback: function () {
            var self = Themify;
            if (themify_vars.TB) {
                ThemifyBuilderModuleJs.animationOnScroll();
            }
            self.wow = new WOW({
                live: true,
                offset: typeof tbLocalScript !== 'undefined' && tbLocalScript ? parseInt(tbLocalScript.animationOffset) : 100
            });
            self.wow.init();

            $('body').on('builder_load_module_partial builder_toggle_frontend', function () {
                self.wow.doSync();
                self.wow.sync();
            });

            // duck-punching WOW to get delay and iteration from classnames
            if (typeof self.wow.__proto__ !== 'undefined') {
                self.wow.__proto__.applyStyle = function (box, hidden) {
                    var delay, duration, iteration;
                    duration = box.getAttribute('data-wow-duration');
                    delay = box.getAttribute('class').match(/animation_effect_delay_((?:\d+\.?\d*|\.\d+))/);
                    if (null != delay)
                        delay = delay[1] + 's';
                    iteration = box.getAttribute('class').match(/animation_effect_repeat_(\d*)/);
                    if (null != iteration)
                        iteration = iteration[1];
                    return this.animate((function (_this) {
                        return function () {
                            return _this.customStyle(box, hidden, duration, delay, iteration);
                        };
                    })(this));
                };
            }
        },
        LoadAsync: function (src, callback, version, defer, test) {
            var id = src.split("/").pop().replace(/\./g, '_'), // Make script filename as ID
                    existElemens = document.getElementById(id);

            if (existElemens) {
                if (callback) {
                    if (test) {
                        var callbackTimer = setInterval(function () {
                            var call = false;
                            try {
                                call = test.call();
                            } catch (e) {
                            }

                            if (call) {
                                clearInterval(callbackTimer);
                                callback.call();
                            }
                        }, 100);
                    } else {
                        setTimeout(callback, 110);
                    }
                }
                return;
            }
            var s, r, t;
            r = false;
            s = document.createElement('script');
            s.type = 'text/javascript';
            s.id = id;
            s.src = !version && 'undefined' !== typeof tbLocalScript ? src + '?version=' + tbLocalScript.version : src;
            if (!defer) {
                s.async = true;
            }
            else {
                s.defer = true;
            }
            s.onload = s.onreadystatechange = function () {
                if (!r && (!this.readyState || this.readyState === 'complete'))
                {
                    r = true;
                    if (callback) {
                        callback();
                    }
                }
            };
            t = document.getElementsByTagName('script')[0];
            t.parentNode.insertBefore(s, t);
        },
        LoadCss: function (href, version, before, media) {

            if ($("link[href='" + href + "']").length > 0) {
                return;
            }
            var doc = window.document;
            var ss = doc.createElement("link");
            var ref;
            if (before) {
                ref = before;
            }
            else {
                var refs = (doc.body || doc.getElementsByTagName("head")[ 0 ]).childNodes;
                ref = refs[ refs.length - 1];
            }

            var sheets = doc.styleSheets;
            ss.rel = "stylesheet";
            ss.href = version ? href + '?version=' + version : href;
            // temporarily set media to something inapplicable to ensure it'll fetch without blocking render
            ss.media = "only x";
            ss.async = 'async';

            // Inject link
            // Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
            ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
            // A method (exposed on return object for external use) that mimics onload by polling until document.styleSheets until it includes the new sheet.
            var onloadcssdefined = function (cb) {
                var resolvedHref = ss.href;
                var i = sheets.length;
                while (i--) {
                    if (sheets[ i ].href === resolvedHref) {
                        return cb();
                    }
                }
                setTimeout(function () {
                    onloadcssdefined(cb);
                });
            };

            // once loaded, set link's media back to `all` so that the stylesheet applies once it loads
            ss.onloadcssdefined = onloadcssdefined;
            onloadcssdefined(function () {
                ss.media = media || "all";
            });
            return ss;
        },
        video: function () {
            if ($('.themify_video_desktop a').length > 0) {
                if (typeof flowplayer === 'undefined') {
                    this.LoadAsync(themify_vars.url + '/js/flowplayer-3.2.4.min.js', this.videoCalback);
                }
                else {
                    this.videoCalback();
                }
            }
        },
        videoCalback: function () {
            $('.themify_video_desktop a').each(function () {
                flowplayer(
                        $(this).attr('id'),
                        themify_vars.url + "/js/flowplayer-3.2.5.swf",
                        {
                            clip: {autoPlay: false}
                        }
                );
            });
        },
        lightboxCallback: function ($el, $args) {
            if ($('.module.module-gallery').length > 0) {
                this.showcaseGallery();
            }
            this.LoadAsync(themify_vars.url + '/js/themify.gallery.js', function () {
                Themify.GalleryCallBack($el, $args);
            }, null, null, function () {
                return ('undefined' !== typeof ThemifyGallery);
            });
        },
        InitGallery: function ($el, $args) {
            var lightboxConditions = ((themifyScript.lightbox.lightboxContentImages && $(themifyScript.lightbox.contentImagesAreas).length) || themifyScript.lightbox.lightboxGalleryOn) ? true : false;
            if (lightboxConditions || $('.module.module-gallery').length > 0 || $('.module.module-image').length > 0 || $('.themify_lightbox').length > 0 || $(themifyScript.lightbox.lightboxSelector).length > 0) {
                this.LoadCss(themify_vars.url + '/css/lightbox.css', null);
                this.LoadAsync(themify_vars.url + '/js/lightbox.js', function () {
                    Themify.lightboxCallback($el, $args);
                    return ('undefined' !== typeof $.fn.magnificPopup);
                });
            }
            else {
                $('body').addClass('themify_lightbox_loaded').removeClass('themify_lightboxed_images');
            }
        },
        GalleryCallBack: function ($el, $args) {
            if (!$el) {
                $el = $(themifyScript.lightboxContext);
            }
            $args = !$args && themifyScript.extraLightboxArgs ? themifyScript.extraLightboxArgs : {};
            ThemifyGallery.init({'context': $el, 'extraLightboxArgs': $args});
            $('body').addClass('themify_lightbox_loaded').removeClass('themify_lightboxed_images');
        },
        showcaseGallery: function () {
            $('body').on('click', '.module.module-gallery.layout-showcase a', function () {
                $(this).closest('.gallery').find('.gallery-showcase-image img').prop('src', $(this).data('image'));
                return false;
            });
        },
        isPageHasBuilderContent: function () {
            var check_builder = $('.themify_builder_content').filter(function () {
                return $.trim($(this).html().toString()).length > 0;
            });
            return check_builder.length;
        }
    };


    Themify.Init();

}(jQuery, window, document));
