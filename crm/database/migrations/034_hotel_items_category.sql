-- ============================================
-- Add category column to hotel_invoice_items
-- ============================================

-- Step 1: Add the category column
ALTER TABLE `hotel_invoice_items`
ADD COLUMN `category` VARCHAR(50) NULL
COMMENT 'دسته‌بندی (hotel, transfer, visa, etc.)'
AFTER `description`;

-- Step 2: Fix collation first so JOIN works
ALTER TABLE `hotel_invoice_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Step 3: Now populate category from catalog (collations match)
UPDATE `hotel_invoice_items` hii
JOIN `invoice_items_catalog` iic ON hii.description = iic.name
SET hii.category = iic.category
WHERE hii.category IS NULL;

-- Step 4: Set 'general' for remaining uncategorized items
UPDATE `hotel_invoice_items`
SET `category` = 'general'
WHERE `category` IS NULL;

-- Step 5: Convert catalog table collation too, for future joins
ALTER TABLE `invoice_items_catalog` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;