<?php
namespace Shared\Migrations;

use Shared\Core\Database;

class Migration003
{
    public static function up(Database $db): void
    {
        // Reservations (Hold)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_reservations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `booking_id` INT DEFAULT NULL COMMENT 'FK → site_bookings.id',
                `reservation_token` VARCHAR(64) NOT NULL UNIQUE,
                `room_id` INT NOT NULL COMMENT 'FK → site_rooms.id',
                `crm_hotel_id` INT NOT NULL,
                `checkin_date` DATE NOT NULL,
                `checkout_date` DATE NOT NULL,
                `quantity` INT DEFAULT 1,
                `status` ENUM('active','expired','converted','cancelled') DEFAULT 'active',
                `expires_at` DATETIME NOT NULL,
                `pricing_snapshot_json` TEXT DEFAULT NULL COMMENT 'Snapshot قیمت لحظه hold',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_token` (`reservation_token`),
                INDEX `idx_status_expires` (`status`, `expires_at`),
                INDEX `idx_room_dates` (`room_id`, `checkin_date`, `checkout_date`),
                INDEX `idx_booking` (`booking_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='نگهداری موقت موجودی (Reservation Hold)'
        ");

        // Bookings
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_bookings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `booking_code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'کد رزرو',
                `user_id` INT DEFAULT NULL COMMENT 'FK → users.id',
                `room_id` INT NOT NULL COMMENT 'FK → site_rooms.id',
                `crm_hotel_id` INT NOT NULL COMMENT 'FK → hotel_rate_hotels.id',
                `checkin_date` DATE NOT NULL,
                `checkout_date` DATE NOT NULL,
                `nights` INT NOT NULL DEFAULT 1,
                `guests_adults` INT DEFAULT 1,
                `guests_children` INT DEFAULT 0,
                `rooms_count` INT DEFAULT 1,
                `base_price` DECIMAL(15,0) NOT NULL DEFAULT 0 COMMENT 'قیمت پایه (IRR)',
                `markup_amount` DECIMAL(15,0) DEFAULT 0,
                `campaign_id` INT DEFAULT NULL COMMENT 'FK → site_campaigns.id',
                `campaign_discount` DECIMAL(15,0) DEFAULT 0,
                `total_price` DECIMAL(15,0) NOT NULL DEFAULT 0,
                `final_price` DECIMAL(15,0) NOT NULL DEFAULT 0,
                `currency` VARCHAR(3) DEFAULT 'IRR',
                `booking_status` VARCHAR(30) DEFAULT 'draft',
                `payment_status` ENUM('unpaid','paid','refunded','partial') DEFAULT 'unpaid',
                `payment_method` VARCHAR(30) DEFAULT NULL,
                `payment_track_id` VARCHAR(100) DEFAULT NULL,
                `payment_ref` VARCHAR(100) DEFAULT NULL,
                `crm_deal_id` INT DEFAULT NULL COMMENT 'FK → deals.id',
                `crm_invoice_id` INT DEFAULT NULL COMMENT 'FK → hotel_invoices.id',
                `agency_id` INT DEFAULT NULL COMMENT 'FK → site_agencies.id',
                `idempotency_key` VARCHAR(64) DEFAULT NULL UNIQUE,
                `notes` TEXT DEFAULT NULL,
                `cancelled_at` DATETIME DEFAULT NULL,
                `cancel_reason` TEXT DEFAULT NULL,
                `version` INT DEFAULT 1 COMMENT 'Optimistic Lock',
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_code` (`booking_code`),
                INDEX `idx_user` (`user_id`),
                INDEX `idx_hotel` (`crm_hotel_id`),
                INDEX `idx_status` (`booking_status`),
                INDEX `idx_payment` (`payment_status`),
                INDEX `idx_checkin` (`checkin_date`),
                INDEX `idx_agency` (`agency_id`),
                INDEX `idx_active` (`deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='رزروها'
        ");

        // Booking guests
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_booking_guests` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `booking_id` INT NOT NULL COMMENT 'FK → site_bookings.id',
                `full_name` VARCHAR(200) NOT NULL,
                `national_code` VARCHAR(20) DEFAULT NULL,
                `phone` VARCHAR(20) DEFAULT NULL,
                `email` VARCHAR(200) DEFAULT NULL,
                `is_primary` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_booking` (`booking_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='اطلاعات مهمان‌ها'
        ");

        // Booking status log (Workflow history)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_booking_status_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `booking_id` INT NOT NULL COMMENT 'FK → site_bookings.id',
                `from_status` VARCHAR(30) DEFAULT NULL,
                `to_status` VARCHAR(30) NOT NULL,
                `changed_by` INT DEFAULT NULL COMMENT 'FK → users.id',
                `reason` TEXT DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_booking` (`booking_id`),
                INDEX `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تاریخچه وضعیت رزروها'
        ");

        // Booking snapshot (complete state at time of booking)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_booking_snapshots` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `booking_id` INT NOT NULL UNIQUE COMMENT 'FK → site_bookings.id',
                `hotel_name` VARCHAR(255) DEFAULT NULL,
                `hotel_star_rating` INT DEFAULT NULL,
                `hotel_city` VARCHAR(100) DEFAULT NULL,
                `hotel_address` TEXT DEFAULT NULL,
                `hotel_phone` VARCHAR(50) DEFAULT NULL,
                `hotel_facilities_json` TEXT DEFAULT NULL,
                `room_type` VARCHAR(100) DEFAULT NULL,
                `room_capacity` INT DEFAULT NULL,
                `room_bed_type` VARCHAR(50) DEFAULT NULL,
                `room_size_sqm` INT DEFAULT NULL,
                `meal_plan` VARCHAR(50) DEFAULT NULL,
                `rate_date_from` DATE DEFAULT NULL,
                `rate_date_to` DATE DEFAULT NULL,
                `rate_season_label` VARCHAR(100) DEFAULT NULL,
                `checkin_date` DATE DEFAULT NULL,
                `checkout_date` DATE DEFAULT NULL,
                `nights` INT DEFAULT NULL,
                `guests_adults` INT DEFAULT NULL,
                `guests_children` INT DEFAULT NULL,
                `rooms_count` INT DEFAULT NULL,
                `guests_json` TEXT DEFAULT NULL,
                `base_price` DECIMAL(15,0) DEFAULT 0,
                `markup_amount` DECIMAL(15,0) DEFAULT 0,
                `campaign_discount` DECIMAL(15,0) DEFAULT 0,
                `total_price` DECIMAL(15,0) DEFAULT 0,
                `final_price` DECIMAL(15,0) DEFAULT 0,
                `currency` VARCHAR(3) DEFAULT 'IRR',
                `cancellation_policy` TEXT DEFAULT NULL,
                `booking_rules` TEXT DEFAULT NULL,
                `pricing_rules_applied_json` TEXT DEFAULT NULL,
                `campaign_applied_json` TEXT DEFAULT NULL,
                `snapshot_version` INT DEFAULT 1,
                `pricing_engine_version` VARCHAR(20) DEFAULT '1.0.0',
                `workflow_version` VARCHAR(20) DEFAULT '1.0.0',
                `campaign_version` VARCHAR(20) DEFAULT '1.0.0',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_booking` (`booking_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Snapshot کامل رزرو در لحظه ثبت'
        ");
    }

    public static function down(Database $db): void
    {
        $db->query("DROP TABLE IF EXISTS `site_booking_snapshots`");
        $db->query("DROP TABLE IF EXISTS `site_booking_status_log`");
        $db->query("DROP TABLE IF EXISTS `site_booking_guests`");
        $db->query("DROP TABLE IF EXISTS `site_bookings`");
        $db->query("DROP TABLE IF EXISTS `site_reservations`");
    }
}