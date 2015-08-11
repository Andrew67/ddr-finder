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
// API router: sends request to appropriate handler based on detected API version
header('Content-type: application/json; charset=utf-8');

// Empty, invalid, or below 20 version field is treated as API 1.x client
if (empty($_GET['version']) || !is_numeric($_GET['version']) || 20 > $_GET['version']) {
    require 'locate-v1.php';
}
// API 2.x clients are 20 through 29
elseif (20 <= $_GET['version'] && 30 > $_GET['version']) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array(
        "error" => "API v2.x support is a work in progress.",
        "errorCode" => 1
    ));
}
// Any other API versions are yet to be implemented
else {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array(
        "error" => "This server only implements API v1.x.",
        "errorCode" => 1
    ));
}