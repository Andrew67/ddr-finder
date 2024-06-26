<?php
/*
 * ddr-finder
 * Copyright (c) 2015-2024 Andrés Cordero
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
    const string SELECT_cols = '`id`, `source_type` AS `src`, `source_id` AS `sid`, `name`, `city`,
        TRUNCATE(`latitude`,4) AS `lat`, TRUNCATE(`longitude`,4) AS `lng`, `hasDDR`';
    const string SELECT_V4cols = ', `country`, `hasPIU`, `hasSMX`';
    const string SELECT_distance = 'TRUNCATE(6371.009*SQRT(POW(RADIANS(`latitude`-:lat1),2)+POW(COS(RADIANS((`latitude`+:lat2)/2))*RADIANS(`longitude`-:lng1),2)),2)
        AS `distance`';
    const string FROM = 'FROM `locations`';
    const string WHERE_radius = '`latitude` > (:lat3-0.5) AND
        `latitude` < (:lat4+0.5) AND
        `longitude` > (:lng2-0.5) AND
        `longitude` < (:lng3+0.5)';
    const string WHERE_box = '`latitude` > :latlower AND
        `latitude` < :latupper AND
        `longitude` > :lnglower AND
        `longitude` < :lngupper';
    const array WHERE_hasGame = [
        'ddr' => '`hasDDR` = 1',
        'piu' => '`hasPIU` = 1',
        'smx' => '`hasSMX` = 1',
    ];

    private PDO $dbh;

    function __construct(PDO $dbh) {
        $this->dbh = $dbh;
    }

    /**
     * Receive all records updated after the given timestamp.
     * @param int $timestamp
     * @param boolean $includeDeletions Whether to include deleted entries since given timestamp (see API v3.1).
     * @return array API format array.
     */
    public function getDump(int $timestamp, bool $includeDeletions): array {
        $notDeletedSql = ($includeDeletions) ? ', 0 AS `deleted`' : '';

        $stmt = $this->dbh->prepare('SELECT ' . self::SELECT_cols . ' ' . $notDeletedSql . ' ' . self::FROM .
            ' WHERE `last_update` > FROM_UNIXTIME(:timestamp) ORDER BY `last_update` ASC');
        $stmt->bindValue(':timestamp', $timestamp, PDO::PARAM_INT);
        $stmt->execute();
        $newAndChangedEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($includeDeletions && $timestamp > 0) {
            $stmt = $this->dbh->prepare("SELECT `id`, '' AS `src`, '' AS `sid`, '' AS `name`, '' AS `city`," .
                ' 0 AS `lat`, 0 AS `lng`, 0 AS `hasDDR`, 1 AS `deleted` FROM `locations_deleted`' .
                ' WHERE `deletion_time` > FROM_UNIXTIME(:timestamp) ORDER BY `deletion_time` ASC');
            $stmt->bindValue(':timestamp', $timestamp, PDO::PARAM_INT);
            $stmt->execute();
            $deletedEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_merge($newAndChangedEntries, $deletedEntries);
        } else {
            return $newAndChangedEntries;
        }
    }

    /**
     * Retrieve all records within +/- 0.5 of the given lat/lng, sorted by distance.
     * @param Coords $coords
     * @param array $src Data sources to pull from.
     * @param boolean|int $limit Number of results to limit the resultset to, or FALSE for no limit.
     * @param boolean $includeV4Fields Whether to include the APIv4 fields in the query (country and new game fields).
     * @param array $games Game IDs (from WHERE_hasGame keys) an arcade should have at least one of, or empty for no filter.
     * @return array API format array.
     */
    public function getRadius(Coords $coords, array $src, bool|int $limit,
                              bool $includeV4Fields = false, array $games = []): array {
        $limitSql = ($limit === false) ? '' : 'LIMIT ' . $limit;

        $stmt = $this->dbh->prepare(
            sprintf("SELECT %s %s, %s %s WHERE %s AND %s %s ORDER BY `distance` ASC %s",
                self::SELECT_cols, $includeV4Fields ? self::SELECT_V4cols : '', self::SELECT_distance,
                self::FROM,
                $this->getSourceString($src), self::WHERE_radius, $this->getGameFilter($games),
                $limitSql));
        $stmt->execute(array(
            ':lat1' => $coords->lat,
            ':lat2' => $coords->lat,
            ':lat3' => $coords->lat,
            ':lat4' => $coords->lat,
            ':lng1' => $coords->lng,
            ':lng2' => $coords->lng,
            ':lng3' => $coords->lng
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all records within the given lat/lng boundaries.
     * @param CoordsBox $boundingBox
     * @param array $src Data sources to pull from.
     * @return array API format array.
     */
    public function getBox(CoordsBox $boundingBox, array $src): array {
        $stmt = $this->dbh->prepare('SELECT ' . self::SELECT_cols . ' ' . self::FROM .
            ' WHERE ' . $this->getSourceString($src) . ' AND ' . self::WHERE_box);
        $stmt->execute(array(
            ':latlower' => $boundingBox->southwest->lat,
            ':lnglower' => $boundingBox->southwest->lng,
            ':latupper' => $boundingBox->northeast->lat,
            ':lngupper' => $boundingBox->northeast->lng
        ));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Builds a conditional based on requested data sources.
     * Example output: `source_type` IN ('navi','zim')
     * @param array $src
     * @return string SQL fragment with safe values.
     */
    private function getSourceString(array $src): string {
        // Handle special value "all"
        if ('all' === $src[0]) return '1=1';

        $q = '`source_type` IN (';

        // The $p is a trick to properly generate the commas in-between values
        $p = '';
        foreach ($src as $source) {
            $q .= $p . $this->dbh->quote($source);
            $p = ',';
        }

        $q .= ')';
        return $q;
    }

    /**
     * Builds a conditional string based on game availability filters.
     * Example output: AND (`hasDDR` = 1 OR `hasPIU` = 1)
     * @param array $games Game values from WHERE_hasGame keys.
     * @return string SQL fragment with safe values.
     */
    private function getGameFilter(array $games): string {
        if (count($games) === 0) return '';
        $queryParts = [];
        foreach ($games as $game) {
            $queryParts[] = self::WHERE_hasGame[$game];
        }
        $query = implode(' OR ', $queryParts);
        return "AND $query";
    }

}