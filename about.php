<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>DDR Finder</title>
    <!-- Setting the OpenGraph URL ensures shared links point to the main page -->
    <meta property="og:url" content="http://www.ddrfinder.tk/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/modern-responsive.css">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
</head>
<body id="page-about">
<!-- This page is meant to be displayed in the About dialog of the Android app -->
<h1>DDR Finder</h1>
<h2>
    Version <?php echo htmlspecialchars($_GET['n']); ?>
    (Revision <?php echo htmlspecialchars($_GET['c']); ?>)
</h2>
<p>This is a work in progress proof of concept.
    No warranty is made regarding operation, and no accuracy of results is guaranteed.
    Location information used for searching is NOT recorded.
    Machine location data collected from <a href="http://zenius-i-vanisher.com/">Zenius -I- vanisher.com</a>
    (no affiliation with said site, data snapshots may not be up-to-date).
    No donations accepted; donate to Z-I-v instead, without it this app would not have been possible!</p>
<div id="main-buttons">
    <a href="https://raw.github.com/Andrew67/DdrFinder/master/LICENSE" class="button bg-color-greenDark fg-color-white">
        View Application License
    </a>
    <a href="https://github.com/Andrew67/ddr-finder" class="button bg-color-pinkDark fg-color-white">
        View Source on GitHub <i class="icon-github-4"></i>
    </a>
</div>
<h4>&copy; 2013 <a href="http://andrew67.com/">Andrés Cordero</a></h4>
<h5>
    Using <a href="http://metroui.org.ua/">Metro UI CSS</a> for page styling<br>
    Arrow icon from the <a href="http://stepmania.com/">StepMania 5</a> default noteskin<br>
    Adapted from the <a href="https://github.com/ltorres8890/Clima">Clima</a> project
</h5>
<script src="javascript/jquery-1.8.3.js"></script>
<script src="javascript/options.js"></script>
</body>
</html>