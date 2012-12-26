// Functionality for main page
$(window).load(function () {
    var enableHighAccuracy = $('#enable-high-accuracy');
    // Load the previous option on page load
    enableHighAccuracy[0].checked = ('localStorage' in window && window['localStorage'] != null && window.localStorage['highAccuracy'] == "true");

    // Save the option for using GPS in the locator (see http://diveintohtml5.info/storage.html)
    enableHighAccuracy.change(function () {
        if ('localStorage' in window && window['localStorage'] != null) {
            window.localStorage['highAccuracy'] = $('#enable-high-accuracy')[0].checked;
        }
    });
});