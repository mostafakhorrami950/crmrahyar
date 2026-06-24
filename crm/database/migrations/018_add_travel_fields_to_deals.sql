-- ============================================
-- Add travel-specific fields to deals table
-- ============================================

ALTER TABLE `deals` 
ADD COLUMN `travel_date_from` DATE NULL COMMENT 'تاریخ ورود/شروع سفر' AFTER `expected_close_date`,
ADD COLUMN `travel_date_to` DATE NULL COMMENT 'تاریخ خروج/پایان سفر' AFTER `travel_date_from`,
ADD COLUMN `passengers_count` INT DEFAULT 0 COMMENT 'تعداد نفرات مسافر' AFTER `travel_date_to`;

-- Also update sales_targets to support custom date ranges
ALTER TABLE `sales_targets`
ADD COLUMN `date_from` DATE NULL COMMENT 'تاریخ شروع بازه هدف' AFTER `month`,
ADD COLUMN `date_to` DATE NULL COMMENT 'تاریخ پایان بازه هدف' AFTER `date_from`;