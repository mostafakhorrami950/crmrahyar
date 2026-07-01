-- ============================================
-- Add category column to hotel_invoice_items
-- ============================================

-- First add the column (this doesn't involve collation)
ALTER TABLE `hotel_invoice_items`
ADD COLUMN `category` VARCHAR(50) NULL
COMMENT 'دسته‌بندی (hotel, transfer, visa, etc.)'
AFTER `description`;

-- Populate category for existing items using COLLATE to fix charset mismatch
UPDATE `hotel_invoice_items` hii
JOIN `invoice_items_catalog` iic ON hii.description COLLATE utf8mb4_unicode_ci = iic.name COLLATE utf8mb4_unicode_ci
SET hii.category = iic.category
WHERE hii.category IS NULL;

-- Set 'general' for items that still have no category
UPDATE `hotel_invoice_items`
SET `category` = 'general'
WHERE `category` IS NULL;

-- Also convert the description column collation for future joins
ALTER TABLE `hotel_invoice_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `hotel_invoice_items` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;