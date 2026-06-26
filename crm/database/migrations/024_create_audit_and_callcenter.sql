-- ============================================
-- Audit Trail & Version Control Tables
-- ============================================

-- Change Logs Table (tracks every change)
CREATE TABLE IF NOT EXISTS `change_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `entity_type` VARCHAR(50) NOT NULL COMMENT 'contact, deal',
    `entity_id` INT NOT NULL,
    `action` ENUM('create', 'update', 'delete') NOT NULL,
    `changes` JSON COMMENT 'JSON of {field: {old: x, new: y}}',
    `snapshot` JSON COMMENT 'Full entity snapshot before change',
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Call Center Phone Line Management
-- ============================================

-- Phone Lines Table (physical phones/extensions)
CREATE TABLE IF NOT EXISTS `phone_lines` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'نام خط یا شماره',
    `phone_number` VARCHAR(20) NOT NULL,
    `description` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Phone Shift Assignments (who has which phone during which shift)
CREATE TABLE IF NOT EXISTS `phone_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `phone_line_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `shift_start` DATETIME NOT NULL,
    `shift_end` DATETIME NOT NULL,
    `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    `notes` TEXT,
    `created_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`phone_line_id`) REFERENCES `phone_lines`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_line_shift` (`phone_line_id`, `shift_start`, `shift_end`),
    INDEX `idx_user_shift` (`user_id`, `shift_start`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Permissions for new features
INSERT IGNORE INTO `permissions` (`name`, `slug`, `group_name`, `description`) VALUES
('مشاهده لاگ تغییرات', 'audit.view', 'گزارشات', 'مشاهده تاریخچه تغییرات'),
('بازگردانی تغییرات', 'audit.rollback', 'گزارشات', 'بازگردانی به نسخه قبلی'),
('مدیریت خطوط تلفن', 'phonelines.manage', 'کال سنتر', 'مدیریت خطوط تلفن و شیفت‌ها');