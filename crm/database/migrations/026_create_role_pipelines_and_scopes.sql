-- Create role_pipelines table for pipeline access control
CREATE TABLE IF NOT EXISTS role_pipelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    pipeline_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_pipeline (role_id, pipeline_id),
    KEY idx_role_id (role_id),
    KEY idx_pipeline_id (pipeline_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add activities.view and calendar.view with scope if not exists
-- These are already in role_permissions but ensure they support scope