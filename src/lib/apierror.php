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
 * Class APIError
 * Helper methods for generating the API error structure.
 */
class APIError {

    // Error code constants
    const VERSION_NOT_SUPPORTED = 1;
    const MISSING_REQUIRED_FIELD = 20;
    const INVALID_DATA_SOURCE = 21;
    const DUMP_FORBIDDEN = 22;
    const OVERSIZED_BOX_FORBIDDEN = 23;
    const TOO_MANY_REQUESTS = 42;
    const TOO_MANY_DECIMALS_COORDINATES = 50;
    const OUT_OF_ORDER_PARAMETERS = 51;
    const OUT_OF_ORDER_FILTERS = 52;
    const OUT_OF_BOUNDS_LIMIT = 53;

    /**
     * Returns the v2.0 API error structure.
     * Sets the HTTP status code to 400 "Bad Request".
     * Recommended usage: output the return value and exit the script.
     *
     * @param int $code Error code.
     * @param string $message User-readable message.
     * @return string API error structure in JSON format.
     */
    public static function getError($code, $message) {
        header('HTTP/1.1 400 Bad Request');
        return json_encode(array(
            'error' => $message,
            'errorCode' => $code
        ));
    }

}