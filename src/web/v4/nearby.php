<?php
/*
 * ddr-finder
 * Copyright (c) 2024 Andrés Cordero
 *
 * Web: https://github.com/Andrew67/ddr-finder
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Set response type and encoding
header('Content-Type: application/geo+json; charset=utf-8');

// Set up class autoloader
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../lib/');
spl_autoload_extensions('.php');
spl_autoload_register();

// Prod / Dev CORS
if (CORSHelper::isCORSAuthorized()) {
    header('Access-Control-Allow-Origin: *');
} else {
    // Avoid expending server resources for unauthorized origins
    header('HTTP/1.1 403 Forbidden');
    exit(1);
}

// Locator API v4.0

// Input field validations
if (empty($_GET['src'])) {
    echo APIError::getError(APIError::MISSING_REQUIRED_FIELD, "The 'src' field is required, but was not specified.");
    exit(1);
}

$sourceId = $_GET['src'];
$source = Sources::$data[$sourceId];
// Validate data source and throw error if invalid data source encountered
if ($source === null) {
    echo APIError::getError(APIError::INVALID_DATA_SOURCE, "Invalid 'src' value specified: {$sourceId}");
    exit(1);
}

// Validate coordinates:
// - Both are present with ","
// - No more than 4 decimal digits each (helps with caching)
if (empty($_GET['ll'])) {
    echo APIError::getError(APIError::MISSING_REQUIRED_FIELD, "The 'll' field is required, but was not specified.");
    exit(1);
}
$latLng = explode(",", $_GET['ll']);
if (count($latLng) < 2) {
    echo APIError::getError(APIError::MISSING_REQUIRED_FIELD, "The 'll' field was specified, but either the latitude or longitude are missing.");
    exit(1);
}
if (preg_match("/\.\d{5,}$/", $latLng[0]) || preg_match("/\.\d{5,}$/", $latLng[1])) {
    echo APIError::getError(APIError::TOO_MANY_DECIMALS_COORDINATES, "The 'll' field was specified, but either the latitude or longitude have too many decimal digits (more than 4).");
    exit(1);
}

// Set Expires header to the time the database update scripts run
$expiresTimestamp = strtotime("next Tuesday 1:20AM");
$expiresTimeString = gmdate('D, d M Y H:i:s \G\M\T', $expiresTimestamp);
header("Expires: {$expiresTimeString}");

$coordinates = new Coords((float) $latLng[0], (float) $latLng[1]);

// TODO: Game filters

// Grab limit parameter if available, otherwise set to 10 (max 50)
$limit = (is_numeric($_GET['limit'])) ? $_GET['limit'] : 10;
if ($limit < 1 || $limit > 50) {
    echo APIError::getError(APIError::OUT_OF_BOUNDS_LIMIT, "The 'limit' field was provided, but was not between 1 and 50 (inclusive).");
    exit(1);
}

// Set up JSON result
// Inject locations data
$locationsHelper = new LocationsHelper(PDOHelper::getConnection());
$locations = $locationsHelper->getRadius($coordinates, [$sourceId], $limit);
$geoJSONConverter = new GeoJSONConverter(new GameAvailabilityHelper(), $source);

// bbox: SW point, NE point
$bbox = json_encode([
    $coordinates->lng - 0.5, $coordinates->lat - 0.5,
    $coordinates->lng + 0.5, $coordinates->lat + 0.5]);

echo "{\"type\":\"FeatureCollection\",\"bbox\":{$bbox},\"features\":[";
$leadingComma = '';
foreach ($locations as $item) {
    echo $leadingComma . json_encode($geoJSONConverter->convertFeatureV4($item), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $leadingComma = ',';
}
echo ']}';
