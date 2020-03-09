ddr-finder
==========

HTML5 DDR Locator Application (targeted at Mobile); could be modified into a general location finder.

Live Demo
---------
The official working demo is at https://ddrfinder.andrew67.com/, using data snapshots from multiple sources.

UI
--
Using Metro UI responsive CSS; see https://metroui.org.ua/.

License
-------
MIT license (see LICENSE); excludes favicon.png, arcade-machine.jpg, apple-touch-icon.png,
and other items that have their own license declarations (such as the fonts, icons and stylesheets from Metro UI).

Custom Version
--------------
* Modify all absolute URLs and copyright notices (keeping proper MIT license attribution in place).
* Create a db-conf.php file to connect to your database, created by the schema script in the `sql/` folder.
* Get a static maps API key for Google Maps and change the value in `locator.js` for `GMAPS_API_KEY`.

## Database ##
MySQL is used.

### db-conf.php Format ###
```php
<?php
return array('driver' => 'mysql',
             'host' => 'localhost',
             'username' => 'user',
             'password' => 'pass',
             'database' => 'ddrfinder',
);
```

### Table Schema ###
See the `sql` directory for table creation and schema upgrade scripts.

Acknowledgments
---------------
* [Zenius -I- vanisher.com](https://zenius-i-vanisher.com/) for inspiring me to make this
  (and being a comprehensive source of arcade data).
* [Metro UI CSS](https://metroui.org.ua/) for helping me make a simple good-looking interface.
* [Dive into HTML5](https://diveintohtml5.info/geolocation.html) for information on the HTML5 Geolocation API.
* [Wikipedia](https://en.wikipedia.org/wiki/Geographical_distance#Spherical_Earth_projected_to_a_plane) for the
  distance conversion formula.
* [The Open Graph Protocol](https://ogp.me/) for protocol information (makes sharing on social networks look nice).
* The following sources when searching for how to trigger navigation/map apps on mobile platforms:
  * https://habaneroconsulting.com/Blog/Posts/Opening_native_map_apps_from_the_mobile_browser.aspx
  * http://developer.apple.com/library/ios/#featuredarticles/iPhoneURLScheme_Reference/Articles/MapLinks.html (dead link)
  * https://msdn.microsoft.com/en-us/library/windows/apps/jj635237.aspx (for WP7; now redirects to Windows 10 URIs)
  * https://stackoverflow.com/questions/3990110/how-to-show-marker-in-maps-launched-by-geo-uri-intent/7405992#7405992
* [DDR-Navi](http://ddr-navi.jp/) for Japan data.
