-- Add sort_order column to hotel_rate_hotels for custom ordering
ALTER TABLE `hotel_rate_hotels` ADD COLUMN `sort_order` INT DEFAULT 0 COMMENT 'ترتیب نمایش';
ALTER TABLE `hotel_rate_hotels` ADD INDEX `idx_sort_order` (`sort_order`);