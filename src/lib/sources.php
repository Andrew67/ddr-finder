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
 * Contains data and helpers for information regarding the available data sources.
 */
class Sources {
    /** Raw source data, in same format as v4.0 API output. */
    public static array $data = [
        'ziv' => [
            'id' => 'ziv',
            'name' => 'Zenius -I- vanisher.com',
            'scope' => 'world',
            'url:homepage' => 'https://zenius-i-vanisher.com/',
            'url:info' => 'https://zenius-i-vanisher.com/v5.2/arcade.php?id=${sid}#summary',
            'url:info:mobile' => 'https://ddrfinder-proxy.andrew67.com/ziv/info/${sid}',
            'has:ddr' => true,
            'has:piu' => false,
            'has:smx' => false,
        ],
        'navi' => [
            'id' => 'navi',
            'name' => 'DDR-Navi',
            'scope' => 'JP',
            'url:homepage' => 'https://www.ddr-navi.jp/',
            'url:info' => 'https://www.ddr-navi.jp/shop/?id=${sid}',
            'url:info:mobile' => 'https://www.ddr-navi.jp/shop/?id=${sid}',
            'has:ddr' => true,
            'has:piu' => false,
            'has:smx' => false,
        ],
        'osm' => [
            'id' => 'osm',
            'name' => 'OpenStreetMap',
            'scope' => 'world',
            'url:homepage' => 'https://www.openstreetmap.org/',
            'url:info' => 'https://ddrfinder-proxy.andrew67.com/osm/redirect/${sid}',
            'url:info:mobile' => 'https://ddrfinder-proxy.andrew67.com/osm/redirect/${sid}',
            'has:ddr' => false,
            'has:piu' => false,
            'has:smx' => false,
        ],
        // The intention with "fallback" is to provide a URL that redirects to source, based on actual database ID
        'fallback' => [
            'id' => 'fallback',
            'name' => 'Source Website',
            'scope' => 'world',
            'url:homepage' => 'https://ddrfinder.andrew67.com/',
            'url:info' => 'https://ddrfinder.andrew67.com/info.php?id=${id}',
            'url:info:mobile' => 'https://ddrfinder.andrew67.com/info.php?id=${id}',
            'has:ddr' => false,
            'has:piu' => false,
            'has:smx' => false,
        ],
    ];

    /**
     * Converts the given source object to a version compatible legacy APIs.
     * @param array $source The individual source object in today's format.
     * @return array The source object in API v2.0/v3.0 format.
     */
    public static function getLegacyFormat(array $source): array {
        return [
            'shortName' => $source['id'],
            'name' => $source['name'],
            'homepageURL' => $source['url:homepage'],
            'infoURL' => $source['url:info'],
            'mInfoURL' => $source['url:info:mobile'],
            'hasDDR' => $source['has:ddr'],
        ];
    }

    /**
     * Returns a source object with information for the specified sources, and "fallback".
     * Invalid sources are ignored.
     * @param array $sources Array of source ID strings.
     * @return array API v2.0 output format source data.
     */
    public static function getSourceObject(array $sources): array {
        if ('all' === $sources[0]) $sources = array_keys(self::$data);
        else $sources[] = 'fallback';

        $d = array();
        foreach ($sources as $s) {
            if (array_key_exists($s, self::$data)) {
                $d[$s] = self::getLegacyFormat(self::$data[$s]);
            }
        }

        return $d;
    }

    /**
     * Returns a source array with information for the specified sources, and "fallback".
     * Invalid sources are ignored.
     * @param array $sources Array of source ID strings.
     * @return array API v3.0 output format source data.
     */
    public static function getSourceArray(array $sources): array {
        if ('all' === $sources[0]) $sources = array_keys(self::$data);
        else $sources[] = 'fallback';

        $d = array();
        foreach ($sources as $s) {
            if (array_key_exists($s, self::$data)) {
                $d[] = self::getLegacyFormat(self::$data[$s]);
            }
        }

        return $d;
    }

    /**
     * Whether the specified source ID is valid. "all" is valid, while "fallback" is not.
     * @param string $source Source ID to validate.
     * @return bool Whether the specified source ID is valid.
     */
    public static function isValidSource(string $source): bool {
        if ('all' === $source) return true;
        elseif ('fallback' === $source) return false;
        else return array_key_exists($source, self::$data);
    }
}
