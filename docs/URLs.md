# URLs #
This document describes all site/app URLs and their purposes, including legacy and deprecated.

All URL and UIs are now handled by the ddr-finder-ng repository.

*Italic URLs* indicate deprecated URLs.

## Locate API ##
* /locate.php (v1, v2, v3)
* /api/v4 (v4)

See API.md and APIv4.md.

## App(-like) ##
* /app

### Description
* Lacks activity name (e.g. nearby/map).
* Default is nearby for web app, map on phone apps.

## Nearby search ##
* /app/nearby/#ll={latitude},{longitude}(&src={dataSrc}&games={gameIds})(&selected={id})
* */locator(.html)*
* */locator(.html)#loc={accuracy}/{latitude}/{longitude}&src={dataSrc}*
* */locator(.html)?loc={accuracy}/{latitude}/{longitude}&src={dataSrc}*

### Description
* locator.html is the original "nearby" Web UI (originally provided by this project).
* If all parameters are empty, scan for the user's current location and use their dataSrc/gameIds settings.
* If ll is set but not others, use their default values.
* gameIds is a comma-separated list of games that an arcade must have at least one of (empty for no filter).
* In the new version, accuracy will be exact when the user scans for their location,
  but approximate when ll is populated via a shared link.
* If selected is set, pre-open and scroll to the given arcade on results load.

## Interactive Map ##
* /app/map/#ll={latitude},{longitude}&z={zoom}(&src={dataSrc}&games={gameIds})(&selected={id})
* */ng(/index.html)*
* */ng/index.html?mode=standalone*
* */ng(/index.html)?ll={latitude},{longitude}&z={zoom}*
* */app/map/@{latitude},{longitude},{zoom}z*

### Description
* Old /app/map links will redirect such that the zoom parameter has a z in it that must be ignored.
* Lack of location data implies last navigated location or default (which may be current).
* Lack of dataSrc or gameIds imply default. /ng does not support the dataSrc parameter.
* In the new version, zoom will be 1-decimal fractional and latitude/longitude will be truncated
  depending on the zoom level, matching the behavior on openstreetmap.org.
* If selected is set, adjust the map to accommodate it on screen and show its details.
