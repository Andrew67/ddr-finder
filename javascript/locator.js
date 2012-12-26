// Funcationality for locator page
$(window).load(function () {
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
            for (var i = 0; i < locations.length; ++i) {
                arcade_list.append('<li><a href="#">' + locations[i].name + '</a><div><h3>'
                    + locations[i].city + '</h3><h4>Approximately '
                    + locations[i].distance + 'km away</h4><a class="button bg-color-green fg-color-white" href="https://maps.google.com/?q=loc:'
                    + locations[i].latitude + ',' + locations[i].longitude + '">Find on Google Maps <i class="icon-location-2"></i></a><br>'
                    + '<a class="button bg-color-blueDark fg-color-white" href="http://m.zenius-i-vanisher.com/arcadelocations_viewarcade.php?locationid='
                    + locations[i].id + '">Info on Zenius -I- vanisher.com <i class="icon-new-tab"></i></a>'
                    + '</div></li>');
            }
            // Load accordion library after preparing list, because it attaches click events to list elements themselves
            $('body').append('<script src="javascript/accordion.js"><\/script>');
        }
    };
    // Geolocation ok handler
    var handle_ok = function(position) {
        $('#message-waiting').hide();
        $('#message-found-searching').show();
        var coords = '' + position.coords.latitude + ',' + position.coords.longitude;
        $('#current-location-link').attr('href', 'http://maps.google.com/maps?q='+coords+'&ll='+coords+'&z=16&t=h');
        $('#current-location-img').attr('src', 'http://maps.google.com/maps/api/staticmap?center='+coords+'&zoom=16&size=288x216&markers='+coords+'&sensor=false');
        var accuracy = (position.coords.accuracy >= 1000) ?
            '' + (position.coords.accuracy / 1000) + 'km' :
            '' + position.coords.accuracy + ' meters';
        $('#current-location-accuracy').text(accuracy);

        // Locate nearby machines and populate/show list
        $.getJSON('locate.php', {'lat': position.coords.latitude, 'long': position.coords.longitude, 'timestamp': position.timestamp}, handle_data);
    };
    // Geolocation feature detection from Modernizr
    if ('geolocation' in navigator) {
        $('#message-loading').hide();
        $('#message-waiting').show();
        // Check user setting for high accuracy
        var highAccuracy = ('localStorage' in window && window['localStorage'] != null && window.localStorage['highAccuracy'] == "true");
        // Function explained in http://diveintohtml5.info/geolocation.html
        navigator.geolocation.getCurrentPosition(handle_ok, handle_error, {enableHighAccuracy: highAccuracy, maximumAge: 75000});
    }
    else {
        $('#message-loading').hide();
        $('#message-nogeo').show();
    }
});