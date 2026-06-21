-- Fix sms_history foreign keys to allow NULL and not fail on invalid references
-- Drop existing foreign keys first (ignore errors if already gone)
ALTER TABLE sms_history DROP FOREIGN KEY sms_history_ibfk_1;
ALTER TABLE sms_history DROP FOREIGN KEY sms_history_ibfk_2;
ALTER TABLE sms_history DROP FOREIGN KEY sms_history_ibfk_3;

-- Re-add with ON DELETE SET NULL and allow NULL
-- (These will be skipped if columns don't exist or are already modified)