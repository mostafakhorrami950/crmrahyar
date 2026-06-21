-- Add dedicated permissions for activities and calendar
INSERT IGNORE INTO permissions (name, slug, group_name, description) VALUES
('مشاهده فعالیت‌ها', 'activities.view', 'فعالیت‌ها', 'مشاهده و مدیریت فعالیت‌ها'),
('مشاهده تقویم', 'calendar.view', 'تقویم', 'مشاهده تقویم');

-- Grant these permissions to admin role (role_id=1)
INSERT IGNORE INTO role_permissions (role_id, permission) VALUES
(1, 'activities.view'),
(1, 'calendar.view');

-- Grant to operator role (role_id=2) if exists
INSERT IGNORE INTO role_permissions (role_id, permission) VALUES
(2, 'activities.view'),
(2, 'calendar.view');