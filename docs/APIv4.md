# API v4

## Starting notes
1. All requests are `GET` with the magic that avoids having to do CORS `OPTIONS` requests.
2. The order of query parameters must be followed to maximize cache-ability.
3. All cache headers set (`Cache-Control`, `Expires`, etc.) must be honored by CDNs and clients.
4. API implementors are allowed to (and encouraged to) cache and statically generate these endpoints.
5. The starting point for your journey is `/v4/sources.json`.


## /v4/sources.json
The sources endpoint gives you everything you need to know about the different underlying data sources in order to
perform additional queries and present the information properly:
1. Which source is currently the default for API queries.
2. Which games a source dataset is aware of arcades having (such as DDR only, or DDR and PIU).
3. URLs for the home and info pages for each location.
4. And more...

See `/v4/sources.d.ts` for exact field definitions with full JSDocs.


## /v4/all/${src}.geojson
The all endpoint spits out all locations for a given data source `src`.
Consumers are expected to be performant devices using software that's optimized to handle the couple thousand entries,
by way of background threads and other optimizations.
It's expected the burden of the location information is less than the burden of rendering a map on a screen.

The flagship official DDR Finder implementation uses [Mapbox GL JS](https://docs.mapbox.com/mapbox-gl-js/guides).

### Inputs
- `src` (Required): The ID of the data source to use (from the sources endpoint).

### Output
A [GeoJSON](https://geojson.org/) `FeatureCollection` of `Feature` objects, with arcade-specific `properties`.

See `/v4/all.d.ts` for exact field definitions with full JSDocs.

### Errors
- `HTTP 404`: The given data source ID does not exist.


## /v4/all/${src}/${game}.geojson
For data sources that announce support for knowing that arcades have specific games, a pre-filtered data set.
Useful if the consumer only cares about one specific game, and the data is greatly reduced when filtering.

### Inputs
- `src`: See above.
- `game` (Required): The ID of the game to filter by (from the sources endpoint).

### Output
Same as `/v4/all/${src}.geojson`, except the "has game" field for the specific game will always be `true`.

### Errors
- `HTTP 404`: The given data source ID does not exist or
  the data source does not have availability information for that game.


## `/v4/nearby/${src}.geojson`
The nearby API returns a limited amount of locations from a given data source `src`,
in a 1º by 1º bounding box around the given coordinates.
(Up to a 111km radius per [Decimal Degrees](https://en.wikipedia.org/wiki/Decimal_degrees))

This is ideal for consumers that lack the processing power for the full data set, and for quick
"get me locations near me, stat" cases due to the much reduced (but less cache-able) result size,
with the trade-off that coordinates have to be sent to the server side (reduced privacy).

### Inputs
Inputs that are not in the URL are query string parameters, and **MUST** be specified in the same order
as defined in this documentation in order to maximize cache-ability.
- `src` (Required): The ID of the data source to use (from the sources endpoint).
- `ll` (Required): The latitude and longitude to search around, in `latitude,longitude` format (e.g. `18,-66`).
  The amount of decimal digits is limited to 4, and implementations can throw an error if the amount is exceeded.
  This is due to the fact 4 digits is enough for up to 11.1m precision.
  If your geolocation implementation provides uncertainty, consider a further reduction of decimal digits.
- `filter` (Optional): A comma-separated list of game IDs (from the sources endpoint), such that the arcades returned
  each have at least one of the requested games. Game IDs **MUST** be sorted alphabetically, and implementations can
  throw an error if that's not the case.
- `limit` (Optional, default `10`, max `50`): Maximum amount of locations to return.
  Locations are sorted by distance from the coordinates provided in `ll`. `filter` is applied before `limit`.
  As a reminder, locations farther out than the 1º by 1º bounding box will never be returned.

### Output
Same as `/v4/all/${src}.geojson`, except each `Feature` will have a property called `distanceKm`,
containing the distance in kilometers from the coordinates specified in `ll` (max 2 decimal digits).

See `/v4/nearby.d.ts` for exact field definitions with full JSDocs.

### Errors
- `HTTP 404`: The given data source ID does not exist.
- `HTTP 403`: The origin webserver does not have authorization for this API.
- `HTTP 400`: Will contain a human-readable message about which strict URL construction rule was violated
  (parameter order, value order, too many decimal digits in the coordinates, etc).
