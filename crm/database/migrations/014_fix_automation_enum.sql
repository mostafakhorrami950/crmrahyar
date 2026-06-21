-- Fix automation_rules table: change ENUM to VARCHAR for trigger_type and action_type
-- This allows all new trigger/action types without schema changes
ALTER TABLE automation_rules MODIFY COLUMN trigger_type VARCHAR(50) NOT NULL;
ALTER TABLE automation_rules MODIFY COLUMN action_type VARCHAR(50) NOT NULL;