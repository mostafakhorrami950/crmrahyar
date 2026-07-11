<?php
namespace Shared\Migrations;

class Migration006
{
    public static function up($db): void
    {
        // Media Library
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_media` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `file_name` VARCHAR(255) NOT NULL,
                `original_name` VARCHAR(255) DEFAULT NULL,
                `mime_type` VARCHAR(100) DEFAULT NULL,
                `file_size` INT DEFAULT 0,
                `width` INT DEFAULT NULL,
                `height` INT DEFAULT NULL,
                `alt_text` VARCHAR(255) DEFAULT NULL,
                `title` VARCHAR(255) DEFAULT NULL,
                `caption` TEXT DEFAULT NULL,
                `path_original` VARCHAR(500) NOT NULL,
                `path_webp_large` VARCHAR(500) DEFAULT NULL,
                `path_webp_thumb` VARCHAR(500) DEFAULT NULL,
                `path_avif_large` VARCHAR(500) DEFAULT NULL,
                `path_avif_thumb` VARCHAR(500) DEFAULT NULL,
                `entity_type` VARCHAR(30) DEFAULT NULL COMMENT 'نوع موجودیت (hotel, room, blog و...)',
                `entity_id` INT DEFAULT NULL COMMENT 'شناسه موجودیت',
                `sort_order` INT DEFAULT 0,
                `uploaded_by` INT DEFAULT NULL COMMENT 'FK → users.id',
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_entity` (`entity_type`, `entity_id`),
                INDEX `idx_uploaded` (`uploaded_by`),
                INDEX `idx_deleted` (`deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='کتابخانه رسانه'
        ");

        // Search Index
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_search_index` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `entity_type` VARCHAR(30) NOT NULL COMMENT 'نوع موجودیت',
                `entity_id` INT NOT NULL COMMENT 'شناسه موجودیت',
                `title` VARCHAR(255) DEFAULT NULL,
                `description` TEXT DEFAULT NULL,
                `city_id` INT DEFAULT NULL,
                `neighborhood_id` INT DEFAULT NULL,
                `price_min` DECIMAL(15,0) DEFAULT NULL COMMENT 'حداقل قیمت (IRR)',
                `price_max` DECIMAL(15,0) DEFAULT NULL COMMENT 'حداکثر قیمت (IRR)',
                `star_rating` INT DEFAULT NULL,
                `capacity` INT DEFAULT NULL,
                `tags_json` TEXT DEFAULT NULL COMMENT 'JSON تگ‌ها',
                `search_vector` TEXT DEFAULT NULL COMMENT 'متن قابل جستجو',
                `popularity_score` DECIMAL(5,2) DEFAULT 0,
                `rating_score` DECIMAL(3,2) DEFAULT 0,
                `conversion_score` DECIMAL(5,4) DEFAULT 0,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE INDEX `uk_entity` (`entity_type`, `entity_id`),
                FULLTEXT INDEX `ft_search` (`title`, `search_vector`),
                INDEX `idx_city` (`city_id`),
                INDEX `idx_price` (`price_min`, `price_max`),
                INDEX `idx_star` (`star_rating`),
                INDEX `idx_popularity` (`popularity_score`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ایندکس جستجو'
        ");
    }

    public static function down($db): void
    {
        $db->query("DROP TABLE IF EXISTS `site_search_index`");
        $db->query("DROP TABLE IF EXISTS `site_media`");
    }
}