-- Ensure hotel_rate_hotels table exists
CREATE TABLE IF NOT EXISTS `hotel_rate_hotels` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hotel_name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `facilities` TEXT DEFAULT NULL,
    `star_rating` INT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure hotel_id column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `hotel_id` INT NOT NULL DEFAULT 0 AFTER `id`;

-- Ensure date_from column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `date_from` DATE NULL AFTER `room_type`;

-- Ensure date_to column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `date_to` DATE NULL AFTER `date_from`;

-- Ensure price_fulboard_boufeh column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `price_fulboard_boufeh` DECIMAL(15,0) DEFAULT 0 AFTER `price_entekhabifulboard`;

-- Ensure price_entekhabifulboard column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `price_entekhabifulboard` DECIMAL(15,0) DEFAULT 0 AFTER `price_nahar`;

-- Ensure season_label column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `season_label` VARCHAR(100) DEFAULT NULL AFTER `date_to`;

-- Ensure notes column exists
ALTER TABLE `hotel_rate_list` ADD COLUMN `notes` TEXT DEFAULT NULL;