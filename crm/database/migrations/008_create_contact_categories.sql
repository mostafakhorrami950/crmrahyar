-- ============================================
-- Contact Categories (each contact belongs to one group)
-- ============================================

CREATE TABLE IF NOT EXISTS `contact_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#6B7280',
    `is_default` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default categories
INSERT IGNORE INTO `contact_categories` (`name`, `description`, `color`, `is_default`, `sort_order`) VALUES
('مشتری جدید', 'مخاطبینی که هنوز خریدی نداشته‌اند', '#6B7280', 1, 1),
('مشتری وفادار', 'مشتریان ثابت و وفادار', '#10B981', 0, 2),
('تور داخلی', 'متقاضیان تورهای داخلی', '#3B82F6', 0, 3),
('تور خارجی', 'متقاضیان تورهای خارجی', '#8B5CF6', 0, 4),
('تور زیارتی', 'متقاضیان تورهای زیارتی', '#F59E0B', 0, 5),
('VIP', 'مشتریان ویژه', '#EF4444', 0, 6),
('تجاری', 'مشتریان سازمانی و تجاری', '#EC4899', 0, 7),
('در انتظار تصمیم', 'مخاطبینی که در حال بررسی هستند', '#F97316', 0, 8);

-- Add category_id column to contacts table
ALTER TABLE `contacts` ADD COLUMN `category_id` INT DEFAULT NULL AFTER `source`;