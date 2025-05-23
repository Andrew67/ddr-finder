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

// Generate the static `all/${src}.geojson` files from the available data in the database
// Re-run as needed when data changes

// Set up class autoloader
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../lib/');
spl_autoload_extensions('.php');
spl_autoload_register();

// For each source, set up the file path and file handle
$sources = [];
foreach (Sources::$data as $source) {
    $baseFilename = __DIR__ . "/../web/v4/all/{$source['id']}";
    $mainFilename = "{$baseFilename}.geojson";
    $handle = fopen($mainFilename, 'w');
    if ($handle === false) {
        die("Unable to open file {$mainFilename} for writing");
    }

    $ddrHandle = null;
    if ($source['has:ddr']) {
        $ddrFilename = "{$baseFilename}/ddr.geojson";
        $ddrHandle = fopen($ddrFilename, 'w');
        if ($ddrHandle === false) {
            die("Unable to open file {$ddrFilename} for writing");
        }
    }

    $piuHandle = null;
    if ($source['has:piu']) {
        $piuFilename = "{$baseFilename}/piu.geojson";
        $piuHandle = fopen($piuFilename, 'w');
        if ($piuHandle === false) {
            die("Unable to open file {$piuFilename} for writing");
        }
    }

    $smxHandle = null;
    if ($source['has:smx']) {
        $smxFilename = "{$baseFilename}/smx.geojson";
        $smxHandle = fopen($smxFilename, 'w');
        if ($smxHandle === false) {
            die("Unable to open file {$smxFilename} for writing");
        }
    }

    $sources[$source['id']] = (object) [
        'id' => $source['id'],
        'hasDDR' => $source['has:ddr'],
        'hasPIU' => $source['has:piu'],
        'hasSMX' => $source['has:smx'],
        'handle' => $handle,
        'locations' => 0,
        'bytesWritten' => 0,
        'ddr' => (object) [
            'handle' => $ddrHandle,
            'locations' => 0,
            'bytesWritten' => 0,
        ],
        'piu' => (object) [
            'handle' => $piuHandle,
            'locations' => 0,
            'bytesWritten' => 0,
        ],
        'smx' => (object) [
            'handle' => $smxHandle,
            'locations' => 0,
            'bytesWritten' => 0,
        ]
    ];
}

// Prepare GeoJSON FeatureCollection wrapper in each file
foreach ($sources as $source) {
    $geoJsonHeader = '{"type": "FeatureCollection","features": [';
    fwrite($source->handle, $geoJsonHeader);
    if ($source->ddr->handle) fwrite($source->ddr->handle, $geoJsonHeader);
    if ($source->piu->handle) fwrite($source->piu->handle, $geoJsonHeader);
    if ($source->smx->handle) fwrite($source->smx->handle, $geoJsonHeader);
}

// Used below in for the game availability field logic
$gah = new GameAvailabilityHelper();

// Set up database connection, query database table once, then sort out the results into the different files
$dbh = PDOHelper::getConnection();
$locations = $dbh->query("SELECT * FROM `locations`", PDO::FETCH_ASSOC);
while ($location = $locations->fetch()) {
    $source = $sources[$location['source_type']];
    $leadingComma = $source->locations > 0 ? ',' : '';
    $ddrLeadingComma = $source->ddr->locations > 0 ? ',' : '';
    $piuLeadingComma = $source->piu->locations > 0 ? ',' : '';
    $smxLeadingComma = $source->smx->locations > 0 ? ',' : '';

    // Writing the GeoJSON manually to ensure things like numbers are rounded out
    $latitude = number_format($location['latitude'], 4);
    $longitude = number_format($location['longitude'], 4);
    $sid = json_encode($location['source_id'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $name = json_encode($location['name'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $city = json_encode($location['city'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $country = json_encode($location['country'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

    // Game availability fields
    // Since DDR Navi has all locations with DDR, let's set all to 1 for max compression
    $hasDDR = ($source->id === 'navi') ? 1 : $gah->getAvailability($source->hasDDR, $location['hasDDR']);
    $hasPIU = $gah->getAvailability($source->hasPIU, $location['hasPIU']);
    $hasSMX = $gah->getAvailability($source->hasSMX, $location['hasSMX']);

    $geoJson = <<<JSON
    {"type": "Feature",
      "id": {$location['id']},
      "geometry": {"type": "Point",
        "coordinates": [{$longitude}, {$latitude}]
      },
      "properties": {
        "src": "{$location['source_type']}",
        "sid": {$sid},
        "name": {$name},
        "city": {$city},
        "country": {$country},
        "has:ddr": {$hasDDR},
        "has:piu": {$hasPIU},
        "has:smx": {$hasSMX}
      }
    }
    JSON;

    $source->locations += 1;
    $source->bytesWritten += fwrite($source->handle, $leadingComma . $geoJson . PHP_EOL);

    if ($source->ddr->handle && $hasDDR > 0) {
        $source->ddr->locations += 1;
        $source->ddr->bytesWritten += fwrite($source->ddr->handle, $ddrLeadingComma . $geoJson . PHP_EOL);
    }
    if ($source->piu->handle && $hasPIU > 0) {
        $source->piu->locations += 1;
        $source->piu->bytesWritten += fwrite($source->piu->handle, $piuLeadingComma . $geoJson . PHP_EOL);
    }
    if ($source->smx->handle && $hasSMX > 0) {
        $source->smx->locations += 1;
        $source->smx->bytesWritten += fwrite($source->smx->handle, $smxLeadingComma . $geoJson . PHP_EOL);
    }
}

// Wrap up the GeoJSON FeatureCollection wrapper and close each file
foreach ($sources as $source) {
    fwrite($source->handle, ']}');
    fclose($source->handle);

    if ($source->ddr->handle) {
        fwrite($source->ddr->handle, ']}');
        fclose($source->ddr->handle);
    }
    if ($source->piu->handle) {
        fwrite($source->piu->handle, ']}');
        fclose($source->piu->handle);
    }
    if ($source->smx->handle) {
        fwrite($source->smx->handle, ']}');
        fclose($source->smx->handle);
    }
}

echo json_encode($sources, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR);
echo 'Success' . PHP_EOL;
