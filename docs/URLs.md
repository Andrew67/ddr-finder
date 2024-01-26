# URLs #
This document describes all site/app URLs and their purposes, including legacy and deprecated.

*Italic URLs* indicate deprecated URLs.

## Locate API ##
* /locate.php (v1, v2, v3)
* /api/v4 (v4)

See API.md and APIv4.md.
APIv4 is still in the design phase.

## App(-like) ##
* /app

### Description
* Lacks activity name (e.g. nearby/map).
* Default is nearby for web app, map on phone apps.

## Nearby search ##
* */locator(.html)*
* */locator(.html)#loc={accuracy}/{latitude}/{longitude}&src={datasrc}*
* */locator(.html)?loc={accuracy}/{latitude}/{longitude}&src={datasrc}*

### Description
* locator.html is the original "nearby" Web UI (provided by this project).
* Old links will have the html extension and no location data.
* Lack of location data implies current location, lack of src implies default.
* It is still perfectly functional for basic usages, and will be kept up, pending optimizations to size and load time.
* It will include links to the app versions.

## Interactive Map ##
* */ng(/index.html)*
* */ng/index.html?mode=standalone*
* */ng(/index.html)?ll={latitude},{longitude}&z={zoom}*
* */app/map/@{latitude},{longitude},{zoom}z*

### Description
* /ng is the original "app-like" Web UI (provided by ddr-finder-ng).
* Old homescreen links will have index.html and mode=standalone.
* Lack of location data implies last navigated location or default (which may be current).
* Lack of datasrc implies default. /ng does not support the datasrc parameter.