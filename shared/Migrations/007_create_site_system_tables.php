<?php
namespace Shared\Migrations;

use Shared\Core\Database;

class Migration007
{
    public static function up(Database $db): void
    {
        // Workflows
        $db->query("CREATE TABLE IF NOT EXISTS `site_workflows` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(100) NOT NULL,
            `entity_type` VARCHAR(30) NOT NULL DEFAULT 'booking',
            `is_default` TINYINT(1) DEFAULT 0,
            `steps_json` TEXT DEFAULT NULL, `transitions_json` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->query("CREATE TABLE IF NOT EXISTS `site_workflow_transitions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `workflow_id` INT NOT NULL,
            `from_status` VARCHAR(30) NOT NULL, `to_status` VARCHAR(30) NOT NULL,
            `allowed_role` VARCHAR(50) DEFAULT NULL, `requires_payment` TINYINT(1) DEFAULT 0,
            `auto_trigger` VARCHAR(50) DEFAULT NULL, `notification_template` VARCHAR(50) DEFAULT NULL,
            `sort_order` INT DEFAULT 0, `is_active` TINYINT(1) DEFAULT 1,
            INDEX `idx_workflow` (`workflow_id`), INDEX `idx_from` (`from_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Blog posts
        $db->query("CREATE TABLE IF NOT EXISTS `site_blog_posts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `slug` VARCHAR(255) NOT NULL UNIQUE,
            `title` VARCHAR(500) NOT NULL, `excerpt` TEXT DEFAULT NULL,
            `content` LONGTEXT DEFAULT NULL, `cover_media_id` INT DEFAULT NULL,
            `category` VARCHAR(100) DEFAULT NULL, `tags_json` TEXT DEFAULT NULL,
            `author_id` INT DEFAULT NULL, `meta_title` VARCHAR(255) DEFAULT NULL,
            `meta_description` TEXT DEFAULT NULL, `og_image_id` INT DEFAULT NULL,
            `robots_meta` VARCHAR(100) DEFAULT 'index, follow',
            `published_at` DATETIME DEFAULT NULL, `is_published` TINYINT(1) DEFAULT 0,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_slug` (`slug`), INDEX `idx_published` (`is_published`, `published_at`),
            INDEX `idx_category` (`category`), INDEX `idx_deleted` (`deleted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Static pages
        $db->query("CREATE TABLE IF NOT EXISTS `site_pages` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `slug` VARCHAR(255) NOT NULL UNIQUE,
            `title` VARCHAR(500) NOT NULL, `content` LONGTEXT DEFAULT NULL,
            `page_type` VARCHAR(30) DEFAULT 'page',
            `meta_title` VARCHAR(255) DEFAULT NULL, `meta_description` TEXT DEFAULT NULL,
            `robots_meta` VARCHAR(100) DEFAULT 'index, follow',
            `is_active` TINYINT(1) DEFAULT 1, `deleted_at` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_slug` (`slug`), INDEX `idx_type` (`page_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // FAQs
        $db->query("CREATE TABLE IF NOT EXISTS `site_faqs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `question` VARCHAR(500) NOT NULL,
            `answer` TEXT NOT NULL, `category` VARCHAR(50) DEFAULT NULL,
            `entity_type` VARCHAR(30) DEFAULT NULL, `entity_id` INT DEFAULT NULL,
            `sort_order` INT DEFAULT 0, `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_entity` (`entity_type`, `entity_id`), INDEX `idx_category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Reviews
        $db->query("CREATE TABLE IF NOT EXISTS `site_reviews` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `crm_hotel_id` INT NOT NULL,
            `user_id` INT DEFAULT NULL, `booking_id` INT DEFAULT NULL,
            `rating` TINYINT NOT NULL, `title` VARCHAR(255) DEFAULT NULL,
            `comment` TEXT DEFAULT NULL, `pros` TEXT DEFAULT NULL, `cons` TEXT DEFAULT NULL,
            `is_approved` TINYINT(1) DEFAULT 0, `is_verified` TINYINT(1) DEFAULT 0,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_hotel` (`crm_hotel_id`), INDEX `idx_user` (`user_id`),
            INDEX `idx_approved` (`is_approved`), INDEX `idx_rating` (`rating`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Notifications
        $db->query("CREATE TABLE IF NOT EXISTS `site_notifications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL,
            `channel` ENUM('sms','email','push','whatsapp','telegram') NOT NULL,
            `template` VARCHAR(50) DEFAULT NULL, `subject` VARCHAR(255) DEFAULT NULL,
            `body` TEXT DEFAULT NULL, `status` ENUM('pending','sent','failed','delivered') DEFAULT 'pending',
            `sent_at` DATETIME DEFAULT NULL, `error` TEXT DEFAULT NULL,
            `related_entity` VARCHAR(30) DEFAULT NULL, `related_id` INT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_user` (`user_id`), INDEX `idx_status` (`status`), INDEX `idx_channel` (`channel`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Settings
        $db->query("CREATE TABLE IF NOT EXISTS `site_settings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `setting_key` VARCHAR(100) NOT NULL UNIQUE,
            `setting_value` TEXT DEFAULT NULL,
            `setting_type` ENUM('string','boolean','number','json') DEFAULT 'string',
            `setting_group` VARCHAR(50) DEFAULT NULL,
            `validation_rules_json` TEXT DEFAULT NULL,
            `is_cached` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_group` (`setting_group`), INDEX `idx_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Event logs
        $db->query("CREATE TABLE IF NOT EXISTS `site_event_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `event_type` VARCHAR(50) NOT NULL,
            `entity_type` VARCHAR(30) DEFAULT NULL, `entity_id` INT DEFAULT NULL,
            `user_id` INT DEFAULT NULL, `session_id` VARCHAR(64) DEFAULT NULL,
            `ip` VARCHAR(45) DEFAULT NULL, `user_agent` TEXT DEFAULT NULL,
            `data_json` TEXT DEFAULT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_event` (`event_type`, `created_at`), INDEX `idx_entity` (`entity_type`, `entity_id`),
            INDEX `idx_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Analytics (aggregated daily)
        $db->query("CREATE TABLE IF NOT EXISTS `site_analytics` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `date` DATE NOT NULL,
            `metric_type` VARCHAR(50) NOT NULL,
            `entity_type` VARCHAR(30) DEFAULT NULL, `entity_id` INT DEFAULT NULL,
            `metric_value` DECIMAL(15,2) DEFAULT 0, `metric_count` INT DEFAULT 0,
            `dimension_json` TEXT DEFAULT NULL,
            UNIQUE INDEX `uk_metric` (`date`, `metric_type`, `entity_type`, `entity_id`),
            INDEX `idx_date` (`date`), INDEX `idx_type` (`metric_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // SEO Redirects
        $db->query("CREATE TABLE IF NOT EXISTS `site_seo_redirects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `from_url` VARCHAR(500) NOT NULL,
            `to_url` VARCHAR(500) NOT NULL,
            `redirect_type` ENUM('301','302','410') DEFAULT '301',
            `is_active` TINYINT(1) DEFAULT 1, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_from` (`from_url`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Queue jobs
        $db->query("CREATE TABLE IF NOT EXISTS `site_queue_jobs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `queue_name` VARCHAR(50) DEFAULT 'default',
            `job_class` VARCHAR(200) NOT NULL, `payload` TEXT DEFAULT NULL,
            `status` ENUM('pending','processing','completed','failed') DEFAULT 'pending',
            `attempts` INT DEFAULT 0, `max_attempts` INT DEFAULT 3,
            `priority` VARCHAR(20) DEFAULT 'default',
            `available_at` DATETIME NOT NULL, `started_at` DATETIME DEFAULT NULL,
            `completed_at` DATETIME DEFAULT NULL, `error` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_status_priority` (`status`, `priority`, `available_at`),
            INDEX `idx_queue` (`queue_name`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Feature flags
        $db->query("CREATE TABLE IF NOT EXISTS `site_feature_flags` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `feature_key` VARCHAR(50) NOT NULL UNIQUE,
            `is_enabled` TINYINT(1) DEFAULT 0, `config_json` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_key` (`feature_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Outbox (Event-driven processing)
        $db->query("CREATE TABLE IF NOT EXISTS `site_outbox` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `event_type` VARCHAR(100) NOT NULL,
            `payload` TEXT NOT NULL, `status` ENUM('pending','processed','failed') DEFAULT 'pending',
            `attempts` INT DEFAULT 0, `max_attempts` INT DEFAULT 5,
            `available_at` DATETIME NOT NULL, `processed_at` DATETIME DEFAULT NULL,
            `error` TEXT DEFAULT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_status` (`status`, `available_at`), INDEX `idx_event` (`event_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Idempotency keys
        $db->query("CREATE TABLE IF NOT EXISTS `site_idempotency_keys` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `key_hash` VARCHAR(64) NOT NULL UNIQUE,
            `request_path` VARCHAR(500) DEFAULT NULL, `response_body` TEXT DEFAULT NULL,
            `status_code` INT DEFAULT NULL, `expires_at` DATETIME NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_hash` (`key_hash`), INDEX `idx_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Audit logs
        $db->query("CREATE TABLE IF NOT EXISTS `site_audit_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY, `table_name` VARCHAR(50) NOT NULL,
            `record_id` INT NOT NULL, `action` ENUM('INSERT','UPDATE','DELETE') NOT NULL,
            `old_values` JSON DEFAULT NULL, `new_values` JSON DEFAULT NULL,
            `changed_by` INT DEFAULT NULL, `ip` VARCHAR(45) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_table_record` (`table_name`, `record_id`),
            INDEX `idx_action` (`action`), INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public static function down(Database $db): void
    {
        $tables = [
            'site_audit_logs', 'site_idempotency_keys', 'site_outbox',
            'site_feature_flags', 'site_queue_jobs', 'site_seo_redirects',
            'site_analytics', 'site_event_logs', 'site_settings',
            'site_notifications', 'site_reviews', 'site_faqs',
            'site_pages', 'site_blog_posts', 'site_workflow_transitions', 'site_workflows'
        ];
        foreach ($tables as $t) {
            $db->query("DROP TABLE IF EXISTS `{$t}`");
        }
    }
}