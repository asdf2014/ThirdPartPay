;(function ( $, window, document, undefined ) {

	'use strict';

	$.fn.themifyDropdown = function( options ) {

		var settings = $.extend( { }, options );

		return this.each(function(){
			if( $(this).hasClass( 'with-sub-arrow' ) )
				return;

			$(this).addClass( 'with-sub-arrow' )
				.find( 'li.menu-item-has-children > a:not(.themify_lightbox)' )
				.append( '<span class="sub-arrow closed" />' );
		});
	};

	var startX,
		startY;

	$('body').on('touchstart', '.sub-arrow, .with-sub-arrow a', function(e){
		e.stopPropagation();
		startX = getCoord(e, 'X');
		startY = getCoord(e, 'Y');
	})
	.on( 'click touchend', '.sub-arrow', function(e){
		e.stopPropagation();
		// If movement is less than 20px, execute the handler
		if (Math.abs(getCoord(e, 'X') - startX) < 20 && Math.abs(getCoord(e, 'Y') - startY) < 20) {
			var menu_item = $( this ).closest( 'li' );
			var active_tree = $( this ).parents( '.dropdown-open' );
			$( this ).closest( '.with-sub-arrow' ) // get the menu container
				.find( 'li.dropdown-open' ).not( active_tree ) // find open (if any) dropdowns
				.each(function(){
					close_dropdown( $( this ) );
				});

			if( menu_item.hasClass( 'dropdown-open' ) ) {
				close_dropdown( menu_item );
			} else {
				open_dropdown( menu_item );
			}

		}

		return false;
	} )
	// clicking menu items where the URL is only "#" is the same as clicking the dropdown arrow
	.on( 'click touchend', '.with-sub-arrow a', function(e){
		// If movement is less than 20px, execute the handler
		if (Math.abs(getCoord(e, 'X') - startX) < 20 && Math.abs(getCoord(e, 'Y') - startY) < 20) {
			if( $( this ).attr( 'href' ) == '#' ) {
				e.stopPropagation();
				$( this ).find( '> .sub-arrow' ).click();
				return false;
			}
		}
	} );

	function getCoord(e, c) {
		return /touch/.test(e.type) ? (e.originalEvent || e).changedTouches[0]['page' + c] : e['page' + c];
	}

	function open_dropdown( $li ) {
		var dropdown = $li.find( '.sub-menu' ).first()
			.show().css( 'visibility', 'visible' );

		$li.addClass( 'dropdown-open' );
		$li.find( '> a .sub-arrow' ).removeClass( 'closed' ).addClass( 'open' );
	}

	function close_dropdown( $li ) {
		var dropdown = $li.find( '.sub-menu' ).first()
			.hide().css( 'visibility', 'hidden' );

		$li.removeClass( 'dropdown-open' );
		$li.find( '> a .sub-arrow' ).removeClass( 'open' ).addClass( 'closed' );
	}
})( jQuery, window, document );