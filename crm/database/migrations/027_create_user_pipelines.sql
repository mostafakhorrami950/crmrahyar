-- Create user_pipelines table for per-user pipeline access control
CREATE TABLE IF NOT EXISTS user_pipelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pipeline_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_pipeline (user_id, pipeline_id),
    KEY idx_user_id (user_id),
    KEY idx_pipeline_id (pipeline_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;