/*! ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE */
"use strict";
// Functionality for main page
$(function() {
    var sourceSelect = $('#source-select');

    // Save selected source on click
    sourceSelect.on('change', function() {
        localStorage.setItem('datasrc', sourceSelect.val());
    });

    // Set current source as selected button
    var datasrc = localStorage.getItem('datasrc');
    if (null === datasrc) datasrc = 'ziv';
    sourceSelect.val(datasrc);

    // When possible, do geolocation check on main site page, then redirect to a locator with location in URL.
    // This allows us to bypass the "don't request geolocation on page load" violation.
    // As a bonus, the user can share the results screen URL for their location.
    // In case of error, keep original behavior of navigating to locator.html with no location in URL, to retry or show error.
    $('#locate-nearby').on('click', function () {
        var navigateToLocator = function () { location.href = 'locator.html'; },
            navigateToLocatorWithPosition = function (position) {
                // Trim to 4 digits, good for ~10m precision.
                var accuracy = Math.max(10, Math.round(position.coords.accuracy));
                location.href = 'locator.html#loc=' +
                    accuracy + '/' + position.coords.latitude.toFixed(4) + '/' + position.coords.longitude.toFixed(4);
            };

        // Convert search icon to loading spinner.
        $(this).find('i').removeClass('icon-search').addClass('icon-loading icon-ani-spin');

        // Ideal path version from locator.js; locator.html can handle error scenarios.
        try {
            navigator.geolocation.getCurrentPosition(navigateToLocatorWithPosition, navigateToLocator, {
                enableHighAccuracy: false,
                timeout: 5000,
                maximumAge: 300000
            });
        } catch (e) {
            navigateToLocator();
        }
        return false;
    });
});