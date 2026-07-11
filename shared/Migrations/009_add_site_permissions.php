<?php
namespace Shared\Migrations;

class Migration009
{
    public static function up($db): void
    {
        // Add site permissions
        $permissions = [
            ['slug' => 'site.access', 'name' => 'دسترسی به سایت', 'description' => 'دسترسی به پنل مدیریت سایت'],
            ['slug' => 'site.manage_hotels', 'name' => 'مدیریت هتل‌های سایت', 'description' => 'ویرایش پروفایل هتل‌ها در سایت'],
            ['slug' => 'site.manage_rooms', 'name' => 'مدیریت اتاق‌های سایت', 'description' => 'ویرایش اتاق‌ها و عکس‌ها'],
            ['slug' => 'site.manage_content', 'name' => 'مدیریت محتوای سایت', 'description' => 'مدیریت بلاگ، صفحات، FAQ'],
        ];

        foreach ($permissions as $perm) {
            $exists = $db->fetch("SELECT id FROM permissions WHERE slug = :s", [':s' => $perm['slug']]);
            if (!$exists) {
                $db->insert('permissions', $perm);
            }
        }

        // Grant all site permissions to super_admin role (id=1)
        $sitePerms = $db->fetchAll("SELECT id FROM permissions WHERE slug LIKE 'site.%'");
        foreach ($sitePerms as $perm) {
            $exists = $db->fetch(
                "SELECT id FROM role_permissions WHERE role_id = 1 AND permission_id = :pid",
                [':pid' => $perm->id]
            );
            if (!$exists) {
                $db->insert('role_permissions', ['role_id' => 1, 'permission_id' => $perm->id]);
            }
        }
    }

    public static function down($db): void
    {
        $db->query("DELETE FROM role_permissions WHERE permission_id IN (SELECT id FROM permissions WHERE slug LIKE 'site.%')");
        $db->query("DELETE FROM permissions WHERE slug LIKE 'site.%'");
    }
}