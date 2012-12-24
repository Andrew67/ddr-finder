<?php
error_reporting(0);

if (isset($_GET['lat']) && isset($_GET['long'])) {
    $latitude = $_GET['lat'];
    $longitude = $_GET['long'];

    // Format of db-conf.php is
    // return array(); with the values used in the below initializations
    $db_conf = require 'db-conf.php';
    $dsn = "{$db_conf['driver']}:dbname={$db_conf['database']};host={$db_conf['host']}";
    // NOTE: if not using MySQL, remove the 4th argument and use the appropriate method of setting the encoding
    $dbh = new PDO($dsn, $db_conf['username'], $db_conf['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    // NOTE: if your database does not support these math functions, uncomment the second version of this query
    // and uncomment the below block of code that can perform the distance calculation and sorting in PHP
    // See the below block for a more readable version of the formula, and more importantly its source
    $stmt = $dbh->prepare("SELECT `id`,`name`,`city`,`latitude`,`longitude`,
        TRUNCATE(6371.009*SQRT(POW(RADIANS(latitude-:lat),2)+POW(COS(RADIANS((latitude+:lat)/2))*RADIANS(longitude-:long),2)),2) AS distance
        FROM `{$db_conf['table']}`
        WHERE `latitude` > :latlower AND `latitude` < :latupper AND `longitude` > :longlower AND `longitude` < :longupper
        ORDER BY distance ASC LIMIT 10;");
    //$stmt = $dbh->prepare("SELECT * FROM `{$db_conf['table']}` WHERE `latitude` > :latlower AND `latitude` < :latupper AND `longitude` > :longlower AND `longitude` < :longupper ;");

    // Set latitude/longitude lower and upper boundaries
    // See http://en.wikipedia.org/wiki/Latitude#The_length_of_a_degree_of_latitude for a rough idea of the radius represented
    $latlower = $latitude - 0.5;
    $latupper = $latitude + 0.5;
    $longlower = $longitude - 0.5;
    $longupper = $longitude + 0.5;

    // Bind parameters and execute query
    $stmt->bindParam(':lat', $latitude);
    $stmt->bindParam(':long', $longitude);
    $stmt->bindParam(':latlower', $latlower);
    $stmt->bindParam(':latupper', $latupper);
    $stmt->bindParam(':longlower', $longlower);
    $stmt->bindParam(':longupper', $longupper);
    $stmt->execute();

    // Fetch all data for nearby machines
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate arcade distances and add to array values (in km)
    // using formula as defined in http://en.wikipedia.org/wiki/Geographical_distance#Spherical_Earth_projected_to_a_plane
    // NOTE: only uncomment if your database does not have the functions required to calculate this in the query itself (see above)
    /*define('R', 6371.009);
    $distances = array();
    foreach ($locations as &$loc) {
        $d_lat = deg2rad($latitude - $loc['latitude']);
        $d_long = deg2rad($longitude - $loc['longitude']);
        $m_lat = deg2rad(($latitude + $loc['latitude']) / 2);
        $distance = R * sqrt( pow($d_lat, 2) + pow( cos($m_lat) * $d_long, 2) );
        $loc['distance'] = sprintf('%.2f', $distance);
        $distances[] = $distance;
    }

    // Sort locations by distance (closest first)
    array_multisort($distances, $locations);*/

    // Output result in JSON for JavaScript processing
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($locations);
}
else {
    header('HTTP/1.1 400 Bad Request');
    echo '<h1>400 Bad Request</h1><h2>Please specify valid latitude and longitude (?lat=xx&amp;long=xx)</h2>';
}