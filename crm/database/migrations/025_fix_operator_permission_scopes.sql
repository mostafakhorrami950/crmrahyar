-- Fix operator permission scopes: operators should only see their own data
-- Update deals.view scope to 'own' for operator roles (not super_admin)
UPDATE role_permissions rp
JOIN roles r ON rp.role_id = r.id
SET rp.scope = 'own'
WHERE rp.permission = 'deals.view' 
AND rp.scope = 'all'
AND r.slug != 'super_admin'
AND r.slug = 'operator';

-- Update contacts.view scope to 'own' for operator roles
UPDATE role_permissions rp
JOIN roles r ON rp.role_id = r.id
SET rp.scope = 'own'
WHERE rp.permission = 'contacts.view' 
AND rp.scope = 'all'
AND r.slug != 'super_admin'
AND r.slug = 'operator';

-- Update activities.view scope to 'own' for operator roles
UPDATE role_permissions rp
JOIN roles r ON rp.role_id = r.id
SET rp.scope = 'own'
WHERE rp.permission = 'activities.view' 
AND rp.scope = 'all'
AND r.slug != 'super_admin'
AND r.slug = 'operator';

-- Update reports.view scope to 'own' for operator roles
UPDATE role_permissions rp
JOIN roles r ON rp.role_id = r.id
SET rp.scope = 'own'
WHERE rp.permission = 'reports.view' 
AND rp.scope = 'all'
AND r.slug != 'super_admin'
AND r.slug = 'operator';

-- Update calendar.view scope to 'own' for operator roles
UPDATE role_permissions rp
JOIN roles r ON rp.role_id = r.id
SET rp.scope = 'own'
WHERE rp.permission = 'calendar.view' 
AND rp.scope = 'all'
AND r.slug != 'super_admin'
AND r.slug = 'operator';