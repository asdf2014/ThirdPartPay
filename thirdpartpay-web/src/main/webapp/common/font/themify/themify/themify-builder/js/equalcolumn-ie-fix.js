(function($){

	'use strict';

	var ThemifyEqualHeight = {
		makeEqual: function ($obj, target) {
			target = target || null;
			$obj.each(function () {
				var t = 0,
					$target = target ? $(this).find(target) : $(this);
				$target.children().each(function () {
					var $holder = $(this);
					$holder.css('min-height', '');
					if ($holder.height() > t) {
						t = $holder.height();
					}
				});
				$target.children().each(function () {
					$(this).css('min-height', t + 'px');
				});
			});
		},
		equalHeight: function () {
			ThemifyEqualHeight.makeEqual($('.themify_builder_row'), '.row_inner');
			ThemifyEqualHeight.makeEqual($('.themify_builder_sub_row'));
		},
		init: function() {
			this.equalHeight();

			var timeout;
			$(window).resize(function(){
				clearTimeout(timeout);
				timeout = setTimeout(function(){
					var windowW = $(window).width();
					if ( windowW > 681 ) {
						ThemifyEqualHeight.equalHeight();
					} else {
						$('.tb-column').css('min-height', '');
					}
				}, 200);
			});
		}
	}

	// Run on WINDOW load
	$(window).load(function(){
		ThemifyEqualHeight.init();
	});
})(jQuery);