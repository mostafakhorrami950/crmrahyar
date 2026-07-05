-- Hotel definitions (for rate list)
CREATE TABLE IF NOT EXISTS `hotel_rate_hotels` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_name` VARCHAR(255) NOT NULL COMMENT 'نام هتل',
    `description` TEXT DEFAULT NULL COMMENT 'توضیحات هتل',
    `facilities` TEXT DEFAULT NULL COMMENT 'امکانات هتل',
    `star_rating` INT DEFAULT NULL COMMENT 'تعداد ستاره',
    `city` VARCHAR(100) DEFAULT NULL COMMENT 'شهر',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تعریف هتل‌ها برای نرخنامه';

-- Add hotel_id column to hotel_rate_list if not exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `hotel_id` INT NOT NULL DEFAULT 0 COMMENT 'شناسه هتل' AFTER `id`;