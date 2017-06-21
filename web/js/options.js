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
});