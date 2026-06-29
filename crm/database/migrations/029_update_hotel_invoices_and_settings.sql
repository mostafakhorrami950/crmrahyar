-- ============================================
-- Update hotel_invoices table for new features
-- ============================================

ALTER TABLE `hotel_invoices` ADD COLUMN `adults_count` INT DEFAULT 0 COMMENT 'تعداد بزرگسال' AFTER `persons_count`;
ALTER TABLE `hotel_invoices` ADD COLUMN `children_3to5_count` INT DEFAULT 0 COMMENT 'تعداد 3 تا 5 سال (نیم بها)' AFTER `adults_count`;
ALTER TABLE `hotel_invoices` ADD COLUMN `children_under3_count` INT DEFAULT 0 COMMENT 'تعداد زیر 3 سال (رایگان)' AFTER `children_3to5_count`;
ALTER TABLE `hotel_invoices` ADD COLUMN `deposit_amount` DECIMAL(15,2) DEFAULT 0 COMMENT 'مبلغ بیعانه (تومان)' AFTER `final_amount`;
ALTER TABLE `hotel_invoices` ADD COLUMN `payment_token` VARCHAR(100) NULL COMMENT 'توکن لینک پرداخت' AFTER `deposit_amount`;
ALTER TABLE `hotel_invoices` ADD COLUMN `discount_percent` DECIMAL(5,2) DEFAULT 0 COMMENT 'درصد تخفیف خودکار' AFTER `new_price_per_person_night`;
ALTER TABLE `hotel_invoices` ADD COLUMN `invoice_type` ENUM('proforma', 'confirmed') DEFAULT 'proforma' COMMENT 'نوع فاکتور' AFTER `invoice_status`;

-- ============================================
-- Invoice settings table
-- ============================================
CREATE TABLE IF NOT EXISTS `invoice_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default invoice settings
INSERT IGNORE INTO `invoice_settings` (`setting_key`, `setting_value`) VALUES
('invoice_title', 'فاکتور هتل'),
('invoice_subtitle', 'آژانس مسافرتی'),
('invoice_company_name', 'علاءالدین سفیر اسمان'),
('invoice_logo_url', ''),
('invoice_primary_color', '#0d6efd'),
('invoice_secondary_color', '#6c757d'),
('invoice_success_color', '#198754');