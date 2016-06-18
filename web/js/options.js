/*! ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE */
// Functionality for main page
$(window).load(function() {
    // Save selected source on click
    $('#source-select').on('click', 'button', function() {
        localStorage.setItem('datasrc', this.id);
    });
    // Set current source as selected button
    var datasrc = localStorage.getItem('datasrc');
    if (null === datasrc) datasrc = 'ziv';
    $('#' + datasrc).addClass('active');
});