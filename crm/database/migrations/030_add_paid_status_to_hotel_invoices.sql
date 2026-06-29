-- ============================================
-- Add 'paid' status to hotel_invoices invoice_status
-- ============================================

ALTER TABLE `hotel_invoices` MODIFY COLUMN `invoice_status` ENUM('draft', 'final', 'paid', 'cancelled') DEFAULT 'draft' COMMENT 'وضعیت فاکتور';