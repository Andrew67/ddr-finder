<?php
/*
 * ddr-finder
 * Copyright (c) 2012-2018 Andrés Cordero
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

$versionName = (isset($_GET['n'])) ? $_GET['n'] : '???';
$versionCode = (isset($_GET['c'])) ? (int) $_GET['c'] : 0;
define('LATEST_VERSION', 23);

header('Cache-Control: public, max-age=86400');
header('Referrer-Policy: strict-origin');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DDR Finder</title>
    <!-- ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE -->
    <!-- Setting the OpenGraph URL ensures shared links point to the main page -->
    <meta property="og:url" content="https://ddrfinder.andrew67.com/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/minimal.css">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
</head>
<body id="page-about" class="metrouicss">
<!-- This page is meant to be displayed in the About dialog of the Android app -->
<h1>DDR Finder</h1>
<h2>
    Version <?= htmlspecialchars($versionName); ?>
    (Revision <?= htmlspecialchars($versionCode); ?>)
</h2>
<?php if ($versionCode < LATEST_VERSION): ?>
<p class="label warning">New version available!</p>
<?php endif; ?>
<p>No warranty is made regarding operation, and no accuracy or freshness of results is guaranteed.
    Machine location data collected from <a href="http://zenius-i-vanisher.com/">Zenius -I- vanisher.com</a>
    and <a href="http://ddr-navi.jp/">DDR-Navi</a>
    (no affiliation).</p>
<div id="main-buttons">
    <?php if ($versionCode < LATEST_VERSION): ?>
    <a href="https://play.google.com/store/apps/details?id=com.andrew67.ddrfinder" class="button bg-color-greenLight fg-color-white">
        Install New Version
    </a><br>
    <?php endif; ?>
    <a href="https://ddrfinder.andrew67.com/" class="button bg-color-blueDark">
        Project Home Page
    </a><br>
    <a href="https://raw.github.com/Andrew67/DdrFinder/master/LICENSE" class="button bg-color-greenDark fg-color-white">
        View Application License
    </a><br>
    <a href="https://github.com/Andrew67/ddr-finder" class="button bg-color-pinkDark fg-color-white">
        View Source on GitHub
    </a>
</div>
<h4>&copy; 2013&ndash;2018 <a href="http://andrew67.com/">Andrés Cordero</a></h4>
<h5>
    <?php if ($versionCode >= 24): ?>
    Chinese Simplified Translation provided by <a href="https://github.com/AndiZ23">Andi Zhou</a>.<br>
    <?php endif; ?>
    Adapted from the <a href="https://github.com/ltorres8890/Clima">Clima</a> project.<br>
    Arrow icon from the <a href="https://www.stepmania.com/">StepMania 5</a> default noteskin.<br>
    Maps provided by the <a href="https://developers.google.com/maps/documentation/android-api/">Google Maps Android API</a>.<br>
    Map marker clustering provided by the <a href="https://github.com/googlemaps/android-maps-utils/">Google Maps Android API utility library</a>.<br>
    <?php if ($versionCode < 19): ?>
    Loading animation from lsjwzh's <a href="https://github.com/lsjwzh/MaterialLoadingProgressBar">MaterialLoadingProgressBar</a> library.<br>
    <?php elseif ($versionCode < 24): ?>
    Map style based on <a href="https://snazzymaps.com/style/7/icy-blue">Icy Blue</a> by Sahir Memon.<br>
    <?php else: ?>
    Map style based on <a href="https://mapstyle.withgoogle.com/">Aubergine</a> by Google.<br>
    <?php endif; ?>
    HTTP requests handled by Square's <a href="https://github.com/square/okhttp/">OkHttp</a> library.<br>
    <?php if ($versionCode < 21): ?>
    JSON serialization handled by Google's <a href="https://github.com/google/gson">Gson</a> library.
    <?php else: ?>
    JSON serialization handled by Square's <a href="https://github.com/square/moshi">Moshi</a> library.
    <?php endif; ?>
</h5>
</body>
</html>