/* global mejs, _wpmejsSettings */
var ThemifyMediaElement = {};
(function ($) {
	// add mime-type aliases to MediaElement plugin support
	mejs.plugins.silverlight[0].types.push('video/x-ms-wmv');
	mejs.plugins.silverlight[0].types.push('audio/x-ms-wma');

	$(function () {

		ThemifyMediaElement = {
			init: function( $obj ) {
				var settings = {};

				if ( typeof _wpmejsSettings !== 'undefined' ) {
					settings = _wpmejsSettings;
				}

				settings.videoWidth = '100%';
				settings.videoHeight = 'auto';
				settings.enableAutosize = true;

				settings.success = function (mejs) {
					var autoplay, loop;

					if ( 'flash' === mejs.pluginType ) {
						autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
						loop = mejs.attributes.loop && 'false' !== mejs.attributes.loop;

						autoplay && mejs.addEventListener( 'canplay', function () {
							mejs.play();
						}, false );

						loop && mejs.addEventListener( 'ended', function () {
							mejs.play();
						}, false );
					}
				};

				$obj.mediaelementplayer( settings );
			}
		};

		ThemifyMediaElement.init( $('.wp-audio-shortcode, .wp-video-shortcode') );

	});

}(jQuery));