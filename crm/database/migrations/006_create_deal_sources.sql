-- ============================================
-- Deal Sources Table (manageable from settings)
-- ============================================

CREATE TABLE IF NOT EXISTS `deal_sources` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(10) DEFAULT '📌',
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default sources
INSERT IGNORE INTO `deal_sources` (`name`, `icon`, `sort_order`, `is_active`) VALUES
('اینستاگرام', '📸', 1, 1),
('تلگرام', '✈️', 2, 1),
('واتساپ', '💬', 3, 1),
('وبسایت', '🌐', 4, 1),
('گوگل', '🔍', 5, 1),
('معرفی دوستان', '👥', 6, 1),
('تماس تلفنی', '📞', 7, 1),
('مراجعه حضوری', '🏢', 8, 1),
('نمایشگاه', '🎪', 9, 1),
('سایر', '📌', 10, 1);