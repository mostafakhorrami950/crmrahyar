-- ============================================
-- Hotel Invoices Table
-- ============================================

CREATE TABLE IF NOT EXISTS `hotel_invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `deal_id` INT NOT NULL COMMENT 'شناسه معامله',
    `hotel_name` VARCHAR(200) NOT NULL COMMENT 'نام هتل',
    `check_in_date` DATE NOT NULL COMMENT 'تاریخ ورود',
    `check_out_date` DATE NOT NULL COMMENT 'تاریخ خروج',
    `nights` INT NOT NULL DEFAULT 0 COMMENT 'تعداد شب‌ها',
    `persons_count` INT NOT NULL DEFAULT 1 COMMENT 'تعداد نفرات',
    `person_night_count` INT NOT NULL DEFAULT 0 COMMENT 'تعداد نفر-شب',
    `price_per_person_night` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'قیمت هر نفر هر شب (تومان)',
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'مبلغ کل (تومان)',
    `new_price_per_person_night` DECIMAL(15,2) NULL COMMENT 'قیمت جدید هر نفر هر شب (تومان)',
    `discount_percent` DECIMAL(5,2) DEFAULT 0 COMMENT 'درصد تخفیف',
    `discount_amount` DECIMAL(15,2) DEFAULT 0 COMMENT 'مبلغ تخفیف (تومان)',
    `final_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'مبلغ نهایی (تومان)',
    `notes` TEXT NULL COMMENT 'توضیحات',
    `invoice_status` ENUM('draft', 'final', 'cancelled') DEFAULT 'draft' COMMENT 'وضعیت فاکتور',
    `created_by` INT COMMENT 'ایجادکننده',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_deal` (`deal_id`),
    INDEX `idx_status` (`invoice_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;