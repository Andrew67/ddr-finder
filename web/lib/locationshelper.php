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

/**
 * Class LocationsHelper
 * Provides helper methods for fetching location information from the database, returned in API format.
 */
class LocationsHelper {

    // Query constants
    const SELECT_cols = '`id`, `source_type` AS `src`, `source_id` AS `sid`, `name`, `city`,
        `latitude` AS `lat`, `longitude` AS `lng`, `hasDDR`';
    const SELECT_distance = 'TRUNCATE(6371.009*SQRT(POW(RADIANS(`latitude`-:lat),2)+POW(COS(RADIANS((`latitude`+:lat)/2))*RADIANS(`longitude`-:lng),2)),2)
        AS `distance`';
    const FROM = 'FROM `locations`';
    const WHERE_radius = '`latitude` > (:lat-0.5) AND
        `latitude` < (:lat+0.5) AND
        `longitude` > (:lng-0.5) AND
        `longitude` < (:lng+0.5)';
    const WHERE_box = '`latitude` > :latlower AND
        `latitude` < :latupper AND
        `longitude` > :lnglower AND
        `longitude` < :lngupper';

    /** @var PDO Database connection handle. */
    private $dbh;

    /**
     * @param PDO $dbh Previously established connection to the database.
     */
    function __construct(PDO $dbh) {
        $this->dbh = $dbh;
    }

    /**
     * Receive all records updated after the given timestamp.
     * @param int $timestamp
     * @return array API format array.
     */
    public function getDump($timestamp) {
        $stmt = $this->dbh->prepare('SELECT ' . self::SELECT_cols . ' ' . self::FROM .
            ' WHERE `last_update` > :timestamp ORDER BY `last_update` ASC');
        $stmt->bindValue(':timestamp', $timestamp, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all records within +/- 0.5 of the given lat/lng, sorted by distance.
     * @param float $lat
     * @param float $lng
     * @return array API format array.
     */
    public function getRadius($lat, $lng) {
        $stmt = $this->dbh->prepare('SELECT ' . self::SELECT_cols . ', ' . self::SELECT_distance . ' ' . self::FROM .
            ' WHERE ' . self::WHERE_radius . ' ORDER BY `distance` ASC');
        $stmt->execute(array(
            ':lat' => $lat,
            ':lng' => $lng
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}