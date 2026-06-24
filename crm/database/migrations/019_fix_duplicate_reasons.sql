-- Fix duplicate win reasons: keep only one of each name, then add UNIQUE key
-- The migration engine will catch and ignore "Duplicate key name" errors

-- Remove duplicates from deal_win_reasons (keep lowest id)
DELETE t1 FROM deal_win_reasons t1
INNER JOIN deal_win_reasons t2 
WHERE t1.name = t2.name AND t1.id > t2.id;

ALTER TABLE deal_win_reasons ADD UNIQUE KEY unique_win_reason_name (name);

-- Same for deal_loss_reasons if it has duplicates
DELETE t1 FROM deal_loss_reasons t1
INNER JOIN deal_loss_reasons t2 
WHERE t1.name = t2.name AND t1.id > t2.id;

ALTER TABLE deal_loss_reasons ADD UNIQUE KEY unique_loss_reason_name (name);
