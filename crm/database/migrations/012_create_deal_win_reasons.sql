-- Create deal_win_reasons table
CREATE TABLE IF NOT EXISTS deal_win_reasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(10) DEFAULT '✅',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add win_reason_id and win_reason_note columns to deals
-- (Migration engine will ignore "duplicate column" errors if already applied)
ALTER TABLE deals ADD COLUMN win_reason_id INT NULL AFTER loss_reason_id;
ALTER TABLE deals ADD COLUMN win_reason_note TEXT NULL AFTER win_reason_id;

-- Insert default win reasons for travel agency (INSERT IGNORE = skip if exists)
INSERT IGNORE INTO deal_win_reasons (name, icon, sort_order) VALUES
('قیمت مناسب', '💰', 1),
('اعتماد به برند', '⭐', 2),
('تنوع پکیج‌ها', '🌍', 3),
('پشتیبانی عالی', '🤝', 4),
('معرفی دوستان', '👥', 5),
('تخفیف ویژه', '🎁', 6),
('سرعت پاسخگویی', '⚡', 7),
('رزرو آنلاین آسان', '💻', 8),
('تنوع مقاصد', '✈️', 9),
('سایر', '📌', 10);
