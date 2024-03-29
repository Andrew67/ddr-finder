CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_type` varchar(8) NOT NULL,
  `source_id` varchar(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL DEFAULT '',
  `country` char(2) NOT NULL DEFAULT '',
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `hasDDR` tinyint(1) NOT NULL DEFAULT '0',
  `hasPIU` tinyint(1) NOT NULL DEFAULT '0',
  `hasSMX` tinyint(1) NOT NULL DEFAULT '0',
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `composite_id` (`source_type`,`source_id`),
  KEY `coordinates` (`latitude`,`longitude`),
  KEY `source_coordinates` (`source_type`,`latitude`,`longitude`),
  KEY `last_update` (`last_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `locations_deleted` (
  `id` int(11) NOT NULL,
  `source_type` varchar(8) NOT NULL,
  `source_id` varchar(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `hasDDR` tinyint(1) NOT NULL,
  `deletion_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `deletion_time` (`deletion_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;