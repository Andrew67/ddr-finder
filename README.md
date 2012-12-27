ddr-finder
==========

HTML5 DDR Locator Application (targetted at Mobile); could be modified into a general location finder.

Live Demo
---------
The official working demo is at http://ddrfinder.tk/, using data snapshots from the database at http://zenius-i-vanisher.com/.

UI
--
Using Metro UI responsive CSS; see http://metroui.org.ua/.

License
-------
MIT license (see LICENSE); excludes favicon.png, arcade-machine.jpg, apple-touch-icon.png,
and other items that have their own license declarations (such as the fonts, icons and stylesheets from Metro UI).
If making your own version, make sure to modify all absolute URLs and copyright notices.

Database
--------
If you don't use MySQL, you need to modify the PDO constructor in `locate.php` in order to ensure `utf-8` operation.

### db-conf.php Format ###
```php
<?php
return array('driver' => 'mysql',
             'host' => 'localhost',
             'username' => 'user',
             'password' => 'pass',
             'database' => 'ddrfinder',
             'table' => 'locations',
);
```

### Table Schema ###
```sql
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `coordinates` (`latitude`,`longitude`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
```

Acknowledgments
---------------
* [Zenius -I- vanisher.com](http://zenius-i-vanisher.com/) for inspiring me to make this
  (and being a comprehensive source of arcade data).
* [Metro UI CSS](http://metroui.org.ua/) for helping me make a simple good-looking interface.
* [Dive into HTML5](http://diveintohtml5.info/geolocation.html) for information on the HTML5 Geolocation API.
* [Wikipedia](http://en.wikipedia.org/wiki/Geographical_distance#Spherical_Earth_projected_to_a_plane) for the
  distance conversion formula.
* [The Open Graph Protocol](http://ogp.me/) for protocol information (makes sharing on social networks look nice).
* The following sources when searching for how to trigger navigation/map apps on mobile platforms:
  * http://habaneroconsulting.com/Blog/Posts/Opening_native_map_apps_from_the_mobile_browser.aspx
  * http://developer.apple.com/library/ios/#featuredarticles/iPhoneURLScheme_Reference/Articles/MapLinks.html
  * http://msdn.microsoft.com/en-us/library/windows/apps/jj635237.aspx
  * http://stackoverflow.com/questions/3990110/how-to-show-marker-in-maps-launched-by-geo-uri-intent/7405992#7405992

Future Work
-----------
* Support mobile platforms beyond Android/iOS/Windows Phone.
* Handle errors in the AJAX request (currently fails silently).
* Rate limit for `locate.php`.
