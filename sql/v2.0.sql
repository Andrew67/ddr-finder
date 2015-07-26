CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_type` varchar(8) NOT NULL,
  `source_id` varchar(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL DEFAULT '',
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `hasDDR` tinyint(1) NOT NULL DEFAULT '0',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `composite_id` (`source_type`,`source_id`),
  KEY `coordinates` (`latitude`,`longitude`),
  KEY `source_coordinates` (`source_type`,`latitude`,`longitude`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;