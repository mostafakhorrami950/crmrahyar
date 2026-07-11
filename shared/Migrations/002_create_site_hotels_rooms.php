<?php
namespace Shared\Migrations;

use Shared\Core\Database;

class Migration002
{
    public static function up(Database $db): void
    {
        // Hotel profiles (FK to CRM hotel_rate_hotels)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_hotel_profiles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `crm_hotel_id` INT NOT NULL UNIQUE COMMENT 'FK → hotel_rate_hotels.id',
                `city_id` INT DEFAULT NULL COMMENT 'FK → site_cities.id',
                `neighborhood_id` INT DEFAULT NULL COMMENT 'FK → site_neighborhoods.id',
                `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'شناسه URL',
                `address` TEXT DEFAULT NULL COMMENT 'آدرس کامل',
                `latitude` DECIMAL(10,7) DEFAULT NULL,
                `longitude` DECIMAL(10,7) DEFAULT NULL,
                `distance_to_haram_km` DECIMAL(5,2) DEFAULT NULL COMMENT 'فاصله تا حرم',
                `cover_media_id` INT DEFAULT NULL COMMENT 'FK → site_media.id',
                `description_long` TEXT DEFAULT NULL COMMENT 'توضیحات کامل',
                `description_short` VARCHAR(500) DEFAULT NULL COMMENT 'خلاصه',
                `family_friendly` TINYINT(1) DEFAULT 0,
                `couple_friendly` TINYINT(1) DEFAULT 0,
                `budget_friendly` TINYINT(1) DEFAULT 0,
                `luxury` TINYINT(1) DEFAULT 0,
                `featured` TINYINT(1) DEFAULT 0,
                `sort_order` INT DEFAULT 0,
                `meta_title` VARCHAR(255) DEFAULT NULL,
                `meta_description` TEXT DEFAULT NULL,
                `og_image_id` INT DEFAULT NULL,
                `schema_json` TEXT DEFAULT NULL,
                `robots_meta` VARCHAR(100) DEFAULT 'index, follow',
                `is_active` TINYINT(1) DEFAULT 1,
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_crm_hotel` (`crm_hotel_id`),
                INDEX `idx_slug` (`slug`),
                INDEX `idx_city` (`city_id`),
                INDEX `idx_neighborhood` (`neighborhood_id`),
                INDEX `idx_featured` (`featured`),
                INDEX `idx_active` (`is_active`, `deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='پروفایل تکمیلی هتل‌ها برای وبسایت'
        ");

        // Room profiles (Mapping layer - FK to CRM room_type)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_rooms` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `crm_hotel_id` INT NOT NULL COMMENT 'FK → hotel_rate_hotels.id',
                `room_type_key` VARCHAR(100) NOT NULL COMMENT 'نام اتاق در CRM (mapping)',
                `slug` VARCHAR(150) NOT NULL COMMENT 'شناسه URL',
                `capacity_adults` INT DEFAULT 2,
                `capacity_children` INT DEFAULT 0,
                `bed_type` VARCHAR(50) DEFAULT NULL COMMENT 'نوع تخت',
                `size_sqm` INT DEFAULT NULL COMMENT 'مساحت (متر مربع)',
                `description` TEXT DEFAULT NULL,
                `cover_media_id` INT DEFAULT NULL,
                `max_inventory` INT DEFAULT 10 COMMENT 'حداکثر موجودی',
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE INDEX `uk_crm_room` (`crm_hotel_id`, `room_type_key`),
                INDEX `idx_slug` (`slug`),
                INDEX `idx_hotel` (`crm_hotel_id`),
                INDEX `idx_active` (`is_active`, `deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='پروفایل اتاق‌ها (Mapping Layer)'
        ");
    }

    public static function down(Database $db): void
    {
        $db->query("DROP TABLE IF EXISTS `site_rooms`");
        $db->query("DROP TABLE IF EXISTS `site_hotel_profiles`");
    }
}