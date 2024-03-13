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
set_include_path(get_include_path() . PATH_SEPARATOR . '../lib/');
spl_autoload_extensions('.php');
spl_autoload_register();

// For each source, set up the file path and file handle
$sources = [];
foreach (Sources::$data as $source) {
    $filename = __DIR__ . "/../web/v4/all/{$source['id']}.geojson";
    $handle = fopen($filename, 'w');
    if ($handle === false) {
        die("Unable to open file {$filename} for writing");
    }

    $sources[$source['id']] = (object) [
        'filename' => $filename,
        'handle' => $handle,
        'locations' => 0,
        'bytesWritten' => 0,
    ];
}

// Prepare GeoJSON FeatureCollection wrapper in each file
foreach ($sources as $source) {
    fwrite($source->handle, '{"type": "FeatureCollection","features": [');
}

// Set up database connection, query database table once, then sort out the results into the different files
$dbh = PDOHelper::getConnection();
$locations = $dbh->query("SELECT * FROM `locations`", PDO::FETCH_ASSOC);
while ($location = $locations->fetch()) {
    $source = $sources[$location['source_type']];
    $leadingComma = $source->locations > 0 ? ',' : '';

    // Writing the GeoJSON manually to ensure things like numbers are rounded out
    $latitude = number_format($location['latitude'], 4);
    $longitude = number_format($location['longitude'], 4);
    $sid = json_encode($location['source_id'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $name = json_encode($location['name'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $city = json_encode($location['city'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

    // Game availability field logic
    // TODO: -1 for no data available sources, random positive integer when available
    // TODO: With respect for DDR Navi, keep it 1 when available

    $geoJson = <<<JSON
    {$leadingComma}{
      "type": "Feature",
      "id": {$location['id']},
      "geometry": {
        "type": "Point",
        "coordinates": [{$longitude}, {$latitude}]
      },
      "properties": {
        "src": "{$location['source_type']}",
        "sid": {$sid},
        "name": {$name},
        "city": {$city},
        "country": "",
        "has:ddr": {$location['hasDDR']},
        "has:piu": -1,
        "has:smx": -1
      }
    }
    JSON;

    $source->locations += 1;
    $source->bytesWritten += fwrite($source->handle, $geoJson . PHP_EOL);
}

// Wrap up the GeoJSON FeatureCollection wrapper and close each file
foreach ($sources as $source) {
    fwrite($source->handle, ']}');
    fclose($source->handle);
}

var_dump($sources);
echo 'Success' . PHP_EOL;
