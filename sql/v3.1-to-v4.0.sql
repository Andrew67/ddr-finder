ALTER TABLE `locations` ADD COLUMN `country` char(2) NOT NULL DEFAULT '' AFTER `city`;
ALTER TABLE `locations` ADD COLUMN `hasPIU` tinyint(1) NOT NULL DEFAULT '0' AFTER `hasDDR`;
ALTER TABLE `locations` ADD COLUMN `hasSMX` tinyint(1) NOT NULL DEFAULT '0' AFTER `hasPIU`;