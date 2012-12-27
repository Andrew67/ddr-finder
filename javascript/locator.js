// Funcationality for locator page
$(window).load(function () {
    // Prefixes for arcade item info/navigation links
    var GMAPS_PREFIX = 'https://maps.google.com/?q=loc:';
    var ZIV_PREFIX = 'http://m.zenius-i-vanisher.com/arcadelocations_viewarcade.php?locationid=';
    var NAV_PREFIX_ANDROID = 'geo:0,0?q=loc:';
    var NAV_PREFIX_IOS = 'maps:?q=&saddr=Current%20Location&daddr=loc:';
    var NAV_PREFIX_WP7 = 'bingmaps:?q=';
    var NAV_PREFIX = '#';
    if (/Android/i.test(navigator.userAgent)) NAV_PREFIX = NAV_PREFIX_ANDROID;
    else if (/(iPhone)|(iPad)/i.test(navigator.userAgent)) NAV_PREFIX = NAV_PREFIX_IOS;
    else if (/Windows Phone/i.test(navigator.userAgent)) NAV_PREFIX = NAV_PREFIX_WP7;

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
    var handle_data = function(locations) {
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
            // For each location found, clone the main layout, fit in the details and add it to the list
            for (var i = 0; i < locations.length; ++i) {
                var arcade = arcade_list_item.clone();
                arcade.find('.arcade-name').text(locations[i].name);
                arcade.find('.arcade-city').text(locations[i].city);
                arcade.find('.arcade-distance').text(locations[i].distance);
                // Encode location name as label (supported in Google Maps, at least, but they don't like () in the label)
                var label = locations[i].name.replace(/\(/g, '[').replace(/\)/g, ']');
                var mapsuffix = locations[i].latitude + ',' + locations[i].longitude + '(' + encodeURI(label) + ')';
                arcade.find('.arcade-nav').attr('href', NAV_PREFIX + mapsuffix);
                arcade.find('.arcade-gmaps').attr('href', GMAPS_PREFIX + mapsuffix);
                arcade.find('.arcade-ziv').attr('href', ZIV_PREFIX + locations[i].id);
                arcade.appendTo(arcade_list);
            }
            // Execute accordion function on list manually after populating it,
            // since the library attaches click events to the list items themselves (attaching to nothing on page load)
            arcade_list.Accordion();
        }
    };

    // Geolocation ok handler
    var handle_ok = function(position) {
        $('#message-waiting').hide();
        $('#message-found-searching').show();
        var coords = '' + position.coords.latitude + ',' + position.coords.longitude;
        $('#current-location-link').attr('href', 'http://maps.google.com/maps?q='+coords+'&ll='+coords+'&z=16&t=h');
        $('#current-location-img').attr(
            'src',
            'http://maps.google.com/maps/api/staticmap?center='+coords+'&zoom=16&size=288x216&markers='+coords+'&sensor=false'
        );
        var accuracy = (position.coords.accuracy >= 1000) ?
            '' + (position.coords.accuracy / 1000) + 'km' :
            '' + position.coords.accuracy + ' meters';
        $('#current-location-accuracy').text(accuracy);

        // Locate nearby machines and populate/show list
        $.getJSON('locate.php', {
            'lat': position.coords.latitude,
            'long': position.coords.longitude,
            'timestamp': position.timestamp
        }, handle_data);
    };

    // Geolocation feature detection from Modernizr
    if ('geolocation' in navigator) {
        $('#message-loading').hide();
        $('#message-waiting').show();
        // Check user setting for high accuracy
        var highAccuracy = ('localStorage' in window &&
            window['localStorage'] != null &&
            window.localStorage['highAccuracy'] == "true");
        // Function explained in http://diveintohtml5.info/geolocation.html
        navigator.geolocation.getCurrentPosition(handle_ok, handle_error, {
            enableHighAccuracy: highAccuracy,
            maximumAge: 75000
        });
    }
    else {
        $('#message-loading').hide();
        $('#message-nogeo').show();
    }
});