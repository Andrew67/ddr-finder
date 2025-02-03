<?php
/*
 * ddr-finder
 * Copyright (c) 2015-2025 Andrés Cordero
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
// Locator API v2.0/v3.0/v3.1

// Whether to restrict box mode queries to a 1° by 1° box.
// These queries are not expensive for us, but may overload certain clients.
define('RESTRICT_BOX_SIZE', !isset($_GET['canHandleLargeDataset']));

// Whether to add deprecation fields for the Google Play Android app
define('ADD_DEPRECATION_FLAGS', isset($_GET['showDeprecationFlags']));

// Test for presence of "dump" field, as it overrides all others
if (isset($_GET['dump'])) {
    $mode = 'dump';
    $_GET['datasrc'] = 'all';
}

// Set up the datasrc array based on the following rules:
// - $_GET['datasrc'] overrides $_COOKIE['datasrc'].
// - If neither value is present, throw error.
if (empty($_GET['datasrc']) && empty($_COOKIE['datasrc'])) {
    echo APIError::getError(APIError::MISSING_REQUIRED_FIELD, 'The \'datasrc\' field is required, but was not specified.');
    exit(1);
}
/** @var array $datasrc Data sources requested. */
$datasrc = explode(',', (!empty($_GET['datasrc']) ? $_GET['datasrc'] : $_COOKIE['datasrc']));
// Validate data sources and throw error if invalid data source encountered (or if "all" is present, overrides all values)
foreach($datasrc as $source) {
    if ('all' === $source) {
        $datasrc = array('all');
        break;
    } elseif (!Sources::isValidSource($source)) {
        echo APIError::getError(APIError::INVALID_DATA_SOURCE, 'Invalid \'datasrc\' value specified: ' . $source);
        exit(1);
    }
}

// Check for presence of "lat" field to toggle radius mode
if (isset($_GET['lat'])) {
    if (!isset($_GET['lng'])) {
        echo APIError::getError(APIError::MISSING_REQUIRED_FIELD,
            'The \'lat\' field was specified, toggling radius mode, but \'lng\' was not specified.');
        exit(1);
    }
    $mode = 'radius';
    $lat = (float) $_GET['lat'];
    $lng = (float) $_GET['lng'];
    $location = new Coords($lat, $lng);
}

// Box mode: confirm presence of box boundaries
if (!isset($mode)) {
    if (!isset($_GET['latlower']) || !isset($_GET['latupper']) ||
        !isset($_GET['lnglower']) || !isset($_GET['lngupper'])) {
        echo APIError::getError(APIError::MISSING_REQUIRED_FIELD,
            'Box mode was selected, but one or more of the following required fields were not specified: \'latlower\', \'latupper\', \'lnglower\', \'lngupper\'');
        exit(1);
    }
    $mode = 'box';
    $latlower = (float) $_GET['latlower'];
    $lnglower = (float) $_GET['lnglower'];
    $southwest = new Coords($latlower, $lnglower);

    $latupper = (float) $_GET['latupper'];
    $lngupper = (float) $_GET['lngupper'];
    $northeast = new Coords($latupper, $lngupper);

    $boundingBox = new CoordsBox($southwest, $northeast);

    if (RESTRICT_BOX_SIZE && (abs($latupper - $latlower) > 1 || abs($lngupper - $lnglower) > 1)) {
        echo APIError::getError(APIError::OVERSIZED_BOX_FORBIDDEN,
            'Please select box boundaries not exceeding a 1° by 1° box.');
        exit(1);
    }
}

// Set up JSON result
$result = array();

// Inject source information
// v2.0 is an object keyed by shortName, while v3.0 is an array and each element contains shortName field
// v3.1 onwards introduces a toggle for either format
if (20 <= $_GET['version'] && 30 > $_GET['version']) { // API 20 through 29
    $result['sources'] = Sources::getSourceObject($datasrc);
}
elseif (30 == $_GET['version']) { // API 30
    $result['sources'] = Sources::getSourceArray($datasrc);
}
else { // API 31+
    if (isset($_GET['sourceformat']) && 'object' === $_GET['sourceformat']) {
        $result['sources'] = Sources::getSourceObject($datasrc);
    }
    else { // default is array for unset
        $result['sources'] = Sources::getSourceArray($datasrc);
    }
}

// Inject locations data
$result['locations'] = array();
$lochelper = new LocationsHelper(PDOHelper::getConnection());
// Dump mode
if ('dump' === $mode) {
    $result['locations'] = $lochelper->getDump($_GET['dump'], (31 <= $_GET['version']));
}
elseif ('radius' === $mode) {
    $result['locations'] = $lochelper->getRadius($location, $datasrc, RESTRICT_BOX_SIZE ? 20 : false);
}
else /* if ('box' === $mode) */ {
    $result['locations'] = $lochelper->getBox($boundingBox, $datasrc);
}

// (v3.1+) Convert locations output to GeoJSON if requested
if (31 <= $_GET['version'] && isset($_GET['locationformat']) && 'geojson' === $_GET['locationformat']) {
    $result['locations'] = GeoJSONConverter::convertCollection($result['locations']);
}

// Add deprecation flags if requested
if (ADD_DEPRECATION_FLAGS) {
    $result['deprecations'] = ['googlePlay' => 0];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);