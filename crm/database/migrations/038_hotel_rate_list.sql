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

-- Hotel Rate List (نرخنامه هتل‌ها)
CREATE TABLE IF NOT EXISTS `hotel_rate_list` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_id` INT NOT NULL COMMENT 'شناسه هتل',
    `room_type` VARCHAR(100) NOT NULL COMMENT 'نوع اتاق',
    `date_from` DATE NOT NULL COMMENT 'تاریخ شروع',
    `date_to` DATE NOT NULL COMMENT 'تاریخ پایان',
    `season_label` VARCHAR(100) DEFAULT NULL COMMENT 'نام فصل/دوره',
    `price_ekht` DECIMAL(15,0) DEFAULT 0 COMMENT 'اقامت',
    `price_sobhaneh` DECIMAL(15,0) DEFAULT 0 COMMENT 'اقامت+صبحانه',
    `price_nahar` DECIMAL(15,0) DEFAULT 0 COMMENT 'اقامت+صبحانه+ناهار',
    `price_entekhabifulboard` DECIMAL(15,0) DEFAULT 0 COMMENT 'فولبرد انتخابی',
    `price_fulboard_boufeh` DECIMAL(15,0) DEFAULT 0 COMMENT 'فولبرد بوفه',
    `notes` TEXT DEFAULT NULL COMMENT 'توضیحات',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hotel` (`hotel_id`),
    INDEX `idx_date_from` (`date_from`),
    INDEX `idx_date_to` (`date_to`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='نرخنامه هتل‌ها';