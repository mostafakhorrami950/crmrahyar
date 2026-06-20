CREATE TABLE IF NOT EXISTS deal_loss_reasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(10) DEFAULT '😞',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE deals ADD COLUMN loss_reason_id INT NULL AFTER source_id;
ALTER TABLE deals ADD COLUMN loss_reason_note TEXT NULL AFTER loss_reason_id;