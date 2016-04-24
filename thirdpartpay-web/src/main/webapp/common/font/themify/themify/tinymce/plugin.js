/**
 * Routines to add a menu button in WP 3.9 Editor
 */
tinymce.PluginManager.add('themifyMenu', function(ed, url) {

	'use strict';

	function dialog(t, sc, w, h){
		return {
			text : t,
			onclick : function() {
				ed.windowManager.open({
					file : ajaxurl + '?action=themify_editor_menu&shortcode=' + sc + '&title=' + t  + '&nonce=' + themifyEditor.nonce,
					width : w,
					height : h,
					inline : 1
				});
			}
		};
	}
	function wrapDialog(t, sc, w, h){
		return {
			text : t,
			onclick : function() {
				ed.windowManager.open({
					file : ajaxurl + '?action=themify_editor_menu&shortcode=' + sc + '&title=' + t + '&selection=' + encodeURIComponent(ed.selection.getContent()) + '&nonce=' + themifyEditor.nonce,
					width : w,
					height : h,
					inline : 1
				});
			}
		};
	}
	function wrap(t, sc) {
		return {
			text : t,
			onclick : function() {
				ed.selection.setContent('[themify_' + sc + ']' + ed.selection.getContent() + '[/themify_' + sc + ']');
			}
		};
	}
	function col(t, grid) {
		return {
			text : t,
			onclick : function() {
				ed.selection.setContent('[themify_col grid="' + grid + '"]' + ed.selection.getContent() + '[/themify_col]');
			}
		};
	}

	var lang = themifyEditor.editor,
		items = [
			dialog( lang.authorBox, 'author_box', 400, 450 ),
			wrapDialog( lang.box, 'box', 400, 210 ),
			dialog(lang.button, 'button', 400, 550 ),
			{
				text : lang.columns,
				menu: [
					col( lang.half21first, '2-1 first' ),
					col( lang.half21, '2-1' ),
					col( lang.third31first, '3-1 first' ),
					col( lang.third31, '3-1' ),
					col( lang.quarter41first, '4-1 first' ),
					col( lang.quarter41, '4-1' )
				]
			},
			{
				text : lang.customSlider,
				menu: [
					wrapDialog( lang.slider, 'slider', 400, 520 ),
					wrap( lang.slide, 'slide' )
				]
			},
			dialog( lang.flickr, 'flickr', 400, 450 ),
			dialog( lang.horizontalRule, 'hr', 400, 270 ),
			dialog(lang.icon, 'icon', 700, 480 ),
			wrap( lang.isGuest, 'is_guest' ),
			wrap( lang.isLoggedIn, 'is_logged_in' ),
			dialog( lang.listPosts, 'list_posts', 400, 500 ),
			dialog( lang.map, 'map', 400, 420 ),
			dialog( lang.postSlider, 'post_slider', 400, 510 ),
			wrap( lang.quote, 'quote' ),
			dialog( lang.twitter, 'twitter', 400, 340 ),
			dialog( lang.video, 'video', 400, 250 )
		];

	ed.addButton('btnthemifyMenu', {
		type: 'menubutton',
		text: '',
		icon: 'themify',
		tooltip: lang.menuTooltip,
		menu: items
	});

});