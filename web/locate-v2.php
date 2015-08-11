<?php
/*
 * ddr-finder
 * Copyright (c) 2015 Andrés Cordero
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
// Locator API v2.0

// Test for presence of "dump" field, as it overrides all others
if (isset($_GET['dump'])) {
    echo APIError::getError(APIError::DUMP_FORBIDDEN, 'Support for \'dump\' flag is a work in progress.');
    exit(1);
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

// Check for presence of "lat" field to toggle radius mode
if (isset($_GET['lat'])) {
    if (!isset($_GET['lng'])) {
        echo APIError::getError(APIError::MISSING_REQUIRED_FIELD,
            'The \'lat\' field was specified, toggling radius mode, but \'lng\' was not specified.');
        exit(1);
    }
    $mode = 'radius';
    $lat = (int) $_GET['lat'];
    $lng = (int) $_GET['lng'];
}

// Box mode: confirm presence of box boundaries
else {
    if (!isset($_GET['latlower']) || !isset($_GET['latupper']) ||
        !isset($_GET['lnglower']) || !isset($_GET['lngupper'])) {
        echo APIError::getError(APIError::MISSING_REQUIRED_FIELD,
            'Box mode was selected, but one or more of the following required fields were not specified: \'latlower\', \'latupper\', \'lnglower\', \'lngupper\'');
        exit(1);
    }
    $mode = 'box';
    $latlower = (int) $_GET['latlower'];
    $latupper = (int) $_GET['latupper'];
    $lnglower = (int) $_GET['lnglower'];
    $lngupper = (int) $_GET['lngupper'];
}

// Set up JSON result
$result = array();

// Inject source information
$result['sources'] = Sources::getSourceObject($datasrc);

// Inject locations data
$result['locations'] = array();

echo json_encode($result);