<?php
namespace Shared\Migrations;

use Shared\Core\Database;

class Migration005
{
    public static function up(Database $db): void
    {
        // Agencies (FK to CRM users)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_agencies` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL UNIQUE COMMENT 'FK → users.id',
                `agency_name` VARCHAR(200) NOT NULL,
                `discount_percent` DECIMAL(5,2) DEFAULT 0,
                `commission_type` ENUM('percent','fixed') DEFAULT 'percent',
                `commission_value` DECIMAL(10,2) DEFAULT 0,
                `credit_limit` DECIMAL(15,0) DEFAULT 0 COMMENT 'سقف اعتبار (IRR)',
                `wallet_balance` DECIMAL(15,0) DEFAULT 0 COMMENT 'موجودی کیف پول (IRR)',
                `debt_amount` DECIMAL(15,0) DEFAULT 0 COMMENT 'بدهی (IRR)',
                `is_active` TINYINT(1) DEFAULT 1,
                `deleted_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_user` (`user_id`),
                INDEX `idx_active` (`is_active`, `deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='آژانس‌های مسافرتی'
        ");

        // Ledger (دفتر کل مالی)
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_ledger` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `entry_type` ENUM('booking_revenue','payment_received','commission','refund','credit','debit','wallet_topup','wallet_spend') NOT NULL,
                `entity_type` VARCHAR(30) DEFAULT NULL COMMENT 'نوع موجودیت مرتبط',
                `entity_id` INT DEFAULT NULL COMMENT 'شناسه موجودیت مرتبط',
                `agency_id` INT DEFAULT NULL COMMENT 'FK → site_agencies.id',
                `user_id` INT DEFAULT NULL COMMENT 'FK → users.id',
                `debit` DECIMAL(15,0) DEFAULT 0 COMMENT 'بدهکار (IRR)',
                `credit` DECIMAL(15,0) DEFAULT 0 COMMENT 'بستانکار (IRR)',
                `balance` DECIMAL(15,0) DEFAULT 0 COMMENT 'مانده (IRR)',
                `description` TEXT DEFAULT NULL,
                `reference_code` VARCHAR(50) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_type` (`entry_type`),
                INDEX `idx_entity` (`entity_type`, `entity_id`),
                INDEX `idx_agency` (`agency_id`),
                INDEX `idx_user` (`user_id`),
                INDEX `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='دفتر کل مالی'
        ");

        // Agency transactions
        $db->query("
            CREATE TABLE IF NOT EXISTS `site_agency_transactions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `agency_id` INT NOT NULL COMMENT 'FK → site_agencies.id',
                `type` ENUM('credit','debit','payment','commission','refund','wallet_topup') NOT NULL,
                `amount` DECIMAL(15,0) NOT NULL COMMENT 'مبلغ (IRR)',
                `balance_after` DECIMAL(15,0) DEFAULT 0 COMMENT 'مانده بعد از تراکنش',
                `description` TEXT DEFAULT NULL,
                `booking_id` INT DEFAULT NULL COMMENT 'FK → site_bookings.id',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_agency` (`agency_id`),
                INDEX `idx_type` (`type`),
                INDEX `idx_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تراکنش‌های آژانس'
        ");
    }

    public static function down(Database $db): void
    {
        $db->query("DROP TABLE IF EXISTS `site_agency_transactions`");
        $db->query("DROP TABLE IF EXISTS `site_ledger`");
        $db->query("DROP TABLE IF EXISTS `site_agencies`");
    }
}