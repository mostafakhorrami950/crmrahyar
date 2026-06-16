-- Add description column to stages table
ALTER TABLE `stages` ADD COLUMN `description` TEXT DEFAULT NULL AFTER `color`;