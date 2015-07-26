ALTER TABLE `locations` ALTER COLUMN `city` SET DEFAULT '';
ALTER TABLE `locations` ADD COLUMN `source_type` char(8) NOT NULL AFTER `id`;
ALTER TABLE `locations` ADD COLUMN `source_id` int(11) NOT NULL AFTER `source_type`;
UPDATE `locations` SET `source_id`=`id`, `source_type`='ziv';
CREATE UNIQUE INDEX `composite_id` ON `locations` (`source_type`, `source_id`);
CREATE INDEX `source_coordinates` ON `locations` (`source_type`, `latitude`, `longitude`);