/*! ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE */
"use strict";
// Functionality for locator page
$(function () {
    // Stadia Maps Static API URL Builder
    function MapBuilder() {
        this.url = 'https://stadiamaps.com/static/osm_bright?size=296x216@2x&markers=';
        this.nextMarkerNumber = 0;
    }
    MapBuilder.prototype.getURL = function() { return this.url; };
    MapBuilder.prototype.addMyLocationMarker = function(lat, lng) { // Must be called before any addMarker calls
        this.url += lat.toFixed(4) + ',' + lng.toFixed(4) + ',aledide_smooth,d32f2f';
    };
    MapBuilder.prototype.addMarker = function(lat, lng) { // Warning: limited to 9 for labels
        this.url += '|' + lat.toFixed(4) + ',' + lng.toFixed(4)
            + ',,darkblue,' + ++this.nextMarkerNumber;
    };
    var locationMap = new MapBuilder();

    // Prefixes for arcade item navigation links
    var GMAPS_PREFIX = 'https://maps.google.com/?q=loc:';
    var NAV_PREFIX_ANDROID = 'geo:';
    var NAV_PREFIX_IOS = 'maps:?q=&saddr=Current%20Location&daddr=loc:';
    var NAV_PREFIX_WP7 = 'maps:';
    var NAV_PREFIX_W10 = 'bingmaps:?rtp=~pos.';
    var NAV_PREFIX = '#';

    // Navigation URL generator functions
    var nav_url = function(latitude, longitude, label) {
        return NAV_PREFIX + latitude + ',' + longitude + '(' + encodeURIComponent(label) + ')';
    };
    var nav_url_android = function(latitude, longitude, label) {
        return NAV_PREFIX_ANDROID + latitude + ',' + longitude + '?q=' +
            latitude + ',' + longitude + '(' + encodeURIComponent(label) + ')';
    };
    var nav_url_ios = function(latitude, longitude, label) {
        return NAV_PREFIX_IOS + latitude + ',' + longitude + '(' + encodeURIComponent(label) + ')';
    };
    var nav_url_wp7 = function(latitude, longitude) {
        return NAV_PREFIX_WP7 + latitude + ' ' + longitude;
    };
    var nav_url_w10 = function(latitude, longitude, label) {
        return NAV_PREFIX_W10 + latitude + '_' + longitude + '_' + encodeURIComponent(label);
    };

    // Detect platform and set generator function
    var platform = 'mobile';
    if (/Windows Phone/i.test(navigator.userAgent)) nav_url = nav_url_wp7;
    else if (/WM 10/i.test(navigator.userAgent)) nav_url = nav_url_w10;
    else if (/Android/i.test(navigator.userAgent)) nav_url = nav_url_android;
    else if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) nav_url = nav_url_ios;
    else platform = 'pc';
    if (/Mac OS X/i.test(navigator.userAgent)) nav_url = nav_url_ios;
    else if (/Windows NT 10/i.test(navigator.userAgent)) nav_url = nav_url_w10;

    // Get user-selected data source(s) or set to default (Z-I-v)
    var datasrc = localStorage.getItem('datasrc');
    if (null === datasrc) datasrc = 'ziv';

    // Source info URL/name functions
    var info_url = function(metadata, src, id, sid) {
        var property = 'infoURL';
        if ('mobile' === platform) property = 'mInfoURL';
        if (!(src in metadata)) src = 'fallback';
        return metadata[src][property].replace('${id}', id).replace('${sid}', sid);
    };
    var info_name = function(metadata, src) {
        if (!(src in metadata)) src = 'fallback';
        return metadata[src].name;
    };

    // Returns the classes for green checkmark on true, red cross on false.
    var checkmark_cross = function (value) {
        return value ? 'icon-checkmark fg-color-green' : 'icon-cancel-2 fg-color-red';
    };

    // Geolocation error handler
    var handle_geolocation_error = function(error) {
        // Permission denied
        if (error.code === 1) {
            $('#message-waiting').hide();
            $('#message-denied').show();
        }
        // Position unavailable or timeout
        else if (error.code === 2 || error.code === 3) {
            $('#message-waiting').hide();
            $('#message-failed').show();
        }
    };

    // Arcade location data handler function
    var handle_data = function(/*APIData*/ data) {
        var locations = data.locations,
            message_arcade_list = $('#message-arcade-list'),
            arcade_list = $('#arcade-list');

        // Empty out arcade list container from previous runs (when changing hash only, previous entries remained).
        $('.arcade-list-item:not(:first-child)').remove();

        $('#message-found-searching').hide();
        message_arcade_list.show();

        if (locations.length === 0) {
            $('#arcade-list-container').hide();
            $('#arcade-noresults-container').show();
        }
        else {
            // Determine if the selected source has DDR availability provided; set a class if not the case.
            if (!data.sources[datasrc].hasDDR) {
                message_arcade_list.addClass('has-ddr-unavailable');
            }

            // Grab the item layout element.
            // For each location found, clone the layout, fill in the details, and add it to the list.
            var arcade_list_item = $('.arcade-list-item:first-child');
            var arcade_list_items = [];
            for (var i = 0; i < locations.length; ++i) {
                var arcade = arcade_list_item.clone();
                arcade.find('.arcade-name').text(locations[i].name);
                arcade.find('.arcade-city').text(locations[i].city);
                arcade.find('.arcade-distance').text(locations[i].distance);
                arcade.find('.arcade-latitude').text(locations[i].lat.toFixed(5));
                arcade.find('.arcade-longitude').text(locations[i].lng.toFixed(5));
                arcade.find('.arcade-info-name').text(info_name(data.sources, locations[i].src));
                arcade.find('.arcade-has-ddr-value').addClass(checkmark_cross(locations[i].hasDDR));

                var mapsuffix = locations[i].lat + ',' + locations[i].lng + '(' + encodeURIComponent(locations[i].name) + ')';
                arcade.find('.arcade-nav').attr('href', nav_url(locations[i].lat, locations[i].lng, locations[i].name));
                arcade.find('.arcade-gmaps').attr('href', GMAPS_PREFIX + mapsuffix);
                arcade.find('.arcade-info').attr('href',
                    info_url(data.sources, locations[i].src, locations[i].id, locations[i].sid));

                // Hide results past 5, to be revealed via "Load More Results..."
                if (i >= 5) {
                    arcade.hide();
                }
                arcade_list_items.push(arcade);
                arcade.appendTo(arcade_list);

                // The first 5 locations get added to the "Your Location" mini-map as well.
                // To keep the map from zooming out too much, the distance is capped to 15km past the first 3 results,
                // unless result #2 already exceeded these bounds (sparse area).
                if (i < 5 && (i < 3 || locations[i].distance < 15 || locations[1].distance >= 15)) {
                    locationMap.addMarker(locations[i].lat, locations[i].lng);
                }
            }

            // Execute accordion function on list manually after populating it,
            // since the library attaches click events to the list items themselves (attaching to nothing on page load)
            arcade_list.Accordion();

            // Set up "Load More Results..." behavior
            var loadMoreLink = $('#arcade-list-loadmore');
            if (locations.length <= 5) {
                loadMoreLink.hide();
            }

            var nextReveal = 5;
            loadMoreLink.on('click', function() {
                for (var i = nextReveal; i < nextReveal + 5 && i < arcade_list_items.length; ++i) {
                    arcade_list_items[i].show(400);
                }

                // https://www.abeautifulsite.net/smoothly-scroll-to-an-element-without-a-jquery-plugin-2
                $('html, body').animate({
                    scrollTop: loadMoreLink.offset().top
                }, 800);

                nextReveal = i;
                if (nextReveal >= arcade_list_items.length) {
                    loadMoreLink.hide(200);
                }
            });
        }
    };

    // Arcade location API error handler
    var handle_data_error = function() {
        $('#message-found-searching').hide();
        $('#message-api-failed').show();
    };

    // Load location map based on builder so far
    var load_location_map = function() {
        $('#current-location-link').empty();
        $('<img id="current-location-img" alt="Current Location" width="296" height="216" src="' + locationMap.getURL() + '">')
            .appendTo('#current-location-link');
    };

    // Geolocation ok handler
    var handle_geolocation_ok = function(position) {
        $('#message-waiting').hide();
        $('#message-found-searching').show();
        var coords = position.coords.latitude.toFixed(4) + ',' + position.coords.longitude.toFixed(4);
        $('#current-location-link').attr('href', 'ng/?ll='+coords+'&z=16');
        locationMap = new MapBuilder();
        locationMap.addMyLocationMarker(position.coords.latitude, position.coords.longitude);

        // Locate nearby machines and populate/show list
        $.getJSON('locate.php', {
            'version': 20,
            'datasrc': datasrc,
            'lat': position.coords.latitude,
            'lng': position.coords.longitude,
            'canHandleLargeDataset': true
        }).done(handle_data).fail(handle_data_error).always(load_location_map);
    };

    // Code below executes on page load.

    // Clicking the application title returns to main page.
    $('#app-title').on('click', function() {
        location.href = './';
    });

    // Remove "display: none" from accordion elements after slideUp() animation complete; for print support.
    // Attached to beforeprint (IE, Firefox) and mediaQueryList (WebKit).
    var accordion_remove_style = function () { $('[data-role="accordion"] > li > div').removeAttr('style') };
    if (typeof window['onbeforeprint'] !== 'undefined') {
        window['onbeforeprint'] = accordion_remove_style;
    } else if (window.matchMedia) {
        window.matchMedia('print').addListener(accordion_remove_style);
    }

    $('#message-loading').hide();

    // Passing in loc=latitude/longitude in hash/search bypasses original behavior of geolocation on page load.
    // Note: the pattern is set up to accept the old loc=accuracy/latitude/longitude format while ignoring accuracy.
    var loc_pattern = /[#&?]loc=(?:.*\/)?(.*)\/([^&]*)/;
    var handle_loc_hash = function () {
        if (loc_pattern.test(location.href)) {
            var loc_params = loc_pattern.exec(location.href);
            handle_geolocation_ok({
                coords: {
                    latitude: Number(loc_params[1]),
                    longitude: Number(loc_params[2])
                }
            });
        }
    };

    // Passing in src=datasrc in hash/search bypasses original behavior of picking data source from localStorage on page load.
    var src_pattern = /[#&?]src=([^&]*)/;
    var handle_src_hash = function () {
        if (src_pattern.test(location.href)) {
            var src_param = src_pattern.exec(location.href);
            datasrc = src_param[1];
        }
    };

    // Combination hash handler, attach to hash change plus page load.
    var handle_hash = function () {
        handle_src_hash();
        handle_loc_hash();
    };
    $(window).on('hashchange', handle_hash);
    handle_hash();

    // If no loc=, use original behavior of calculating location and using localStorage for data source.
    if (!loc_pattern.test(location.href)) {
        // Geolocation feature detection from Modernizr
        if ('geolocation' in navigator) {
            $('#message-waiting').show();
            // Function explained in http://diveintohtml5.info/geolocation.html
            navigator.geolocation.getCurrentPosition(handle_geolocation_ok, handle_geolocation_error, {
                enableHighAccuracy: false,
                timeout: 5000,
                maximumAge: 300000
            });
        }
        else {
            $('#message-nogeo').show();
        }
    }
});