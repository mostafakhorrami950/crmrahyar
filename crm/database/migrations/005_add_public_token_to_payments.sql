-- ============================================
-- Add public_token column to payments table
-- for public payment page access
-- ============================================

ALTER TABLE `payments` 
ADD COLUMN `public_token` VARCHAR(64) NULL AFTER `track_id`,
ADD COLUMN `return_url` VARCHAR(500) NULL AFTER `public_token`,
ADD INDEX `idx_public_token` (`public_token`);