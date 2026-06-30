-- ============================================
-- Add category column to hotel_invoice_items
-- ============================================

ALTER TABLE `hotel_invoice_items` ADD COLUMN `category` VARCHAR(50) NULL COMMENT 'دسته‌بندی (hotel, transfer, visa, etc.)' AFTER `description`;

-- Populate category for existing items from the catalog
UPDATE `hotel_invoice_items` hii
JOIN `invoice_items_catalog` iic ON hii.description = iic.name
SET hii.category = iic.category
WHERE hii.category IS NULL;