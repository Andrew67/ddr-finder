/*! ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE */
// Funcationality for locator page
$(window).load(function () {
    // Google Maps Static API builder
    function MapBuilder() {
        // Google Maps Static API Key
        var GMAPS_API_KEY = 'AIzaSyAek3wRV_aVi5ZPR8tkI4WxsGcVZjz8MaE';
        this.url = 'https://maps.google.com/maps/api/staticmap?size=288x216&key='+GMAPS_API_KEY;
        this.nextMarkerNumber = 1;
    }
    MapBuilder.prototype.getURL = function() { return this.url; };
    MapBuilder.prototype.addMyLocationMarker = function(coords) { // Coords in "lat,lng" format
        this.url += '&markers='+coords;
    };
    MapBuilder.prototype.addMarker = function(coords) { // Warning: Static Maps API limited to 9 for labels
        this.url += '&markers=color:blue|label:'+(this.nextMarkerNumber++)+'|'+coords;
    };
    var locationMap = new MapBuilder();

    // Prefixes for arcade item navigation links
    var GMAPS_PREFIX = 'https://maps.google.com/?q=loc:';
    var NAV_PREFIX_ANDROID = 'geo:';
    var NAV_PREFIX_IOS = 'maps:?q=&saddr=Current%20Location&daddr=loc:';
    var NAV_PREFIX_WP7 = 'maps:';
    var NAV_PREFIX = '#';

    // Navigation URL generator functions
    var nav_url = function(latitude, longitude, label) {
        return NAV_PREFIX + latitude + ',' + longitude + '(' + encodeURI(label) + ')';
    };
    var nav_url_android = function(latitude, longitude, label) {
        return NAV_PREFIX_ANDROID + latitude + ',' + longitude + '?q=' +
            latitude + ',' + longitude + '(' + encodeURI(label) + ')';
    };
    var nav_url_ios = function(latitude, longitude, label) {
        return NAV_PREFIX_IOS + latitude + ',' + longitude + '(' + encodeURI(label) + ')';
    };
    var nav_url_wp7 = function(latitude, longitude) {
        return NAV_PREFIX_WP7 + latitude + ' ' + longitude;
    };

    // Detect platform and set generator function
    var platform = 'mobile';
    if (/Android/i.test(navigator.userAgent)) nav_url = nav_url_android;
    else if (/(iPhone)|(iPad)/i.test(navigator.userAgent)) nav_url = nav_url_ios;
    else if (/Windows Phone/i.test(navigator.userAgent)) nav_url = nav_url_wp7;
    else platform = 'pc';

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

    // Geolocation error handler
    var handle_error = function(error) {
        // Permission denied
        if (error.code == 1) {
            $('#message-waiting').hide();
            $('#message-denied').show();
        }
        // Position unavailable or timeout
        else if (error.code == 2 || error.code == 3) {
            $('#message-waiting').hide();
            $('#message-failed').show();
        }
    };

    // Arcade location data handler function
    var handle_data = function(data) {
        var locations = data['locations'];

        $('#message-found-searching').hide();
        $('#message-arcade-list').show();
        var arcade_list = $('#arcade-list');
        if (locations.length == 0) {
            $('#arcade-list-container').hide();
            $('#arcade-noresults-container').show();
        }
        else {
            // Grab the item layout element
            var arcade_list_item = $('.arcade-list-item:first-child');
            // For each location found, clone the main layout, fill in the details, and add it to the list
            for (var i = 0; i < locations.length; ++i) {
                var arcade = arcade_list_item.clone();
                arcade.find('.arcade-name').text(locations[i].name);
                arcade.find('.arcade-city').text(locations[i].city);
                arcade.find('.arcade-distance').text(locations[i].distance);
                arcade.find('.arcade-latitude').text(locations[i].lat.toFixed(6));
                arcade.find('.arcade-longitude').text(locations[i].lng.toFixed(6));
                arcade.find('.arcade-info-name').text(info_name(data.sources, locations[i].src));
                // Encode location name as label (supported in Google Maps, at least, but they don't like () in the label)
                var label = locations[i].name.replace(/\(/g, '[').replace(/\)/g, ']');
                var mapsuffix = locations[i].lat + ',' + locations[i].lng + '(' + encodeURI(label) + ')';
                arcade.find('.arcade-nav').attr('href', nav_url(locations[i].lat, locations[i].lng, label));
                arcade.find('.arcade-gmaps').attr('href', GMAPS_PREFIX + mapsuffix);
                arcade.find('.arcade-info').attr('href',
                    info_url(data.sources, locations[i].src, locations[i].id, locations[i].sid));
                arcade.appendTo(arcade_list);

                // The first 5 locations get added to the "Your Location" mini-map as well
                if (i < 5) {
                    locationMap.addMarker(locations[i].lat + ',' + locations[i].lng);
                }
            }
            // Execute accordion function on list manually after populating it,
            // since the library attaches click events to the list items themselves (attaching to nothing on page load)
            arcade_list.Accordion();
        }
    };

    // Arcade location API error handler
    var handle_data_error = function() {
        $('#message-found-searching').hide();
        $('#message-api-failed').show();
    };

    // Load location map based on builder so far
    var load_location_map = function() {
        $('#current-location-img').attr('src', locationMap.getURL());
    };

    // Geolocation ok handler
    var handle_ok = function(position) {
        $('#message-waiting').hide();
        $('#message-found-searching').show();
        var coords = '' + position.coords.latitude + ',' + position.coords.longitude;
        $('#current-location-link').attr('href', 'https://maps.google.com/maps?q='+coords+'&ll='+coords+'&z=16&t=h');
        locationMap.addMyLocationMarker(coords);
        var accuracy = (position.coords.accuracy >= 1000) ?
            '' + (position.coords.accuracy / 1000).toFixed(2) + 'km' :
            '' + position.coords.accuracy.toFixed() + ' meters';
        $('#current-location-accuracy').text(accuracy);

        // Locate nearby machines and populate/show list
        $.getJSON('locate.php', {
            'version': 20,
            'datasrc': datasrc,
            'lat': position.coords.latitude,
            'lng': position.coords.longitude
        }).done(handle_data).fail(handle_data_error).always(load_location_map);
    };

    // Geolocation feature detection from Modernizr
    if ('geolocation' in navigator) {
        $('#message-loading').hide();
        $('#message-waiting').show();
        // Function explained in http://diveintohtml5.info/geolocation.html
        navigator.geolocation.getCurrentPosition(handle_ok, handle_error, {
            enableHighAccuracy: false,
            maximumAge: 75000
        });
    }
    else {
        $('#message-loading').hide();
        $('#message-nogeo').show();
    }

    // Clicking the application title returns to main page
    $('#app-title').on('click', function() {
        window.location.href = "index.html";
    });
});