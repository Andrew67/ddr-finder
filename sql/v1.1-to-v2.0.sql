ALTER TABLE `locations` ALTER COLUMN `city` SET DEFAULT '';
ALTER TABLE `locations` ADD COLUMN `last_update` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `locations` ADD COLUMN `source_type` varchar(8) NOT NULL AFTER `id`;
ALTER TABLE `locations` ADD COLUMN `source_id` varchar(16) NOT NULL AFTER `source_type`;
UPDATE `locations` SET `source_id`=`id`, `source_type`='ziv';
CREATE UNIQUE INDEX `composite_id` ON `locations` (`source_type`, `source_id`);
CREATE INDEX `source_coordinates` ON `locations` (`source_type`, `latitude`, `longitude`);
CREATE INDEX `last_update` ON `locations` (`last_update`);