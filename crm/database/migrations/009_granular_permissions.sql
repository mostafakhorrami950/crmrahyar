-- Migration 009: Granular Permission System
-- Add scope column to differentiate between 'own' and 'all' data access

-- Add scope column to role_permissions
ALTER TABLE `role_permissions` ADD COLUMN `scope` ENUM('own','all') NOT NULL DEFAULT 'all' AFTER `permission`;

-- Create permissions seed table (replaces old permissions table if needed)
-- First clear old permissions that lack proper structure
TRUNCATE TABLE `permissions`;

-- Insert comprehensive permissions grouped by module
INSERT INTO `permissions` (`name`, `slug`, `group_name`, `description`) VALUES
-- Dashboard
('مشاهده داشبورد', 'dashboard.view', 'داشبورد', 'مشاهده صفحه داشبورد و آمار کلی'),

-- Deals (معاملات)
('مشاهده معاملات', 'deals.view', 'معاملات', 'مشاهده لیست معاملات'),
('ایجاد معامله', 'deals.create', 'معاملات', 'ایجاد معامله جدید'),
('ویرایش معامله', 'deals.edit', 'معاملات', 'ویرایش اطلاعات معامله'),
('حذف معامله', 'deals.delete', 'معاملات', 'حذف معامله'),
('تغییر مرحله معامله', 'deals.move', 'معاملات', 'انتقال معامله بین مراحل کانبان'),
('ثبت فعالیت معامله', 'deals.activity', 'معاملات', 'ثبت فعالیت یادداشت/تماس/جلسه برای معامله'),

-- Contacts (مخاطبین)
('مشاهده مخاطبین', 'contacts.view', 'مخاطبین', 'مشاهده لیست مخاطبین'),
('ایجاد مخاطب', 'contacts.create', 'مخاطبین', 'ایجاد مخاطب جدید'),
('ویرایش مخاطب', 'contacts.edit', 'مخاطبین', 'ویرایش اطلاعات مخاطب'),
('حذف مخاطب', 'contacts.delete', 'مخاطبین', 'حذف مخاطب'),
('مشاهده جزئیات مخاطب', 'contacts.detail', 'مخاطبین', 'مشاهده جزئیات و معاملات مخاطب'),

-- Pipelines (پایپ لاین)
('مشاهده پایپ لاین', 'pipelines.view', 'پایپ لاین', 'مشاهده لیست و کانبان پایپ لاین'),
('مدیریت پایپ لاین', 'pipelines.manage', 'پایپ لاین', 'ایجاد، ویرایش و حذف پایپ لاین'),

-- Payments (پرداخت)
('مشاهده پرداخت‌ها', 'payments.view', 'پرداخت', 'مشاهده تاریخچه پرداخت‌ها'),
('ایجاد لینک پرداخت', 'payments.create', 'پرداخت', 'ایجاد لینک پرداخت برای معامله'),

-- SMS (پیامک)
('ارسال پیامک', 'sms.send', 'پیامک', 'ارسال پیامک تکی و انبوه'),
('مشاهده تاریخچه پیامک', 'sms.view', 'پیامک', 'مشاهده تاریخچه ارسال پیامک'),

-- Reports (گزارشات)
('مشاهده گزارشات', 'reports.view', 'گزارشات', 'مشاهده گزارشات فروش و عملکرد'),

-- Users (کاربران)
('مشاهده کاربران', 'users.view', 'کاربران', 'مشاهده لیست کاربران'),
('مدیریت کاربران', 'users.manage', 'کاربران', 'ایجاد، ویرایش و حذف کاربران'),

-- Roles (نقش‌ها)
('مشاهده نقش‌ها', 'roles.view', 'نقش‌ها', 'مشاهده لیست نقش‌ها و دسترسی‌ها'),
('مدیریت نقش‌ها', 'roles.manage', 'نقش‌ها', 'ایجاد، ویرایش و حذف نقش‌ها'),

-- Settings (تنظیمات)
('مشاهده تنظیمات', 'settings.view', 'تنظیمات', 'مشاهده تنظیمات سیستم'),
('مدیریت تنظیمات', 'settings.manage', 'تنظیمات', 'تغییر تنظیمات سیستم'),

-- Database (پایگاه داده')
('مشاهده بکاپ‌ها', 'database.view', 'پایگاه داده', 'مشاهده و دانلود بکاپ‌ها'),
('مدیریت پایگاه داده', 'database.manage', 'پایگاه داده', 'ایجاد بکاپ و تعمیر دیتابیس'),

-- Activity Log (لاگ فعالیت‌ها)
('مشاهده لاگ فعالیت‌ها', 'activitylog.view', 'لاگ فعالیت‌ها', 'مشاهده تاریخچه فعالیت‌های سیستم');

-- Clean old role_permissions to start fresh with new system
TRUNCATE TABLE `role_permissions`;

-- Seed super_admin with ALL permissions with 'all' scope
INSERT INTO `role_permissions` (`role_id`, `permission`, `scope`)
SELECT 1, slug, 'all' FROM permissions;