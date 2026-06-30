-- ============================================
-- Invoice Items Catalog
-- ============================================

CREATE TABLE IF NOT EXISTS `invoice_items_catalog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL COMMENT 'نام آیتم',
    `description` TEXT NULL COMMENT 'توضیحات',
    `default_price` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'قیمت پیش‌فرض (تومان)',
    `category` VARCHAR(100) DEFAULT 'general' COMMENT 'دسته‌بندی',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT 'فعال/غیرفعال',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default items
INSERT INTO `invoice_items_catalog` (`name`, `description`, `default_price`, `category`) VALUES
('اقامت هتل', 'هزینه اقامت در هتل', 0, 'hotel'),
('ترانسفر فرودگاهی', 'هزینه ترانسفر فرودگاهی', 0, 'transfer'),
('ویزا', 'هزینه ویزا', 0, 'visa'),
('بیمه مسافرتی', 'هزینه بیمه مسافرتی', 0, 'insurance'),
('بلیط هواپیما', 'هزینه بلیط هواپیما', 0, 'flight'),
('گشت شهری', 'هزینه گشت شهری', 0, 'tour'),
('راهنما', 'هزینه راهنمای تور', 0, 'guide'),
('غذا', 'هزینه وعده غذایی', 0, 'meal'),
('سایر', 'سایر هزینه‌ها', 0, 'other');

-- Update invoice_status enum to include new statuses
-- Note: This is handled by the existing migration, we just need to ensure the values are correct