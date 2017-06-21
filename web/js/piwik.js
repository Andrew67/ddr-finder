"use strict";
var _paq = _paq || [];
_paq.push(["setDomains", ["*.ddrfinder.andrew67.com"]]);
_paq.push(['trackPageView']);
(function() {
    var u="//analytics.andrew67.com/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '2']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
})();

$(function() {
    $('#android-app-download').on('click', function() {
        _paq.push(['trackGoal', 2]);
    });
    var arcadeList = $('#arcade-list');
    arcadeList.on('click', '.arcade-nav', function() {
        _paq.push(['trackGoal', 3]);
    });
    arcadeList.on('click', '.arcade-info', function() {
        _paq.push(['trackGoal', 5]);
    });
    arcadeList.on('click', '.arcade-gmaps', function() {
        _paq.push(['trackGoal', 6]);
    });
});