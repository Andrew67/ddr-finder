<?php
error_reporting(0);

if (isset($_GET['lat']) && isset($_GET['long'])) {
    $latitude = $_GET['lat'];
    $longitude = $_GET['long'];

    // Format of db-conf.php is
    // return array(); with the values used in the below initializations
    $db_conf = require 'db-conf.php';
    $dsn = "{$db_conf['driver']}:dbname={$db_conf['database']};host={$db_conf['host']}";
    $dbh = new PDO($dsn, $db_conf['username'], $db_conf['password']);
    $stmt = $dbh->prepare("SELECT * FROM `{$db_conf['table']}` WHERE `latitude` > :latlower AND `latitude` < :latupper AND `longitude` > :longlower AND `longitude` < :longupper LIMIT 10;");

    // Set latitude/longitude lower and upper boundaries
    $latlower = $latitude - 0.5;
    $latupper = $latitude + 0.5;
    $longlower = $longitude - 0.5;
    $longupper = $longitude + 0.5;
    $stmt->bindParam(':latlower', $latlower);
    $stmt->bindParam(':latupper', $latupper);
    $stmt->bindParam(':longlower', $longlower);
    $stmt->bindParam(':longupper', $longupper);
    $stmt->execute();

    // For returning data in JSON format with correct encoding
    header('Content-type: application/json; charset=utf-8');
    // Fetch all data for nearby machines
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate arcade distances and add to array values (in km)
    // using formula as defined in http://en.wikipedia.org/wiki/Geographical_distance#Spherical_Earth_projected_to_a_plane
    define('R', 6371.009);
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
    array_multisort($distances, $locations);
    echo json_encode($locations);
}
else {
    header('HTTP/1.1 400 Bad Request');
    echo '<h1>400 Bad Request</h1><h2>Please specify valid latitude and longitude (?lat=xx&amp;long=xx)</h2>';
}