<?php
/*
 * ddr-finder
 * Copyright (c) 2012-2015 Andrés Cordero
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

if (isset($_GET['lat']) && isset($_GET['long']) && empty($_GET['source'])) {
    $latitude = $_GET['lat'];
    $longitude = $_GET['long'];
    $mode = 'radius';
}
elseif (isset($_GET['source']) && 'android' === $_GET['source']) {
    if (!isset($_GET['latlower']) || !isset($_GET['latupper'])
        || !isset($_GET['longlower']) || !isset($_GET['longupper'])
        || 1 < abs($_GET['latupper'] - $_GET['latlower'])
        || 1 < abs($_GET['longlower'] - $_GET['longupper'])
    ) {
        header('HTTP/1.1 400 Bad Request');
        echo '<h1>400 Bad Request</h1>
                <h2>
                    Please specify boundaries (?latlower=xx&amp;latupper=xx&amp;longlower=xx&amp;longupper=xx)
                    not exceeding a 1° by 1° box
                </h2>';
        exit(1);
    }
    $latitude = null;
    $longitude = null;
    $mode = 'box';
    $latlower = $_GET['latlower'];
    $latupper = $_GET['latupper'];
    $longlower = $_GET['longlower'];
    $longupper = $_GET['longupper'];
}
else {
    header('HTTP/1.1 400 Bad Request');
    echo '<h1>400 Bad Request</h1><h2>Please specify latitude and longitude (?lat=xx&amp;long=xx)</h2>';
    exit(1);
}

// Format of db-conf.php is
// return array(); with the values used in the below initializations
$db_conf = require 'db-conf.php';
$dsn = "{$db_conf['driver']}:dbname={$db_conf['database']};host={$db_conf['host']}";
$dbh = new PDO($dsn, $db_conf['username'], $db_conf['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

// Set reasonable latitude/longitude lower and upper boundaries in radius mode
// If in box mode, these have already been set by the request
// See http://en.wikipedia.org/wiki/Latitude#The_length_of_a_degree_of_latitude for a rough idea of the radius represented
if ('radius' === $mode) {
    $latlower = $latitude - 0.5;
    $latupper = $latitude + 0.5;
    $longlower = $longitude - 0.5;
    $longupper = $longitude + 0.5;

    // Using distance formula as defined in
    // http://en.wikipedia.org/wiki/Geographical_distance#Spherical_Earth_projected_to_a_plane
    $query = "SELECT `source_id` AS `id`,`name`,`city`,`latitude`,`longitude`,`hasDDR`,
      TRUNCATE(6371.009*SQRT(POW(RADIANS(`latitude`-:lat),2)+POW(COS(RADIANS((`latitude`+:lat)/2))*RADIANS(`longitude`-:long),2)),2) AS `distance`
      FROM `locations`
      WHERE
        `source_type` = 'ziv' AND
        `latitude` > :latlower AND
        `latitude` < :latupper AND
        `longitude` > :longlower AND
        `longitude` < :longupper
      ORDER BY `distance` ASC";
}
else /* if ('box' === $mode) */ {
    $query = "SELECT `source_id` AS `id`,`name`,`city`,`latitude`,`longitude`,`hasDDR`
      FROM `locations`
      WHERE
        `source_type` = 'ziv' AND
        `latitude` > :latlower AND
        `latitude` < :latupper AND
        `longitude` > :longlower AND
        `longitude` < :longupper";
}

// Bind parameters and execute query
$stmt = $dbh->prepare($query);
if ('radius' === $mode) {
    $stmt->bindParam(':lat', $latitude);
    $stmt->bindParam(':long', $longitude);
}
$stmt->bindParam(':latlower', $latlower);
$stmt->bindParam(':latupper', $latupper);
$stmt->bindParam(':longlower', $longlower);
$stmt->bindParam(':longupper', $longupper);
$stmt->execute();

// Fetch all data for nearby machines
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output result in JSON
header('Content-type: application/json; charset=utf-8');
echo json_encode($locations);
