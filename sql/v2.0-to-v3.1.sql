ALTER TABLE `locations` CONVERT TO CHARACTER SET utf8mb4;
CREATE TABLE IF NOT EXISTS `locations_deleted` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;