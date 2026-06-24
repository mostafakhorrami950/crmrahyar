CREATE TABLE IF NOT EXISTS `ai_analyses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `model` VARCHAR(100),
    `prompt_summary` TEXT,
    `result` LONGTEXT,
    `deals_count` INT DEFAULT 0,
    `total_amount` DECIMAL(15,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;