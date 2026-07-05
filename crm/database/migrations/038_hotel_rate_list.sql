-- Hotel Rate List (نرخنامه هتل‌ها)
CREATE TABLE IF NOT EXISTS `hotel_rate_list` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_name` VARCHAR(255) NOT NULL COMMENT 'نام هتل',
    `room_type` VARCHAR(100) NOT NULL COMMENT 'نوع اتاق',
    `rate_date` DATE NOT NULL COMMENT 'تاریخ نرخ',
    `season_label` VARCHAR(100) DEFAULT NULL COMMENT 'نام فصل/دوره',
    `price_ekht` DECIMAL(15,0) DEFAULT 0 COMMENT 'اقامت',
    `price_sobhaneh` DECIMAL(15,0) DEFAULT 0 COMMENT 'اقامت+صبحانه',
    `price_nahar` DECIMAL(15,0) DEFAULT 0 COMMENT 'اقامت+صبحانه+ناهار',
    `price_fulboard` DECIMAL(15,0) DEFAULT 0 COMMENT 'فولبرد (صبحانه+ناهار+شام)',
    `price_entekhabifulboard` DECIMAL(15,0) DEFAULT 0 COMMENT 'فولبرد انتخابی',
    `price_boufeh` DECIMAL(15,0) DEFAULT 0 COMMENT 'بوفه',
    `notes` TEXT DEFAULT NULL COMMENT 'توضیحات',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hotel` (`hotel_name`),
    INDEX `idx_date` (`rate_date`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='نرخنامه هتل‌ها';