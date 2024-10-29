/**
 * some small jquery plugins, copied into this file because it's silly to create a new JS file for it (performance wise)
 *
 *  
 * jQuery Cookie plugin
 *  https://github.com/carhartl/jquery-cookie
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
*/
function adherder_cookie(key, value, options) {

    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

/*
 * jQuery UUID Generator 2.0
 * http://plugins.jquery.com/project/uidGen
 * 
 * Copyright 2011 Tiago Mikhael Pastorello Freire a.k.a Brazilian Joe
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/MIT
 *
 * Functionality: 
 * Usage 1: define the default prefix by using an object with the
 * property prefix as a parameter which contains a string value, 
 * and/or determine the uuid generation mode:
 * (sequential|random) {prefix: 'id',mode:'sequential'}
 * Usage 2: call the function jQuery.uuid() with a 
 * string parameter p to be used as a prefix to generate a random uuid;
 * Usage 3: call the function jQuery.uuid() with no parameters 
 * to generate a uuid with the default prefix; defaul prefix: '' (empty string)
 */
function adherder_uidGen () {
    return "ctopt-uid-" + adherder_uidGen_gen2();
};
  
/*
 Generate fragment of random numbers
*/
function adherder_uidGen_uuidlet () {
    return(((1+Math.random())*0x10000)|0).toString(16).substring(1);
};

/*
 Generates random uuid
*/
function adherder_uidGen_gen2 () {
    return (adherder_uidGen_uuidlet()
      +adherder_uidGen_uuidlet()
      +'-'
      +adherder_uidGen_uuidlet()
      +'-'
      +adherder_uidGen_uuidlet()
      +'-'
      +adherder_uidGen_uuidlet()
      +'-'
      +adherder_uidGen_uuidlet()
      +adherder_uidGen_uuidlet()
      +adherder_uidGen_uuidlet()
    );
};

jQuery(document).ready( function() {
  var uid = adherder_cookie('ctopt_uid');
  if(!uid) {
    adherder_cookie(
      'ctopt_uid', 
      adherder_uidGen(), 
      { expires: 31, path: '/' }
    );
  }
  jQuery('.adherder_placeholder').each(function(ad) {
	  var widget = jQuery(this).parent();
	  jQuery.post(
		AdHerder.ajaxurl,
		{
			action : 'adherder_display_ajax'
		},
		function(data) {
			widget.html(data);
			adherder_track_ad(widget.find('.ctopt'));
		}
	  );
  });
  adherder_track_ads();
});

function adherder_track_conversion(adId) {
	jQuery.post(
		AdHerder.ajaxurl,
		{
			action : 'adherder_track_conversion',
			ad_id  : adId
		}
	);
}

function adherder_track_impression(adId) {
	jQuery.post(
		AdHerder.ajaxurl,
		{
			action : 'adherder_track_impression',
			ad_id  : adId
		}
	);
}

function adherder_track_ads() {
	jQuery('.ctopt').each(function(ad) {
		adherder_track_ad(jQuery(this));
	});
}

function adherder_track_ad(ad) {
	var classList = ad.attr('class').split(/\s+/);
	var adId;
	for(var i = 0; i < classList.length; i++) {
		var className = classList[i];
		if(className.lastIndexOf('ctoptid', 0) === 0) {
			adId = className.slice(className.lastIndexOf('-')+1);
			adherder_track_impression(adId); 
		}
	}
	if(adId) {
		ad.find('a').click(function(e) { 
			adherder_track_conversion(adId); 
		});
	}
}