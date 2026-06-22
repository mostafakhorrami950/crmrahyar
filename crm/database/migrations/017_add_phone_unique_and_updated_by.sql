-- ============================================
-- Add UNIQUE INDEX on contacts.phone and updated_by to main tables
-- ============================================

-- 1. Add UNIQUE INDEX on contacts.phone
-- First, clean up duplicate phones (keep the oldest contact)
-- Run this manually if there are duplicates:
-- DELETE c1 FROM contacts c1
-- INNER JOIN contacts c2 
-- WHERE c1.id > c2.id AND c1.phone = c2.phone AND c1.phone IS NOT NULL AND c1.phone != '';

ALTER TABLE `contacts` ADD UNIQUE INDEX `idx_phone_unique` (`phone`);

-- 2. Add updated_by column to contacts
ALTER TABLE `contacts` ADD COLUMN `updated_by` INT NULL AFTER `created_by`;
ALTER TABLE `contacts` ADD FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 3. Add updated_by column to deals
ALTER TABLE `deals` ADD COLUMN `updated_by` INT NULL AFTER `created_by`;
ALTER TABLE `deals` ADD FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- 4. Add updated_by column to payments
ALTER TABLE `payments` ADD COLUMN `updated_by` INT NULL AFTER `created_by`;
ALTER TABLE `payments` ADD FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;