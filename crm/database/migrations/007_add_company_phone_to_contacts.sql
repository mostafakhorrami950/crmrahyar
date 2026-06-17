-- ============================================
-- Add company_phone field to contacts table
-- ============================================

ALTER TABLE `contacts` ADD COLUMN IF NOT EXISTS `company_phone` VARCHAR(20) NULL AFTER `phone`;