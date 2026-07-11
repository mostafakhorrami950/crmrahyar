<?php
namespace Shared\Migrations;

class Migration004
{
    public static function up($db): void
    {
        // Pricing rules (markup layers)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_pricing_rules` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL COMMENT 'نام قانون',
                `rule_level` ENUM('global','hotel','room_type','season','daily') NOT NULL DEFAULT 'global',
                `target_id` INT DEFAULT NULL COMMENT 'شناسه هدف (hotel_id, room_id و...)',
                `markup_type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
                `markup_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
                `priority` INT DEFAULT 0,
                `valid_from` DATE DEFAULT NULL,
                `valid_to` DATE DEFAULT NULL,
                `is_active` TINYINT(1) DEFAULT 1,
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_level_target` (`rule_level`, `target_id`),
                INDEX `idx_active` (`is_active`, `deleted_at`),
                INDEX `idx_priority` (`priority`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='قوانین روکشی قیمت'
        ");

        // Room daily rates (calendar pricing)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_room_daily_rates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `room_id` INT NOT NULL COMMENT 'FK → site_rooms.id',
                `date` DATE NOT NULL,
                `price` DECIMAL(15,0) DEFAULT NULL COMMENT 'قیمت روزانه (IRR) - NULL = از CRM',
                `capacity_override` INT DEFAULT NULL COMMENT 'ظرفیت override',
                `stop_sell` TINYINT(1) DEFAULT 0,
                `min_stay` INT DEFAULT NULL,
                `max_stay` INT DEFAULT NULL,
                `checkin_allowed` TINYINT(1) DEFAULT 1,
                `checkout_allowed` TINYINT(1) DEFAULT 1,
                `notes` TEXT DEFAULT NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE INDEX `uk_room_date` (`room_id`, `date`),
                INDEX `idx_date` (`date`),
                INDEX `idx_stop_sell` (`stop_sell`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تقویم قیمت و موجودی روزانه'
        ");

        // Campaigns
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_campaigns` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(200) NOT NULL,
                `campaign_type` ENUM('coupon','promo_code','flash_sale','early_booking','last_minute','seasonal_offer','promotion','campaign') NOT NULL,
                `code` VARCHAR(50) DEFAULT NULL UNIQUE COMMENT 'کد تخفیف',
                `discount_type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
                `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0,
                `min_amount` DECIMAL(15,0) DEFAULT NULL COMMENT 'حداقل مبلغ سفارش (IRR)',
                `max_discount` DECIMAL(15,0) DEFAULT NULL COMMENT 'حداکثر مبلغ تخفیف (IRR)',
                `valid_from` DATETIME DEFAULT NULL,
                `valid_to` DATETIME DEFAULT NULL,
                `usage_limit` INT DEFAULT NULL COMMENT 'محدودیت استفاده',
                `used_count` INT DEFAULT 0,
                `target_hotels_json` TEXT DEFAULT NULL COMMENT 'JSON آرایه hotel_ids',
                `target_rooms_json` TEXT DEFAULT NULL COMMENT 'JSON آرایه room_ids',
                `priority` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_code` (`code`),
                INDEX `idx_type` (`campaign_type`),
                INDEX `idx_active_dates` (`is_active`, `valid_from`, `valid_to`),
                INDEX `idx_deleted` (`deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='کمپین‌ها و پروموشن‌ها'
        ");
    }

    public static function down($db): void
    {
        $db->query("DROP TABLE IF EXISTS `site_campaigns`");
        $db->query("DROP TABLE IF EXISTS `site_room_daily_rates`");
        $db->query("DROP TABLE IF EXISTS `site_pricing_rules`");
    }
}