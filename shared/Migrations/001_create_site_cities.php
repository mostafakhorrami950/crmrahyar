<?php
namespace Shared\Migrations;

class Migration001
{
    public static function up($db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_cities` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL COMMENT 'نام شهر',
                `slug` VARCHAR(120) NOT NULL UNIQUE COMMENT 'شناسه URL',
                `province` VARCHAR(100) DEFAULT NULL COMMENT 'استان',
                `latitude` DECIMAL(10,7) DEFAULT NULL COMMENT 'عرض جغرافیایی',
                `longitude` DECIMAL(10,7) DEFAULT NULL COMMENT 'طول جغرافیایی',
                `description` TEXT DEFAULT NULL COMMENT 'توضیحات',
                `cover_media_id` INT DEFAULT NULL COMMENT 'تصویر پوشش',
                `meta_title` VARCHAR(255) DEFAULT NULL,
                `meta_description` TEXT DEFAULT NULL,
                `schema_json` TEXT DEFAULT NULL COMMENT 'Schema.org JSON-LD',
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_slug` (`slug`),
                INDEX `idx_active` (`is_active`),
                INDEX `idx_province` (`province`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='شهرها'
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `site_neighborhoods` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `city_id` INT NOT NULL COMMENT 'شناسه شهر',
                `name` VARCHAR(100) NOT NULL COMMENT 'نام منطقه/محله',
                `slug` VARCHAR(120) NOT NULL COMMENT 'شناسه URL',
                `distance_to_haram_km` DECIMAL(5,2) DEFAULT NULL COMMENT 'فاصله تا حرم (کیلومتر)',
                `description` TEXT DEFAULT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_city` (`city_id`),
                INDEX `idx_slug` (`slug`),
                UNIQUE INDEX `uk_city_slug` (`city_id`, `slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='محله‌ها و مناطق'
        ");
    }

    public static function down($db): void
    {
        $db->query("DROP TABLE IF EXISTS `site_neighborhoods`");
        $db->query("DROP TABLE IF EXISTS `site_cities`");
    }
}