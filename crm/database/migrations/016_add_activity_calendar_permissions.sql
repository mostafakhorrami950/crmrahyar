-- Add dedicated permissions for activities and calendar
INSERT IGNORE INTO permissions (name, label, category) VALUES
('activities.view', 'مشاهده فعالیت‌ها', 'فعالیت‌ها'),
('calendar.view', 'مشاهده تقویم', 'تقویم');

-- Grant these permissions to admin role (role_id=1)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE name IN ('activities.view', 'calendar.view');

-- Grant to operator role (role_id=2) if exists
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN ('activities.view', 'calendar.view');