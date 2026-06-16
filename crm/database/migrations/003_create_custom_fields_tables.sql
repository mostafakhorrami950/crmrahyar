-- ============================================
-- Custom Fields & Database Repair System
-- ============================================

-- Custom Fields Definition
CREATE TABLE IF NOT EXISTS `custom_fields` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL COMMENT 'deals or contacts',
    `field_name` VARCHAR(100) NOT NULL,
    `field_label` VARCHAR(200) NOT NULL,
    `field_type` VARCHAR(50) NOT NULL DEFAULT 'text' COMMENT 'text, number, textarea, select, date, checkbox',
    `field_options` TEXT COMMENT 'JSON options for select',
    `is_required` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `order_index` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_entity_active` (`entity_type`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Custom Field Values
CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `field_id` INT NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT NOT NULL,
    `value` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`field_id`) REFERENCES `custom_fields`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_field_value` (`field_id`, `entity_type`, `entity_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Database Repair Log
CREATE TABLE IF NOT EXISTS `db_repair_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `table_name` VARCHAR(100),
    `action` VARCHAR(50),
    `description` TEXT,
    `status` VARCHAR(20) DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;