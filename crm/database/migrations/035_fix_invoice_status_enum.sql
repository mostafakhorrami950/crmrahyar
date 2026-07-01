-- ============================================
-- Fix invoice_status ENUM to match code values
-- ============================================

-- Update ENUM to include the values the code actually uses
ALTER TABLE `hotel_invoices` MODIFY COLUMN `invoice_status`
    ENUM('draft','final','paid','cancelled','pending','settled','prepaid')
    DEFAULT 'pending' COMMENT 'وضعیت فاکتور';

-- Update old status values to new ones
UPDATE `hotel_invoices` SET `invoice_status` = 'pending' WHERE `invoice_status` = 'draft';
UPDATE `hotel_invoices` SET `invoice_status` = 'settled' WHERE `invoice_status` = 'final';