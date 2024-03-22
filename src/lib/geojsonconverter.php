<?php
/*
 * ddr-finder
 * Copyright (c) 2016-2024 Andrés Cordero
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
 * Class GeoJSONConverter
 * Provides helper methods for converting location data from LocationsHelper into a data structure that becomes
 * valid GeoJSON when passed through json_encode(), for API v3.1 output support.
 */
class GeoJSONConverter {

    /**
     * Takes in a single location entry and converts into a GeoJSON feature.
     * @param array $location A location entry from the database, from a LocationsHelper result array.
     * @return array GeoJSON format array for a Feature.
     */
    public static function convertFeature(array $location): array {
        $output = [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $location['lng'], (float) $location['lat']]
            ],
            'properties' => [
                'id' => (int) $location['id'],
                'src' => $location['src'],
                'sid' => $location['sid'],
                'name' => $location['name'],
                'city' => $location['city'],
                'hasDDR' => (int) $location['hasDDR']
            ]
        ];
        if (isset($location['distance'])) {
            $output['properties']['distance'] = (float) $location['distance'];
        }
        return $output;
    }

    /**
     * Takes in a result set of locations and converts into a GeoJSON feature collection.
     * @param array $locations A location array from the database, from LocationsHelper.
     * @return array GeoJSON format array for a FeatureCollection.
     */
    public static function convertCollection(array $locations): array {
        $output = [
            'type' => 'FeatureCollection',
            'features' => []
        ];
        foreach ($locations as $item) {
            $output['features'][] = self::convertFeature($item);
        }
        return $output;
    }

}