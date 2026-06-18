-- ============================================
-- Add company_phone field to contacts table
-- ============================================
ALTER TABLE `contacts` ADD COLUMN `company_phone` VARCHAR(20) NULL AFTER `phone`;