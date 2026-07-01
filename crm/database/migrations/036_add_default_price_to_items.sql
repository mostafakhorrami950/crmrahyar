-- ============================================
-- Add default_price column to hotel_invoice_items
-- This stores the catalog price at time of invoice creation
-- so that catalog price changes don't affect existing invoices
-- ============================================

ALTER TABLE `hotel_invoice_items`
    ADD COLUMN `default_price` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `unit_price`;

-- Update existing items: set default_price = unit_price for existing records
UPDATE `hotel_invoice_items` SET `default_price` = `unit_price` WHERE `default_price` = 0;