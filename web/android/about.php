<?php
/*
 * ddr-finder
 * Copyright (c) 2012-2023 Andrés Cordero
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

header('Cache-Control: public, max-age=86400');
header('Referrer-Policy: strict-origin');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>About</title>
    <!-- ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE -->
    <!-- Setting the OpenGraph URL ensures shared links point to the main page -->
    <meta property="og:url" content="https://ddrfinder.andrew67.com/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <link rel="stylesheet" href="../css/minimal.css">
    <link rel="shortcut icon" href="../images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="../images/apple-touch-icon.png">
</head>
<body id="page-about">
<!-- This page is meant to be displayed in the About dialog of the Android app -->
<h1>DDR Finder</h1>
<h2>Version <?= htmlspecialchars($versionName); ?></h2>
<p>No warranty is made regarding operation, and no accuracy or freshness of results is guaranteed.
    Machine location data collected from
    <a href="https://zenius-i-vanisher.com/" target="_blank" rel="noopener">Zenius -I- vanisher.com</a>,
    <a href="https://www.ddr-navi.jp/" target="_blank" rel="noopener">DDR-Navi</a>, and
    <a href="https://www.openstreetmap.org/" target="_blank" rel="noopener">OpenStreetMap</a>
    (no affiliation).
<div id="main-buttons">
    <a href="https://ddrfinder.andrew67.com/" class="button color-blueDark">
        Project Home Page
    </a><br>
    <a href="https://raw.github.com/Andrew67/DdrFinder/master/LICENSE" class="button color-greenDark">
        View Application License
    </a><br>
    <a href="https://github.com/Andrew67/ddr-finder" class="button color-pinkDark">
        View Source on GitHub
    </a>
</div>
<h4>&copy; 2013&ndash;2023 <a href="https://andrew67.com/">Andrés Cordero</a></h4>
<h5>
    Chinese Simplified Translation provided by <a href="https://github.com/AndiZ23">Andi Zhou</a>.<br>
    Adapted from the <a href="https://github.com/ltorres8890/Clima">Clima</a> project.<br>
    App arrow icon from the <a href="https://www.stepmania.com/">StepMania 5</a> lambda noteskin.<br>
    "<a href="https://thenounproject.com/icon/25579/">Map Marker</a>" icon by meghan hade from
        <a href="https://thenounproject.com">the Noun Project</a>.<br>
    "<a href="https://thenounproject.com/icon/237640/">arcade</a>" icon by Icon 54 from
        <a href="https://thenounproject.com">the Noun Project</a>.<br>
    "<a href="https://thenounproject.com/icon/3044571/">Arrow</a>" icon by Al Haddad, ID from
        <a href="https://thenounproject.com">the Noun Project</a>.<br>
    Calculator icon from <a href="https://icons.getbootstrap.com/">Bootstrap Icons</a>.<br>
    Maps provided by the
        <a href="https://developers.google.com/maps/documentation/android-api/">Google Maps Android API</a>.<br>
    Map marker clustering provided by the
        <a href="https://github.com/googlemaps/android-maps-utils/">Google Maps Android API utility library</a>.<br>
    Map style (light mode) based on <a href="https://snazzymaps.com/style/83/muted-blue">Muted Blue</a> on Snazzy Maps.<br>
    Map style (dark mode) based on <a href="https://mapstyle.withgoogle.com/">Aubergine</a> by Google.<br>
    HTTP requests handled by Square's <a href="https://github.com/square/okhttp/">OkHttp</a> library.<br>
    JSON serialization handled by Square's <a href="https://github.com/square/moshi">Moshi</a> library.
</h5>
</body>
</html>