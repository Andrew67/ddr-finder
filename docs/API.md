# locator.php #
All below descriptions are GET requests to locator.php with the given parameters, and JSON is returned as defined.
Technically they're all returned as JSON strings since the PDO driver in PHP returns all results as strings by default.
v1 APIs will only return ZIv-sourced data, while v2 will provide more options.
v3 makes a breaking change to more easily support deserialization of data source information through libraries such as Gson.

## v3.x.2 (deprecation flags) ##
Modification to handle displaying graceful / modal prompts for users to use the web app after the
Google Play Android version is end-of-life.

### Input parameters ###
* showDeprecationFlags (optional): when set to any value, returns a set of booleans meant to be used by the Google Play version of
  the Android app, directing users toward the web app after it's no longer able to receive bugfix updates.

### Return value ###
* Object called deprecations with below fields:
* googlePlay (int): When set to 0, no change is made to the app UI.
  When set to 1, the UI must show a gentle clickable banner guiding users to check out the web app.
  When set to 2, the UI must show a modal which forces users into the web app.
  This will only happen once the web app has feature parity, _or_ the app has broken in a way that needs an update.

## v3.x.1 / v2.0.1 / v1.x.1 (large data set support) ##
Adds a version-independent optional flag that clients can set to say "I can gracefully handle more than 20 results in
radius mode, and box mode results for an 'oversized' box". As clients get upgraded (pagination, pin consolidation),
they can start setting this flag. The server implementation is free to refuse and return the usual "oversized box" error,
so box mode clients must still be prepared to show the error.
Original implementations count on the server implementing these restrictions.
As various clients have sprung up and the results compress well, this has made less sense to keep as-is.

### Input parameters ###
* canHandleLargeDataset (optional): the mere presence of this field lets the server know it should feel free to send
  more than 20 results in radius mode, and big boxes in box mode. The absence of this field is the default legacy behavior of capping results/queries on the server side to make up for client side deficiencies.

## v3.1 (sync support, sources fluidity, GeoJSON) ##
Adds new features to facilitate clients that want to sync using the "dump" command,
clients that prefer the v2.0 data source keying, and clients that have access to GeoJSON libraries.
Input/output is the same as v2.0, with the following additions/modifications:

### Input parameters ###
* version (int): Must be set to 31 to toggle this API version.
* sourceformat (string, optional): "array"/"object", defaults to "array", determines the format of the sources output.
* locationformat (string, optional): "ddrfinder"/"geojson", defaults to "ddrfinder", determines the format of the locations output.

### Return value ###
* sources: When sourceformat is set to "array", the output matches v3.0 output.
  When it is set to "object", it matches v2.0 output (object keyed by shortName).
* locations: When locationformat is set to "ddrfinder", the output matches v2.0 (plus the addition below).
  When it is set to "geojson", this key is a proper GeoJSON data structure representing a FeatureCollection,
  and each location is a Feature, where every value that the "ddrfinder" format provides is provided
  in the "properties" object (except the coordinates). For example (note that lng goes before lat!):
```
{ "type": "FeatureCollection",
    "features": [
      { "type": "Feature",
        "geometry": {"type": "Point", "coordinates": [/*lng*/ 67.0, /*lat*/ 0.5]},
        "properties": {
            "name": "Jimmy's Arcade",
            "id": 38,
            /* ... */
        }
      }
    ]
}
```
#### Locations (JSON array) ####
A new field is added to location objects:
* deleted (boolean, optional): 0 for false, 1 for true; appears only in "dump" mode when the timestamp
  is not equal to 0, and when true is used to mean that the given entry represents that the entry with the given id
  has been removed from the database between the timestamp and now.
  Clients that use "dump" to sync should remove it from their cache. No other fields are guaranteed to be valid (not even src/sid).
  A client that calls itself API v3.1 compliant but does not intend to delete should take care to ignore entries that have this flag set to true.

## v3.0 (sources as array mode) ##
Created to allow Gson and similar libraries to be used for API clients.
Input/output is the same as v2.0, with the following additions/modifications:

### Input parameters ###
* version (int): Must be set to 30 to toggle this API version.

### Return value ###
* sources (**array**, see below): array of metadata describing the sources used in the creation of the result.

#### Sources (JSON array) ####
* shortName (string): id of source (e.g. "ziv", "navi", "fallback").

## v2.0 (multi-source mode) ##
Use when you want data from a different source than ZIv. v2 output is not compatible with v1 clients, as the base JSON is an object containing metadata.

### Input parameters ###
* version (int): Must be set to 20 to toggle this API version.
* dump (int, unix timestamp, optional): when this field is present, all the below fields are ignored.
  Requests a dump of all data updated after the given timestamp. Use 0 to catch all data. The server is not required to implement this and may throw an error instead.
* datasrc* (string): comma-separated list of source aliases to request data from (currently valid are "ziv", "navi", and "osm"), or the catch-all value "all".
* lat (double): current user latitude. The presence of this field determines radius mode.
* lng (double): current user longitude. Required when lat is specified.
* latlower (double): lower latitude boundary of bounding box. Required for box mode if lat is not present.
* latupper (double): upper latitude boundary of bounding box. Required for box mode.
* lnglower (double): lower latitude boundary of bounding box. Required for box mode.
* lngupper (double): upper longitude boundary of bounding box. Required for box mode.

`*` This value can be provided either via a cookie or a GET parameter. GET value takes priority over cookie.

In box mode, server is allowed to, but not required to, throw an error when `abs(latupper - latlower) < 1` and `abs(lnglower - lngupper) < 1` does not hold true.

### Return value ###
Return value is a JSON structure, the top level is as follows:
* error (string, optional): only present when HTTP status code is 4XX. Human-readable explanation of the error condition.
* errorCode (int, optional): only present when HTTP status code is 4XX. Codes:
 * 1: client API version is not supported (for example, a 3.0 server doesn't support 2.0 clients).
 * 20: a required field is missing.
 * 21: invalid data source specified. Server may choose to silently ignore invalid data sources instead.
 * 22: server does not allow use of "dump" flag.
 * 23: server does not allow "oversized box".
 * 42: client is making too many consecutive requests; temporarily blocked ("enhance your calm").
* sources (object, see below): array of metadata describing the sources used in the creation of the result, keyed by shortName (e.g. "ziv, "navi", "fallback").
* locations (array, see below): array of arcade locations.

#### Sources (JSON object, keyed by shortName) ####
Describes a data source. Use the special source "fallback" in case a piece of location data contains a source type that is not part of this array.

Each element contains the following fields:
* name (string): title of source (e.g. "Zenius-I-vanisher.com", "DDR-Navi").
* homepageURL (string): URL of home page for source (e.g. "zenius-i-vanisher.com", "ddr-navi.jp").
* infoURL (string): URL of info page for source. Special strings "${sid}" and "${id}" must be replaced with the value of "source_id" or "id" fields respectively to construct full URL for a location.
* mInfoURL (string): same as infoURL, but for mobile devices.
* hasDDR (boolean): true/false, whether the data source contained enough information to determine a meaningful value for the "hasDDR" field.

#### Locations (JSON array) ####
Each element contains the following fields:
* id (int): location id (can be used to pull up the information page with the fallback infoURL).
* src (string): shortname of data source.
* sid (string): id of the location in the source data (can be used to pull up information using infoURL).
* name (string): name of arcade.
* city (string): city of arcade (e.g. "Raleigh, NC" or "東京都").
  Due to data source limitations, this field may be empty or represent a more vague area (such as a state or prefecture).
* lat (double): latitude of arcade.
* lng (double): longitude of arcade.
* hasDDR (boolean, optional): 0 for false, 1 for true, whether the arcade contains dance games (DDR/ITG/PIU). 
  Due to data source limitations, this field may not provide a meaningful value.
* distance (double, optional): distance in km from input coordinates to arcade location coordinates.
  Present only when the request was made in radius mode.

## v1.1 (radius mode) ##
Use when what you have is the user's current location.

Input parameters:
```
lat (double): current user latitude.
long (double): current user longitude.
```
Returns results within +/- 0.5 degrees of the given coordinates.

Return values within items in JSON array:
```
id (int): ZIv arcade location id (can be used to pull up the information page).
name (string): name of arcade.
city (string): city of arcade (e.g. "Raleigh, NC").
latitude (double): latitude of arcade.
longitude (double): longitude of arcade.
hasDDR (boolean): 0 for false, 1 for true, whether the arcade contains dance games (DDR/ITG/PIU).
distance (double): distance in km from input coordinates to arcade location coordinates.
```

## v1.1 (box mode) ##
Use when what you have is a map view with corners.

Input parameters:
```
source (string): must be set to "android". The presence of this field determines box mode.
latlower (double): lower latitude boundary of bounding box.
latupper (double): upper latitude boundary of bounding box.
longlower (double): lower longitude boundary of bounding box.
longupper (double): upper longitude boundary of bounding box.
```
Where `abs(latupper - latlower) < 1` and `abs(longlower - longupper) < 1`.

Returns results within the bounding box coordinates.

Return values within items in JSON array:
```
id (int): ZIv arcade location id (can be used to pull up the information page).
name (string): name of arcade.
city (string): city of arcade (e.g. "Raleigh, NC").
latitude (double): latitude of arcade.
longitude (double): longitude of arcade.
hasDDR (boolean): 0 for false, 1 for true, whether the arcade contains dance games (DDR/ITG/PIU).
```