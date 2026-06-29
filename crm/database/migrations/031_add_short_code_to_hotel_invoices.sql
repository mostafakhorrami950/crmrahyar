-- ============================================
-- Add short_code to hotel_invoices for short payment URLs
-- ============================================

ALTER TABLE `hotel_invoices` ADD COLUMN `short_code` VARCHAR(10) NULL UNIQUE COMMENT 'کد کوتاه لینک پرداخت' AFTER `payment_token`;

-- Generate short codes for existing invoices with payment_token
UPDATE `hotel_invoices` SET `short_code` = LOWER(CONCAT(
    SUBSTRING(MD5(RAND()), 1, 3),
    SUBSTRING(MD5(RAND()), 1, 3)
)) WHERE `payment_token` IS NOT NULL AND `short_code` IS NULL;