/*!
 * Multiple jQuery Tabs
 * www.ilovecolors.com.ar/multiple-jquery-tabs/
 *
 * Copyright (c) 2010 Elio Rivero (http://ilovecolors.com.ar)
 * Licensed under the GPL (http://www.opensource.org/licenses/gpl-license.php) license.
 *
 * Built on top of the jQuery library
 * http://jquery.com
 *
 */

'use strict';

//arrays of objects to collect previous and current tab
var previous = [];
var current = [];
//array to store IDs of our tabs
//store setInterval reference
var tablist = [];

//change tab and highlight current tab title
function change(block){
	//don't do anything if it's the same tab
	if(current[block].reference == previous[block].reference)
		return;

	jQuery(block + ' .ilc-tab#' + current[block].reference).show();	
		
	//clear highlight from previous tab title
	jQuery(block + ' .ilc-htabs a[href="#' + previous[block].reference + '"]').parents('li').removeClass('select');
	
	//highlight currenttab title
	jQuery(block + ' .ilc-htabs a[href="#' + current[block].reference + '"]').parents('li').addClass('select');
	
	//hide the other tabs
	jQuery("#" + previous[block].reference).hide();
	previous[block].reference = current[block].reference;
}
function Tab(blockid){
	var z = 0;
	this.block = blockid;
	this.next = function (){
		previous[this.block].reference = jQuery(this.block + ' .ilc-htabs a').get()[z].href.split('#')[1];
		if(z >= jQuery(this.block + ' .ilc-htabs a').get().length-1) z = 0; else z++;
		current[this.block].reference  = jQuery(this.block + ' .ilc-htabs a').get()[z].href.split('#')[1];
		change(this.block);
	};
}

function Reference(reference){ this.reference = reference; }
function ilcTabs(tobj){
	for ( var key in tobj) {
		
		var params = tobj[key].split('_');
		var block = params[0];
		
		//initialize tabs, display the current tab
		jQuery(block + " .ilc-tab:not(:first)").hide();
		jQuery(block + " .ilc-tab:first").show();
		
		//highlight the current tab title
		jQuery(block + ' .ilc-htabs a:first').parents('li').addClass('select');
		
		previous[block] = new Reference(jQuery(block + " .ilc-htabs a:first").attr("href").split('#')[1]);
		
		//is actually a reference to the second tab
		current[block]  = new Reference(jQuery(block + ' .ilc-htabs a').get()[1].href.split('#')[1]);
		
		//create new Tab to store values for rotation and setInterval id
		tablist[block] = new Tab(block);
		
		if (params[1] != undefined) {
			//set interval to repeat - next line commented
			//store in - next line commented
			tablist[block].intervalid = setInterval("tablist['" + block + "'].next()", params[1]);
		}
		
		//handler for clicking on tabs
		jQuery(block + " .ilc-htabs a").click(function(event){
			
			//store reference to clicked tab
			var target = "#"+event.target.getAttribute("href").split('#')[1],
				tblock = "#"+jQuery(target).parent().parent().attr("id");
			
			current[tblock].reference = jQuery(this).attr("href").split('#')[1];  
			
			//display referenced tab
			change(tblock);
			
			//if tab is clicked, stop rotating 
			clearInterval(tablist[tblock].intervalid);
			
			return false;
		});
	}
}