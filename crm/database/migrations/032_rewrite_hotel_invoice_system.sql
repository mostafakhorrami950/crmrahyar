-- ============================================
-- Rewrite Hotel Invoice System
-- ============================================

-- Create hotel_invoice_items table for line items
CREATE TABLE IF NOT EXISTS `hotel_invoice_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT NOT NULL COMMENT 'شناسه فاکتور',
    `description` VARCHAR(500) NOT NULL COMMENT 'شرح آیتم',
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1 COMMENT 'تعداد',
    `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'قیمت واحد (تومان)',
    `total_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'مبلغ کل (تومان)',
    `sort_order` INT DEFAULT 0 COMMENT 'ترتیب نمایش',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`invoice_id`) REFERENCES `hotel_invoices`(`id`) ON DELETE CASCADE,
    INDEX `idx_invoice` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add new columns to hotel_invoices
ALTER TABLE `hotel_invoices` ADD COLUMN `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'جمع کل (تومان)' AFTER `person_night_count`;
ALTER TABLE `hotel_invoices` ADD COLUMN `tax_percent` DECIMAL(5,2) DEFAULT 0 COMMENT 'درصد مالیات' AFTER `subtotal`;
ALTER TABLE `hotel_invoices` ADD COLUMN `tax_amount` DECIMAL(15,2) DEFAULT 0 COMMENT 'مبلغ مالیات (تومان)' AFTER `tax_percent`;
ALTER TABLE `hotel_invoices` ADD COLUMN `service_fee` DECIMAL(15,2) DEFAULT 0 COMMENT 'هزینه خدمات (تومان)' AFTER `tax_amount`;
ALTER TABLE `hotel_invoices` ADD COLUMN `currency` VARCHAR(10) DEFAULT 'IRR' COMMENT 'واحد پول' AFTER `service_fee`;
ALTER TABLE `hotel_invoices` ADD COLUMN `exchange_rate` DECIMAL(15,2) DEFAULT 1 COMMENT 'نرخ ارز' AFTER `currency`;
ALTER TABLE `hotel_invoices` ADD COLUMN `guest_name` VARCHAR(200) NULL COMMENT 'نام میهمان' AFTER `hotel_name`;
ALTER TABLE `hotel_invoices` ADD COLUMN `guest_phone` VARCHAR(20) NULL COMMENT 'تلفن میهمان' AFTER `guest_name`;
ALTER TABLE `hotel_invoices` ADD COLUMN `room_type` VARCHAR(100) NULL COMMENT 'نوع اتاق' AFTER `guest_phone`;
ALTER TABLE `hotel_invoices` ADD COLUMN `room_number` VARCHAR(50) NULL COMMENT 'شماره اتاق' AFTER `room_type`;
ALTER TABLE `hotel_invoices` ADD COLUMN `meal_plan` VARCHAR(100) NULL COMMENT 'نوع وعده غذایی' AFTER `room_number`;
ALTER TABLE `hotel_invoices` ADD COLUMN `transfer_included` TINYINT(1) DEFAULT 0 COMMENT 'شامل ترانسفر' AFTER `meal_plan`;
ALTER TABLE `hotel_invoices` ADD COLUMN `visa_included` TINYINT(1) DEFAULT 0 COMMENT 'شامل ویزا' AFTER `transfer_included`;
ALTER TABLE `hotel_invoices` ADD COLUMN `insurance_included` TINYINT(1) DEFAULT 0 COMMENT 'شامل بیمه' AFTER `visa_included`;
ALTER TABLE `hotel_invoices` ADD COLUMN `extra_services` TEXT NULL COMMENT 'خدمات اضافی' AFTER `insurance_included`;
ALTER TABLE `hotel_invoices` ADD COLUMN `payment_terms` TEXT NULL COMMENT 'شرایط پرداخت' AFTER `extra_services`;
ALTER TABLE `hotel_invoices` ADD COLUMN `valid_until` DATE NULL COMMENT 'تاریخ اعتبار فاکتور' AFTER `payment_terms`;
ALTER TABLE `hotel_invoices` ADD COLUMN `invoice_number` VARCHAR(50) NULL COMMENT 'شماره فاکتور' AFTER `valid_until`;
ALTER TABLE `hotel_invoices` ADD COLUMN `footer_text` TEXT NULL COMMENT 'متن فوتر فاکتور' AFTER `invoice_number`;

-- Add footer_text to invoice_settings
INSERT IGNORE INTO `invoice_settings` (`setting_key`, `setting_value`) VALUES
('invoice_footer_text', 'این فاکتور به صورت الکترونیکی صادر شده است. در صورت نیاز با ما تماس بگیرید.'),
('invoice_terms', 'شرایط پرداخت: پرداخت نقدی یا انتقال بانکی. لطفاً قبل از تاریخ ورود، مبلغ فاکتور را تسویه نمایید.');